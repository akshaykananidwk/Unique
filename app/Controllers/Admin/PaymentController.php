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
            // Only somebody allowed to give a discount can write anything off.
            $discount = (float)($_POST['discount_amount'] ?? 0);
            if ($discount > 0 && !Acl::can('payment.discount')) {
                throw new \RuntimeException('You are not allowed to give a discount on a payment.');
            }
            $paymentId = OrderService::addPayment($orderId, [
                'amount' => (float)($_POST['amount'] ?? 0),
                'discount_amount' => $discount,
                'paid_at' => !empty($_POST['paid_at']) ? date('Y-m-d H:i:s', (int)strtotime((string)$_POST['paid_at'])) : now(),
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

    /**
     * Every payment in one place — the screen that was missing.
     *
     * Filterable by customer, mode, type and date, with the totals a shop actually asks
     * for: how much came in, how much was written off, and how much is still owed.
     */
    public function index(): void
    {
        Acl::require('payment.view');
        $where = ['p.deleted_at IS NULL'];
        $params = [];

        $q = trim((string)($_GET['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(p.receipt_no LIKE ? OR o.job_no LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like, $like);
        }
        $customerId = (int)($_GET['customer'] ?? 0);
        if ($customerId > 0) {
            $where[] = 'p.customer_id = ?';
            $params[] = $customerId;
        }
        $mode = trim((string)($_GET['mode'] ?? ''));
        if (in_array($mode, ['cash', 'upi', 'card', 'bank', 'cheque', 'credit'], true)) {
            $where[] = 'p.mode = ?';
            $params[] = $mode;
        }
        $type = trim((string)($_GET['type'] ?? ''));
        if (in_array($type, ['advance', 'part', 'final', 'refund'], true)) {
            $where[] = 'p.type = ?';
            $params[] = $type;
        }
        if (!empty($_GET['from'])) {
            $where[] = 'DATE(p.paid_at) >= ?';
            $params[] = $_GET['from'];
        }
        if (!empty($_GET['to'])) {
            $where[] = 'DATE(p.paid_at) <= ?';
            $params[] = $_GET['to'];
        }
        $whereSql = implode(' AND ', $where);

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 40;
        $total = (int)DB::val(
            'SELECT COUNT(*) FROM `' . tbl('payments') . '` p
             JOIN `' . tbl('orders') . '` o ON o.id = p.order_id
             JOIN `' . tbl('customers') . "` c ON c.id = p.customer_id WHERE $whereSql",
            $params
        );
        $rows = DB::all(
            'SELECT p.*, o.job_no, o.total AS order_total, o.balance_amount,
                    c.id AS cust_id, c.name AS customer_name, c.phone AS customer_phone,
                    u.name AS received_by
             FROM `' . tbl('payments') . '` p
             JOIN `' . tbl('orders') . '` o ON o.id = p.order_id
             JOIN `' . tbl('customers') . '` c ON c.id = p.customer_id
             LEFT JOIN `' . tbl('users') . "` u ON u.id = p.received_by_user_id
             WHERE $whereSql ORDER BY p.paid_at DESC, p.id DESC
             LIMIT $perPage OFFSET " . (($page - 1) * $perPage),
            $params
        );
        $sums = DB::get(
            'SELECT COALESCE(SUM(p.amount),0) AS received, COALESCE(SUM(p.discount_amount),0) AS discount
             FROM `' . tbl('payments') . '` p
             JOIN `' . tbl('orders') . '` o ON o.id = p.order_id
             JOIN `' . tbl('customers') . "` c ON c.id = p.customer_id WHERE $whereSql",
            $params
        );
        $customer = $customerId > 0
            ? DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE id = ?', [$customerId]) : null;
        $customerOutstanding = $customer ? self::outstandingFor($customerId) : 0.0;
        $pages = max(1, (int)ceil($total / $perPage));

        $this->render('payments/index', compact('rows', 'sums', 'total', 'page', 'pages', 'q', 'mode', 'type', 'customer', 'customerId', 'customerOutstanding'));
    }

    /** Correct a receipt that was written down wrong. */
    public function edit(string $id): void
    {
        Acl::require('payment.edit');
        $payment = $this->findPayment((int)$id);
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [(int)$payment['order_id']]);
        $customer = DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE id = ?', [(int)$payment['customer_id']]);
        $edits = DB::all(
            'SELECT pe.*, u.name AS by_user FROM `' . tbl('payment_edits') . '` pe
             LEFT JOIN `' . tbl('users') . '` u ON u.id = pe.changed_by_user_id
             WHERE pe.payment_id = ? ORDER BY pe.id DESC',
            [(int)$id]
        );
        $this->render('payments/edit', compact('payment', 'order', 'customer', 'edits'));
    }

    public function update(string $id): void
    {
        Acl::require('payment.edit');
        $payment = $this->findPayment((int)$id);
        $orderId = (int)$payment['order_id'];

        $amount = round((float)($_POST['amount'] ?? 0), 2);
        $discount = round(max(0.0, (float)($_POST['discount_amount'] ?? 0)), 2);
        if ($discount > 0 && !Acl::can('payment.discount')) {
            flash('danger', 'You are not allowed to give a discount on a payment.');
            redirect(admin_url('payments/' . $id . '/edit'));
        }
        if ($payment['type'] === 'refund') {
            $amount = -abs($amount);
            $discount = 0.0;
        } elseif ($amount < 0) {
            flash('danger', 'Payment amount cannot be negative.');
            redirect(admin_url('payments/' . $id . '/edit'));
        }
        if ($amount == 0.0 && $discount == 0.0) {
            flash('danger', 'Enter an amount, a discount, or both.');
            redirect(admin_url('payments/' . $id . '/edit'));
        }

        // What the order would owe if this receipt were not here at all.
        $others = (float)DB::val(
            'SELECT COALESCE(SUM(amount + discount_amount),0) FROM `' . tbl('payments') . '`
             WHERE order_id = ? AND id <> ? AND deleted_at IS NULL',
            [$orderId, (int)$id]
        );
        $orderTotal = (float)DB::val('SELECT total FROM `' . tbl('orders') . '` WHERE id = ?', [$orderId]);
        if ($payment['type'] !== 'refund' && $others + $amount + $discount > $orderTotal + 0.009) {
            flash('danger', sprintf(
                'That would collect %s against a bill of %s. The rest of this order already has %s against it.',
                fmt_money($others + $amount + $discount), fmt_money($orderTotal), fmt_money($others)
            ));
            redirect(admin_url('payments/' . $id . '/edit'));
        }

        $new = [
            'amount' => $amount,
            'discount_amount' => $discount,
            'mode' => in_array($_POST['mode'] ?? '', ['cash', 'upi', 'card', 'bank', 'cheque', 'credit'], true) ? $_POST['mode'] : $payment['mode'],
            'paid_at' => !empty($_POST['paid_at']) ? date('Y-m-d H:i:s', (int)strtotime((string)$_POST['paid_at'])) : $payment['paid_at'],
            'reference' => trim((string)($_POST['reference'] ?? '')) ?: null,
            'note' => trim((string)($_POST['note'] ?? '')) ?: null,
        ];

        // Record what changed before changing it — a receipt that can be edited has to
        // explain itself later.
        $moneyFields = ['amount', 'discount_amount'];
        foreach ($new as $field => $value) {
            // Money compares as a number: "0.00" and "0" are the same figure, and logging
            // that as a change would bury the real edits in noise.
            $changed = in_array($field, $moneyFields, true)
                ? abs((float)$value - (float)$payment[$field]) > 0.004
                : (string)$value !== (string)$payment[$field];
            if ($changed) {
                DB::insert('payment_edits', [
                    'payment_id' => (int)$id,
                    'changed_by_user_id' => (int)$this->user['id'],
                    'field' => $field,
                    'old_value' => mb_substr((string)$payment[$field], 0, 255),
                    'new_value' => mb_substr((string)$value, 0, 255),
                    'created_at' => now(),
                ]);
            }
        }

        DB::update('payments', $new, ['id' => (int)$id]);
        OrderService::recalcPayments($orderId);
        OrderService::maybeComplete($orderId);
        Logger::activity('payment', 'edit', 'payment', (int)$id,
            'Edited receipt ' . $payment['receipt_no'] . ' — now ' . fmt_money($amount)
            . ($discount > 0 ? ' + ' . fmt_money($discount) . ' discount' : ''));
        flash('success', 'Receipt ' . $payment['receipt_no'] . ' updated and balances recalculated.');
        redirect(admin_url('payments/' . $id . '/edit'));
    }

    /**
     * Take money from a party against everything they owe.
     *
     * A customer rarely pays bill by bill — he hands over a lump sum. This spreads it over
     * his unpaid orders, oldest first, so nothing has to be worked out by hand.
     */
    public function collect(string $customerId): void
    {
        Acl::require('payment.create');
        $customer = DB::get(
            'SELECT * FROM `' . tbl('customers') . '` WHERE id = ? AND deleted_at IS NULL',
            [(int)$customerId]
        );
        if (!$customer) {
            abort(404, 'Customer not found.');
        }

        $amount = round((float)($_POST['amount'] ?? 0), 2);
        $discount = round(max(0.0, (float)($_POST['discount_amount'] ?? 0)), 2);
        if ($discount > 0 && !Acl::can('payment.discount')) {
            flash('danger', 'You are not allowed to give a discount.');
            redirect(admin_url('customers/' . $customerId));
        }
        if ($amount <= 0 && $discount <= 0) {
            flash('danger', 'Enter an amount, a discount, or both.');
            redirect(admin_url('customers/' . $customerId));
        }

        $pending = DB::all(
            'SELECT id, job_no, balance_amount FROM `' . tbl('orders') . '`
             WHERE customer_id = ? AND deleted_at IS NULL AND is_cancelled = 0 AND balance_amount > 0
             ORDER BY order_date, id',
            [(int)$customerId]
        );
        if (!$pending) {
            flash('warning', $customer['name'] . ' has nothing outstanding.');
            redirect(admin_url('customers/' . $customerId));
        }

        $mode = in_array($_POST['mode'] ?? '', ['cash', 'upi', 'card', 'bank', 'cheque', 'credit'], true) ? $_POST['mode'] : 'cash';
        $paidAt = !empty($_POST['paid_at']) ? date('Y-m-d H:i:s', (int)strtotime((string)$_POST['paid_at'])) : now();
        $leftMoney = $amount;
        $leftDiscount = $discount;
        $touched = [];

        foreach ($pending as $order) {
            if ($leftMoney <= 0 && $leftDiscount <= 0) {
                break;
            }
            $owed = round((float)$order['balance_amount'], 2);
            // Money first, then the write-off mops up whatever is left on that bill.
            $payHere = min($leftMoney, $owed);
            $discHere = min($leftDiscount, round($owed - $payHere, 2));
            if ($payHere <= 0 && $discHere <= 0) {
                continue;
            }
            try {
                OrderService::addPayment((int)$order['id'], [
                    'amount' => $payHere,
                    'discount_amount' => $discHere,
                    'type' => 'part',
                    'mode' => $mode,
                    'paid_at' => $paidAt,
                    'reference' => trim((string)($_POST['reference'] ?? '')) ?: null,
                    'note' => trim((string)($_POST['note'] ?? '')) ?: 'Part of a lump-sum settlement',
                ], (int)$this->user['id']);
                $leftMoney = round($leftMoney - $payHere, 2);
                $leftDiscount = round($leftDiscount - $discHere, 2);
                $touched[] = $order['job_no'];
            } catch (\Throwable $e) {
                flash('danger', 'Stopped at ' . $order['job_no'] . ': ' . $e->getMessage());
                redirect(admin_url('customers/' . $customerId));
            }
        }

        $usedMoney = round($amount - $leftMoney, 2);
        $usedDiscount = round($discount - $leftDiscount, 2);
        Logger::activity('payment', 'collect', 'customer', (int)$customerId,
            'Settled ' . fmt_money($usedMoney) . ' from ' . $customer['name']
            . ' across ' . count($touched) . ' order(s)');

        $msg = fmt_money($usedMoney) . ' put against ' . count($touched) . ' order(s): ' . implode(', ', $touched) . '.';
        if ($usedDiscount > 0) {
            $msg .= ' ' . fmt_money($usedDiscount) . ' allowed as discount.';
        }
        if ($leftMoney > 0.009) {
            $msg .= ' ' . fmt_money($leftMoney) . ' was more than the outstanding and was NOT taken.';
        }
        flash($leftMoney > 0.009 ? 'warning' : 'success', $msg);
        redirect(admin_url('customers/' . $customerId));
    }

    /** What a party still owes across every live order — the "how much to collect" figure. */
    public static function outstandingFor(int $customerId): float
    {
        return (float)DB::val(
            'SELECT COALESCE(SUM(balance_amount),0) FROM `' . tbl('orders') . '`
             WHERE customer_id = ? AND deleted_at IS NULL AND is_cancelled = 0',
            [$customerId]
        );
    }

    private function findPayment(int $id): array
    {
        $payment = DB::get('SELECT * FROM `' . tbl('payments') . '` WHERE id = ? AND deleted_at IS NULL', [$id]);
        if (!$payment) {
            abort(404, 'Payment not found.');
        }
        return $payment;
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
