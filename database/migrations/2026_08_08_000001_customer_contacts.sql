-- One customer, many people.
--
-- A company like Tata gives work through several people, each with their own mobile.
-- Until now every number was a separate customer, so the same company's jobs were scattered
-- and there was no way to open "Tata" and see everything that had been done for them.
--
-- From here: `customers` is the company (or the walk-in person, which is just a company of
-- one), and `customer_contacts` holds the people. Every order records which person gave it.

CREATE TABLE IF NOT EXISTS `customer_contacts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `whatsapp` VARCHAR(20) NULL,
  `email` VARCHAR(150) NULL,
  `designation` VARCHAR(80) NULL,
  -- The one used when an order does not name anybody.
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `notes` VARCHAR(255) NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  -- A mobile number belongs to one person. Looking a number up must never be ambiguous.
  UNIQUE KEY `uq_contact_phone` (`phone`),
  KEY `idx_contact_customer` (`customer_id`,`is_primary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Every existing customer becomes their own primary contact, so no number stops working
-- and no lookup changes behaviour on the day this ships.
INSERT INTO `customer_contacts`
  (`customer_id`,`name`,`phone`,`whatsapp`,`email`,`is_primary`,`created_at`,`updated_at`)
SELECT c.`id`, c.`name`, c.`phone`, c.`whatsapp`, c.`email`, 1, NOW(), NOW()
FROM `customers` c
WHERE c.`deleted_at` IS NULL
  AND NOT EXISTS (SELECT 1 FROM `customer_contacts` cc WHERE cc.`phone` = c.`phone`);

-- Which person handed this order over.
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `contact_id` INT UNSIGNED NULL AFTER `customer_id`;
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_orders_contact` (`contact_id`);

-- Existing orders point at that customer's primary contact.
UPDATE `orders` o
  JOIN `customer_contacts` cc ON cc.`customer_id` = o.`customer_id` AND cc.`is_primary` = 1
SET o.`contact_id` = cc.`id`
WHERE o.`contact_id` IS NULL;
