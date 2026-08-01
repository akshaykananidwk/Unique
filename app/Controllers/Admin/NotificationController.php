<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\DB;

class NotificationController extends Controller
{
    public function index(): void
    {
        $rows = DB::all(
            'SELECT * FROM `' . tbl('notifications') . '` WHERE user_id = ? ORDER BY id DESC LIMIT 100',
            [(int)$this->user['id']]
        );
        $this->render('dashboard/notifications', compact('rows'));
    }

    public function markRead(): void
    {
        DB::run(
            'UPDATE `' . tbl('notifications') . '` SET is_read = 1 WHERE user_id = ?',
            [(int)$this->user['id']]
        );
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
            json_response(['ok' => true]);
        }
        redirect(admin_url('notifications'));
    }
}
