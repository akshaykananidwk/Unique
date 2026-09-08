<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Status;

/**
 * Where every scheduled task lives.
 *
 * ---------------------------------------------------------------------------
 * ADDING A NEW SCHEDULED JOB
 *   1. add a handler below, keyed by a unique job_key
 *   2. insert one row into cron_jobs (job_key, name, schedule_type, interval/time)
 * That is all. No new server cron, no changes to Scheduler.
 * ---------------------------------------------------------------------------
 *
 * A handler returns ['items' => int, 'message' => string] — items is what shows in the
 * admin log so a run reads as "sent 12" rather than just "done". Throwing marks the run
 * failed and records the message; the scheduler handles locking, timing and retries.
 */
class CronJobs
{
    /** @return array<string,callable():array> */
    public static function handlers(): array
    {
        return [
            'whatsapp.queue'             => [self::class, 'whatsappQueue'],
            'whatsapp.retry_failed'      => [self::class, 'whatsappRetryFailed'],
            'proof.reminders'            => [self::class, 'proofReminders'],
            'order.overdue'              => [self::class, 'overdueAlerts'],
            'payment.balance_reminders'  => [self::class, 'balanceReminders'],
            'cash.handover_expiry'       => [self::class, 'cashHandoverExpiry'],
            'report.daily_summary'       => [self::class, 'dailySummary'],
            'system.backup'              => [self::class, 'backup'],
            'system.update_check'        => [self::class, 'updateCheck'],
            'system.cleanup'             => [self::class, 'cleanup'],
        ];
    }

    public static function handler(string $jobKey): ?callable
    {
        $all = self::handlers();
        return isset($all[$jobKey]) ? $all[$jobKey] : null;
    }

    // ---------------------------------------------------------------- messaging

    /** Send whatever is due in the WhatsApp queue. */
    public static function whatsappQueue(): array
    {
        if (!Settings::getBool('wa_enabled', false)) {
            return ['items' => 0, 'message' => 'WhatsApp is switched off in settings.'];
        }
        $sent = Whatsapp::processQueue();
        $pending = (int)DB::val(
            'SELECT COUNT(*) FROM `' . tbl('whatsapp_queue') . "` WHERE status = 'pending'"
        );
        return ['items' => $sent, 'message' => "Sent $sent message(s); $pending still queued."];
    }

    /** Put failed messages that still have attempts left back in the queue. */
    public static function whatsappRetryFailed(): array
    {
        $rows = DB::run(
            'UPDATE `' . tbl('whatsapp_queue') . "` SET status = 'pending', scheduled_at = NOW()
             WHERE status = 'failed' AND attempts < max_attempts
               AND created_at > DATE_SUB(NOW(), INTERVAL 3 DAY)"
        )->rowCount();
        return ['items' => $rows, 'message' => $rows > 0 ? "Re-queued $rows failed message(s)." : 'Nothing to retry.'];
    }

    // ---------------------------------------------------------------- reminders

    /** Customers who have not opened their proof after N hours. */
    public static function proofReminders(): array
    {
        $hours = Settings::getInt('proof_reminder_hours', 24);
        $proofs = DB::all(
            'SELECT dp.id FROM `' . tbl('design_proofs') . "` dp
             WHERE dp.status = 'pending' AND dp.viewed_at IS NULL
               AND dp.sent_at < DATE_SUB(NOW(), INTERVAL ? HOUR)
               AND NOT EXISTS (SELECT 1 FROM `" . tbl('whatsapp_queue') . "` wq
                               WHERE wq.event_key = 'proof.reminder' AND wq.ref_type = 'proof' AND wq.ref_id = dp.id)
             LIMIT 50",
            [$hours]
        );
        foreach ($proofs as $proof) {
            WaEvents::proofReminder((int)$proof['id']);
            // Tag the row we just queued so the same proof is never chased twice.
            DB::run(
                'UPDATE `' . tbl('whatsapp_queue') . "` SET ref_type = 'proof', ref_id = ?
                 WHERE event_key = 'proof.reminder' AND ref_type = 'order' ORDER BY id DESC LIMIT 1",
                [(int)$proof['id']]
            );
        }
        $n = count($proofs);
        return ['items' => $n, 'message' => $n > 0 ? "Reminded $n customer(s) about a proof." : 'No proofs need chasing.'];
    }

    /** Jobs past their due date — tell the manager, once per job. */
    public static function overdueAlerts(): array
    {
        $items = DB::all(
            'SELECT oi.id FROM `' . tbl('order_items') . '` oi
             JOIN `' . tbl('orders') . "` o ON o.id = oi.order_id
             WHERE oi.due_date < NOW() AND o.deleted_at IS NULL AND o.is_cancelled = 0
               AND oi.status NOT IN ('delivered','completed','cancelled')
               AND NOT EXISTS (SELECT 1 FROM `" . tbl('whatsapp_queue') . "` wq
                               WHERE wq.event_key = 'order.overdue' AND wq.ref_type = 'order_item' AND wq.ref_id = oi.id)
             LIMIT 50"
        );
        foreach ($items as $item) {
            WaEvents::overdueAlert((int)$item['id']);
            DB::run(
                'UPDATE `' . tbl('whatsapp_queue') . "` SET ref_type = 'order_item', ref_id = ?
                 WHERE event_key = 'order.overdue' AND ref_type = 'order' ORDER BY id DESC LIMIT 1",
                [(int)$item['id']]
            );
        }
        $n = count($items);
        return ['items' => $n, 'message' => $n > 0 ? "Flagged $n overdue job(s)." : 'Nothing overdue.'];
    }

    /** Delivered orders still carrying a balance — chase at most once a week each. */
    public static function balanceReminders(): array
    {
        $days = Settings::getInt('balance_reminder_days', 7);
        $orders = DB::all(
            'SELECT o.id FROM `' . tbl('orders') . "` o
             WHERE o.balance_amount > 0 AND o.deleted_at IS NULL AND o.is_cancelled = 0
               AND o.status IN ('delivered','completed','ready_for_delivery')
               AND o.order_date < DATE_SUB(NOW(), INTERVAL ? DAY)
               AND NOT EXISTS (SELECT 1 FROM `" . tbl('whatsapp_queue') . "` wq
                               WHERE wq.event_key = 'payment.balance_reminder' AND wq.ref_type = 'order'
                                 AND wq.ref_id = o.id AND wq.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY))
             LIMIT 25",
            [$days]
        );
        foreach ($orders as $order) {
            WaEvents::balanceReminder((int)$order['id']);
        }
        $n = count($orders);
        return ['items' => $n, 'message' => $n > 0 ? "Sent $n payment reminder(s)." : 'No balances due a reminder.'];
    }

    // ---------------------------------------------------------------- reports

    /** One summary a day, and only one. */
    public static function dailySummary(): array
    {
        $already = DB::val(
            'SELECT id FROM `' . tbl('whatsapp_queue') . "` WHERE event_key = 'system.daily_summary'
             AND DATE(created_at) = CURDATE() LIMIT 1"
        );
        if ($already) {
            return ['items' => 0, 'message' => 'Already sent today.'];
        }
        WaEvents::dailySummary();
        return ['items' => 1, 'message' => 'Daily summary queued.'];
    }

    // ---------------------------------------------------------------- system

    public static function backup(): array
    {
        $result = Backup::create(Settings::getBool('upd_include_uploads', false), 'auto');
        if (!($result['ok'] ?? false)) {
            throw new \RuntimeException('Backup failed: ' . ($result['error'] ?? 'unknown error'));
        }
        $pruned = Backup::prune(Settings::getInt('upd_keep_backups', 5));
        return ['items' => 1, 'message' => 'Backup created: ' . basename((string)$result['path'])
            . (is_int($pruned) && $pruned > 0 ? " (pruned $pruned old)" : '')];
    }

    public static function updateCheck(): array
    {
        if (!Settings::getBool('upd_auto_check', true) || (string)Settings::get('upd_repo_owner', '') === '') {
            return ['items' => 0, 'message' => 'Auto-check is switched off, or no repository configured.'];
        }
        $result = (new Updater())->checkForUpdate();
        if (!($result['ok'] ?? false)) {
            throw new \RuntimeException('Check failed: ' . ($result['message'] ?? 'unknown error'));
        }
        $available = (bool)($result['available'] ?? false);
        return [
            'items' => $available ? 1 : 0,
            'message' => $available ? 'Update available: ' . ($result['latest_version'] ?? '?') : 'Already up to date.',
        ];
    }

    /**
     * Close cash handovers whose code was never used. Until this runs the money is shown
     * as promised out, which is not true any more — it never left the sender's pocket.
     */
    public static function cashHandoverExpiry(): array
    {
        $n = \App\Models\CashBook::expireStale();
        return [
            'items' => $n,
            'message' => $n ? "Expired $n unfinished handover(s)." : 'Nothing left hanging.',
        ];
    }

    /** Keep the tables from growing without limit, and repair anything an update cannot. */
    public static function cleanup(): array
    {
        $done = [];
        $total = 0;

        // The updater is not allowed to touch uploads/, so a broken guard there can only be
        // fixed in place. Checking daily costs nothing and it never rewrites a healthy file.
        $repairs = Hardening::run();
        foreach ($repairs as $repair) {
            Logger::file('cron', 'self-heal: ' . $repair);
        }

        $n = DB::run('DELETE FROM `' . tbl('cron_runs') . '` WHERE started_at < DATE_SUB(NOW(), INTERVAL 30 DAY)')->rowCount();
        if ($n) { $done[] = "$n scheduler log(s)"; $total += $n; }

        $n = DB::run('DELETE FROM `' . tbl('whatsapp_queue') . "` WHERE status = 'sent'
                      AND sent_at < DATE_SUB(NOW(), INTERVAL 90 DAY)")->rowCount();
        if ($n) { $done[] = "$n sent message(s)"; $total += $n; }

        $n = DB::run('DELETE FROM `' . tbl('customer_otps') . '` WHERE expires_at < DATE_SUB(NOW(), INTERVAL 7 DAY)')->rowCount();
        if ($n) { $done[] = "$n expired OTP(s)"; $total += $n; }

        $n = DB::run('DELETE FROM `' . tbl('login_attempts') . '` WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)')->rowCount();
        if ($n) { $done[] = "$n login attempt(s)"; $total += $n; }

        $parts = [];
        if ($done) {
            $parts[] = 'Removed ' . implode(', ', $done) . '.';
        }
        foreach ($repairs as $repair) {
            $parts[] = 'Repaired ' . $repair . '.';
            $total++;
        }
        return ['items' => $total, 'message' => $parts ? implode(' ', $parts) : 'Nothing to clean up.'];
    }
}
