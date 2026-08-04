<?php
declare(strict_types=1);

namespace App\Core;

class Acl
{
    /** Permission codes for the logged-in user's role (cached per session). */
    public static function permissions(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }
        $cacheKey = 'acl_permissions';
        // acl_version is bumped whenever role permissions change (updates, role edits), so a
        // user already signed in picks up new permissions without having to log out first.
        $aclVersion = Settings::getInt('acl_version', 1);
        if (isset($_SESSION[$cacheKey])
            && ($_SESSION['acl_role_id'] ?? 0) === (int)$user['role_id']
            && ($_SESSION['acl_version'] ?? null) === $aclVersion) {
            return $_SESSION[$cacheKey];
        }
        $rows = DB::all(
            'SELECT p.code FROM `' . tbl('role_permissions') . '` rp
             JOIN `' . tbl('permissions') . '` p ON p.id = rp.permission_id
             WHERE rp.role_id = ?',
            [(int)$user['role_id']]
        );
        $codes = array_column($rows, 'code');
        $_SESSION[$cacheKey] = $codes;
        $_SESSION['acl_role_id'] = (int)$user['role_id'];
        $_SESSION['acl_version'] = $aclVersion;
        return $codes;
    }

    public static function can(string $code): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        if ($user['role_slug'] === 'super_admin') {
            return true;
        }
        return in_array($code, self::permissions(), true);
    }

    /** Abort 403 unless the user holds the permission. */
    public static function require(string $code): void
    {
        if (!self::can($code)) {
            Logger::activity('acl', 'denied', null, null, 'Denied permission: ' . $code);
            abort(403, 'You do not have permission to perform this action.');
        }
    }

    /** Branch ids the user may act in (all for super admin / all-branch viewers). */
    public static function branchIds(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }
        if ($user['role_slug'] === 'super_admin' || self::can('order.view_all')) {
            return array_map('intval', array_column(
                DB::all('SELECT id FROM `' . tbl('branches') . '` WHERE is_active = 1'),
                'id'
            ));
        }
        if (isset($_SESSION['acl_branches']) && ($_SESSION['acl_user_id'] ?? 0) === (int)$user['id']) {
            return $_SESSION['acl_branches'];
        }
        $ids = array_map('intval', array_column(
            DB::all('SELECT branch_id FROM `' . tbl('user_branches') . '` WHERE user_id = ?', [(int)$user['id']]),
            'branch_id'
        ));
        if ($user['primary_branch_id'] && !in_array((int)$user['primary_branch_id'], $ids, true)) {
            $ids[] = (int)$user['primary_branch_id'];
        }
        $_SESSION['acl_branches'] = $ids;
        $_SESSION['acl_user_id'] = (int)$user['id'];
        return $ids;
    }

    public static function canAccessBranch(int $branchId): bool
    {
        return in_array($branchId, self::branchIds(), true);
    }

    public static function requireBranch(int $branchId): void
    {
        if (!self::canAccessBranch($branchId)) {
            abort(403, 'You do not have access to this branch.');
        }
    }

    /** SQL fragment + params limiting a query to accessible branches. */
    public static function branchFilter(string $column): array
    {
        $ids = self::branchIds();
        if (!$ids) {
            return ['1=0', []];
        }
        $marks = implode(',', array_fill(0, count($ids), '?'));
        return ["$column IN ($marks)", $ids];
    }
}
