<?php
declare(strict_types=1);

namespace App\Models;

/** Order-item status state machine. */
class Status
{
    /** Stage ranks — the order status is the LOWEST rank among its items. */
    public const RANKS = [
        'design_pending' => 1,
        'design_in_progress' => 2,
        'proof_sent' => 3,
        'change_requested' => 4,
        'design_approved' => 5,
        'ready_for_print' => 6,
        'printing' => 7,
        'post_press' => 8,
        'ready_for_delivery' => 9,
        'delivered' => 10,
        'completed' => 11,
    ];

    public const SPECIAL = ['cancelled'];

    /** The design loop — items that need no design skip these entirely. */
    public const DESIGN_STAGES = [
        'design_pending', 'design_in_progress', 'proof_sent', 'change_requested', 'design_approved',
    ];

    public const LABELS = [
        'design_pending' => 'Design Pending',
        'design_in_progress' => 'Design In Progress',
        'proof_sent' => 'Proof Sent',
        'change_requested' => 'Changes Requested',
        'design_approved' => 'Design Approved',
        'ready_for_print' => 'Ready for Print',
        'printing' => 'Printing',
        'post_press' => 'Post-press',
        'ready_for_delivery' => 'Ready for Delivery',
        'delivered' => 'Delivered',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /** Bootstrap-ish badge colors. */
    public const COLORS = [
        'design_pending' => 'info',
        'design_in_progress' => 'primary',
        'proof_sent' => 'warning',
        'change_requested' => 'warning',
        'design_approved' => 'success',
        'ready_for_print' => 'primary',
        'printing' => 'primary',
        'post_press' => 'primary',
        'ready_for_delivery' => 'success',
        'delivered' => 'success',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];

    /** Is this one of the design-loop stages? (replaces fragile rank-range checks) */
    public static function isDesignStage(string $status): bool
    {
        return in_array($status, self::DESIGN_STAGES, true);
    }

    public static function label(string $status): string
    {
        return self::LABELS[$status] ?? ucwords(str_replace('_', ' ', $status));
    }

    public static function color(string $status): string
    {
        return self::COLORS[$status] ?? 'secondary';
    }

    public static function rank(string $status): int
    {
        return self::RANKS[$status] ?? 0;
    }

    public static function isFinal(string $status): bool
    {
        return in_array($status, ['delivered', 'completed', 'cancelled'], true);
    }

    /** The canonical next stage on the forward path. */
    public static function next(string $status, bool $requiresDesign): ?string
    {
        $path = $requiresDesign
            ? ['design_pending', 'design_in_progress', 'proof_sent', 'design_approved',
               'ready_for_print', 'printing', 'post_press', 'ready_for_delivery', 'delivered', 'completed']
            : ['ready_for_print', 'printing', 'post_press', 'ready_for_delivery', 'delivered', 'completed'];
        $index = array_search($status, $path, true);
        if ($index === false || $index + 1 >= count($path)) {
            return null;
        }
        return $path[$index + 1];
    }

    /**
     * Is $from → $to a legal transition?
     * @return array{0:bool,1:string} allowed + reason (also flags backward moves needing a note)
     */
    public static function validate(string $from, string $to, bool $requiresDesign, bool $isManager): array
    {
        if ($from === $to) {
            return [false, 'Status is already ' . self::label($to) . '.'];
        }
        if (!isset(self::RANKS[$to]) && !in_array($to, self::SPECIAL, true)) {
            return [false, 'Unknown status.'];
        }
        if ($from === 'cancelled') {
            return [false, 'A cancelled item cannot change status.'];
        }
        if ($to === 'cancelled') {
            return [true, ''];
        }
        // Items that skip the design loop must not enter design stages
        if (!$requiresDesign && self::isDesignStage($to)) {
            return [false, 'This item does not require design.'];
        }
        // A row still sitting on a retired stage (pending / on_hold / quality_check /
        // out_for_delivery) ranks 0, so the forward-move rule below lets it rejoin the flow
        // on its own — without handing out a free pass around the backward-move rules.
        // change_requested loops back to design_in_progress
        if ($from === 'change_requested' && $to === 'design_in_progress') {
            return [true, ''];
        }
        if ($from === 'proof_sent' && in_array($to, ['change_requested', 'design_approved'], true)) {
            return [true, ''];
        }
        $fromRank = self::rank($from);
        $toRank = self::rank($to);
        if ($toRank > $fromRank) {
            return [true, ''];
        }
        // Backward — managers only, and a reason is required (enforced by controller)
        if ($isManager) {
            return [true, 'backward'];
        }
        return [false, 'Only a Branch Manager or Super Admin can move a job backwards.'];
    }

    /** Customer-facing progress percentage (0–100) for an order/item status. */
    public static function progressPercent(string $status, bool $requiresDesign = true): int
    {
        if ($status === 'cancelled') {
            return 0;
        }
        if (in_array($status, ['completed', 'delivered'], true)) {
            return 100;
        }
        $path = $requiresDesign
            ? ['design_pending', 'design_in_progress', 'proof_sent', 'design_approved',
               'ready_for_print', 'printing', 'post_press', 'ready_for_delivery', 'delivered', 'completed']
            : ['ready_for_print', 'printing', 'post_press', 'ready_for_delivery', 'delivered', 'completed'];
        $index = array_search($status, $path, true);
        if ($index === false) {
            // change_requested / retired legacy stages — approximate from rank
            return max(5, (int)round(self::rank($status) / max(1, count(self::RANKS)) * 100));
        }
        return (int)round(($index + 1) / count($path) * 100);
    }

    public static function isOverdue(?string $dueDate, string $status): bool
    {
        if (!$dueDate || self::isFinal($status)) {
            return false;
        }
        return strtotime($dueDate) < time();
    }
}
