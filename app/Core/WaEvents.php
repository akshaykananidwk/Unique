<?php
declare(strict_types=1);

namespace App\Core;

/** Builds placeholder data for each business event and queues the right templates. */
class WaEvents
{
    /** Common placeholder payload for an order (+optional item). */
    public static function orderData(array $order, ?array $item = null): array
    {
        $customer = DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE id = ?', [(int)$order['customer_id']]);
        $branch = DB::get('SELECT * FROM `' . tbl('branches') . '` WHERE id = ?', [(int)$order['branch_id']]);
        $staff = $order['taken_by_user_id']
            ? DB::get('SELECT name FROM `' . tbl('users') . '` WHERE id = ?', [(int)$order['taken_by_user_id']])
            : null;

        $items = DB::all('SELECT * FROM `' . tbl('order_items') . '` WHERE order_id = ? ORDER BY sort_order, id', [(int)$order['id']]);
        $itemsList = '';
        foreach ($items as $i => $oi) {
            $itemsList .= ($i + 1) . '. ' . $oi['item_name_snapshot'] . ' × ' . rtrim(rtrim((string)$oi['qty'], '0'), '.') . "\n";
        }

        $designer = null;
        if ($item && $item['assigned_designer_id']) {
            $designer = DB::get('SELECT name, phone FROM `' . tbl('users') . '` WHERE id = ?', [(int)$item['assigned_designer_id']]);
        }

        return [
            'customer_name' => $customer['name'] ?? '',
            'customer_phone' => $customer['phone'] ?? '',
            'job_no' => $order['job_no'],
            'order_date' => fmt_date($order['order_date'], true),
            'due_date' => fmt_date($item['due_date'] ?? $order['due_date']),
            'priority' => ucfirst((string)$order['priority']),
            'items_list' => trim($itemsList),
            'item_name' => $item['item_name_snapshot'] ?? '',
            'qty' => $item ? rtrim(rtrim((string)$item['qty'], '0'), '.') : '',
            'total' => number_format((float)$order['total'], 2),
            'advance' => number_format((float)$order['paid_amount'], 2),
            'paid' => number_format((float)$order['paid_amount'], 2),
            'balance' => number_format((float)$order['balance_amount'], 2),
            'tracking_link' => base_url('track/' . $order['tracking_token']),
            'proof_link' => '',
            'receipt_link' => '',
            'designer_link' => admin_url('my-jobs'),
            'branch_name' => $branch['name'] ?? '',
            'branch_phone' => $branch['phone'] ?? '',
            'branch_address' => trim(($branch['address'] ?? '') . ' ' . ($branch['city'] ?? '')),
            'staff_name' => $staff['name'] ?? '',
            'designer_name' => $designer['name'] ?? '',
            'feedback_text' => '',
            'revision_no' => '',
            'business_name' => (string)Settings::get('business_name', ''),
            'business_phone' => (string)Settings::get('business_phone', ''),
            'today' => fmt_date(now()),
        ];
    }

    private static function ctx(array $order, array $data, ?string $designerPhone = null): array
    {
        return [
            'customer_phone' => $data['customer_phone'],
            'designer_phone' => $designerPhone,
            'branch_id' => (int)$order['branch_id'],
            'ref_type' => 'order',
            'ref_id' => (int)$order['id'],
        ];
    }

    public static function orderCreated(int $orderId): void
    {
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [$orderId]);
        if (!$order) {
            return;
        }
        $data = self::orderData($order);
        Whatsapp::queueTemplate('order_created_customer', $data, self::ctx($order, $data));
        Whatsapp::queueTemplate('order_created_manager', $data, self::ctx($order, $data));

        // One message per assigned design item
        $items = DB::all(
            'SELECT * FROM `' . tbl('order_items') . '` WHERE order_id = ? AND assigned_designer_id IS NOT NULL',
            [$orderId]
        );
        foreach ($items as $item) {
            self::designerAssigned((int)$item['id']);
        }
    }

    public static function designerAssigned(int $orderItemId): void
    {
        $item = DB::get('SELECT * FROM `' . tbl('order_items') . '` WHERE id = ?', [$orderItemId]);
        if (!$item || !$item['assigned_designer_id']) {
            return;
        }
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [(int)$item['order_id']]);
        $designer = DB::get('SELECT phone, name FROM `' . tbl('users') . '` WHERE id = ?', [(int)$item['assigned_designer_id']]);
        if (!$order || !$designer) {
            return;
        }
        $data = self::orderData($order, $item);
        Whatsapp::queueTemplate('order_created_designer', $data, self::ctx($order, $data, (string)$designer['phone']));
    }

    public static function proofSent(int $proofId): void
    {
        [$proof, $item, $order] = self::proofChain($proofId);
        if (!$proof) {
            return;
        }
        $data = self::orderData($order, $item);
        $data['proof_link'] = base_url('proof/' . $proof['proof_token']);
        Whatsapp::queueTemplate('proof_ready_customer', $data, self::ctx($order, $data));
    }

    public static function proofReminder(int $proofId): void
    {
        [$proof, $item, $order] = self::proofChain($proofId);
        if (!$proof) {
            return;
        }
        $data = self::orderData($order, $item);
        $data['proof_link'] = base_url('proof/' . $proof['proof_token']);
        Whatsapp::queueTemplate('proof_reminder_customer', $data, self::ctx($order, $data));
    }

    public static function changeRequested(int $proofId, string $feedback, int $revisionNo): void
    {
        [$proof, $item, $order] = self::proofChain($proofId);
        if (!$proof || !$item['assigned_designer_id']) {
            return;
        }
        $designer = DB::get('SELECT phone FROM `' . tbl('users') . '` WHERE id = ?', [(int)$item['assigned_designer_id']]);
        $data = self::orderData($order, $item);
        $data['feedback_text'] = $feedback !== '' ? $feedback : '(voice note attached — listen on your job board)';
        $data['revision_no'] = (string)$revisionNo;
        Whatsapp::queueTemplate('change_requested_designer', $data, self::ctx($order, $data, $designer['phone'] ?? null));
    }

    public static function proofApproved(int $proofId): void
    {
        [$proof, $item, $order] = self::proofChain($proofId);
        if (!$proof) {
            return;
        }
        $designer = $item['assigned_designer_id']
            ? DB::get('SELECT phone FROM `' . tbl('users') . '` WHERE id = ?', [(int)$item['assigned_designer_id']])
            : null;
        $data = self::orderData($order, $item);
        Whatsapp::queueTemplate('proof_approved_designer', $data, self::ctx($order, $data, $designer['phone'] ?? null));
        Whatsapp::queueTemplate('proof_approved_production', $data, self::ctx($order, $data));
        Whatsapp::queueTemplate('order_created_manager', $data, self::ctx($order, $data));
    }

    public static function statusChanged(int $orderId, string $newStatus): void
    {
        $map = [
            'printing' => 'printing_started_customer',
            'ready_for_delivery' => 'ready_for_delivery_customer',
            'delivered' => 'delivered_customer',
        ];
        if (!isset($map[$newStatus])) {
            return;
        }
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [$orderId]);
        if (!$order) {
            return;
        }
        $data = self::orderData($order);
        Whatsapp::queueTemplate($map[$newStatus], $data, self::ctx($order, $data));
    }

    public static function paymentReceived(int $paymentId): void
    {
        $payment = DB::get('SELECT * FROM `' . tbl('payments') . '` WHERE id = ?', [$paymentId]);
        if (!$payment) {
            return;
        }
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [(int)$payment['order_id']]);
        $data = self::orderData($order);
        $data['paid'] = number_format((float)$payment['amount'], 2);
        $data['advance'] = number_format((float)$order['paid_amount'], 2);
        $data['receipt_link'] = base_url('receipt/' . $order['tracking_token'] . '/' . $payment['id']);
        Whatsapp::queueTemplate('payment_received_customer', $data, self::ctx($order, $data));
    }

    public static function balanceReminder(int $orderId): void
    {
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [$orderId]);
        if (!$order) {
            return;
        }
        $data = self::orderData($order);
        Whatsapp::queueTemplate('balance_reminder_customer', $data, self::ctx($order, $data));
    }

    public static function orderCancelled(int $orderId): void
    {
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [$orderId]);
        if (!$order) {
            return;
        }
        $data = self::orderData($order);
        Whatsapp::queueTemplate('order_cancelled_customer', $data, self::ctx($order, $data));
    }

    public static function overdueAlert(int $orderItemId): void
    {
        $item = DB::get('SELECT * FROM `' . tbl('order_items') . '` WHERE id = ?', [$orderItemId]);
        if (!$item) {
            return;
        }
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [(int)$item['order_id']]);
        $data = self::orderData($order, $item);
        $data['priority'] = ucfirst(str_replace('_', ' ', (string)$item['status']));
        Whatsapp::queueTemplate('overdue_alert_manager', $data, self::ctx($order, $data));
    }

    public static function dailySummary(): void
    {
        $today = date('Y-m-d');
        $orders = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('orders') . "` WHERE DATE(order_date) = ? AND deleted_at IS NULL", [$today]);
        $value = (float)DB::val('SELECT COALESCE(SUM(total),0) FROM `' . tbl('orders') . "` WHERE DATE(order_date) = ? AND deleted_at IS NULL", [$today]);
        $collection = (float)DB::val('SELECT COALESCE(SUM(amount),0) FROM `' . tbl('payments') . "` WHERE DATE(paid_at) = ? AND deleted_at IS NULL AND type != 'refund'", [$today]);
        $delivered = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('orders') . "` WHERE DATE(delivered_at) = ? AND deleted_at IS NULL", [$today]);
        $overdue = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('orders') . "` WHERE due_date < NOW() AND status NOT IN ('delivered','completed','cancelled') AND deleted_at IS NULL");
        $pendingBalance = (float)DB::val('SELECT COALESCE(SUM(balance_amount),0) FROM `' . tbl('orders') . "` WHERE status NOT IN ('cancelled') AND deleted_at IS NULL");

        $summary = "Orders today: $orders (₹" . number_format($value, 2) . ")\n"
            . 'Collections today: ₹' . number_format($collection, 2) . "\n"
            . "Delivered today: $delivered\n"
            . "Overdue jobs: $overdue\n"
            . 'Total pending balance: ₹' . number_format($pendingBalance, 2);

        $data = [
            'business_name' => (string)Settings::get('business_name', ''),
            'today' => fmt_date(now()),
            'items_list' => $summary,
        ];
        Whatsapp::queueTemplate('daily_summary_admin', $data, []);
    }

    /** @return array{0:?array,1:?array,2:?array} proof, item, order */
    private static function proofChain(int $proofId): array
    {
        $proof = DB::get('SELECT * FROM `' . tbl('design_proofs') . '` WHERE id = ?', [$proofId]);
        if (!$proof) {
            return [null, null, null];
        }
        $item = DB::get('SELECT * FROM `' . tbl('order_items') . '` WHERE id = ?', [(int)$proof['order_item_id']]);
        $order = $item ? DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [(int)$item['order_id']]) : null;
        return [$proof, $item, $order];
    }
}
