<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\DB;
use App\Core\Logger;

class UserController extends Controller
{
    public function index(): void
    {
        Acl::require('user.manage');
        $users = DB::all(
            'SELECT u.*, r.name AS role_name, b.name AS branch_name
             FROM `' . tbl('users') . '` u
             JOIN `' . tbl('roles') . '` r ON r.id = u.role_id
             LEFT JOIN `' . tbl('branches') . '` b ON b.id = u.primary_branch_id
             WHERE u.deleted_at IS NULL ORDER BY u.name'
        );
        $this->render('users/index', compact('users'));
    }

    public function create(): void
    {
        Acl::require('user.manage');
        $this->render('users/form', [
            'staff' => null,
            'roles' => DB::all('SELECT * FROM `' . tbl('roles') . "` WHERE slug != 'customer' ORDER BY name"),
            'branches' => DB::all('SELECT * FROM `' . tbl('branches') . '` WHERE is_active = 1 ORDER BY sort_order'),
            'userBranches' => [],
        ]);
    }

    public function store(): void
    {
        Acl::require('user.manage');
        $data = $this->validated();
        if ($data === null) {
            redirect(admin_url('users/create'));
        }
        $password = (string)($_POST['password'] ?? '');
        if (strlen($password) < 8) {
            flash('danger', 'Password must be at least 8 characters.');
            redirect(admin_url('users/create'));
        }
        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        $data['created_at'] = now();
        $id = DB::insert('users', $data);
        $this->syncBranches($id);
        Logger::activity('user', 'create', 'user', $id, 'User ' . $data['name'] . ' created');
        flash('success', 'User created.');
        redirect(admin_url('users'));
    }

    public function edit(string $id): void
    {
        Acl::require('user.manage');
        $staff = DB::get('SELECT * FROM `' . tbl('users') . '` WHERE id = ? AND deleted_at IS NULL', [(int)$id]);
        if (!$staff) {
            abort(404, 'User not found.');
        }
        $this->render('users/form', [
            'staff' => $staff,
            'roles' => DB::all('SELECT * FROM `' . tbl('roles') . "` WHERE slug != 'customer' ORDER BY name"),
            'branches' => DB::all('SELECT * FROM `' . tbl('branches') . '` WHERE is_active = 1 ORDER BY sort_order'),
            'userBranches' => array_map('intval', array_column(
                DB::all('SELECT branch_id FROM `' . tbl('user_branches') . '` WHERE user_id = ?', [(int)$id]),
                'branch_id'
            )),
        ]);
    }

    public function update(string $id): void
    {
        Acl::require('user.manage');
        $staff = DB::get('SELECT * FROM `' . tbl('users') . '` WHERE id = ? AND deleted_at IS NULL', [(int)$id]);
        if (!$staff) {
            abort(404, 'User not found.');
        }
        $data = $this->validated((int)$id);
        if ($data === null) {
            redirect(admin_url('users/' . $id . '/edit'));
        }
        $password = (string)($_POST['password'] ?? '');
        if ($password !== '') {
            if (strlen($password) < 8) {
                flash('danger', 'Password must be at least 8 characters.');
                redirect(admin_url('users/' . $id . '/edit'));
            }
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $data['updated_at'] = now();
        DB::update('users', $data, ['id' => (int)$id]);
        $this->syncBranches((int)$id);
        Logger::activity('user', 'update', 'user', (int)$id, 'User ' . $data['name'] . ' updated');
        flash('success', 'User updated.');
        redirect(admin_url('users'));
    }

    public function delete(string $id): void
    {
        Acl::require('user.manage');
        if ((int)$id === (int)$this->user['id']) {
            flash('danger', 'You cannot delete your own account.');
            redirect(admin_url('users'));
        }
        DB::update('users', ['deleted_at' => now(), 'is_active' => 0], ['id' => (int)$id]);
        Logger::activity('user', 'delete', 'user', (int)$id, 'User deleted (soft)');
        flash('success', 'User deleted.');
        redirect(admin_url('users'));
    }

    private function validated(?int $ignoreId = null): ?array
    {
        $name = trim((string)($_POST['name'] ?? ''));
        $phone = local_phone((string)($_POST['phone'] ?? ''));
        $roleId = (int)($_POST['role_id'] ?? 0);
        if ($name === '' || !$phone || !$roleId) {
            flash('danger', 'Name, valid phone and role are required.');
            return null;
        }
        $dupe = DB::get(
            'SELECT id FROM `' . tbl('users') . '` WHERE phone = ?' . ($ignoreId ? ' AND id != ' . $ignoreId : ''),
            [$phone]
        );
        if ($dupe) {
            flash('danger', 'Another user already has this phone number.');
            return null;
        }
        return [
            'name' => $name,
            'phone' => $phone,
            'email' => trim((string)($_POST['email'] ?? '')) ?: null,
            'role_id' => $roleId,
            // Single shop — everyone belongs to the one Main Branch.
            'primary_branch_id' => \App\Core\Acl::mainBranchId(),
            'designer_capacity' => $_POST['designer_capacity'] !== '' ? max(1, (int)$_POST['designer_capacity']) : null,
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
        ];
    }

    /** Single shop: everyone is on the one Main Branch, so there is nothing to choose. */
    private function syncBranches(int $userId): void
    {
        DB::delete('user_branches', ['user_id' => $userId]);
        DB::run(
            'INSERT IGNORE INTO `' . tbl('user_branches') . '` (user_id, branch_id) VALUES (?,?)',
            [$userId, \App\Core\Acl::mainBranchId()]
        );
    }
}
