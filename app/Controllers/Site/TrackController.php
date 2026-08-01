<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Settings;
use App\Core\View;
use App\Core\Whatsapp;
use App\Models\Status;

class TrackController
{
    public function form(): void
    {
        View::render('track/form', [], 'layouts/public');
    }

    /** GET /my-orders — show the mobile-entry form, or the list once verified in session. */
    public function myOrdersForm(): void
    {
        $phone = $_SESSION['tracked_phone'] ?? null;
        if ($phone) {
            $this->renderMyOrders((string)$phone);
            return;
        }
        View::render('track/my_orders_form', [], 'layouts/public');
    }

    /** POST /my-orders — customer enters their mobile number. */
    public function myOrders(): void
    {
        Csrf::check();
        $phone = local_phone((string)($_POST['phone'] ?? ''));
        if (!$phone) {
            flash('danger', 'Please enter a valid 10-digit mobile number.');
            redirect(base_url('my-orders'));
        }
        $customer = DB::get('SELECT id FROM `' . tbl('customers') . '` WHERE phone = ? AND deleted_at IS NULL', [$phone]);
        if (!$customer) {
            flash('danger', 'No orders found for this mobile number.');
            redirect(base_url('my-orders'));
        }
        // Secure with a WhatsApp OTP when enabled; otherwise show directly.
        if (Settings::getBool('public_otp_required', true) && Whatsapp::enabled()) {
            $this->sendOtp($phone);
            $_SESSION['track_pending_phone'] = $phone;
            View::render('track/my_orders_otp', ['phone' => $phone], 'layouts/public');
            return;
        }
        $_SESSION['tracked_phone'] = $phone;
        redirect(base_url('my-orders'));
    }

    /** POST /my-orders/verify — confirm the OTP, then unlock the list. */
    public function verifyMyOrders(): void
    {
        Csrf::check();
        $phone = $_SESSION['track_pending_phone'] ?? null;
        if (!$phone) {
            redirect(base_url('my-orders'));
        }
        $otp = preg_replace('/\D/', '', (string)($_POST['otp'] ?? ''));
        $row = DB::get(
            'SELECT * FROM `' . tbl('customer_otps') . '` WHERE phone = ? AND verified_at IS NULL ORDER BY id DESC LIMIT 1',
            [$phone]
        );
        if (!$row || strtotime((string)$row['expires_at']) < time() || (int)$row['attempts'] >= 5
            || !password_verify($otp, (string)$row['otp_hash'])) {
            if ($row) {
                DB::run('UPDATE `' . tbl('customer_otps') . '` SET attempts = attempts + 1 WHERE id = ?', [$row['id']]);
            }
            flash('danger', 'Wrong or expired code. Please try again.');
            View::render('track/my_orders_otp', ['phone' => $phone], 'layouts/public');
            return;
        }
        DB::update('customer_otps', ['verified_at' => now()], ['id' => $row['id']]);
        $_SESSION['tracked_phone'] = $phone;
        unset($_SESSION['track_pending_phone']);
        redirect(base_url('my-orders'));
    }

    private function sendOtp(string $phone): void
    {
        $otp = (string)random_int(100000, 999999);
        DB::insert('customer_otps', [
            'phone' => $phone,
            'otp_hash' => password_hash($otp, PASSWORD_DEFAULT),
            'expires_at' => date('Y-m-d H:i:s', time() + Settings::getInt('otp_expiry_minutes', 10) * 60),
            'ip' => request_ip(),
            'created_at' => now(),
        ]);
        $business = (string)Settings::get('business_name', 'Krishna Print');
        Whatsapp::queueRaw(
            $phone,
            "Your $business tracking code is: *$otp*\nValid for " . Settings::getInt('otp_expiry_minutes', 10) . ' minutes.',
            null, null, 'otp', 'otp', null, 1
        );
    }

    /** Render the list of all orders for a verified mobile number. */
    private function renderMyOrders(string $phone): void
    {
        $customer = DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE phone = ? AND deleted_at IS NULL', [$phone]);
        if (!$customer) {
            unset($_SESSION['tracked_phone']);
            redirect(base_url('my-orders'));
        }
        $orders = DB::all(
            'SELECT o.*, b.name AS branch_name FROM `' . tbl('orders') . '` o
             JOIN `' . tbl('branches') . '` b ON b.id = o.branch_id
             WHERE o.customer_id = ? AND o.deleted_at IS NULL ORDER BY o.created_at DESC',
            [(int)$customer['id']]
        );
        foreach ($orders as &$o) {
            $o['items'] = DB::all(
                'SELECT item_name_snapshot, qty, unit, status, requires_design FROM `' . tbl('order_items') . '`
                 WHERE order_id = ? ORDER BY sort_order',
                [(int)$o['id']]
            );
            $o['is_overdue'] = Status::isOverdue($o['due_date'], (string)$o['status']);
        }
        unset($o);
        View::render('track/my_orders', compact('customer', 'orders'), 'layouts/public');
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

    /** GET /my-orders/exit — forget the verified number. */
    public function exitMyOrders(): void
    {
        unset($_SESSION['tracked_phone'], $_SESSION['track_pending_phone']);
        flash('success', 'You have been signed out of order tracking.');
        redirect(base_url('my-orders'));
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
