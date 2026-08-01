<?php
declare(strict_types=1);

// Every 15 minutes: proof reminders, overdue alerts, balance reminders, daily summary.
if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

use App\Core\DB;
use App\Core\Logger;
use App\Core\Settings;
use App\Core\WaEvents;

if (!is_installed()) {
    exit(0);
}

$lock = fopen(BASE_PATH . '/storage/reminders.lock', 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    exit(0);
}

try {
    // 1. Proofs unviewed for N hours → remind customer (once per proof)
    $hours = Settings::getInt('proof_reminder_hours', 24);
    $proofs = DB::all(
        'SELECT dp.id FROM `' . tbl('design_proofs') . "` dp
         WHERE dp.status = 'pending' AND dp.viewed_at IS NULL
           AND dp.sent_at < DATE_SUB(NOW(), INTERVAL $hours HOUR)
           AND NOT EXISTS (SELECT 1 FROM `" . tbl('whatsapp_queue') . "` wq
                           WHERE wq.event_key = 'proof.reminder' AND wq.ref_type = 'proof' AND wq.ref_id = dp.id)"
    );
    foreach ($proofs as $proof) {
        WaEvents::proofReminder((int)$proof['id']);
        DB::run(
            'UPDATE `' . tbl('whatsapp_queue') . "` SET ref_type = 'proof', ref_id = ?
             WHERE event_key = 'proof.reminder' AND ref_type = 'order' ORDER BY id DESC LIMIT 1",
            [(int)$proof['id']]
        );
    }

    // 2. Newly overdue items → alert branch manager (once per item)
    $overdue = DB::all(
        'SELECT oi.id FROM `' . tbl('order_items') . '` oi
         JOIN `' . tbl('orders') . "` o ON o.id = oi.order_id
         WHERE oi.due_date < NOW() AND o.deleted_at IS NULL AND o.is_cancelled = 0
           AND oi.status NOT IN ('delivered','completed','cancelled')
           AND NOT EXISTS (SELECT 1 FROM `" . tbl('whatsapp_queue') . "` wq
                           WHERE wq.event_key = 'order.overdue' AND wq.ref_type = 'order_item' AND wq.ref_id = oi.id)"
    );
    foreach ($overdue as $item) {
        WaEvents::overdueAlert((int)$item['id']);
        DB::run(
            'UPDATE `' . tbl('whatsapp_queue') . "` SET ref_type = 'order_item', ref_id = ?
             WHERE event_key = 'order.overdue' AND ref_type = 'order' ORDER BY id DESC LIMIT 1",
            [(int)$item['id']]
        );
    }

    // 3. Balance reminders: delivered/completed orders with balance > 0, N days old, max one per week
    $days = Settings::getInt('balance_reminder_days', 7);
    $orders = DB::all(
        'SELECT o.id FROM `' . tbl('orders') . "` o
         WHERE o.balance_amount > 0 AND o.deleted_at IS NULL AND o.is_cancelled = 0
           AND o.status IN ('delivered','completed','ready_for_delivery')
           AND o.order_date < DATE_SUB(NOW(), INTERVAL $days DAY)
           AND NOT EXISTS (SELECT 1 FROM `" . tbl('whatsapp_queue') . "` wq
                           WHERE wq.event_key = 'payment.balance_reminder' AND wq.ref_type = 'order' AND wq.ref_id = o.id
                             AND wq.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY))
         LIMIT 25"
    );
    foreach ($orders as $order) {
        WaEvents::balanceReminder((int)$order['id']);
    }

    // 4. Daily summary at 21:00 IST (fires in the 21:00–21:14 window)
    if ((int)date('H') === 21 && (int)date('i') < 15) {
        $already = DB::val(
            'SELECT id FROM `' . tbl('whatsapp_queue') . "` WHERE event_key = 'system.daily_summary'
             AND DATE(created_at) = CURDATE() LIMIT 1"
        );
        if (!$already) {
            WaEvents::dailySummary();
        }
    }
} catch (Throwable $e) {
    Logger::file('reminders', 'Error: ' . $e->getMessage());
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
