-- Repair for sites that already ran the first cut of 2026_08_04_000003, which sent every
-- On Hold job back to the first stage instead of the stage it was paused at. A job whose
-- LAST recorded history event is "-> on_hold", yet which now sits at the first stage, can
-- only have been moved there by that migration — so put it back where it was paused.
-- Harmless no-op on installs that got the corrected migration.

UPDATE `order_items` oi
SET oi.`status` = (
        SELECT h.`from_status` FROM `order_status_history` h
         WHERE h.`order_item_id` = oi.`id` AND h.`to_status` = 'on_hold' AND h.`from_status` IS NOT NULL
         ORDER BY h.`id` DESC LIMIT 1)
WHERE oi.`status` IN ('design_pending', 'ready_for_print')
  AND (SELECT h2.`to_status` FROM `order_status_history` h2
        WHERE h2.`order_item_id` = oi.`id` ORDER BY h2.`id` DESC LIMIT 1) = 'on_hold'
  AND (SELECT h3.`from_status` FROM `order_status_history` h3
        WHERE h3.`order_item_id` = oi.`id` AND h3.`to_status` = 'on_hold' AND h3.`from_status` IS NOT NULL
        ORDER BY h3.`id` DESC LIMIT 1) IS NOT NULL;

-- A restored stage may itself be one of the retired ones.
UPDATE `order_items` SET `status` = 'post_press'         WHERE `status` = 'quality_check';
UPDATE `order_items` SET `status` = 'ready_for_delivery' WHERE `status` = 'out_for_delivery';

-- Re-derive every live order's stage from its items (lowest stage wins), which also corrects
-- orders the first cut set from "does any item need design" rather than from the real stages.
UPDATE `orders` o
SET o.`status` = COALESCE((
        SELECT oi.`status` FROM `order_items` oi
         WHERE oi.`order_id` = o.`id` AND oi.`status` <> 'cancelled'
         ORDER BY FIELD(oi.`status`,
             'design_pending', 'design_in_progress', 'proof_sent', 'change_requested', 'design_approved',
             'ready_for_print', 'printing', 'post_press', 'ready_for_delivery', 'delivered', 'completed'),
             oi.`id`
         LIMIT 1
    ), o.`status`)
WHERE o.`deleted_at` IS NULL
  AND o.`is_cancelled` = 0
  AND o.`status` NOT IN ('completed', 'cancelled');
