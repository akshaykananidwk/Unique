<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\Auth;
use App\Core\View;

abstract class Controller
{
    protected array $user;

    public function __construct()
    {
        $this->user = Auth::requireLogin();
    }

    protected function render(string $view, array $data = []): void
    {
        $data['user'] = $this->user;
        View::render($view, $data, 'layouts/admin');
    }

    protected function isManager(): bool
    {
        return in_array($this->user['role_slug'], ['super_admin', 'branch_manager'], true);
    }

    /** Resolve + authorise the branch filter from ?branch_id. Returns [whereSql, params, selectedBranchId]. */
    protected function branchScope(string $column): array
    {
        $selected = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;
        if ($selected !== null) {
            Acl::requireBranch($selected);
            return ["$column = ?", [$selected], $selected];
        }
        [$sql, $params] = Acl::branchFilter($column);
        return [$sql, $params, null];
    }
}
