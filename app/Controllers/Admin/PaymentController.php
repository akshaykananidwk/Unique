<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\DB;
use App\Core\Logger;
use App\Core\View;
use App\Models\OrderService;

class PaymentController extends Controller
{
    public function store(): void
    {
        Acl::require('payment.create');
        $orderId = (int)($_POST['order_id'] ?? 0);
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ? AND deleted_at IS NULL', [$orderId]);
        if (!$order) {
            abort(404, 'Order not found.');
        }
        Acl::requireBranch((int)$order['branch_id']);
        $type = in_array($_POST['type'] ?? '', ['advance', 'part', 'final', 'refund'], true) ? $_POST['type'] : 'part';
        if ($type === 'refund') {
            Acl::require('payment.refund');
        }
        try {
            $paymentId = OrderService::addPayment($orderId, [
                'amount' => (float)($_POST['amount'] ?? 0),
                'type' => $type,
                'mode' => in_array($_POST['mode'] ?? '', ['cash', 'upi', 'card', 'bank', 'cheque', 'credit'], true) ? $_POST['mode'] : 'cash',
                'reference' => trim((string)($_POST['reference'] ?? '')) ?: null,
                'note' => trim((string)($_POST['note'] ?? '')) ?: null,
            ], (int)$this->user['id']);
            flash('success', 'Payment recorded. <a class="alert-link" target="_blank" href="' . e(admin_url('payments/' . $paymentId . '/receipt')) . '">Print receipt</a>');
        } catch (\Throwable $e) {
            flash('danger', 'Payment failed: ' . $e->getMessage());
        }
        redirect(admin_url('orders/' . $orderId));
    }

    public function delete(string $id): void
    {
        // Restricted to Super Admin by design
        if ($this->user['role_slug'] !== 'super_admin') {
            abort(403, 'Only the Super Admin can delete payments.');
        }
        Acl::require('payment.delete');
        $payment = DB::get('SELECT * FROM `' . tbl('payments') . '` WHERE id = ? AND deleted_at IS NULL', [(int)$id]);
        if (!$payment) {
            abort(404, 'Payment not found.');
        }
        DB::update('payments', ['deleted_at' => now()], ['id' => (int)$id]);
        OrderService::recalcPayments((int)$payment['order_id']);
        Logger::activity('payment', 'delete', 'payment', (int)$id,
            'Deleted payment ' . $payment['receipt_no'] . ' of ₹' . $payment['amount'] . ' (soft)');
        flash('success', 'Payment deleted and balances recalculated.');
        redirect(admin_url('orders/' . $payment['order_id']));
    }

    public function receipt(string $id): void
    {
        Acl::require('payment.view');
        $payment = DB::get('SELECT * FROM `' . tbl('payments') . '` WHERE id = ? AND deleted_at IS NULL', [(int)$id]);
        if (!$payment) {
            abort(404, 'Payment not found.');
        }
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [(int)$payment['order_id']]);
        Acl::requireBranch((int)$order['branch_id']);
        $customer = DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE id = ?', [(int)$payment['customer_id']]);
        $branch = DB::get('SELECT * FROM `' . tbl('branches') . '` WHERE id = ?', [(int)$payment['branch_id']]);
        $format = ($_GET['format'] ?? 'a5') === 'thermal' ? 'thermal' : 'a5';
        View::render('payments/receipt', compact('payment', 'order', 'customer', 'branch', 'format'), 'layouts/print');
    }

    public function cashbook(): void
    {
        Acl::require('payment.view');
        [$bw, $bp, $selectedBranch] = $this->branchScope('p.branch_id');
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['date'] ?? '')) ? $_GET['date'] : date('Y-m-d');

        $byMode = DB::all(
            'SELECT p.mode, COUNT(*) AS cnt, COALESCE(SUM(p.amount),0) AS total
             FROM `' . tbl('payments') . "` p
             WHERE $bw AND DATE(p.paid_at) = ? AND p.deleted_at IS NULL
             GROUP BY p.mode ORDER BY total DESC",
            [...$bp, $date]
        );
        $rows = DB::all(
            'SELECT p.*, o.job_no, c.name AS customer_name, u.name AS received_by
             FROM `' . tbl('payments') . '` p
             JOIN `' . tbl('orders') . '` o ON o.id = p.order_id
             JOIN `' . tbl('customers') . '` c ON c.id = p.customer_id
             LEFT JOIN `' . tbl('users') . "` u ON u.id = p.received_by_user_id
             WHERE $bw AND DATE(p.paid_at) = ? AND p.deleted_at IS NULL
             ORDER BY p.paid_at",
            [...$bp, $date]
        );
        $opening = (float)DB::val(
            'SELECT COALESCE(SUM(p.amount),0) FROM `' . tbl('payments') . "` p
             WHERE $bw AND DATE(p.paid_at) < ? AND p.deleted_at IS NULL",
            [...$bp, $date]
        );
        $branches = DB::all('SELECT id, name FROM `' . tbl('branches') . '` WHERE is_active = 1 ORDER BY sort_order');
        $this->render('payments/cashbook', compact('byMode', 'rows', 'opening', 'date', 'branches', 'selectedBranch'));
    }
}
