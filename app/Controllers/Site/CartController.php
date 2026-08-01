<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Uploader;
use App\Core\View;
use App\Models\Pricing;

class CartController
{
    public function index(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        $total = array_sum(array_map(fn($line) => (float)$line['amount'], $cart));
        View::render('public/cart', compact('cart', 'total'), 'layouts/public');
    }

    public function add(): void
    {
        Csrf::check();
        $item = DB::get(
            'SELECT * FROM `' . tbl('items') . '` WHERE id = ? AND is_active = 1 AND show_on_public = 1 AND deleted_at IS NULL',
            [(int)($_POST['item_id'] ?? 0)]
        );
        if (!$item) {
            flash('danger', 'Product not found.');
            redirect(base_url('products'));
        }
        $qty = max((float)$item['min_qty'], (float)($_POST['qty'] ?? 1));

        // Collect option values as spec
        $spec = [];
        $options = DB::all('SELECT * FROM `' . tbl('item_options') . '` WHERE item_id = ? ORDER BY sort_order', [(int)$item['id']]);
        foreach ($options as $option) {
            $key = 'opt_' . $option['field_key'];
            if ($option['field_type'] === 'checkbox') {
                $value = array_map('strval', (array)($_POST[$key] ?? []));
            } else {
                $value = $_POST[$key] ?? '';
            }
            if ((int)$option['is_required'] === 1 && ($value === '' || $value === [])
                && $option['field_type'] !== 'file') {
                flash('danger', 'Please fill "' . $option['label'] . '".');
                redirect(base_url('product/' . strtolower((string)$item['sku'])));
            }
            if ($value !== '' && $value !== []) {
                $spec[$option['field_key']] = $value;
            }
        }

        $calc = Pricing::calculate($item, $qty, $spec, 0.0);
        $line = [
            'key' => bin2hex(random_bytes(6)),
            'item_id' => (int)$item['id'],
            'name' => $item['name'],
            'sku' => $item['sku'],
            'qty' => $qty,
            'unit' => $item['unit'],
            'rate' => $calc['rate'],
            'amount' => $calc['amount'],
            'spec' => $spec,
            'spec_text' => $calc['spec_text'],
            'file' => null,
        ];

        if ((int)$item['allow_customer_file_upload'] === 1 && !empty($_FILES['artwork']['name'])) {
            $stored = Uploader::store($_FILES['artwork'], 'artwork/' . date('Y/m'), Uploader::ARTWORK, 15);
            if ($stored['ok']) {
                $line['file'] = $stored;
            }
        }

        $_SESSION['cart'][$line['key']] = $line;
        flash('success', $item['name'] . ' added to your order.');
        redirect(base_url('cart'));
    }

    public function remove(): void
    {
        Csrf::check();
        $key = (string)($_POST['key'] ?? '');
        unset($_SESSION['cart'][$key]);
        flash('success', 'Item removed.');
        redirect(base_url('cart'));
    }
}
