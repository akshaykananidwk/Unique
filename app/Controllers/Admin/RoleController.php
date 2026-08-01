<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\DB;
use App\Core\Logger;

class RoleController extends Controller
{
    public function index(): void
    {
        Acl::require('role.manage');
        $roles = DB::all(
            'SELECT r.*, (SELECT COUNT(*) FROM `' . tbl('users') . '` u WHERE u.role_id = r.id AND u.deleted_at IS NULL) AS user_count
             FROM `' . tbl('roles') . '` r ORDER BY r.id'
        );
        $permissions = DB::all('SELECT * FROM `' . tbl('permissions') . '` ORDER BY module, code');
        $matrix = [];
        foreach (DB::all('SELECT role_id, permission_id FROM `' . tbl('role_permissions') . '`') as $rp) {
            $matrix[(int)$rp['role_id']][(int)$rp['permission_id']] = true;
        }
        $this->render('users/roles', compact('roles', 'permissions', 'matrix'));
    }

    public function store(): void
    {
        Acl::require('role.manage');
        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            flash('danger', 'Role name is required.');
            redirect(admin_url('roles'));
        }
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '_', $name) ?? '', '_'));
        if (DB::get('SELECT id FROM `' . tbl('roles') . '` WHERE slug = ?', [$slug])) {
            flash('danger', 'A role with this name already exists.');
            redirect(admin_url('roles'));
        }
        $id = DB::insert('roles', [
            'name' => $name,
            'slug' => $slug,
            'is_system' => 0,
            'description' => trim((string)($_POST['description'] ?? '')) ?: null,
        ]);
        Logger::activity('role', 'create', 'role', $id, "Role $name created");
        flash('success', 'Role created — now tick its permissions.');
        redirect(admin_url('roles'));
    }

    public function savePermissions(string $id): void
    {
        Acl::require('role.manage');
        $role = DB::get('SELECT * FROM `' . tbl('roles') . '` WHERE id = ?', [(int)$id]);
        if (!$role) {
            abort(404, 'Role not found.');
        }
        if ($role['slug'] === 'super_admin') {
            flash('danger', 'Super Admin always has every permission.');
            redirect(admin_url('roles'));
        }
        $ids = array_map('intval', (array)($_POST['permission_ids'] ?? []));
        DB::transaction(function () use ($id, $ids) {
            DB::delete('role_permissions', ['role_id' => (int)$id]);
            foreach ($ids as $pid) {
                if ($pid > 0) {
                    DB::run('INSERT IGNORE INTO `' . tbl('role_permissions') . '` (role_id, permission_id) VALUES (?,?)', [(int)$id, $pid]);
                }
            }
        });
        Logger::activity('role', 'permissions', 'role', (int)$id, 'Permissions updated for ' . $role['name']);
        flash('success', 'Permissions saved. Users get them on their next page load.');
        redirect(admin_url('roles'));
    }

    public function delete(string $id): void
    {
        Acl::require('role.manage');
        $role = DB::get('SELECT * FROM `' . tbl('roles') . '` WHERE id = ?', [(int)$id]);
        if (!$role) {
            abort(404, 'Role not found.');
        }
        if ((int)$role['is_system'] === 1) {
            flash('danger', 'Built-in roles cannot be deleted.');
            redirect(admin_url('roles'));
        }
        $count = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('users') . '` WHERE role_id = ? AND deleted_at IS NULL', [(int)$id]);
        if ($count > 0) {
            flash('danger', 'Reassign the users in this role first.');
            redirect(admin_url('roles'));
        }
        DB::delete('role_permissions', ['role_id' => (int)$id]);
        DB::delete('roles', ['id' => (int)$id]);
        Logger::activity('role', 'delete', 'role', (int)$id, 'Role ' . $role['name'] . ' deleted');
        flash('success', 'Role deleted.');
        redirect(admin_url('roles'));
    }
}
