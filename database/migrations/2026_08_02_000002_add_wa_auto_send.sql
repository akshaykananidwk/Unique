-- Auto-send WhatsApp messages immediately (without waiting for the cron worker).
INSERT INTO `settings` (`group_key`, `setting_key`, `setting_value`, `is_encrypted`)
SELECT 'whatsapp', 'wa_auto_send', '1', 0
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key` = 'wa_auto_send');
