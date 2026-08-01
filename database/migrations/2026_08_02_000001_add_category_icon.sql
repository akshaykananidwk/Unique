-- Add an optional Bootstrap-icon name to categories (for the public site cards).
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `icon` VARCHAR(40) NULL AFTER `image`;
