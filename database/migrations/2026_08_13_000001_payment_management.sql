-- Payment management, properly.
--
-- Until now money could only be added to an order and deleted by the Super Admin. A wrong
-- amount meant deleting the receipt and writing it again, there was no one screen showing
-- every payment, and there was no way to settle a party who pays a little less — the
-- balance just sat there for ever.

-- ------------------------------------------------------------------ settlement discount
-- Money written off when a party settles short: "bill 1000, he pays 950, close it".
-- It is NOT income, so it is kept apart from the amount actually received.
ALTER TABLE `payments` ADD COLUMN IF NOT EXISTS `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `amount`;

-- The same figure rolled up on the order, so a bill can show what was allowed off.
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `settled_discount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `paid_amount`;

-- ------------------------------------------------------------------------ edit history
-- A receipt that can be edited needs to say what it used to be.
CREATE TABLE IF NOT EXISTS `payment_edits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` INT UNSIGNED NOT NULL,
  `changed_by_user_id` INT UNSIGNED NULL,
  `field` VARCHAR(40) NOT NULL,
  `old_value` VARCHAR(255) NULL,
  `new_value` VARCHAR(255) NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_payment_edits` (`payment_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------- permissions
INSERT IGNORE INTO `permissions` (`code`, `module`, `label`) VALUES
  ('payment.edit',     'payment', 'Edit a recorded payment'),
  ('payment.discount', 'payment', 'Allow a discount when settling a payment');

-- Editing money is a manager's job; the counter records and prints.
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE p.code IN ('payment.edit', 'payment.discount')
  AND r.slug IN ('super_admin', 'branch_manager', 'accountant');

-- Permission cache is keyed on this, so it applies without anyone logging out.
INSERT INTO `settings` (`group_key`,`setting_key`,`setting_value`,`is_encrypted`)
SELECT 'system','acl_version','2',0
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='acl_version');
UPDATE `settings` SET `setting_value` = CAST(CAST(`setting_value` AS UNSIGNED) + 1 AS CHAR)
WHERE `setting_key` = 'acl_version';
