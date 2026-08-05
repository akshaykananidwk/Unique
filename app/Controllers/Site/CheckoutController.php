<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Settings;
use App\Core\View;
use App\Core\Whatsapp;
use App\Models\OrderService;

class CheckoutController
{
    public function form(): void
    {
        if (empty($_SESSION['cart'])) {
            flash('danger', 'Your cart is empty.');
            redirect(base_url('products'));
        }
        $branches = DB::all(
            'SELECT * FROM `' . tbl('branches') . "` WHERE is_active = 1 AND type = 'shop' ORDER BY sort_order"
        );
        if (!$branches) {
            $branches = DB::all('SELECT * FROM `' . tbl('branches') . '` WHERE is_active = 1 ORDER BY sort_order');
        }
        View::render('public/checkout', [
            'cart' => $_SESSION['cart'],
            'branches' => $branches,
            'otpRequired' => Settings::getBool('public_otp_required', true),
        ], 'layouts/public');
    }

    public function submit(): void
    {
        Csrf::check();
        if (empty($_SESSION['cart'])) {
            flash('danger', 'Your cart is empty.');
            redirect(base_url('products'));
        }
        // Honeypot + per-IP throttle
        if (!empty($_POST['company_website'])) {
            redirect(base_url()); // bot — silently drop
        }
        $recent = (int)DB::val(
            'SELECT COUNT(*) FROM `' . tbl('public_order_throttle') . '` WHERE ip = ? AND created_at > ?',
            [request_ip(), date('Y-m-d H:i:s', time() - 3600)]
        );
        if ($recent >= 5) {
            flash('danger', 'Too many orders from this connection. Please try again later or call us.');
            redirect(base_url('checkout'));
        }

        $phone = local_phone((string)($_POST['phone'] ?? ''));
        $name = trim((string)($_POST['name'] ?? ''));
        if (!$phone || $name === '') {
            flash('danger', 'Please enter your name and a valid 10-digit mobile number.');
            redirect(base_url('checkout'));
        }

        $details = [
            'name' => $name,
            'phone' => $phone,
            'address' => trim((string)($_POST['address'] ?? '')),
            'city' => trim((string)($_POST['city'] ?? '')),
            'branch_id' => \App\Core\Acl::mainBranchId(),
            'delivery_type' => ($_POST['delivery_type'] ?? '') === 'delivery' ? 'delivery' : 'pickup',
            'note' => trim((string)($_POST['note'] ?? '')),
        ];
        $branch = DB::get('SELECT id FROM `' . tbl('branches') . '` WHERE id = ? AND is_active = 1', [$details['branch_id']]);
        if (!$branch) {
            $branch = DB::get('SELECT id FROM `' . tbl('branches') . '` WHERE is_active = 1 ORDER BY sort_order LIMIT 1');
            $details['branch_id'] = (int)$branch['id'];
        }
        $_SESSION['checkout_details'] = $details;

        if (Settings::getBool('public_otp_required', true) && Whatsapp::enabled()) {
            $this->sendOtp($phone);
            View::render('public/verify_otp', ['phone' => $phone], 'layouts/public');
            return;
        }
        $this->placeOrder();
    }

    public function verifyOtp(): void
    {
        Csrf::check();
        $details = $_SESSION['checkout_details'] ?? null;
        if (!$details) {
            redirect(base_url('checkout'));
        }
        $otp = preg_replace('/\D/', '', (string)($_POST['otp'] ?? ''));
        $row = DB::get(
            'SELECT * FROM `' . tbl('customer_otps') . '` WHERE phone = ? AND verified_at IS NULL ORDER BY id DESC LIMIT 1',
            [$details['phone']]
        );
        if (!$row || strtotime((string)$row['expires_at']) < time()) {
            flash('danger', 'The code has expired. Please request a new one.');
            View::render('public/verify_otp', ['phone' => $details['phone']], 'layouts/public');
            return;
        }
        if ((int)$row['attempts'] >= 5) {
            flash('danger', 'Too many wrong attempts. Please request a new code.');
            View::render('public/verify_otp', ['phone' => $details['phone']], 'layouts/public');
            return;
        }
        if (!password_verify($otp, (string)$row['otp_hash'])) {
            DB::run('UPDATE `' . tbl('customer_otps') . '` SET attempts = attempts + 1 WHERE id = ?', [$row['id']]);
            flash('danger', 'Wrong code — please check the WhatsApp message and try again.');
            View::render('public/verify_otp', ['phone' => $details['phone']], 'layouts/public');
            return;
        }
        DB::update('customer_otps', ['verified_at' => now()], ['id' => $row['id']]);
        $this->placeOrder();
    }

    public function resendOtp(): void
    {
        Csrf::check();
        $details = $_SESSION['checkout_details'] ?? null;
        if (!$details) {
            redirect(base_url('checkout'));
        }
        $lastSent = DB::get(
            'SELECT created_at FROM `' . tbl('customer_otps') . '` WHERE phone = ? ORDER BY id DESC LIMIT 1',
            [$details['phone']]
        );
        if ($lastSent && strtotime((string)$lastSent['created_at']) > time() - 60) {
            flash('danger', 'Please wait a minute before requesting another code.');
        } else {
            $this->sendOtp($details['phone']);
            flash('success', 'A new code has been sent on WhatsApp.');
        }
        View::render('public/verify_otp', ['phone' => $details['phone']], 'layouts/public');
    }

    public function placed(string $token): void
    {
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE tracking_token = ? AND deleted_at IS NULL', [$token]);
        if (!$order) {
            abort(404, 'Order not found.');
        }
        View::render('public/order_placed', compact('order'), 'layouts/public');
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
            "Your $business verification code is: *$otp*\nValid for " . Settings::getInt('otp_expiry_minutes', 10) . " minutes. Do not share it.",
            null, null, 'otp', 'otp', null, 1
        );
    }

    private function placeOrder(): void
    {
        $details = $_SESSION['checkout_details'];
        $cart = $_SESSION['cart'];
        try {
            $result = OrderService::createOrder([
                'source' => 'public',
                'user_id' => null,
                'branch_id' => (int)$details['branch_id'],
                'customer' => [
                    'phone' => $details['phone'],
                    'name' => $details['name'],
                    'address' => $details['address'],
                    'city' => $details['city'],
                ],
                'priority' => 'normal',
                'delivery_type' => $details['delivery_type'],
                'delivery_address' => $details['delivery_type'] === 'delivery' ? $details['address'] : null,
                'customer_note' => $details['note'] ?: null,
                'items' => array_map(fn($line) => [
                    'item_id' => $line['item_id'],
                    'qty' => $line['qty'],
                    'spec' => $line['spec'],
                ], array_values($cart)),
                'advance' => ['amount' => 0],
            ]);
        } catch (\Throwable $e) {
            flash('danger', 'Could not place the order: ' . $e->getMessage());
            redirect(base_url('checkout'));
        }

        // Attach any files uploaded with cart lines
        foreach (array_values($cart) as $index => $line) {
            if (!empty($line['file']['path'])) {
                $itemRow = DB::get(
                    'SELECT id FROM `' . tbl('order_items') . '` WHERE order_id = ? AND sort_order = ?',
                    [$result['order_id'], $index]
                );
                DB::insert('order_attachments', [
                    'order_id' => $result['order_id'],
                    'order_item_id' => $itemRow['id'] ?? null,
                    'file_path' => $line['file']['path'],
                    'original_name' => $line['file']['original'] ?? null,
                    'mime' => $line['file']['mime'] ?? null,
                    'size_bytes' => $line['file']['size'] ?? null,
                    'kind' => 'customer_artwork',
                    'uploaded_by_customer' => 1,
                    'created_at' => now(),
                ]);
            }
        }
        DB::insert('public_order_throttle', ['ip' => request_ip(), 'created_at' => now()]);
        unset($_SESSION['cart'], $_SESSION['checkout_details']);
        redirect(base_url('order-placed/' . $result['tracking_token']));
    }
}
