<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\CronJobs;
use App\Core\DB;
use App\Core\Logger;
use App\Core\Scheduler;
use App\Core\Settings;

/**
 * Admin → Cron Jobs.
 *
 * One screen for every background task in the system: what it is, when it last ran, when it
 * runs next, whether it is healthy, and the buttons to run, pause or unstick it by hand.
 */
class CronController extends Controller
{
    private const PERM = 'settings.manage';

    public function index(): void
    {
        Acl::require(self::PERM);

        $health = Scheduler::health();
        $jobs = Scheduler::all();
        $handlers = CronJobs::handlers();

        // A job row with no handler is dead weight — say so rather than failing silently at 2am.
        foreach ($jobs as &$job) {
            $job['has_handler'] = isset($handlers[$job['job_key']]);
        }
        unset($job);

        $filter = trim((string)($_GET['job'] ?? ''));
        $runs = Scheduler::runs($filter !== '' ? $filter : null, 40);
        $stats = $this->stats();
        $secret = (string)Settings::get('cron_web_secret', '');
        $cronCommand = '* * * * * /usr/bin/php ' . BASE_PATH . '/cron/run.php >> /dev/null 2>&1';

        $this->render('settings/cron', compact('health', 'jobs', 'runs', 'filter', 'stats', 'secret', 'cronCommand'));
    }

    /** Full execution history, paged. */
    public function history(): void
    {
        Acl::require(self::PERM);
        $filter = trim((string)($_GET['job'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = 50;

        $where = ['1=1'];
        $params = [];
        if ($filter !== '') {
            $where[] = 'job_key = ?';
            $params[] = $filter;
        }
        if (in_array($status, ['success', 'failed', 'running', 'skipped'], true)) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        $sql = implode(' AND ', $where);

        $total = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('cron_runs') . '` WHERE ' . $sql, $params);
        $runs = DB::all(
            'SELECT * FROM `' . tbl('cron_runs') . '` WHERE ' . $sql
            . ' ORDER BY id DESC LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per),
            $params
        );
        $jobs = Scheduler::all();
        $pages = max(1, (int)ceil($total / $per));

        $this->render('settings/cron_history', compact('runs', 'jobs', 'filter', 'status', 'page', 'pages', 'total'));
    }

    /** Run one job now, or the whole tick if no job is named. */
    public function run(): void
    {
        Acl::require(self::PERM);
        $jobKey = trim((string)($_POST['job_key'] ?? ''));

        if ($jobKey === '') {
            $result = Scheduler::tick('manual');
            Logger::activity('cron', 'tick', null, null, sprintf(
                'Manual tick: %d ran, %d failed, %d skipped', $result['ran'], $result['failed'], $result['skipped']
            ));
            flash(
                $result['failed'] > 0 ? 'warning' : 'success',
                sprintf('Ran everything that was due — %d ran, %d failed, %d skipped.',
                    $result['ran'], $result['failed'], $result['skipped'])
            );
            $this->back();
        }

        $result = Scheduler::runJob($jobKey, 'manual', (int)$this->user['id']);
        Logger::activity('cron', $jobKey, null, null, 'Manual run: ' . $result['status'] . ' — ' . $result['message']);
        flash(
            $result['status'] === 'success' ? 'success' : ($result['status'] === 'failed' ? 'danger' : 'warning'),
            $this->jobName($jobKey) . ': ' . $result['message']
        );
        $this->back();
    }

    /** Re-run every job whose last run failed. */
    public function retryFailed(): void
    {
        Acl::require(self::PERM);

        $failing = DB::all(
            'SELECT job_key FROM `' . tbl('cron_jobs') . "` WHERE last_status = 'failed' AND is_enabled = 1"
        );
        if (!$failing) {
            flash('info', 'Nothing has failed — there is nothing to retry.');
            $this->back();
        }

        $ok = 0;
        $bad = 0;
        foreach ($failing as $row) {
            $result = Scheduler::runJob((string)$row['job_key'], 'retry', (int)$this->user['id']);
            $result['status'] === 'success' ? $ok++ : $bad++;
        }
        Logger::activity('cron', 'retry', null, null, "Retried failed jobs: $ok recovered, $bad still failing");
        flash($bad > 0 ? 'warning' : 'success', "Retried " . count($failing) . " job(s): $ok recovered, $bad still failing.");
        $this->back();
    }

    /** Pause or resume a single job. */
    public function toggle(): void
    {
        Acl::require(self::PERM);
        $jobKey = trim((string)($_POST['job_key'] ?? ''));
        if (!Scheduler::get($jobKey)) {
            flash('danger', 'Unknown job.');
            $this->back();
        }
        $on = !empty($_POST['enabled']);
        Scheduler::setEnabled($jobKey, $on);
        Logger::activity('cron', $jobKey, null, null, $on ? 'Job enabled' : 'Job disabled');
        flash('success', $this->jobName($jobKey) . ($on ? ' is on.' : ' is paused.'));
        $this->back();
    }

    /** Change how often a job runs. */
    public function schedule(): void
    {
        Acl::require(self::PERM);
        $jobKey = trim((string)($_POST['job_key'] ?? ''));
        $job = Scheduler::get($jobKey);
        if (!$job) {
            flash('danger', 'Unknown job.');
            $this->back();
        }

        $type = ($_POST['schedule_type'] ?? 'interval') === 'daily' ? 'daily' : 'interval';
        $minutes = max(1, min(10080, (int)($_POST['interval_minutes'] ?? 5)));
        $time = trim((string)($_POST['run_at_time'] ?? '00:00'));
        if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/', $time)) {
            $time = '00:00:00';
        } elseif (strlen($time) === 5) {
            $time .= ':00';
        }

        $data = [
            'schedule_type' => $type,
            'interval_minutes' => $minutes,
            'run_at_time' => $type === 'daily' ? $time : null,
            'updated_at' => now(),
        ];
        DB::update('cron_jobs', $data, ['job_key' => $jobKey]);

        // Re-aim next_run_at at the new schedule so the change takes effect straight away.
        $updated = Scheduler::get($jobKey);
        if ($updated) {
            DB::update('cron_jobs', ['next_run_at' => Scheduler::nextRunAt($updated)], ['job_key' => $jobKey]);
        }
        Logger::activity('cron', $jobKey, null, null, 'Schedule changed to ' . ($type === 'daily' ? "daily at $time" : "every $minutes min"));
        flash('success', $this->jobName($jobKey) . ' schedule saved.');
        $this->back();
    }

    /** Clear a lock left behind by a run that died mid-flight. */
    public function releaseLock(): void
    {
        Acl::require(self::PERM);
        $jobKey = trim((string)($_POST['job_key'] ?? ''));
        if (!Scheduler::get($jobKey)) {
            flash('danger', 'Unknown job.');
            $this->back();
        }
        Scheduler::releaseLock($jobKey);
        Logger::activity('cron', $jobKey, null, null, 'Stuck lock cleared');
        flash('success', $this->jobName($jobKey) . ': lock cleared, it can run again.');
        $this->back();
    }

    /** Issue (or clear) the secret for the URL-triggered fallback tick. */
    public function webKey(): void
    {
        Acl::require(self::PERM);
        if (!empty($_POST['clear'])) {
            Settings::set('cron_web_secret', '', 'cron');
            flash('success', 'URL trigger switched off.');
            $this->back();
        }
        Settings::set('cron_web_secret', bin2hex(random_bytes(20)), 'cron');
        Logger::activity('cron', 'web_secret', null, null, 'Cron URL key regenerated');
        flash('success', 'A new cron URL was generated. The old one no longer works.');
        $this->back();
    }

    /** Live status for the auto-refreshing header, so the page does not have to reload. */
    public function status(): void
    {
        Acl::require(self::PERM);
        header('Content-Type: application/json');
        $health = Scheduler::health();
        echo json_encode([
            'state' => $health['state'],
            'note' => $health['note'],
            'last_tick_at' => $health['last_tick_at'] ? fmt_date($health['last_tick_at'], true) : '',
            'tick_count' => $health['tick_count'],
            'running' => array_values(array_map(
                fn($j) => $j['job_key'],
                array_filter(Scheduler::all(), fn($j) => $j['last_status'] === 'running')
            )),
        ]);
        exit;
    }

    // ------------------------------------------------------------------ helpers

    /** Last 24h at a glance. */
    private function stats(): array
    {
        $row = DB::get(
            "SELECT COUNT(*) total,
                    SUM(status = 'success') ok,
                    SUM(status = 'failed') failed,
                    SUM(items) items,
                    ROUND(AVG(duration_ms)) avg_ms
             FROM `" . tbl('cron_runs') . '` WHERE started_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)'
        ) ?: [];
        return [
            'total' => (int)($row['total'] ?? 0),
            'ok' => (int)($row['ok'] ?? 0),
            'failed' => (int)($row['failed'] ?? 0),
            'items' => (int)($row['items'] ?? 0),
            'avg_ms' => (int)($row['avg_ms'] ?? 0),
        ];
    }

    private function jobName(string $jobKey): string
    {
        $job = Scheduler::get($jobKey);
        return $job ? (string)$job['name'] : $jobKey;
    }

    /** Return to the cron screen, keeping any job filter that was open. */
    private function back(): void
    {
        $job = trim((string)($_POST['back_job'] ?? ''));
        redirect(admin_url('system/cron' . ($job !== '' ? '?job=' . urlencode($job) : '')));
    }
}
