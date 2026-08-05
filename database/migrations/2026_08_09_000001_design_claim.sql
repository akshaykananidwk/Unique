-- Designers accept their own work.
--
-- Until now a job sat invisible until a manager assigned it. Anyone can take an order, so
-- the design side should work the same way: unclaimed jobs are on a shared board, a designer
-- accepts one, and from that moment it is theirs — which is what the monthly report counts.

-- When it was accepted, and by whom (normally the designer themselves, sometimes a manager).
ALTER TABLE `order_items` ADD COLUMN IF NOT EXISTS `claimed_at` DATETIME NULL AFTER `designer_assigned_at`;
ALTER TABLE `order_items` ADD COLUMN IF NOT EXISTS `claimed_by_user_id` INT UNSIGNED NULL AFTER `claimed_at`;

-- Jobs already assigned count as accepted when they were assigned, so history is not lost.
UPDATE `order_items`
SET `claimed_at` = COALESCE(`designer_assigned_at`, NOW()),
    `claimed_by_user_id` = `assigned_designer_id`
WHERE `assigned_designer_id` IS NOT NULL AND `claimed_at` IS NULL;

CREATE INDEX IF NOT EXISTS `idx_items_claim` ON `order_items` (`assigned_designer_id`, `claimed_at`);

-- Designers need to see and accept from the shared board.
INSERT IGNORE INTO `permissions` (`code`, `module`, `label`)
VALUES ('design.claim', 'design', 'Accept a design job from the shared board');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE p.code = 'design.claim' AND r.slug IN ('super_admin', 'branch_manager', 'designer');

-- Permission cache is keyed on this, so bumping it applies the grant without a re-login.
INSERT INTO `settings` (`group_key`,`setting_key`,`setting_value`,`is_encrypted`)
SELECT 'system','acl_version','2',0
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='acl_version');
UPDATE `settings` SET `setting_value` = CAST(CAST(`setting_value` AS UNSIGNED) + 1 AS CHAR)
WHERE `setting_key` = 'acl_version';
