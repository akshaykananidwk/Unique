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

    /**
     * Kept so every list query keeps its shape, but this is a single shop: there is one
     * Main Branch, nothing to choose between, and no ?branch_id to honour.
     *
     * @return array{0:string,1:array,2:null}
     */
    protected function branchScope(string $column): array
    {
        return ['1=1', [], null];
    }
}
