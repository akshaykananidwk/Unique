<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\DB;
use App\Core\View;

class HomeController
{
    public function index(): void
    {
        $categories = DB::all(
            'SELECT * FROM `' . tbl('categories') . '` WHERE is_active = 1 AND show_on_public = 1 ORDER BY sort_order LIMIT 8'
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
