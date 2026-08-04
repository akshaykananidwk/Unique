-- Centralised scheduler: ONE server cron every minute, the app decides what is due.
-- Replaces the four separate crontab entries (whatsapp_worker, reminders, auto_backup,
-- update_check) without losing any of the work they did.

CREATE TABLE IF NOT EXISTS `cron_jobs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_key` VARCHAR(60) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) NULL,
  `group_name` VARCHAR(40) NOT NULL DEFAULT 'general',
  -- interval: run every N minutes.  daily: run once a day at run_at_time.
  `schedule_type` ENUM('interval','daily') NOT NULL DEFAULT 'interval',
  `interval_minutes` INT UNSIGNED NOT NULL DEFAULT 5,
  `run_at_time` TIME NULL,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  -- Book-keeping
  `last_run_at` DATETIME NULL,
  `last_finished_at` DATETIME NULL,
  `last_status` ENUM('never','running','success','failed','skipped') NOT NULL DEFAULT 'never',
  `last_message` VARCHAR(500) NULL,
  `last_duration_ms` INT UNSIGNED NULL,
  `last_items` INT UNSIGNED NOT NULL DEFAULT 0,
  `next_run_at` DATETIME NULL,
  `run_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `fail_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `consecutive_failures` INT UNSIGNED NOT NULL DEFAULT 0,
  -- Locking: a claimed job carries a token and a timestamp; a stale lock is reclaimed.
  `locked_at` DATETIME NULL,
  `lock_token` VARCHAR(40) NULL,
  `timeout_seconds` INT UNSIGNED NOT NULL DEFAULT 300,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cron_key` (`job_key`),
  KEY `idx_cron_due` (`is_enabled`,`next_run_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cron_runs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_key` VARCHAR(60) NOT NULL,
  `started_at` DATETIME NOT NULL,
  `finished_at` DATETIME NULL,
  `duration_ms` INT UNSIGNED NULL,
  `status` ENUM('running','success','failed','skipped') NOT NULL DEFAULT 'running',
  `items` INT UNSIGNED NOT NULL DEFAULT 0,
  `message` TEXT NULL,
  `triggered_by` ENUM('schedule','manual','retry') NOT NULL DEFAULT 'schedule',
  `user_id` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  KEY `idx_runs_job` (`job_key`,`started_at`),
  KEY `idx_runs_started` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The jobs that exist today, migrated one-for-one from the old cron files.
INSERT IGNORE INTO `cron_jobs`
  (`job_key`,`name`,`description`,`group_name`,`schedule_type`,`interval_minutes`,`run_at_time`,`timeout_seconds`,`is_enabled`,`created_at`,`updated_at`)
VALUES
  ('whatsapp.queue','WhatsApp Message Queue','Sends queued WhatsApp messages, respecting the per-minute rate limit',
   'messaging','interval',1,NULL,120,1,NOW(),NOW()),
  ('whatsapp.retry_failed','Retry Failed Messages','Re-queues WhatsApp messages that failed but still have attempts left',
   'messaging','interval',30,NULL,120,1,NOW(),NOW()),
  ('proof.reminders','Proof Follow-up Reminders','Reminds customers about design proofs they have not opened',
   'reminders','interval',15,NULL,180,1,NOW(),NOW()),
  ('order.overdue','Overdue Job Alerts','Alerts the manager about jobs that have passed their due date',
   'reminders','interval',15,NULL,180,1,NOW(),NOW()),
  ('payment.balance_reminders','Payment Due Reminders','Reminds customers with an unpaid balance on delivered orders',
   'reminders','interval',60,NULL,180,1,NOW(),NOW()),
  ('report.daily_summary','Daily Summary Report','Sends the owner the day''s orders, collections and pending figures',
   'reports','daily','1440','21:00:00',180,1,NOW(),NOW()),
  ('system.backup','Automatic Backup','Takes a database backup and prunes older ones',
   'system','daily','1440','02:00:00',900,1,NOW(),NOW()),
  ('system.update_check','Update Check','Checks GitHub for a newer version. Never installs on its own',
   'system','daily','1440','03:30:00',300,1,NOW(),NOW()),
  ('system.cleanup','Housekeeping','Trims old scheduler history, sent messages and expired OTPs',
   'system','daily','1440','04:00:00',300,1,NOW(),NOW());

-- Everything is due immediately on first install so nothing waits a full cycle.
UPDATE `cron_jobs` SET `next_run_at` = NOW() WHERE `next_run_at` IS NULL;

-- Heartbeat of the master tick itself, for the health panel.
INSERT INTO `settings` (`group_key`,`setting_key`,`setting_value`,`is_encrypted`)
SELECT 'cron','cron_last_tick_at','',0
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='cron_last_tick_at');
INSERT INTO `settings` (`group_key`,`setting_key`,`setting_value`,`is_encrypted`)
SELECT 'cron','cron_tick_count','0',0
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key`='cron_tick_count');
