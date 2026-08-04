-- Simplify the job flow: retire Pending, Quality Check, Out for Delivery and On Hold.
-- Existing rows are moved to the nearest kept stage, never forward past work that has not
-- actually happened (quality_check falls back to post_press, out_for_delivery to ready_for_delivery).

-- Job items ------------------------------------------------------------------
UPDATE `order_items`
SET `status` = CASE WHEN `requires_design` = 1 THEN 'design_pending' ELSE 'ready_for_print' END
WHERE `status` IN ('pending', 'on_hold');

UPDATE `order_items` SET `status` = 'post_press'         WHERE `status` = 'quality_check';
UPDATE `order_items` SET `status` = 'ready_for_delivery' WHERE `status` = 'out_for_delivery';

-- Orders ---------------------------------------------------------------------
UPDATE `orders` o
SET o.`status` = CASE
        WHEN EXISTS (SELECT 1 FROM `order_items` oi WHERE oi.`order_id` = o.`id` AND oi.`requires_design` = 1)
            THEN 'design_pending'
        ELSE 'ready_for_print'
    END
WHERE o.`status` IN ('pending', 'on_hold');

UPDATE `orders` SET `status` = 'post_press'         WHERE `status` = 'quality_check';
UPDATE `orders` SET `status` = 'ready_for_delivery' WHERE `status` = 'out_for_delivery';

-- New rows should start in the design queue, not the retired 'pending' stage.
ALTER TABLE `orders`      MODIFY `status` VARCHAR(40) NOT NULL DEFAULT 'design_pending';
ALTER TABLE `order_items` MODIFY `status` VARCHAR(40) NOT NULL DEFAULT 'design_pending';
