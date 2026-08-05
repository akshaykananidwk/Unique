<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\DB;
use App\Core\Logger;
use App\Models\CustomerBook;

class CustomerController extends Controller
{
    public function index(): void
    {
        Acl::require('customer.view');
        $q = trim((string)($_GET['q'] ?? ''));
        $where = ['c.deleted_at IS NULL'];
        $params = [];
        if ($q !== '') {
            // Search the people as well as the account: a company is often remembered by
            // the person who calls, or by their number.
            $where[] = '(c.name LIKE ? OR c.phone LIKE ? OR EXISTS ('
                . 'SELECT 1 FROM `' . tbl('customer_contacts') . '` cc'
                . ' WHERE cc.customer_id = c.id AND cc.deleted_at IS NULL'
                . ' AND (cc.name LIKE ? OR cc.phone LIKE ?)))';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like, $like);
        }
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $whereSql = implode(' AND ', $where);
        $total = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('customers') . "` c WHERE $whereSql", $params);
        $customers = DB::all(
            'SELECT c.*,
                (SELECT COUNT(*) FROM `' . tbl('orders') . '` o WHERE o.customer_id = c.id AND o.deleted_at IS NULL AND o.is_cancelled = 0) AS order_count,
                (SELECT COALESCE(SUM(o.balance_amount),0) FROM `' . tbl('orders') . '` o WHERE o.customer_id = c.id AND o.deleted_at IS NULL AND o.is_cancelled = 0) AS outstanding,
                (SELECT COUNT(*) FROM `' . tbl('customer_contacts') . '` cc WHERE cc.customer_id = c.id AND cc.deleted_at IS NULL) AS contact_count
             FROM `' . tbl('customers') . "` c WHERE $whereSql
             ORDER BY c.created_at DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage),
            $params
        );
        $this->render('customers/index', compact('customers', 'q', 'total', 'page', 'perPage'));
    }

    public function create(): void
    {
        Acl::require('customer.create');
        $priceGroups = DB::all('SELECT * FROM `' . tbl('price_groups') . '` ORDER BY name');
        $this->render('customers/form', ['customer' => null, 'priceGroups' => $priceGroups, 'contacts' => []]);
    }

    public function store(): void
    {
        Acl::require('customer.create');
        $data = $this->validated();
        if (DB::get('SELECT id FROM `' . tbl('customers') . '` WHERE phone = ? AND deleted_at IS NULL', [$data['phone']])) {
            flash('danger', 'A customer with this phone number already exists.');
            redirect(admin_url('customers/create'));
        }
        $data += [
            'source' => 'counter',
            'created_by' => (int)$this->user['id'],
            'branch_id' => (int)$this->user['primary_branch_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $id = DB::insert('customers', $data);
        Logger::activity('customer', 'create', 'customer', $id, 'Customer ' . $data['name'] . ' created');
        flash('success', 'Customer added.');
        redirect(admin_url('customers/' . $id));
    }

    public function show(string $id): void
    {
        Acl::require('customer.view');
        $customer = $this->find((int)$id);
        // Every order for the account, whichever of its people gave it — this is the whole
        // point of the contact book: open the company, see all the work.
        $orders = DB::all(
            'SELECT o.*, b.name AS branch_name, cc.name AS contact_name, cc.phone AS contact_phone
             FROM `' . tbl('orders') . '` o
             JOIN `' . tbl('branches') . '` b ON b.id = o.branch_id
             LEFT JOIN `' . tbl('customer_contacts') . '` cc ON cc.id = o.contact_id
             WHERE o.customer_id = ? AND o.deleted_at IS NULL ORDER BY o.created_at DESC',
            [(int)$id]
        );
        $contacts = CustomerBook::contacts((int)$id);
        $payments = DB::all(
            'SELECT p.*, o.job_no FROM `' . tbl('payments') . '` p
             JOIN `' . tbl('orders') . '` o ON o.id = p.order_id
             WHERE p.customer_id = ? AND p.deleted_at IS NULL ORDER BY p.paid_at DESC',
            [(int)$id]
        );
        $totals = [
            'billed' => array_sum(array_map(fn($o) => (int)$o['is_cancelled'] ? 0 : (float)$o['total'], $orders)),
            'paid' => array_sum(array_map(fn($p) => (float)$p['amount'], $payments)),
        ];
        $totals['outstanding'] = $totals['billed'] - $totals['paid'];
        $this->render('customers/show', compact('customer', 'orders', 'payments', 'totals', 'contacts'));
    }

    public function edit(string $id): void
    {
        Acl::require('customer.edit');
        $customer = $this->find((int)$id);
        $priceGroups = DB::all('SELECT * FROM `' . tbl('price_groups') . '` ORDER BY name');
        $contacts = CustomerBook::contacts((int)$id);
        $this->render('customers/form', compact('customer', 'priceGroups', 'contacts'));
    }

    public function update(string $id): void
    {
        Acl::require('customer.edit');
        $customer = $this->find((int)$id);
        $data = $this->validated();
        $dupe = DB::get(
            'SELECT id FROM `' . tbl('customers') . '` WHERE phone = ? AND id != ? AND deleted_at IS NULL',
            [$data['phone'], (int)$id]
        );
        if ($dupe) {
            flash('danger', 'Another customer already uses this phone number.');
            redirect(admin_url('customers/' . $id . '/edit'));
        }
        $data['updated_at'] = now();
        DB::update('customers', $data, ['id' => (int)$id]);
        Logger::activity('customer', 'update', 'customer', (int)$id, 'Customer ' . $customer['name'] . ' updated');
        flash('success', 'Customer updated.');
        redirect(admin_url('customers/' . $id));
    }

    public function delete(string $id): void
    {
        Acl::require('customer.delete');
        $customer = $this->find((int)$id);
        $open = (int)DB::val(
            'SELECT COUNT(*) FROM `' . tbl('orders') . "` WHERE customer_id = ? AND deleted_at IS NULL
             AND status NOT IN ('completed','cancelled')",
            [(int)$id]
        );
        if ($open > 0) {
            flash('danger', 'Cannot delete — this customer has open orders.');
            redirect(admin_url('customers/' . $id));
        }
        DB::update('customers', ['deleted_at' => now()], ['id' => (int)$id]);
        Logger::activity('customer', 'delete', 'customer', (int)$id, 'Customer ' . $customer['name'] . ' deleted (soft)');
        flash('success', 'Customer deleted.');
        redirect(admin_url('customers'));
    }

    private function validated(): array
    {
        $phone = local_phone((string)($_POST['phone'] ?? ''));
        $name = trim((string)($_POST['name'] ?? ''));
        if (!$phone || $name === '') {
            flash('danger', 'Name and a valid 10-digit phone number are required.');
            redirect(admin_url('customers'));
        }
        return [
            'name' => $name,
            'phone' => $phone,
            'whatsapp' => local_phone((string)($_POST['whatsapp'] ?? '')) ?: $phone,
            'email' => trim((string)($_POST['email'] ?? '')) ?: null,
            'address' => trim((string)($_POST['address'] ?? '')) ?: null,
            'pincode' => trim((string)($_POST['pincode'] ?? '')) ?: null,
            'gstin' => trim((string)($_POST['gstin'] ?? '')) ?: null,
            'customer_type' => in_array($_POST['customer_type'] ?? '', ['retail', 'dealer', 'corporate'], true)
                ? $_POST['customer_type'] : 'retail',
            'price_group_id' => !empty($_POST['price_group_id']) ? (int)$_POST['price_group_id'] : null,
            'notes' => trim((string)($_POST['notes'] ?? '')) ?: null,
            'is_blocked' => !empty($_POST['is_blocked']) ? 1 : 0,
        ];
    }

    // ------------------------------------------------------- contacts (people)

    /** Add another person, with their own number, to this account. */
    public function addContact(string $id): void
    {
        Acl::require('customer.edit');
        $customer = $this->find((int)$id);
        try {
            CustomerBook::addContact((int)$id, [
                'name' => $_POST['contact_name'] ?? '',
                'phone' => $_POST['contact_phone'] ?? '',
                'whatsapp' => $_POST['contact_whatsapp'] ?? '',
                'designation' => $_POST['contact_designation'] ?? '',
            ]);
            Logger::activity('customer', 'contact_add', 'customer', (int)$id,
                'Contact added to ' . $customer['name']);
            flash('success', 'Contact added.');
        } catch (\Throwable $e) {
            flash('danger', $e->getMessage());
        }
        redirect(admin_url('customers/' . $id . '/edit'));
    }

    public function updateContact(string $id, string $contactId): void
    {
        Acl::require('customer.edit');
        $this->find((int)$id);
        $contact = CustomerBook::contact((int)$contactId);
        if (!$contact || (int)$contact['customer_id'] !== (int)$id) {
            flash('danger', 'That contact does not belong to this customer.');
            redirect(admin_url('customers/' . $id . '/edit'));
        }
        try {
            CustomerBook::updateContact((int)$contactId, [
                'name' => $_POST['name'] ?? $contact['name'],
                'phone' => $_POST['phone'] ?? $contact['phone'],
                'whatsapp' => $_POST['whatsapp'] ?? $contact['whatsapp'],
                'designation' => $_POST['designation'] ?? $contact['designation'],
            ]);
            if (!empty($_POST['make_primary'])) {
                CustomerBook::makePrimary((int)$contactId);
            }
            flash('success', 'Contact saved.');
        } catch (\Throwable $e) {
            flash('danger', $e->getMessage());
        }
        redirect(admin_url('customers/' . $id . '/edit'));
    }

    public function deleteContact(string $id, string $contactId): void
    {
        Acl::require('customer.edit');
        $this->find((int)$id);
        $contact = CustomerBook::contact((int)$contactId);
        if (!$contact || (int)$contact['customer_id'] !== (int)$id) {
            flash('danger', 'That contact does not belong to this customer.');
            redirect(admin_url('customers/' . $id . '/edit'));
        }
        try {
            CustomerBook::removeContact((int)$contactId);
            Logger::activity('customer', 'contact_delete', 'customer', (int)$id, 'Contact removed');
            flash('success', 'Contact removed. Their past orders stay on this customer.');
        } catch (\Throwable $e) {
            flash('danger', $e->getMessage());
        }
        redirect(admin_url('customers/' . $id . '/edit'));
    }

    private function find(int $id): array
    {
        $customer = DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE id = ? AND deleted_at IS NULL', [$id]);
        if (!$customer) {
            abort(404, 'Customer not found.');
        }
        return $customer;
    }
}
