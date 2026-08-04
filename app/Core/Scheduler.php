<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Central scheduler.
 *
 * One server cron runs `php cron/run.php` every minute. That calls tick(), which works out
 * which registered jobs are due, claims each one with a database lock, runs it, and records
 * the outcome. Nothing else on the server needs a crontab entry.
 *
 * Adding a job later is one entry in CronJobs::handlers() plus one row in cron_jobs — no new
 * server cron, no changes here.
 *
 * Safety properties:
 *  - A job is claimed with a conditional UPDATE, so two ticks racing can never both take it.
 *  - A lock older than the job's timeout is treated as dead and reclaimed, so a crashed run
 *    cannot block a job forever.
 *  - next_run_at is advanced from the scheduled time, so a slow tick does not drift.
 */
class Scheduler
{
    /** A tick will not run longer than this, so it never overlaps the next minute badly. */
    private const TICK_BUDGET_SECONDS = 50;

    /** @return array<int,array<string,mixed>> every registered job with its state */
    public static function all(): array
    {
        return DB::all('SELECT * FROM `' . tbl('cron_jobs') . '` ORDER BY group_name, name');
    }

    public static function get(string $jobKey): ?array
    {
        return DB::get('SELECT * FROM `' . tbl('cron_jobs') . '` WHERE job_key = ?', [$jobKey]);
    }

    /**
     * The master tick. Runs every due job, one at a time, within a time budget.
     * @return array{ran:int,skipped:int,failed:int,jobs:array<int,string>}
     */
    public static function tick(string $triggeredBy = 'schedule'): array
    {
        $started = time();
        $ran = 0;
        $failed = 0;
        $skipped = 0;
        $names = [];

        Settings::set('cron_last_tick_at', now(), 'cron');
        Settings::set('cron_tick_count', (string)(Settings::getInt('cron_tick_count', 0) + 1), 'cron');

        foreach (self::due() as $job) {
            if (time() - $started > self::TICK_BUDGET_SECONDS) {
                $skipped++;   // out of time; it stays due and the next minute picks it up
                continue;
            }
            $result = self::runJob((string)$job['job_key'], $triggeredBy, null);
            if ($result['status'] === 'success') {
                $ran++;
                $names[] = (string)$job['job_key'];
            } elseif ($result['status'] === 'failed') {
                $failed++;
                $names[] = $job['job_key'] . ' (failed)';
            } else {
                $skipped++;
            }
        }
        return ['ran' => $ran, 'skipped' => $skipped, 'failed' => $failed, 'jobs' => $names];
    }

    /** Jobs that are enabled, due, and not already held by a live lock. */
    public static function due(): array
    {
        return DB::all(
            'SELECT * FROM `' . tbl('cron_jobs') . '`
             WHERE is_enabled = 1
               AND (next_run_at IS NULL OR next_run_at <= NOW())
               AND (locked_at IS NULL OR locked_at < DATE_SUB(NOW(), INTERVAL timeout_seconds SECOND))
             ORDER BY next_run_at IS NULL DESC, next_run_at'
        );
    }

    /**
     * Claim, run and record one job.
     *
     * @param string $triggeredBy schedule | manual | retry
     * @return array{status:string,message:string,items:int}
     */
    public static function runJob(string $jobKey, string $triggeredBy = 'manual', ?int $userId = null): array
    {
        $job = self::get($jobKey);
        if (!$job) {
            return ['status' => 'skipped', 'message' => 'Unknown job.', 'items' => 0];
        }
        $handler = CronJobs::handler($jobKey);
        if (!$handler) {
            return ['status' => 'skipped', 'message' => 'No handler registered for this job.', 'items' => 0];
        }

        // --- claim the lock atomically; whoever changes a row wins -----------------
        $token = bin2hex(random_bytes(16));
        $claimed = DB::run(
            'UPDATE `' . tbl('cron_jobs') . '`
             SET locked_at = NOW(), lock_token = ?, last_status = ?, last_run_at = NOW(), updated_at = NOW()
             WHERE job_key = ?
               AND (locked_at IS NULL OR locked_at < DATE_SUB(NOW(), INTERVAL timeout_seconds SECOND))',
            [$token, 'running', $jobKey]
        )->rowCount();
        if ($claimed === 0) {
            return ['status' => 'skipped', 'message' => 'Already running elsewhere.', 'items' => 0];
        }

        $runId = DB::insert('cron_runs', [
            'job_key' => $jobKey,
            'started_at' => now(),
            'status' => 'running',
            'triggered_by' => in_array($triggeredBy, ['schedule', 'manual', 'retry'], true) ? $triggeredBy : 'manual',
            'user_id' => $userId,
        ]);

        $begin = microtime(true);
        $status = 'success';
        $message = '';
        $items = 0;
        try {
            $out = $handler();
            if (is_array($out)) {
                $items = (int)($out['items'] ?? 0);
                $message = (string)($out['message'] ?? '');
            } elseif (is_string($out)) {
                $message = $out;
            }
            if ($message === '') {
                $message = 'Done.';
            }
        } catch (\Throwable $e) {
            $status = 'failed';
            $message = $e->getMessage();
            Logger::file('cron', $jobKey . ' FAILED: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        }
        $ms = (int)round((microtime(true) - $begin) * 1000);

        DB::update('cron_runs', [
            'finished_at' => now(),
            'duration_ms' => $ms,
            'status' => $status,
            'items' => $items,
            'message' => mb_substr($message, 0, 2000),
        ], ['id' => $runId]);

        // --- release + schedule the next run -------------------------------------
        $fresh = self::get($jobKey) ?? $job;
        DB::run(
            'UPDATE `' . tbl('cron_jobs') . '`
             SET locked_at = NULL, lock_token = NULL,
                 last_finished_at = NOW(), last_status = ?, last_message = ?, last_duration_ms = ?, last_items = ?,
                 run_count = run_count + 1,
                 fail_count = fail_count + ?,
                 consecutive_failures = ?,
                 next_run_at = ?, updated_at = NOW()
             WHERE job_key = ? AND (lock_token = ? OR lock_token IS NULL)',
            [
                $status,
                mb_substr($message, 0, 500),
                $ms,
                $items,
                $status === 'failed' ? 1 : 0,
                $status === 'failed' ? (int)$fresh['consecutive_failures'] + 1 : 0,
                self::nextRunAt($job),
                $jobKey,
                $token,
            ]
        );

        return ['status' => $status, 'message' => $message, 'items' => $items];
    }

    /** When should this job run next, counted from now. */
    public static function nextRunAt(array $job): string
    {
        if (($job['schedule_type'] ?? 'interval') === 'daily') {
            $time = (string)($job['run_at_time'] ?: '00:00:00');
            $today = date('Y-m-d ') . $time;
            // If today's slot has passed, aim at tomorrow's.
            return strtotime($today) > time() ? $today : date('Y-m-d H:i:s', strtotime($today . ' +1 day'));
        }
        $minutes = max(1, (int)($job['interval_minutes'] ?? 5));
        return date('Y-m-d H:i:s', time() + $minutes * 60);
    }

    /** Human summary of the whole scheduler, for the admin panel. */
    public static function health(): array
    {
        $lastTick = (string)Settings::get('cron_last_tick_at', '');
        $ago = $lastTick !== '' ? time() - strtotime($lastTick) : null;
        $jobs = self::all();

        // Either it failed repeatedly, or its most recent run failed — both need an admin's eye.
        $failing = array_values(array_filter(
            $jobs,
            fn($j) => (int)$j['consecutive_failures'] > 0 || $j['last_status'] === 'failed'
        ));
        $stuck = array_values(array_filter($jobs, fn($j) =>
            $j['locked_at'] !== null && strtotime((string)$j['locked_at']) < time() - (int)$j['timeout_seconds']));
        $overdue = array_values(array_filter($jobs, fn($j) =>
            (int)$j['is_enabled'] === 1 && $j['next_run_at'] !== null
            && strtotime((string)$j['next_run_at']) < time() - 600));

        if ($ago === null) {
            $state = 'never';
            $note = 'The master cron has never run. Add the server cron command below.';
        } elseif ($ago > 900) {
            $state = 'down';
            $note = 'No tick for ' . self::ago($ago) . '. The server cron looks stopped.';
        } elseif ($ago > 180) {
            $state = 'late';
            $note = 'Last tick ' . self::ago($ago) . ' ago — expected every minute.';
        } elseif ($failing) {
            $state = 'failing';
            $note = count($failing) . ' job(s) failing.';
        } else {
            $state = 'ok';
            $note = 'Running normally. Last tick ' . self::ago($ago) . ' ago.';
        }

        return [
            'state' => $state,
            'note' => $note,
            'last_tick_at' => $lastTick,
            'tick_count' => Settings::getInt('cron_tick_count', 0),
            'jobs_total' => count($jobs),
            'jobs_enabled' => count(array_filter($jobs, fn($j) => (int)$j['is_enabled'] === 1)),
            'failing' => $failing,
            'stuck' => $stuck,
            'overdue' => $overdue,
        ];
    }

    public static function ago(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        }
        if ($seconds < 3600) {
            return (int)floor($seconds / 60) . 'm';
        }
        if ($seconds < 86400) {
            return (int)floor($seconds / 3600) . 'h';
        }
        return (int)floor($seconds / 86400) . 'd';
    }

    /** Force-release a lock left behind by a crashed run. */
    public static function releaseLock(string $jobKey): void
    {
        DB::run(
            'UPDATE `' . tbl('cron_jobs') . "` SET locked_at = NULL, lock_token = NULL,
             last_status = 'failed', last_message = 'Lock cleared by an admin', updated_at = NOW()
             WHERE job_key = ?",
            [$jobKey]
        );
        DB::run(
            'UPDATE `' . tbl('cron_runs') . "` SET status = 'failed', finished_at = NOW(),
             message = CONCAT(COALESCE(message,''), ' [lock cleared by admin]')
             WHERE job_key = ? AND status = 'running'",
            [$jobKey]
        );
    }

    public static function setEnabled(string $jobKey, bool $on): void
    {
        DB::update('cron_jobs', [
            'is_enabled' => $on ? 1 : 0,
            'next_run_at' => $on ? now() : null,
            'updated_at' => now(),
        ], ['job_key' => $jobKey]);
    }

    /** @return array<int,array<string,mixed>> recent runs, newest first */
    public static function runs(?string $jobKey = null, int $limit = 100): array
    {
        if ($jobKey !== null && $jobKey !== '') {
            return DB::all(
                'SELECT * FROM `' . tbl('cron_runs') . '` WHERE job_key = ? ORDER BY id DESC LIMIT ' . (int)$limit,
                [$jobKey]
            );
        }
        return DB::all('SELECT * FROM `' . tbl('cron_runs') . '` ORDER BY id DESC LIMIT ' . (int)$limit);
    }
}
