-- One shop, one branch, everywhere.
--
-- Branches were built for a chain. This is a single press: every screen that asked "which
-- branch?" was a question with one possible answer, and it kept turning up while setting
-- up roles. So everything is folded into one Main Branch and the choice disappears.
--
-- The branch_id columns stay — orders, payments, users and job numbers all hang off them —
-- they just always point at the one branch now.

-- 1. Everything moves to the lowest-numbered branch, which becomes Main Branch.
SET @main := (SELECT MIN(`id`) FROM `branches`);

UPDATE `orders`    SET `branch_id` = @main WHERE `branch_id` <> @main OR `branch_id` IS NULL;
UPDATE `payments`  SET `branch_id` = @main WHERE `branch_id` <> @main OR `branch_id` IS NULL;
UPDATE `customers` SET `branch_id` = @main WHERE `branch_id` <> @main OR `branch_id` IS NULL;
UPDATE `users`     SET `primary_branch_id` = @main WHERE `primary_branch_id` <> @main OR `primary_branch_id` IS NULL;

-- Nobody needs extra branch access when there is only one.
DELETE FROM `user_branches` WHERE `branch_id` <> @main;

UPDATE `branches`
SET `name` = 'Main Branch', `type` = 'shop', `is_active` = 1, `sort_order` = 0, `updated_at` = NOW()
WHERE `id` = @main;

-- 2. The rest are gone. Deactivated rather than deleted: old rows may still point at them
--    through a foreign key, and losing a branch name would break nothing but read badly.
UPDATE `branches` SET `is_active` = 0, `updated_at` = NOW() WHERE `id` <> @main;

-- 3. Branch permissions no longer mean anything, so they leave the roles screen entirely.
DELETE rp FROM `role_permissions` rp
  JOIN `permissions` p ON p.id = rp.permission_id
 WHERE p.code IN ('branch.manage', 'report.branch', 'dashboard.view_all_branches', 'order.view_all');
DELETE FROM `permissions`
 WHERE `code` IN ('branch.manage', 'report.branch', 'dashboard.view_all_branches', 'order.view_all');

-- "View orders (own branch)" no longer means anything either.
UPDATE `permissions` SET `label` = 'View orders' WHERE `code` = 'order.view';

-- Permission cache is keyed on this, so the roles screen updates without a re-login.
INSERT INTO `settings` (`group_key`,`setting_key`,`setting_value`,`is_encrypted`)
SELECT 'system','acl_version','2',0
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='acl_version');
UPDATE `settings` SET `setting_value` = CAST(CAST(`setting_value` AS UNSIGNED) + 1 AS CHAR)
WHERE `setting_key` = 'acl_version';

-- 4. "Branch Manager" reads oddly when there are no branches. The slug stays (code refers
--    to it); only the name people see changes.
UPDATE `roles` SET `name` = 'Manager' WHERE `slug` = 'branch_manager' AND `name` = 'Branch Manager';
