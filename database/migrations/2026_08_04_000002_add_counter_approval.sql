-- Remember that a design was approved face-to-face at the counter, so the job keeps
-- flowing through every later stage without asking for the online approval again.
ALTER TABLE `order_items` ADD COLUMN IF NOT EXISTS `counter_approved_at` DATETIME NULL AFTER `revision_count`;
ALTER TABLE `order_items` ADD COLUMN IF NOT EXISTS `counter_approved_by` INT UNSIGNED NULL AFTER `counter_approved_at`;
