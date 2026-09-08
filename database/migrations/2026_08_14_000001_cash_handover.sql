-- Cash in hand, and handing it over.
--
-- Money is taken at the counter by whoever is standing there, so at any moment the cash of
-- the day is split between several pockets. Nothing recorded that. This adds two things:
--
--   1. Cash in hand per person — every cash receipt sits with the person who took it until
--      they pass it on. Refunds paid out of the same pocket come off it.
--   2. A handover that both people have to be present for. The sender starts it, a code
--      appears on the RECEIVER's screen, the receiver reads it out, the sender types it in.
--      Only then does the money move: off the sender's book, onto the receiver's. Neither
--      person can move money on their own, which is the whole point of a handover.
--
-- Completed handovers are never edited or deleted. A mistake is put right by handing the
-- money back the other way, so the trail always reads forwards.

CREATE TABLE IF NOT EXISTS `cash_handovers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ref_no` VARCHAR(30) NOT NULL,
  `from_user_id` INT UNSIGNED NOT NULL,
  `to_user_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `note` VARCHAR(255) NULL,
  `status` ENUM('pending','completed','declined','cancelled','expired') NOT NULL DEFAULT 'pending',
  -- The code is shown to the receiver, so it has to be readable again — it is kept
  -- encrypted with the app key rather than hashed, and only ever handed to that one user.
  `otp_code` VARCHAR(255) NOT NULL,
  `otp_expires_at` DATETIME NOT NULL,
  `otp_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `otp_issued_count` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `closed_at` DATETIME NULL,
  `closed_by_user_id` INT UNSIGNED NULL,
  `close_reason` VARCHAR(255) NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_handover_ref` (`ref_no`),
  KEY `idx_handover_from` (`from_user_id`,`status`),
  KEY `idx_handover_to` (`to_user_id`,`status`),
  KEY `idx_handover_open` (`status`,`otp_expires_at`),
  KEY `idx_handover_when` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------- permissions
INSERT IGNORE INTO `permissions` (`code`, `module`, `label`) VALUES
  ('cash.view',     'payment', 'See your own cash in hand and hand it over'),
  ('cash.view_all', 'payment', 'See how much cash every member of staff is holding');

-- Anybody who can take money can hold it and pass it on.
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE p.code = 'cash.view'
  AND r.slug IN ('super_admin', 'branch_manager', 'accountant', 'counter_staff', 'delivery', 'production', 'designer');

-- Seeing everyone's pocket is a manager's job.
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE p.code = 'cash.view_all'
  AND r.slug IN ('super_admin', 'branch_manager', 'accountant');

-- --------------------------------------------------------------------------- housekeeping
-- A handover nobody finished should not sit open for ever, holding money that is really
-- still in the sender's pocket.
INSERT IGNORE INTO `cron_jobs`
  (`job_key`,`name`,`description`,`group_name`,`schedule_type`,`interval_minutes`,`run_at_time`,`timeout_seconds`,`is_enabled`,`created_at`,`updated_at`)
VALUES
  ('cash.handover_expiry','Expire Unfinished Handovers','Closes cash handovers whose code was never used, so the money stays with the sender',
   'reminders','interval',15,NULL,120,1,NOW(),NOW());

-- Permission cache is keyed on this, so it applies without anyone logging out.
INSERT INTO `settings` (`group_key`,`setting_key`,`setting_value`,`is_encrypted`)
SELECT 'system','acl_version','2',0
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='acl_version');
UPDATE `settings` SET `setting_value` = CAST(CAST(`setting_value` AS UNSIGNED) + 1 AS CHAR)
WHERE `setting_key` = 'acl_version';
