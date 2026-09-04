-- Anyone can design.
--
-- Design work used to belong to the "designer" role and nothing else, so when the design
-- staff were busy and the owner did a job himself it could not be recorded against him —
-- his name did not even appear in the list to assign to.
--
-- From here a designer is simply anyone whose role carries "Upload design proofs". Tick it
-- for a role on the Roles screen and everyone in it appears in every designer dropdown, on
-- the shared board, and in the monthly design report. No code change, no special role.

-- The permission now decides who is a designer, so it says so.
UPDATE `permissions`
SET `label` = 'Design jobs — appear as a designer, take work and upload proofs'
WHERE `code` = 'design.upload';

UPDATE `permissions`
SET `label` = 'Accept a design job from the shared board'
WHERE `code` = 'design.claim';

-- The manager runs the shop and covers design when needed, so give them the same access the
-- owner has. Everyone else is left alone — the shop decides on the Roles screen.
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE p.code IN ('design.upload', 'design.claim', 'design.view')
  AND r.slug IN ('super_admin', 'branch_manager', 'designer');

-- Work that was assigned before the claim stamp existed still has to count for the month it
-- was done in, so backfill anything that was missed.
UPDATE `order_items`
SET `claimed_at` = `designer_assigned_at`,
    `claimed_by_user_id` = `assigned_designer_id`
WHERE `assigned_designer_id` IS NOT NULL
  AND `claimed_at` IS NULL
  AND `designer_assigned_at` IS NOT NULL;

-- Permission cache is keyed on this, so the change applies without anyone logging out.
INSERT INTO `settings` (`group_key`,`setting_key`,`setting_value`,`is_encrypted`)
SELECT 'system','acl_version','2',0
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='acl_version');
UPDATE `settings` SET `setting_value` = CAST(CAST(`setting_value` AS UNSIGNED) + 1 AS CHAR)
WHERE `setting_key` = 'acl_version';
