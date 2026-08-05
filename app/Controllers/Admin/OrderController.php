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
        foreach ($items as $item) {
            // Retired legacy stages fall back to their own column so nothing silently disappears.
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
            'SELECT * FROM `' . tbl('categories') . '` WHERE is_active = 1 ORDER BY sort_order, name'
        );
        $designers = DB::all(
            'SELECT u.id, u.name,
                    (SELECT COUNT(*) FROM `' . tbl('order_items') . "` oi WHERE oi.assigned_designer_id = u.id
                     AND oi.status IN ('design_pending','design_in_progress','proof_sent','change_requested')) AS open_jobs
             FROM `" . tbl('users') . '` u JOIN `' . tbl('roles') . "` r ON r.id = u.role_id
             WHERE r.slug = 'designer' AND u.is_active = 1 AND u.deleted_at IS NULL ORDER BY u.name"
        );
        $staff = DB::all(
            'SELECT id, name FROM `' . tbl('users') . '` WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name'
        );
        // Item names are free text now; offer what has been typed before as suggestions.
        $nameSuggestions = array_column(DB::all(
            'SELECT DISTINCT item_name_snapshot AS n FROM `' . tbl('order_items') . '` ORDER BY n LIMIT 400'
        ), 'n');
        $this->render('orders/create', compact('branches', 'categories', 'designers', 'staff', 'nameSuggestions'));
    }

    public function store(): void
    {
        Acl::require('order.create');
        $branchId = (int)($_POST['branch_id'] ?? 0);
        Acl::requireBranch($branchId);

        $itemsRaw = json_decode((string)($_POST['items_json'] ?? '[]'), true);
        if (!is_array($itemsRaw) || count($itemsRaw) === 0) {
            $this->backToCreate('Add at least one item to the order.');
        }
        // Checked here as well as in the browser: a form can always reach the server without
        // the browser's own checks running, and losing a full order to that is not acceptable.
        if (trim((string)($_POST['customer_phone'] ?? '')) === '') {
            $this->backToCreate('Enter the customer’s mobile number.');
        }
        if (empty($_POST['customer_id']) && trim((string)($_POST['customer_name'] ?? '')) === '') {
            $this->backToCreate('This is a new customer — enter their name.');
        }

        try {
            $result = OrderService::createOrder([
                'source' => 'counter',
                'user_id' => (int)$this->user['id'],
                'branch_id' => $branchId,
                // Blank job number = generate the next one automatically.
                'job_no' => trim((string)($_POST['job_no'] ?? '')),
                'order_date' => $_POST['order_date'] ?? null,
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
                'accepted_by_user_id' => !empty($_POST['accepted_by_user_id'])
                    ? (int)$_POST['accepted_by_user_id'] : (int)$this->user['id'],
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
            $this->backToCreate('Could not save the order: ' . $e->getMessage());
        }

        $this->storeReferenceFiles((int)$result['order_id']);

        flash('success', 'Order ' . $result['job_no'] . ' saved.');
        redirect(admin_url('orders/' . $result['order_id'] . '?print=1'));
    }

    /**
     * Send the user back to the New Order form with everything they typed still there.
     *
     * A whole order can take minutes to write up. Losing it to a missing mobile number and
     * an empty form is the worst possible outcome, so nothing is ever thrown away — the
     * form comes back filled in, with the reason at the top.
     */
    private function backToCreate(string $message): never
    {
        // Files are the one thing a browser will not hand back, so say so rather than
        // letting them be quietly dropped.
        $hadFiles = !empty(array_filter((array)($_FILES['reference_files']['name'] ?? [])));
        flash('danger', $message . ($hadFiles ? ' Your details are still here — please pick the reference files again.' : ''));
        keep_old($_POST);
        redirect(admin_url('orders/create'));
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
        // Who owns this order end to end.
        $people = [
            'taken_by' => $order['taken_by_user_id']
                ? DB::val('SELECT name FROM `' . tbl('users') . '` WHERE id = ?', [(int)$order['taken_by_user_id']]) : null,
            'accepted_by' => !empty($order['accepted_by_user_id'])
                ? DB::val('SELECT name FROM `' . tbl('users') . '` WHERE id = ?', [(int)$order['accepted_by_user_id']]) : null,
            'designers' => array_column(DB::all(
                'SELECT DISTINCT u.name FROM `' . tbl('order_items') . '` oi
                 JOIN `' . tbl('users') . '` u ON u.id = oi.assigned_designer_id
                 WHERE oi.order_id = ? ORDER BY u.name',
                [(int)$id]
            ), 'name'),
        ];
        $this->render('orders/show', compact('order', 'items', 'payments', 'history', 'attachments', 'customer', 'branch', 'designers', 'people'));
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
        // Each line carries its category (matched via its catalogue item, else by the snapshot
        // name) so the editor knows whether to show foot x foot fields.
        // oi.calc_mode is the line's own — a light board mixes sq.ft and per-piece lines in
        // one category, so it must not be re-derived from the category here.
        $items = DB::all(
            'SELECT oi.*, COALESCE(ci.id, cn.id) AS category_id
             FROM `' . tbl('order_items') . '` oi
             LEFT JOIN `' . tbl('items') . '` i ON i.id = oi.item_id
             LEFT JOIN `' . tbl('categories') . '` ci ON ci.id = i.category_id
             LEFT JOIN `' . tbl('categories') . '` cn ON cn.name = oi.category_name_snapshot
             WHERE oi.order_id = ? ORDER BY oi.sort_order, oi.id',
            [(int)$order['id']]
        );
        $categories = DB::all('SELECT * FROM `' . tbl('categories') . '` WHERE is_active = 1 ORDER BY sort_order, name');
        $designers = DB::all(
            'SELECT u.id, u.name FROM `' . tbl('users') . '` u JOIN `' . tbl('roles') . "` r ON r.id = u.role_id
             WHERE r.slug = 'designer' AND u.is_active = 1 AND u.deleted_at IS NULL ORDER BY u.name"
        );
        $staff = DB::all('SELECT id, name FROM `' . tbl('users') . '` WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name');
        $nameSuggestions = array_column(DB::all(
            'SELECT DISTINCT item_name_snapshot AS n FROM `' . tbl('order_items') . '` ORDER BY n LIMIT 400'
        ), 'n');
        // Reference files already on the order, so they can be seen and removed from here.
        $attachments = DB::all(
            'SELECT * FROM `' . tbl('order_attachments') . '` WHERE order_id = ? ORDER BY id',
            [(int)$order['id']]
        );
        $this->render('orders/edit', compact('order', 'customer', 'items', 'categories', 'designers', 'staff', 'nameSuggestions', 'attachments'));
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
            keep_old($_POST);
            redirect(admin_url('orders/' . $id . '/edit'));
        }
        try {
            OrderService::syncOrderItems((int)$id, $itemsRaw, (int)$this->user['id']);
        } catch (\Throwable $e) {
            flash('danger', 'Could not update the items: ' . $e->getMessage());
            keep_old($_POST);
            redirect(admin_url('orders/' . $id . '/edit'));
        }

        // Job number can be re-typed at any time; it just has to stay unique.
        $jobNo = trim((string)($_POST['job_no'] ?? ''));
        if ($jobNo === '') {
            $jobNo = (string)$order['job_no'];
        } elseif ($jobNo !== (string)$order['job_no']) {
            $taken = DB::val('SELECT id FROM `' . tbl('orders') . '` WHERE job_no = ? AND id <> ?', [$jobNo, (int)$id]);
            if ($taken) {
                flash('danger', 'Job number “' . $jobNo . '” is already used by another order.');
                keep_old($_POST);
            redirect(admin_url('orders/' . $id . '/edit'));
            }
            Logger::activity('order', 'job_no', 'order', (int)$id, 'Job number ' . $order['job_no'] . ' → ' . $jobNo);
        }

        DB::update('orders', [
            'job_no' => $jobNo,
            'order_date' => !empty($_POST['order_date'])
                ? date('Y-m-d H:i:s', (int)strtotime((string)$_POST['order_date']))
                : $order['order_date'],
            'priority' => in_array($_POST['priority'] ?? '', ['normal', 'urgent', 'rush'], true) ? $_POST['priority'] : $order['priority'],
            'due_date' => !empty($_POST['due_date']) ? date('Y-m-d H:i:s', (int)strtotime((string)$_POST['due_date'])) : $order['due_date'],
            'discount_type' => null, // no discount in this workflow
            'discount_value' => 0,
            'accepted_by_user_id' => !empty($_POST['accepted_by_user_id'])
                ? (int)$_POST['accepted_by_user_id'] : ($order['accepted_by_user_id'] ?? null),
            'delivery_charge' => (float)($_POST['delivery_charge'] ?? 0),
            'delivery_type' => ($_POST['delivery_type'] ?? '') === 'delivery' ? 'delivery' : 'pickup',
            'delivery_address' => $_POST['delivery_address'] ?? null,
            'customer_note' => $_POST['customer_note'] ?? null,
            'internal_note' => $_POST['internal_note'] ?? null,
            'updated_at' => now(),
        ], ['id' => (int)$id]);
        OrderService::recalcTotals((int)$id);
        OrderService::recalcPayments((int)$id);

        // Reference files can be added or dropped whenever the order is edited.
        $removed = $this->removeReferenceFiles((int)$id, (array)($_POST['remove_attachments'] ?? []));
        $added = $this->storeReferenceFiles((int)$id);

        Logger::activity('order', 'update', 'order', (int)$id, 'Order ' . $order['job_no'] . ' updated');
        $note = 'Order updated.';
        if ($added) {
            $note .= ' ' . $added . ' file(s) attached.';
        }
        if ($removed) {
            $note .= ' ' . $removed . ' file(s) removed.';
        }
        flash('success', $note);
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
        // "Customer approved in person" — only staff who hold the override permission may skip
        // the online proof approval (e.g. the customer OK'd the design across the counter).
        $override = !empty($_POST['approval_override']) && Acl::can('order.override_approval');
        $result = OrderService::changeItemStatus((int)$id, $to, (int)$this->user['id'], $note, false, $this->isManager(), $override);
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

    /** Quick status change straight from the orders list — applies to every live job in the order. */
    public function orderStatus(string $id): void
    {
        Acl::require('order.change_status');
        $order = $this->findOrder((int)$id);
        // Return to the list the user came from, but never follow an off-site URL.
        $back = (string)($_POST['back'] ?? '');
        $back = str_starts_with($back, admin_url('orders')) ? $back : admin_url('orders');
        $to = (string)($_POST['status'] ?? '');
        if ($to === '' || $to === (string)$order['status']) {
            redirect($back);
        }
        $note = trim((string)($_POST['note'] ?? ''));
        $override = !empty($_POST['approval_override']) && Acl::can('order.override_approval');
        // On a live order, a line cancelled on purpose stays cancelled. But when the WHOLE
        // order is cancelled, picking a stage brings all of it back — otherwise a cancelled
        // order would be a dead end with no way out from the list.
        $orderCancelled = (int)$order['is_cancelled'] === 1 || $order['status'] === 'cancelled';
        $items = DB::all(
            'SELECT id FROM `' . tbl('order_items') . '` WHERE order_id = ?'
            . ($orderCancelled ? '' : " AND status <> 'cancelled'") . ' ORDER BY sort_order, id',
            [(int)$id]
        );

        $done = 0;
        $already = 0;
        $reasons = [];
        foreach ($items as $item) {
            $result = OrderService::changeItemStatus(
                (int)$item['id'], $to, (int)$this->user['id'], $note, false, $this->isManager(), $override
            );
            if ($result['ok']) {
                $done++;
            } elseif (str_starts_with((string)($result['error'] ?? ''), 'Status is already')) {
                $already++;
            } else {
                $reasons[(string)($result['error'] ?? 'Could not update.')] = true;
            }
        }

        if ($done > 0) {
            Logger::activity('order', 'status', 'order', (int)$id,
                $order['job_no'] . ': ' . $done . ' job(s) → ' . $to . ' (from the orders list)');
            flash($reasons ? 'warning' : 'success',
                $order['job_no'] . ' moved to ' . Status::label($to) . ' (' . $done . ' job' . ($done === 1 ? '' : 's') . ').'
                . ($reasons ? ' Not everything moved: ' . implode(' ', array_keys($reasons)) : ''));
        } elseif ($already > 0 && !$reasons) {
            flash('info', $order['job_no'] . ' is already at ' . Status::label($to) . '.');
        } else {
            flash('danger', $reasons ? implode(' ', array_keys($reasons)) : 'Nothing to update on this order.');
        }
        redirect($back);
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

    /**
     * Save any reference files sent with the form. Accepts one or many under
     * reference_files[], plus the legacy per-item item_file_{index} inputs.
     * @return int how many were stored
     */
    private function storeReferenceFiles(int $orderId): int
    {
        $saved = 0;
        $save = function (array $file, ?int $itemId) use ($orderId, &$saved): void {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                return;
            }
            $stored = Uploader::store($file, 'artwork/' . date('Y/m'), Uploader::ARTWORK);
            if (!$stored['ok']) {
                flash('warning', 'Could not attach ' . ($file['name'] ?? 'a file') . ': ' . ($stored['error'] ?? 'rejected'));
                return;
            }
            DB::insert('order_attachments', [
                'order_id' => $orderId,
                'order_item_id' => $itemId,
                'file_path' => $stored['path'],
                'original_name' => $stored['original'],
                'mime' => $stored['mime'],
                'size_bytes' => $stored['size'],
                'kind' => 'customer_artwork',
                'uploaded_by' => (int)$this->user['id'],
                'created_at' => now(),
            ]);
            $saved++;
        };

        // Multiple files under one input arrive as parallel arrays.
        if (!empty($_FILES['reference_files']) && is_array($_FILES['reference_files']['name'] ?? null)) {
            $f = $_FILES['reference_files'];
            foreach (array_keys($f['name']) as $i) {
                $save([
                    'name' => $f['name'][$i], 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i],
                    'error' => $f['error'][$i], 'size' => $f['size'][$i],
                ], null);
            }
        }
        // Legacy per-item inputs (public site / older forms).
        foreach (array_keys($_FILES) as $key) {
            if (preg_match('/^item_file_(\d+)$/', (string)$key, $m)) {
                $row = DB::get(
                    'SELECT id FROM `' . tbl('order_items') . '` WHERE order_id = ? AND sort_order = ?',
                    [$orderId, (int)$m[1]]
                );
                $save($_FILES[$key], isset($row['id']) ? (int)$row['id'] : null);
            }
        }
        return $saved;
    }

    /**
     * Delete attachments the editor ticked off. Scoped to this order, so an id from another
     * order cannot be posted in, and the file on disk goes with the row.
     *
     * @param array<int|string> $ids
     * @return int how many were removed
     */
    private function removeReferenceFiles(int $orderId, array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $rows = DB::all(
            'SELECT id, file_path FROM `' . tbl('order_attachments') . "` WHERE order_id = ? AND id IN ($in)",
            array_merge([$orderId], $ids)
        );
        $gone = 0;
        foreach ($rows as $row) {
            $path = BASE_PATH . '/uploads/' . ltrim((string)$row['file_path'], '/');
            if (is_file($path)) {
                @unlink($path);
            }
            DB::run('DELETE FROM `' . tbl('order_attachments') . '` WHERE id = ?', [(int)$row['id']]);
            $gone++;
        }
        if ($gone) {
            Logger::activity('order', 'attachment', 'order', $orderId, "Removed $gone reference file(s)");
        }
        return $gone;
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
