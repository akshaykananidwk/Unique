-- Simplify the job flow: retire Pending, Quality Check, Out for Delivery and On Hold.
-- Existing rows move to the nearest kept stage. Nothing is pushed forward past work that has
-- not happened, and nothing is dragged back past work that HAS happened.

-- Job items ------------------------------------------------------------------

-- 'pending' really was the first stage, so the first stage is where it belongs.
UPDATE `order_items`
SET `status` = CASE WHEN `requires_design` = 1 THEN 'design_pending' ELSE 'ready_for_print' END
WHERE `status` = 'pending';

-- 'on_hold' could be entered from ANY stage, so a held job must go back to the stage it was
-- paused at — read from its own history. Only fall back to the start if that is unknown.
UPDATE `order_items` oi
SET oi.`status` = COALESCE(
    (SELECT h.`from_status` FROM `order_status_history` h
      WHERE h.`order_item_id` = oi.`id` AND h.`to_status` = 'on_hold' AND h.`from_status` IS NOT NULL
      ORDER BY h.`id` DESC LIMIT 1),
    CASE WHEN oi.`requires_design` = 1 THEN 'design_pending' ELSE 'ready_for_print' END)
WHERE oi.`status` = 'on_hold';

-- Now fold the remaining retired stages onto the nearest kept one. Runs last so it also
-- catches a retired stage that was just restored from history above.
UPDATE `order_items` SET `status` = 'post_press'         WHERE `status` = 'quality_check';
UPDATE `order_items` SET `status` = 'ready_for_delivery' WHERE `status` = 'out_for_delivery';

-- Orders ---------------------------------------------------------------------
-- The order stage is the LOWEST stage among its live items (same rule as
-- OrderService::recomputeOrderStatus), not a guess from whether design is involved.
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
WHERE o.`status` IN ('pending', 'on_hold', 'quality_check', 'out_for_delivery');

-- New rows should start in the design queue, not the retired 'pending' stage.
ALTER TABLE `orders`      MODIFY `status` VARCHAR(40) NOT NULL DEFAULT 'design_pending';
ALTER TABLE `order_items` MODIFY `status` VARCHAR(40) NOT NULL DEFAULT 'design_pending';
