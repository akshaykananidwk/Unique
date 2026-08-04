<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\DB;
use App\Core\Logger;
use App\Core\Uploader;
use App\Core\WaEvents;
use App\Core\Whatsapp;
use App\Models\OrderService;
use App\Models\Status;

class OrderController extends Controller
{
    public function index(): void
    {
        Acl::require('order.view');
        [$bw, $bp, $selectedBranch] = $this->branchScope('o.branch_id');
        $where = [$bw, 'o.deleted_at IS NULL'];
        $params = $bp;

        $status = trim((string)($_GET['status'] ?? ''));
        if ($status === 'overdue') {
            $where[] = "o.due_date < NOW() AND o.status NOT IN ('delivered','completed','cancelled')";
        } elseif ($status === 'needs_review') {
            $where[] = 'o.needs_review = 1';
        } elseif ($status !== '') {
            $where[] = 'o.status = ?';
            $params[] = $status;
        }
        $q = trim((string)($_GET['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(o.job_no LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($_GET['from'])) {
            $where[] = 'DATE(o.order_date) >= ?';
            $params[] = $_GET['from'];
        }
        if (!empty($_GET['to'])) {
            $where[] = 'DATE(o.order_date) <= ?';
            $params[] = $_GET['to'];
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $whereSql = implode(' AND ', $where);
        $total = (int)DB::val(
            'SELECT COUNT(*) FROM `' . tbl('orders') . '` o JOIN `' . tbl('customers') . "` c ON c.id=o.customer_id WHERE $whereSql",
            $params
        );
        $orders = DB::all(
            'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone, b.name AS branch_name
             FROM `' . tbl('orders') . '` o
             JOIN `' . tbl('customers') . '` c ON c.id = o.customer_id
             JOIN `' . tbl('branches') . "` b ON b.id = o.branch_id
             WHERE $whereSql ORDER BY o.created_at DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage),
            $params
        );
        $branches = DB::all('SELECT id, name FROM `' . tbl('branches') . '` WHERE is_active = 1 ORDER BY sort_order');
        $this->render('orders/index', compact('orders', 'branches', 'selectedBranch', 'total', 'page', 'perPage', 'status', 'q'));
    }

    public function kanban(): void
    {
        Acl::require('order.view');
        [$bw, $bp] = $this->branchScope('o.branch_id');
        $items = DB::all(
            'SELECT oi.*, o.job_no, o.priority, o.id AS order_id, c.name AS customer_name, u.name AS designer_name
             FROM `' . tbl('order_items') . '` oi
             JOIN `' . tbl('orders') . '` o ON o.id = oi.order_id
             JOIN `' . tbl('customers') . '` c ON c.id = o.customer_id
             LEFT JOIN `' . tbl('users') . "` u ON u.id = oi.assigned_designer_id
             WHERE $bw AND o.deleted_at IS NULL AND o.is_cancelled = 0
               AND oi.status NOT IN ('delivered','completed','cancelled')
             ORDER BY oi.due_date ASC",
            $bp
        );
        $columns = [];
        foreach (array_keys(Status::RANKS) as $statusKey) {
            if (in_array($statusKey, ['delivered', 'completed'], true)) {
                continue;
            }
            $columns[$statusKey] = [];
        }
        $columns['on_hold'] = [];
        foreach ($items as $item) {
            $columns[$item['status']][] = $item;
        }
        $this->render('orders/kanban', compact('columns'));
    }

    public function create(): void
    {
        Acl::require('order.create');
        $branchIds = Acl::branchIds();
        $marks = implode(',', array_fill(0, max(1, count($branchIds)), '?'));
        $branches = $branchIds
            ? DB::all('SELECT * FROM `' . tbl('branches') . "` WHERE id IN ($marks) AND is_active = 1 ORDER BY sort_order", $branchIds)
            : [];
        $categories = DB::all(
            'SELECT c.*, (SELECT COUNT(*) FROM `' . tbl('items') . '` i WHERE i.category_id = c.id AND i.is_active = 1 AND i.deleted_at IS NULL) AS item_count
             FROM `' . tbl('categories') . '` c WHERE c.is_active = 1 ORDER BY c.sort_order'
        );
        $items = DB::all(
            'SELECT id, category_id, name, unit, base_price, pricing_type, min_qty FROM `' . tbl('items') . '`
             WHERE is_active = 1 AND deleted_at IS NULL ORDER BY sort_order, name'
        );
        $designers = DB::all(
            'SELECT u.id, u.name,
                    (SELECT COUNT(*) FROM `' . tbl('order_items') . "` oi WHERE oi.assigned_designer_id = u.id
                     AND oi.status IN ('design_pending','design_in_progress','proof_sent','change_requested')) AS open_jobs
             FROM `" . tbl('users') . '` u JOIN `' . tbl('roles') . "` r ON r.id = u.role_id
             WHERE r.slug = 'designer' AND u.is_active = 1 AND u.deleted_at IS NULL ORDER BY u.name"
        );
        $this->render('orders/create', compact('branches', 'categories', 'items', 'designers'));
    }

    public function store(): void
    {
        Acl::require('order.create');
        $branchId = (int)($_POST['branch_id'] ?? 0);
        Acl::requireBranch($branchId);

        $itemsRaw = json_decode((string)($_POST['items_json'] ?? '[]'), true);
        if (!is_array($itemsRaw) || count($itemsRaw) === 0) {
            flash('danger', 'Add at least one item to the order.');
            redirect(admin_url('orders/create'));
        }

        try {
            $result = OrderService::createOrder([
                'source' => 'counter',
                'user_id' => (int)$this->user['id'],
                'branch_id' => $branchId,
                'customer' => [
                    'id' => $_POST['customer_id'] ?? null,
                    'phone' => $_POST['customer_phone'] ?? '',
                    'name' => $_POST['customer_name'] ?? '',
                    'whatsapp' => $_POST['customer_whatsapp'] ?? '',
                    'address' => $_POST['customer_address'] ?? '',
                    'city' => $_POST['customer_city'] ?? '',
                    'gstin' => $_POST['customer_gstin'] ?? '',
                ],
                'priority' => in_array($_POST['priority'] ?? '', ['normal', 'urgent', 'rush'], true) ? $_POST['priority'] : 'normal',
                'delivery_type' => ($_POST['delivery_type'] ?? '') === 'delivery' ? 'delivery' : 'pickup',
                'delivery_address' => $_POST['delivery_address'] ?? null,
                'customer_note' => $_POST['customer_note'] ?? null,
                'internal_note' => $_POST['internal_note'] ?? null,
                'discount_type' => in_array($_POST['discount_type'] ?? '', ['flat', 'percent'], true) ? $_POST['discount_type'] : null,
                'discount_value' => (float)($_POST['discount_value'] ?? 0),
                'delivery_charge' => (float)($_POST['delivery_charge'] ?? 0),
                'items' => $itemsRaw,
                'advance' => [
                    'amount' => (float)($_POST['advance_amount'] ?? 0),
                    'mode' => $_POST['advance_mode'] ?? 'cash',
                    'reference' => $_POST['advance_reference'] ?? null,
                ],
                // Yes/No popup on save: '0' = don't WhatsApp the customer the confirmation.
                'notify_customer' => ($_POST['notify_customer'] ?? '1') !== '0',
            ]);
        } catch (\Throwable $e) {
            flash('danger', 'Could not save the order: ' . $e->getMessage());
            redirect(admin_url('orders/create'));
        }

        // Per-item file uploads arrive as item_file_{index}
        foreach (array_keys($_FILES) as $fileKey) {
            if (preg_match('/^item_file_(\d+)$/', (string)$fileKey, $m)) {
                $stored = Uploader::store($_FILES[$fileKey], 'artwork/' . date('Y/m'), Uploader::ARTWORK);
                if ($stored['ok']) {
                    $itemRow = DB::get(
                        'SELECT id FROM `' . tbl('order_items') . '` WHERE order_id = ? AND sort_order = ?',
                        [$result['order_id'], (int)$m[1]]
                    );
                    DB::insert('order_attachments', [
                        'order_id' => $result['order_id'],
                        'order_item_id' => $itemRow['id'] ?? null,
                        'file_path' => $stored['path'],
                        'original_name' => $stored['original'],
                        'mime' => $stored['mime'],
                        'size_bytes' => $stored['size'],
                        'kind' => 'customer_artwork',
                        'uploaded_by' => (int)$this->user['id'],
                        'created_at' => now(),
                    ]);
                }
            }
        }

        flash('success', 'Order ' . $result['job_no'] . ' saved.');
        redirect(admin_url('orders/' . $result['order_id'] . '?print=1'));
    }

    public function show(string $id): void
    {
        Acl::require('order.view');
        $order = $this->findOrder((int)$id);
        $items = DB::all(
            'SELECT oi.*, u.name AS designer_name FROM `' . tbl('order_items') . '` oi
             LEFT JOIN `' . tbl('users') . '` u ON u.id = oi.assigned_designer_id
             WHERE oi.order_id = ? ORDER BY oi.sort_order, oi.id',
            [(int)$id]
        );
        foreach ($items as &$item) {
            $item['proofs'] = DB::all(
                'SELECT dp.*, (SELECT COUNT(*) FROM `' . tbl('proof_feedback') . '` pf WHERE pf.proof_id = dp.id) AS feedback_count
                 FROM `' . tbl('design_proofs') . '` dp WHERE dp.order_item_id = ? ORDER BY dp.version DESC',
                [(int)$item['id']]
            );
        }
        unset($item);
        $payments = DB::all(
            'SELECT p.*, u.name AS received_by FROM `' . tbl('payments') . '` p
             LEFT JOIN `' . tbl('users') . '` u ON u.id = p.received_by_user_id
             WHERE p.order_id = ? AND p.deleted_at IS NULL ORDER BY p.paid_at',
            [(int)$id]
        );
        $history = DB::all(
            'SELECT h.*, u.name AS by_user FROM `' . tbl('order_status_history') . '` h
             LEFT JOIN `' . tbl('users') . '` u ON u.id = h.changed_by_user_id
             WHERE h.order_id = ? ORDER BY h.created_at DESC, h.id DESC LIMIT 100',
            [(int)$id]
        );
        $attachments = DB::all('SELECT * FROM `' . tbl('order_attachments') . '` WHERE order_id = ?', [(int)$id]);
        $customer = DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE id = ?', [(int)$order['customer_id']]);
        $branch = DB::get('SELECT * FROM `' . tbl('branches') . '` WHERE id = ?', [(int)$order['branch_id']]);
        $designers = DB::all(
            'SELECT u.id, u.name FROM `' . tbl('users') . '` u JOIN `' . tbl('roles') . "` r ON r.id = u.role_id
             WHERE r.slug = 'designer' AND u.is_active = 1 AND u.deleted_at IS NULL ORDER BY u.name"
        );
        $this->render('orders/show', compact('order', 'items', 'payments', 'history', 'attachments', 'customer', 'branch', 'designers'));
    }

    public function edit(string $id): void
    {
        Acl::require('order.edit');
        $order = $this->findOrder((int)$id);
        if (in_array($order['status'], ['delivered', 'completed', 'cancelled'], true)) {
            flash('danger', 'Delivered, completed or cancelled orders cannot be edited.');
            redirect(admin_url('orders/' . $id));
        }
        $customer = DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE id = ?', [(int)$order['customer_id']]);
        $items = DB::all(
            'SELECT oi.*, i.pricing_type FROM `' . tbl('order_items') . '` oi
             LEFT JOIN `' . tbl('items') . '` i ON i.id = oi.item_id
             WHERE oi.order_id = ? ORDER BY oi.sort_order, oi.id',
            [(int)$order['id']]
        );
        // Catalog for the "Add Item" builder (same data the create page uses)
        $categories = DB::all(
            'SELECT c.*, (SELECT COUNT(*) FROM `' . tbl('items') . '` i WHERE i.category_id = c.id AND i.is_active = 1 AND i.deleted_at IS NULL) AS item_count
             FROM `' . tbl('categories') . '` c WHERE c.is_active = 1 ORDER BY c.sort_order'
        );
        $catalog = DB::all(
            'SELECT id, category_id, name, unit, base_price, pricing_type, min_qty FROM `' . tbl('items') . '`
             WHERE is_active = 1 AND deleted_at IS NULL ORDER BY sort_order, name'
        );
        $designers = DB::all(
            'SELECT u.id, u.name,
                    (SELECT COUNT(*) FROM `' . tbl('order_items') . "` oi WHERE oi.assigned_designer_id = u.id
                     AND oi.status IN ('design_pending','design_in_progress','proof_sent','change_requested')) AS open_jobs
             FROM `" . tbl('users') . '` u JOIN `' . tbl('roles') . "` r ON r.id = u.role_id
             WHERE r.slug = 'designer' AND u.is_active = 1 AND u.deleted_at IS NULL ORDER BY u.name"
        );
        $this->render('orders/edit', compact('order', 'customer', 'items', 'categories', 'catalog', 'designers'));
    }

    /** Order-level fields plus per-item price/qty edits (discount, delivery, notes, priority, due date, line rates). */
    public function update(string $id): void
    {
        Acl::require('order.edit');
        $order = $this->findOrder((int)$id);
        if (in_array($order['status'], ['delivered', 'completed', 'cancelled'], true)) {
            flash('danger', 'Delivered, completed or cancelled orders cannot be edited.');
            redirect(admin_url('orders/' . $id));
        }

        // Full item reconciliation: add / edit / remove lines in one shot.
        $itemsRaw = json_decode((string)($_POST['items_json'] ?? '[]'), true);
        if (!is_array($itemsRaw) || count($itemsRaw) === 0) {
            flash('danger', 'An order must keep at least one item. Add an item before saving.');
            redirect(admin_url('orders/' . $id . '/edit'));
        }
        try {
            OrderService::syncOrderItems((int)$id, $itemsRaw, (int)$this->user['id']);
        } catch (\Throwable $e) {
            flash('danger', 'Could not update the items: ' . $e->getMessage());
            redirect(admin_url('orders/' . $id . '/edit'));
        }

        DB::update('orders', [
            'priority' => in_array($_POST['priority'] ?? '', ['normal', 'urgent', 'rush'], true) ? $_POST['priority'] : $order['priority'],
            'due_date' => !empty($_POST['due_date']) ? date('Y-m-d H:i:s', (int)strtotime((string)$_POST['due_date'])) : $order['due_date'],
            'discount_type' => in_array($_POST['discount_type'] ?? '', ['flat', 'percent'], true) ? $_POST['discount_type'] : null,
            'discount_value' => (float)($_POST['discount_value'] ?? 0),
            'delivery_charge' => (float)($_POST['delivery_charge'] ?? 0),
            'delivery_type' => ($_POST['delivery_type'] ?? '') === 'delivery' ? 'delivery' : 'pickup',
            'delivery_address' => $_POST['delivery_address'] ?? null,
            'customer_note' => $_POST['customer_note'] ?? null,
            'internal_note' => $_POST['internal_note'] ?? null,
            'updated_at' => now(),
        ], ['id' => (int)$id]);
        OrderService::recalcTotals((int)$id);
        OrderService::recalcPayments((int)$id);
        Logger::activity('order', 'update', 'order', (int)$id, 'Order ' . $order['job_no'] . ' updated');
        flash('success', 'Order updated.');
        redirect(admin_url('orders/' . $id));
    }

    public function cancel(string $id): void
    {
        Acl::require('order.cancel');
        $order = $this->findOrder((int)$id);
        $result = OrderService::cancelOrder((int)$id, (string)($_POST['reason'] ?? ''), (int)$this->user['id']);
        flash($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Order ' . $order['job_no'] . ' cancelled.' : $result['error']);
        redirect(admin_url('orders/' . $id));
    }

    /** Hard(soft)-delete an order — Super Admin only. Reverses payments/balances via soft delete. */
    public function delete(string $id): void
    {
        if ($this->user['role_slug'] !== 'super_admin') {
            abort(403, 'Only the Super Admin can delete orders.');
        }
        Acl::require('order.delete');
        $order = $this->findOrder((int)$id);
        DB::update('orders', ['deleted_at' => now()], ['id' => (int)$id]);
        DB::run('UPDATE `' . tbl('payments') . '` SET deleted_at = ? WHERE order_id = ? AND deleted_at IS NULL', [now(), (int)$id]);
        Logger::activity('order', 'delete', 'order', (int)$id, 'Deleted order ' . $order['job_no'] . ' (soft)');
        flash('success', 'Order ' . $order['job_no'] . ' deleted.');
        redirect(admin_url('orders'));
    }

    public function confirmPublic(string $id): void
    {
        Acl::require('order.edit');
        $order = $this->findOrder((int)$id);
        DB::update('orders', ['needs_review' => 0, 'updated_at' => now()], ['id' => (int)$id]);
        Logger::activity('order', 'confirm_public', 'order', (int)$id, 'Public order ' . $order['job_no'] . ' confirmed');
        flash('success', 'Order confirmed and released to production flow.');
        redirect(admin_url('orders/' . $id));
    }

    public function itemStatus(string $id): void
    {
        Acl::require('order.change_status');
        $item = DB::get('SELECT * FROM `' . tbl('order_items') . '` WHERE id = ?', [(int)$id]);
        if (!$item) {
            abort(404, 'Job item not found.');
        }
        $order = $this->findOrder((int)$item['order_id']);
        $to = (string)($_POST['status'] ?? '');
        $note = trim((string)($_POST['note'] ?? ''));
        $result = OrderService::changeItemStatus((int)$id, $to, (int)$this->user['id'], $note, false, $this->isManager());
        if ($result['ok']) {
            Logger::activity('order', 'status', 'order_item', (int)$id,
                $order['job_no'] . ': ' . $item['status'] . ' → ' . $to . ($note !== '' ? " ($note)" : ''));
        }
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
            json_response($result, $result['ok'] ? 200 : 422);
        }
        flash($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Status updated to ' . Status::label($to) . '.' : $result['error']);
        redirect(admin_url('orders/' . $item['order_id']));
    }

    public function assignDesigner(string $id): void
    {
        Acl::require('order.assign');
        $item = DB::get('SELECT * FROM `' . tbl('order_items') . '` WHERE id = ?', [(int)$id]);
        if (!$item) {
            abort(404, 'Job item not found.');
        }
        $this->findOrder((int)$item['order_id']); // branch check
        $result = OrderService::assignDesigner((int)$id, (int)($_POST['designer_id'] ?? 0), (int)$this->user['id']);
        flash($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Designer assigned.' : $result['error']);
        redirect(admin_url('orders/' . $item['order_id']));
    }

    public function jobCard(string $id): void
    {
        Acl::require('order.view');
        $order = $this->findOrder((int)$id);
        $items = DB::all('SELECT * FROM `' . tbl('order_items') . '` WHERE order_id = ? ORDER BY sort_order', [(int)$id]);
        $customer = DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE id = ?', [(int)$order['customer_id']]);
        $branch = DB::get('SELECT * FROM `' . tbl('branches') . '` WHERE id = ?', [(int)$order['branch_id']]);
        $format = ($_GET['format'] ?? 'a5') === 'thermal' ? 'thermal' : 'a5';
        \App\Core\View::render('orders/job_card', compact('order', 'items', 'customer', 'branch', 'format'), 'layouts/print');
    }

    /** Quick action: send tracking link or a custom text to the customer. */
    public function sendWhatsapp(string $id): void
    {
        Acl::require('order.view');
        $order = $this->findOrder((int)$id);
        $customer = DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE id = ?', [(int)$order['customer_id']]);
        $text = trim((string)($_POST['message'] ?? ''));
        if ($text === '') {
            $data = WaEvents::orderData($order);
            $text = "Namaste {$data['customer_name']} 🙏\nJob *{$order['job_no']}* — track your order here:\n{$data['tracking_link']}\n{$data['business_name']}";
        }
        $queued = Whatsapp::queueRaw((string)$customer['phone'], $text, null, null, 'manual', 'order', (int)$order['id'], 3);
        flash($queued ? 'success' : 'danger', $queued ? 'WhatsApp message queued.' : 'Could not queue — check the customer number.');
        redirect(admin_url('orders/' . $id));
    }

    private function findOrder(int $id): array
    {
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ? AND deleted_at IS NULL', [$id]);
        if (!$order) {
            abort(404, 'Order not found.');
        }
        if (!Acl::can('order.view_all')) {
            Acl::requireBranch((int)$order['branch_id']);
        }
        return $order;
    }
}
