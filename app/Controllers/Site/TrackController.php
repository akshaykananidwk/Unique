<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\View;

class TrackController
{
    public function form(): void
    {
        View::render('track/form', [], 'layouts/public');
    }

    /** Job No + registered mobile — never job number alone. */
    public function lookup(): void
    {
        Csrf::check();
        $jobNo = strtoupper(trim((string)($_POST['job_no'] ?? '')));
        $phone = local_phone((string)($_POST['phone'] ?? ''));
        if ($jobNo === '' || !$phone) {
            flash('danger', 'Enter both the job number and the mobile number used on the order.');
            redirect(base_url('track'));
        }
        $order = DB::get(
            'SELECT o.* FROM `' . tbl('orders') . '` o
             JOIN `' . tbl('customers') . '` c ON c.id = o.customer_id
             WHERE o.job_no = ? AND c.phone = ? AND o.deleted_at IS NULL',
            [$jobNo, $phone]
        );
        if (!$order) {
            flash('danger', 'No order found for that job number and mobile combination.');
            redirect(base_url('track'));
        }
        redirect(base_url('track/' . $order['tracking_token']));
    }

    public function byToken(string $token): void
    {
        if (!preg_match('/^[a-f0-9]{32,64}$/', $token)) {
            abort(404, 'Invalid tracking link.');
        }
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE tracking_token = ? AND deleted_at IS NULL', [$token]);
        if (!$order) {
            abort(404, 'Order not found.');
        }
        $customer = DB::get('SELECT name, phone FROM `' . tbl('customers') . '` WHERE id = ?', [(int)$order['customer_id']]);
        $branch = DB::get('SELECT * FROM `' . tbl('branches') . '` WHERE id = ?', [(int)$order['branch_id']]);
        $items = DB::all(
            'SELECT oi.*, (SELECT dp.proof_token FROM `' . tbl('design_proofs') . '` dp
                           WHERE dp.order_item_id = oi.id AND dp.status IN (\'pending\',\'approved\')
                           ORDER BY dp.version DESC LIMIT 1) AS latest_proof_token
             FROM `' . tbl('order_items') . '` oi WHERE oi.order_id = ? ORDER BY oi.sort_order',
            [(int)$order['id']]
        );
        View::render('track/timeline', compact('order', 'customer', 'branch', 'items'), 'layouts/public');
    }

    /** Printable receipt reachable from the WhatsApp link (tracking token + payment id). */
    public function receipt(string $token, string $paymentId): void
    {
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE tracking_token = ? AND deleted_at IS NULL', [$token]);
        if (!$order) {
            abort(404, 'Not found.');
        }
        $payment = DB::get(
            'SELECT * FROM `' . tbl('payments') . '` WHERE id = ? AND order_id = ? AND deleted_at IS NULL',
            [(int)$paymentId, (int)$order['id']]
        );
        if (!$payment) {
            abort(404, 'Receipt not found.');
        }
        $customer = DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE id = ?', [(int)$order['customer_id']]);
        $branch = DB::get('SELECT * FROM `' . tbl('branches') . '` WHERE id = ?', [(int)$order['branch_id']]);
        View::render('payments/receipt', [
            'payment' => $payment, 'order' => $order, 'customer' => $customer, 'branch' => $branch, 'format' => 'a5',
        ], 'layouts/print');
    }
}
