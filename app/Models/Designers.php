<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

/**
 * Who can design.
 *
 * There is no "designer role" any more. A designer is simply anyone whose role carries the
 * "Upload design proofs" permission — so if the design staff are tied up and the owner does
 * a job themselves, it is their job, it appears against their name, and it counts in their
 * month. Give the permission to a role on the Roles screen and everyone in it shows up
 * wherever designers are listed. No code change, no special role.
 *
 * One rule, one place: every dropdown, every board and every report asks this class.
 */
class Designers
{
    /** Holding this permission is what makes somebody a designer. */
    public const PERMISSION = 'design.upload';

    /**
     * SQL that is true for a user who may design. Written as a subquery so it can be dropped
     * into any WHERE clause without another join.
     */
    public static function sqlCanDesign(string $userAlias = 'u'): string
    {
        return 'EXISTS (SELECT 1 FROM `' . tbl('role_permissions') . '` rp
                        JOIN `' . tbl('permissions') . "` p ON p.id = rp.permission_id
                        WHERE rp.role_id = $userAlias.role_id AND p.code = '" . self::PERMISSION . "')";
    }

    /**
     * Everyone who can design right now, for the assign dropdowns.
     *
     * @return array<int,array{id:int,name:string,role_name:string,open_jobs:int}>
     */
    public static function all(): array
    {
        return DB::all(
            'SELECT u.id, u.name, r.name AS role_name,
                    (SELECT COUNT(*) FROM `' . tbl('order_items') . "` oi
                     WHERE oi.assigned_designer_id = u.id
                       AND oi.status IN ('design_pending','design_in_progress','proof_sent','change_requested')
                    ) AS open_jobs
             FROM `" . tbl('users') . '` u
             JOIN `' . tbl('roles') . '` r ON r.id = u.role_id
             WHERE u.is_active = 1 AND u.deleted_at IS NULL AND ' . self::sqlCanDesign('u') . '
             ORDER BY u.name'
        );
    }

    public static function canDesign(int $userId): bool
    {
        return (bool)DB::val(
            'SELECT 1 FROM `' . tbl('users') . '` u
             WHERE u.id = ? AND u.is_active = 1 AND u.deleted_at IS NULL AND ' . self::sqlCanDesign('u'),
            [$userId]
        );
    }

    /**
     * Who a report should cover: everyone who can design today, plus anyone who has actually
     * done design work — so somebody whose role changed later does not vanish from last
     * month's figures.
     */
    public static function sqlReportPool(string $userAlias = 'u'): string
    {
        $oi = tbl('order_items');
        $dp = tbl('design_proofs');
        return '(' . self::sqlCanDesign($userAlias) . "
                 OR EXISTS (SELECT 1 FROM `$oi` oi2 WHERE oi2.assigned_designer_id = $userAlias.id)
                 OR EXISTS (SELECT 1 FROM `$dp` dp2 WHERE dp2.uploaded_by_user_id = $userAlias.id))";
    }

    /** The least-loaded designer, for auto-assignment. Null when nobody can design. */
    public static function leastLoaded(): ?int
    {
        $rows = self::all();
        if (!$rows) {
            return null;
        }
        usort($rows, fn($a, $b) => (int)$a['open_jobs'] <=> (int)$b['open_jobs']);
        return (int)$rows[0]['id'];
    }
}
