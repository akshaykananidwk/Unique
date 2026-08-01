<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\DB;
use App\Core\View;

class ProductController
{
    public function index(): void
    {
        $categories = DB::all(
            'SELECT * FROM `' . tbl('categories') . '` WHERE is_active = 1 AND show_on_public = 1 ORDER BY sort_order'
        );
        foreach ($categories as &$category) {
            $category['items'] = DB::all(
                'SELECT * FROM `' . tbl('items') . '` WHERE category_id = ? AND is_active = 1 AND show_on_public = 1
                 AND deleted_at IS NULL ORDER BY sort_order, name',
                [(int)$category['id']]
            );
        }
        unset($category);
        View::render('public/products', compact('categories'), 'layouts/public');
    }

    public function show(string $slug): void
    {
        // Slug here is the item SKU (lowercased) for a stable public URL
        $item = DB::get(
            'SELECT i.*, c.name AS category_name FROM `' . tbl('items') . '` i
             JOIN `' . tbl('categories') . '` c ON c.id = i.category_id
             WHERE LOWER(i.sku) = LOWER(?) AND i.is_active = 1 AND i.show_on_public = 1 AND i.deleted_at IS NULL',
            [$slug]
        );
        if (!$item) {
            abort(404, 'Product not found.');
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
        $slabs = DB::all('SELECT * FROM `' . tbl('price_slabs') . '` WHERE item_id = ? ORDER BY min_qty', [(int)$item['id']]);
        View::render('public/product', compact('item', 'options', 'slabs'), 'layouts/public');
    }
}
