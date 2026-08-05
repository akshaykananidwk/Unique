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
    /**
     * This is a single shop. Branches were a chain feature and are gone from every screen —
     * one Main Branch is all there is, so these helpers simply answer "yes" and stay in
     * place because orders, payments and job numbers still hang off branch_id.
     */
    public static function branchIds(): array
    {
        return array_map('intval', array_column(
            DB::all('SELECT id FROM `' . tbl('branches') . '` WHERE is_active = 1'),
            'id'
        ));
    }

    /** The one active branch every new record belongs to. */
    public static function mainBranchId(): int
    {
        return (int)DB::val('SELECT id FROM `' . tbl('branches') . '` WHERE is_active = 1 ORDER BY sort_order, id LIMIT 1');
    }

    public static function canAccessBranch(int $branchId): bool
    {
        return true;
    }

    public static function requireBranch(int $branchId): void
    {
        // Nothing to check with a single branch.
    }

    /** SQL fragment + params limiting a query to accessible branches. Always everything now. */
    public static function branchFilter(string $column): array
    {
        return ['1=1', []];
    }
}
