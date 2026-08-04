-- Let managers mark a design approved in person when the customer OK's it across the counter,
-- instead of being blocked waiting for the online proof approval.
INSERT INTO `permissions` (`code`, `module`, `label`)
SELECT 'order.override_approval', 'order', 'Mark design approved in person (skip online approval)'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `code` = 'order.override_approval');

-- Branch Managers get it (Super Admin implicitly holds every permission).
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.`id`, p.`id`
FROM `roles` r, `permissions` p
WHERE r.`slug` = 'branch_manager'
  AND p.`code` = 'order.override_approval'
  AND NOT EXISTS (
      SELECT 1 FROM `role_permissions` rp WHERE rp.`role_id` = r.`id` AND rp.`permission_id` = p.`id`
  );

-- Bump the ACL version so staff already signed in pick the new permission up on their next
-- page load instead of having to log out and back in.
INSERT INTO `settings` (`group_key`, `setting_key`, `setting_value`, `is_encrypted`)
SELECT 'app', 'acl_version', '2', 0
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key` = 'acl_version');
UPDATE `settings` SET `setting_value` = CAST(`setting_value` AS UNSIGNED) + 1 WHERE `setting_key` = 'acl_version';
