<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\DB;
use App\Core\Logger;
use App\Core\Uploader;

class CatalogController extends Controller
{
    // ---------------------------------------------------------------- Categories

    public function categories(): void
    {
        Acl::require('category.manage');
        $categories = DB::all(
            'SELECT c.*, (SELECT COUNT(*) FROM `' . tbl('items') . '` i WHERE i.category_id = c.id AND i.deleted_at IS NULL) AS item_count
             FROM `' . tbl('categories') . '` c ORDER BY c.sort_order, c.name'
        );
        foreach ($categories as &$category) {
            $category['components'] = DB::all(
                'SELECT * FROM `' . tbl('category_components') . '` WHERE category_id = ? ORDER BY sort_order, name',
                [(int)$category['id']]
            );
        }
        unset($category);
        $this->render('catalog/categories', compact('categories'));
    }

    public function storeCategory(): void
    {
        Acl::require('category.manage');
        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            flash('danger', 'Category name is required.');
            redirect(admin_url('categories'));
        }
        $slug = $this->slugify($name, 'categories');
        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $stored = Uploader::store($_FILES['image'], 'logos', Uploader::IMAGES, 5);
            if ($stored['ok']) {
                $image = $stored['path'];
            }
        }
        $id = DB::insert('categories', [
            'name' => $name,
            'slug' => $slug,
            'image' => $image,
            'icon' => trim((string)($_POST['icon'] ?? '')) ?: null,
            'description' => trim((string)($_POST['description'] ?? '')) ?: null,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'show_on_public' => !empty($_POST['show_on_public']) ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Logger::activity('catalog', 'create', 'category', $id, "Category $name created");
        flash('success', 'Category added.');
        redirect(admin_url('categories'));
    }

    public function updateCategory(string $id): void
    {
        Acl::require('category.manage');
        $category = DB::get('SELECT * FROM `' . tbl('categories') . '` WHERE id = ?', [(int)$id]);
        if (!$category) {
            abort(404, 'Category not found.');
        }
        $updates = [
            'name' => trim((string)($_POST['name'] ?? $category['name'])),
            'icon' => trim((string)($_POST['icon'] ?? '')) ?: null,
            'description' => trim((string)($_POST['description'] ?? '')) ?: null,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'show_on_public' => !empty($_POST['show_on_public']) ? 1 : 0,
            // How this category's lines are worked out, and what GST they carry.
            'calc_mode' => in_array($_POST['calc_mode'] ?? '', ['simple', 'sqft', 'mixed'], true)
                ? $_POST['calc_mode'] : ($category['calc_mode'] ?? 'simple'),
            'tax_percent' => max(0, min(100, (float)($_POST['tax_percent'] ?? 0))),
            'requires_design' => !empty($_POST['requires_design']) ? 1 : 0,
            'updated_at' => now(),
        ];
        if (!empty($_FILES['image']['name'])) {
            $stored = Uploader::store($_FILES['image'], 'logos', Uploader::IMAGES, 5);
            if ($stored['ok']) {
                $updates['image'] = $stored['path'];
            }
        }
        DB::update('categories', $updates, ['id' => (int)$id]);
        Logger::activity('catalog', 'update', 'category', (int)$id, 'Category updated');
        flash('success', 'Category updated.');
        redirect(admin_url('categories'));
    }

    /**
     * Replace a category's component presets from the inline editor.
     * Adding, renaming, reordering or removing a component is pure data — no code change.
     */
    public function saveComponents(string $id): void
    {
        Acl::require('category.manage');
        $category = DB::get('SELECT * FROM `' . tbl('categories') . '` WHERE id = ?', [(int)$id]);
        if (!$category) {
            abort(404, 'Category not found.');
        }
        $names = (array)($_POST['comp_name'] ?? []);
        $modes = (array)($_POST['comp_mode'] ?? []);
        $units = (array)($_POST['comp_unit'] ?? []);

        DB::transaction(function () use ($id, $names, $modes, $units) {
            DB::delete('category_components', ['category_id' => (int)$id]);
            $sort = 0;
            $seen = [];
            foreach ($names as $i => $name) {
                $name = trim((string)$name);
                if ($name === '' || isset($seen[mb_strtolower($name)])) {
                    continue; // blank row, or the same component twice
                }
                $seen[mb_strtolower($name)] = true;
                DB::insert('category_components', [
                    'category_id' => (int)$id,
                    'name' => mb_substr($name, 0, 120),
                    'calc_mode' => (($modes[$i] ?? 'simple') === 'sqft') ? 'sqft' : 'simple',
                    'unit' => mb_substr(trim((string)($units[$i] ?? 'pcs')) ?: 'pcs', 0, 20),
                    'sort_order' => ++$sort,
                    'is_active' => 1,
                ]);
            }
        });
        // A category holding any foot x foot component is a mixed one.
        $hasSqft = (int)DB::val(
            "SELECT COUNT(*) FROM `" . tbl('category_components') . "` WHERE category_id = ? AND calc_mode = 'sqft'",
            [(int)$id]
        ) > 0;
        $count = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('category_components') . '` WHERE category_id = ?', [(int)$id]);
        if ($count > 0) {
            DB::update('categories', ['calc_mode' => $hasSqft ? 'mixed' : $category['calc_mode'], 'updated_at' => now()], ['id' => (int)$id]);
        }
        Logger::activity('catalog', 'components', 'category', (int)$id,
            'Components updated for ' . $category['name'] . ' (' . $count . ')');
        flash('success', 'Components saved for “' . $category['name'] . '”.');
        redirect(admin_url('categories'));
    }

    public function deleteCategory(string $id): void
    {
        Acl::require('category.manage');
        $category = DB::get('SELECT * FROM `' . tbl('categories') . '` WHERE id = ?', [(int)$id]);
        if (!$category) {
            abort(404, 'Category not found.');
        }
        // Deleting a category takes its items with it. Items are soft-deleted so past orders
        // (which keep their own name/price snapshots) stay intact and reports still add up.
        $count = (int)DB::val(
            'SELECT COUNT(*) FROM `' . tbl('items') . '` WHERE category_id = ? AND deleted_at IS NULL',
            [(int)$id]
        );
        DB::transaction(function () use ($id) {
            DB::run(
                'UPDATE `' . tbl('items') . '` SET deleted_at = ?, updated_at = ? WHERE category_id = ? AND deleted_at IS NULL',
                [now(), now(), (int)$id]
            );
            DB::delete('categories', ['id' => (int)$id]);
        });
        Logger::activity('catalog', 'delete', 'category', (int)$id,
            'Category ' . $category['name'] . ' deleted with ' . $count . ' item(s)');
        flash('success', 'Category “' . $category['name'] . '” deleted'
            . ($count > 0 ? ' along with ' . $count . ' item' . ($count === 1 ? '' : 's') . '.' : '.'));
        redirect(admin_url('categories'));
    }

    // ---------------------------------------------------------------- Items

    public function items(): void
    {
        Acl::require('item.manage');
        $items = DB::all(
            'SELECT i.*, c.name AS category_name,
                (SELECT COUNT(*) FROM `' . tbl('item_options') . '` io WHERE io.item_id = i.id) AS option_count
             FROM `' . tbl('items') . '` i JOIN `' . tbl('categories') . '` c ON c.id = i.category_id
             WHERE i.deleted_at IS NULL ORDER BY c.sort_order, i.sort_order, i.name'
        );
        $this->render('catalog/items', compact('items'));
    }

    public function createItem(): void
    {
        Acl::require('item.manage');
        $categories = DB::all('SELECT * FROM `' . tbl('categories') . '` ORDER BY sort_order, name');
        $this->render('catalog/item_form', ['item' => null, 'categories' => $categories, 'options' => [], 'slabs' => []]);
    }

    public function storeItem(): void
    {
        Acl::require('item.manage');
        $data = $this->itemData();
        $data['created_at'] = now();
        $id = DB::insert('items', $data);
        Logger::activity('catalog', 'create', 'item', $id, 'Item ' . $data['name'] . ' created');
        flash('success', 'Item created — now add its options below.');
        redirect(admin_url('items/' . $id . '/edit'));
    }

    public function editItem(string $id): void
    {
        Acl::require('item.manage');
        $item = DB::get('SELECT * FROM `' . tbl('items') . '` WHERE id = ? AND deleted_at IS NULL', [(int)$id]);
        if (!$item) {
            abort(404, 'Item not found.');
        }
        $categories = DB::all('SELECT * FROM `' . tbl('categories') . '` ORDER BY sort_order, name');
        $options = DB::all('SELECT * FROM `' . tbl('item_options') . '` WHERE item_id = ? ORDER BY sort_order, id', [(int)$id]);
        foreach ($options as &$option) {
            $option['values'] = DB::all(
                'SELECT * FROM `' . tbl('item_option_values') . '` WHERE option_id = ? ORDER BY sort_order, id',
                [(int)$option['id']]
            );
        }
        unset($option);
        $slabs = DB::all('SELECT * FROM `' . tbl('price_slabs') . '` WHERE item_id = ? ORDER BY min_qty', [(int)$id]);
        $this->render('catalog/item_form', compact('item', 'categories', 'options', 'slabs'));
    }

    public function updateItem(string $id): void
    {
        Acl::require('item.manage');
        $item = DB::get('SELECT * FROM `' . tbl('items') . '` WHERE id = ? AND deleted_at IS NULL', [(int)$id]);
        if (!$item) {
            abort(404, 'Item not found.');
        }
        $data = $this->itemData((int)$id);
        DB::update('items', $data, ['id' => (int)$id]);
        Logger::activity('catalog', 'update', 'item', (int)$id, 'Item ' . $data['name'] . ' updated');
        flash('success', 'Item updated.');
        redirect(admin_url('items/' . $id . '/edit'));
    }

    public function deleteItem(string $id): void
    {
        Acl::require('item.manage');
        DB::update('items', ['deleted_at' => now()], ['id' => (int)$id]);
        Logger::activity('catalog', 'delete', 'item', (int)$id, 'Item deleted (soft)');
        flash('success', 'Item deleted.');
        redirect(admin_url('items'));
    }

    /** Replace an item's option set from the JSON editor payload. */
    public function saveOptions(string $id): void
    {
        Acl::require('item.manage');
        $item = DB::get('SELECT * FROM `' . tbl('items') . '` WHERE id = ? AND deleted_at IS NULL', [(int)$id]);
        if (!$item) {
            abort(404, 'Item not found.');
        }
        $payload = json_decode((string)($_POST['options_json'] ?? '[]'), true);
        if (!is_array($payload)) {
            flash('danger', 'Invalid options data.');
            redirect(admin_url('items/' . $id . '/edit'));
        }
        DB::transaction(function () use ($id, $payload) {
            $oldIds = array_column(
                DB::all('SELECT id FROM `' . tbl('item_options') . '` WHERE item_id = ?', [(int)$id]),
                'id'
            );
            foreach ($oldIds as $oldId) {
                DB::delete('item_option_values', ['option_id' => (int)$oldId]);
            }
            DB::delete('item_options', ['item_id' => (int)$id]);
            foreach (array_values($payload) as $index => $opt) {
                $fieldKey = preg_replace('/[^a-z0-9_]/', '', strtolower((string)($opt['field_key'] ?? '')));
                if ($fieldKey === '' || empty($opt['label'])) {
                    continue;
                }
                $optionId = DB::insert('item_options', [
                    'item_id' => (int)$id,
                    'label' => substr((string)$opt['label'], 0, 100),
                    'field_key' => $fieldKey,
                    'field_type' => in_array($opt['field_type'] ?? '', ['text', 'number', 'select', 'radio', 'checkbox', 'textarea', 'date', 'file', 'color'], true)
                        ? $opt['field_type'] : 'text',
                    'is_required' => !empty($opt['is_required']) ? 1 : 0,
                    'help_text' => substr((string)($opt['help_text'] ?? ''), 0, 255) ?: null,
                    'sort_order' => $index,
                    'affects_price' => !empty($opt['affects_price']) ? 1 : 0,
                ]);
                foreach (array_values($opt['values'] ?? []) as $vIndex => $value) {
                    if (empty($value['label'])) {
                        continue;
                    }
                    DB::insert('item_option_values', [
                        'option_id' => $optionId,
                        'label' => substr((string)$value['label'], 0, 100),
                        'price_delta' => (float)($value['price_delta'] ?? 0),
                        'price_mode' => in_array($value['price_mode'] ?? '', ['add', 'multiply', 'replace'], true)
                            ? $value['price_mode'] : 'add',
                        'is_default' => !empty($value['is_default']) ? 1 : 0,
                        'sort_order' => $vIndex,
                    ]);
                }
            }
        });
        Logger::activity('catalog', 'options', 'item', (int)$id, 'Item options saved');
        flash('success', 'Options saved. The order forms update immediately.');
        redirect(admin_url('items/' . $id . '/edit'));
    }

    public function saveSlabs(string $id): void
    {
        Acl::require('price.manage');
        $payload = json_decode((string)($_POST['slabs_json'] ?? '[]'), true);
        if (!is_array($payload)) {
            flash('danger', 'Invalid slab data.');
            redirect(admin_url('items/' . $id . '/edit'));
        }
        DB::transaction(function () use ($id, $payload) {
            DB::delete('price_slabs', ['item_id' => (int)$id]);
            foreach ($payload as $slab) {
                $min = (int)($slab['min_qty'] ?? 0);
                $rate = (float)($slab['rate'] ?? 0);
                if ($min <= 0 || $rate <= 0) {
                    continue;
                }
                DB::insert('price_slabs', [
                    'item_id' => (int)$id,
                    'min_qty' => $min,
                    'max_qty' => !empty($slab['max_qty']) ? (int)$slab['max_qty'] : null,
                    'rate' => $rate,
                ]);
            }
        });
        Logger::activity('catalog', 'slabs', 'item', (int)$id, 'Price slabs saved');
        flash('success', 'Price slabs saved.');
        redirect(admin_url('items/' . $id . '/edit'));
    }

    private function itemData(?int $ignoreId = null): array
    {
        $name = trim((string)($_POST['name'] ?? ''));
        $sku = strtoupper(trim((string)($_POST['sku'] ?? '')));
        if ($name === '' || $sku === '' || empty($_POST['category_id'])) {
            flash('danger', 'Name, SKU and category are required.');
            redirect(admin_url('items'));
        }
        $dupe = DB::get(
            'SELECT id FROM `' . tbl('items') . '` WHERE sku = ?' . ($ignoreId ? ' AND id != ' . $ignoreId : ''),
            [$sku]
        );
        if ($dupe) {
            flash('danger', 'SKU already in use.');
            redirect(admin_url('items'));
        }
        $data = [
            'category_id' => (int)$_POST['category_id'],
            'name' => $name,
            'sku' => $sku,
            'short_description' => trim((string)($_POST['short_description'] ?? '')) ?: null,
            'description' => trim((string)($_POST['description'] ?? '')) ?: null,
            'unit' => trim((string)($_POST['unit'] ?? 'pcs')) ?: 'pcs',
            'pricing_type' => in_array($_POST['pricing_type'] ?? '', ['fixed', 'per_unit', 'slab', 'area'], true)
                ? $_POST['pricing_type'] : 'per_unit',
            'base_price' => (float)($_POST['base_price'] ?? 0),
            'min_qty' => max(1, (int)($_POST['min_qty'] ?? 1)),
            'step_qty' => max(1, (int)($_POST['step_qty'] ?? 1)),
            'tax_percent' => (float)($_POST['tax_percent'] ?? 0),
            'requires_design' => !empty($_POST['requires_design']) ? 1 : 0,
            'default_turnaround_hours' => max(1, (int)($_POST['default_turnaround_hours'] ?? 24)),
            'show_on_public' => !empty($_POST['show_on_public']) ? 1 : 0,
            'allow_customer_file_upload' => !empty($_POST['allow_customer_file_upload']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'updated_at' => now(),
        ];
        if (!empty($_FILES['image']['name'])) {
            $stored = Uploader::store($_FILES['image'], 'logos', Uploader::IMAGES, 5);
            if ($stored['ok']) {
                $data['image'] = $stored['path'];
            }
        }
        return $data;
    }

    private function slugify(string $name, string $table): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name) ?? '', '-'));
        $base = $slug;
        $i = 2;
        while (DB::get('SELECT id FROM `' . tbl($table) . '` WHERE slug = ?', [$slug])) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
