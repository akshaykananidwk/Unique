-- Three things a print shop actually needs.
--
-- 1. ONE MAN, TWO FIRMS
--    A number was unique across the whole book, so one mobile could belong to one customer
--    and no more. But one man often runs two firms — "Shree Traders" and "Om Enterprise" —
--    and phones about both from the same number. The number now belongs to the person in
--    each firm, and the order screen asks which firm the bill is for.
--
-- 2. WHO PREPARED THE JOB
--    The bill and job card can say who took the order, who did the work, and who took the
--    money. Only the first was recorded.

-- --------------------------------------------------------------------- 1. shared numbers
-- Unique per customer instead of globally: the same number may appear under two firms, but
-- never twice inside one firm.
ALTER TABLE `customer_contacts` DROP INDEX `uq_contact_phone`;
ALTER TABLE `customer_contacts` ADD UNIQUE KEY `uq_contact_customer_phone` (`customer_id`, `phone`);
-- Looking a number up is now a list, so it needs its own index.
CREATE INDEX IF NOT EXISTS `idx_contact_phone` ON `customer_contacts` (`phone`);

-- The account's own number was unique too, which would block the second firm just as hard.
-- It stays indexed for lookups, it just no longer has to be one of a kind.
ALTER TABLE `customers` DROP INDEX `uq_customers_phone`;
CREATE INDEX IF NOT EXISTS `idx_customers_phone` ON `customers` (`phone`);

-- --------------------------------------------------------------- 2. who prepared the job
-- The person who actually made the job. Usually the designer; sometimes whoever printed it.
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `prepared_by_user_id` INT UNSIGNED NULL AFTER `accepted_by_user_id`;
CREATE INDEX IF NOT EXISTS `idx_orders_prepared_by` ON `orders` (`prepared_by_user_id`);

-- Fill it in from the design work already recorded, so old bills print a name too.
UPDATE `orders` o
  JOIN (
    SELECT oi.order_id, MIN(oi.assigned_designer_id) AS designer_id
    FROM `order_items` oi
    WHERE oi.assigned_designer_id IS NOT NULL
    GROUP BY oi.order_id
  ) d ON d.order_id = o.id
SET o.`prepared_by_user_id` = d.designer_id
WHERE o.`prepared_by_user_id` IS NULL;

-- --------------------------------------------------------------------- 3. the work export
INSERT IGNORE INTO `permissions` (`code`, `module`, `label`)
VALUES ('report.work', 'report', 'Work Done report — who did which job, exportable to Excel');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE p.code = 'report.work' AND r.slug IN ('super_admin', 'branch_manager', 'accountant');

-- Permission cache is keyed on this, so it applies without anyone logging out.
INSERT INTO `settings` (`group_key`,`setting_key`,`setting_value`,`is_encrypted`)
SELECT 'system','acl_version','2',0
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='acl_version');
UPDATE `settings` SET `setting_value` = CAST(CAST(`setting_value` AS UNSIGNED) + 1 AS CHAR)
WHERE `setting_key` = 'acl_version';
