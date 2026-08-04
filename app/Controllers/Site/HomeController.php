<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\DB;
use App\Core\View;

class HomeController
{
    public function index(): void
    {
        // Only categories that actually have something to show — never advertise an empty one.
        $categories = DB::all(
            'SELECT c.*, (SELECT COUNT(*) FROM `' . tbl('items') . '` i
                          WHERE i.category_id = c.id AND i.is_active = 1 AND i.show_on_public = 1 AND i.deleted_at IS NULL) AS item_count
             FROM `' . tbl('categories') . '` c WHERE c.is_active = 1 AND c.show_on_public = 1
             HAVING item_count > 0 ORDER BY c.sort_order LIMIT 8'
        );
        $items = DB::all(
            'SELECT i.*, c.slug AS category_slug FROM `' . tbl('items') . '` i
             JOIN `' . tbl('categories') . '` c ON c.id = i.category_id
             WHERE i.is_active = 1 AND i.show_on_public = 1 AND i.deleted_at IS NULL
             ORDER BY i.sort_order LIMIT 8'
        );
        View::render('public/home', compact('categories', 'items'), 'layouts/public');
    }
}
