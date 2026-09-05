<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\DB;
use App\Models\CustomerBook;
use App\Models\Designers;
use App\Core\View;
use App\Models\Pricing;

class ApiController extends Controller
{
    /**
     * ?phone=98765xxxxx → every firm this number is under, with how each is doing.
     *
     * Usually one. When a man runs two firms from one mobile it is two, and the order
     * screen makes him pick which firm the bill is for.
     */
    public function customerLookup(): void
    {
        Acl::require('customer.view');
        $matches = CustomerBook::findAllByPhone((string)($_GET['phone'] ?? ''));
        if (!$matches) {
            json_response(['ok' => true, 'found' => false, 'matches' => []]);
        }

        $out = [];
        foreach ($matches as $contact) {
            $customerId = (int)$contact['customer_id'];
            $customer = DB::get(
                'SELECT id, name, phone, whatsapp, email, address, pincode, gstin, customer_type, is_blocked
                 FROM `' . tbl('customers') . '` WHERE id = ?',
                [$customerId]
            );
            $stats = DB::get(
                'SELECT COUNT(*) AS order_count, COALESCE(SUM(balance_amount),0) AS outstanding
                 FROM `' . tbl('orders') . '` WHERE customer_id = ? AND deleted_at IS NULL AND is_cancelled = 0',
                [$customerId]
            );
            $out[] = [
                'customer' => $customer,
                'contact' => [
                    'id' => (int)$contact['id'],
                    'name' => $contact['name'],
                    'phone' => $contact['phone'],
                    'designation' => $contact['designation'],
                    'is_primary' => (int)$contact['is_primary'],
                ],
                'contact_count' => count(CustomerBook::contacts($customerId)),
                'order_count' => (int)$stats['order_count'],
                'outstanding' => (float)$stats['outstanding'],
            ];
        }

        // First entry stays at the top level so nothing that reads the old shape breaks.
        json_response(['ok' => true, 'found' => true, 'matches' => $out] + $out[0]);
    }

    /** ?q=tata → companies to attach a new number to. */
    public function customerSearch(): void
    {
        Acl::require('customer.view');
        json_response(['ok' => true, 'results' => CustomerBook::search((string)($_GET['q'] ?? ''))]);
    }

    /** A category's question set + how its lines are calculated, for the order form. */
    public function categoryOptions(string $id): void
    {
        Acl::require('order.create');
        $category = DB::get('SELECT * FROM `' . tbl('categories') . '` WHERE id = ? AND is_active = 1', [(int)$id]);
        if (!$category) {
            json_response(['ok' => false, 'error' => 'Category not found'], 404);
        }
        $options = DB::all(
            'SELECT * FROM `' . tbl('category_options') . '` WHERE category_id = ? ORDER BY sort_order, id',
            [(int)$category['id']]
        );
        foreach ($options as &$option) {
            $option['values'] = DB::all(
                'SELECT * FROM `' . tbl('category_option_values') . '` WHERE option_id = ? ORDER BY sort_order, id',
                [(int)$option['id']]
            );
        }
        unset($option);
        // Preset components for build-up jobs (light board, banner + frame + labour…).
        // Each carries its own calculation mode, so one order can mix sq.ft and per-piece lines.
        $components = DB::all(
            'SELECT id, name, calc_mode, unit FROM `' . tbl('category_components') . '`
             WHERE category_id = ? AND is_active = 1 ORDER BY sort_order, name',
            [(int)$category['id']]
        );
        json_response(['ok' => true, 'category' => [
            'id' => (int)$category['id'],
            'name' => $category['name'],
            'calc_mode' => $category['calc_mode'] ?? 'simple',
            // What GST this category's lines get by default: its own, else the shop default.
            'tax_percent' => \App\Models\OrderService::gstPercent([], $category),
            'requires_design' => (int)($category['requires_design'] ?? 1),
        ], 'components' => array_map(fn($c) => [
            'id' => (int)$c['id'],
            'name' => $c['name'],
            'calc_mode' => $c['calc_mode'],
            'unit' => $c['unit'],
        ], $components),
            'html' => View::partial('orders/_category_fields', ['options' => $options])]);
    }

    /** Rendered option fields HTML + item meta for the order form modal. */
    public function itemOptions(string $id): void
    {
        Acl::require('order.create');
        $item = DB::get(
            'SELECT i.*, c.name AS category_name FROM `' . tbl('items') . '` i
             JOIN `' . tbl('categories') . '` c ON c.id = i.category_id
             WHERE i.id = ? AND i.is_active = 1 AND i.deleted_at IS NULL',
            [(int)$id]
        );
        if (!$item) {
            json_response(['ok' => false, 'error' => 'Item not found'], 404);
        }
        $options = DB::all(
            'SELECT * FROM `' . tbl('item_options') . '` WHERE item_id = ? ORDER BY sort_order, id',
            [(int)$item['id']]
        );
        foreach ($options as &$option) {
            $option['values'] = DB::all(
                'SELECT * FROM `' . tbl('item_option_values') . '` WHERE option_id = ? ORDER BY sort_order, id',
                [(int)$option['id']]
            );
        }
        unset($option);
        $html = View::partial('orders/_option_fields', ['options' => $options, 'item' => $item]);
        json_response(['ok' => true, 'item' => [
            'id' => (int)$item['id'],
            'name' => $item['name'],
            'unit' => $item['unit'],
            'pricing_type' => $item['pricing_type'],
            'base_price' => (float)$item['base_price'],
            'min_qty' => (int)$item['min_qty'],
            'step_qty' => (int)$item['step_qty'],
            'tax_percent' => (float)$item['tax_percent'],
            'requires_design' => (int)$item['requires_design'],
            'turnaround_hours' => (int)$item['default_turnaround_hours'],
            'allow_upload' => (int)$item['allow_customer_file_upload'],
        ], 'html' => $html]);
    }

    /** POST item_id, qty, spec{} (+optional customer_id) → server-calculated rate. */
    public function calcPrice(): void
    {
        Acl::require('order.create');
        $body = json_decode((string)file_get_contents('php://input'), true) ?: $_POST;
        $item = DB::get('SELECT * FROM `' . tbl('items') . '` WHERE id = ? AND deleted_at IS NULL', [(int)($body['item_id'] ?? 0)]);
        if (!$item) {
            json_response(['ok' => false, 'error' => 'Item not found'], 404);
        }
        $groupDiscount = 0.0;
        if (!empty($body['customer_id'])) {
            $groupDiscount = (float)(DB::val(
                'SELECT pg.discount_percent FROM `' . tbl('customers') . '` c
                 JOIN `' . tbl('price_groups') . '` pg ON pg.id = c.price_group_id WHERE c.id = ?',
                [(int)$body['customer_id']]
            ) ?? 0);
        }
        $qty = max(1.0, (float)($body['qty'] ?? 1));
        $spec = is_array($body['spec'] ?? null) ? $body['spec'] : [];
        $calc = Pricing::calculate($item, $qty, $spec, $groupDiscount);
        json_response(['ok' => true] + $calc + ['tax_percent' => (float)$item['tax_percent']]);
    }

    /** Global header search: job no / customer name / phone. */
    public function search(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        if (mb_strlen($q) < 2) {
            json_response(['ok' => true, 'results' => []]);
        }
        [$bw, $bp] = Acl::branchFilter('o.branch_id');
        $like = '%' . $q . '%';
        $rows = DB::all(
            'SELECT o.id, o.job_no, o.status, o.total, o.balance_amount, c.name AS customer, c.phone
             FROM `' . tbl('orders') . '` o
             JOIN `' . tbl('customers') . "` c ON c.id = o.customer_id
             WHERE $bw AND o.deleted_at IS NULL
               AND (o.job_no LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)
             ORDER BY o.created_at DESC LIMIT 10",
            [...$bp, $like, $like, $like]
        );
        $results = array_map(fn($r) => [
            'label' => $r['job_no'] . ' — ' . $r['customer'] . ' (' . $r['phone'] . ')',
            'status' => $r['status'],
            'url' => admin_url('orders/' . $r['id']),
        ], $rows);
        json_response(['ok' => true, 'results' => $results]);
    }

    /** Designer dropdown with open-job counts. */
    public function designers(): void
    {
        Acl::require('order.assign');
        $rows = Designers::all();
        json_response(['ok' => true, 'designers' => $rows]);
    }
}
