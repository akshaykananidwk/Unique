-- Order entry rework:
--   * items are typed free-hand, so order_items.item_id becomes optional
--   * the CATEGORY now carries the question set and the calculation mode
--   * foot x foot lines store quantity, width, height and the resulting square feet
--   * rate is always entered by hand, so no price lives on the category
--   * columns reserved for a future commission module (unused for now)

-- Category drives the form and the maths ---------------------------------------
ALTER TABLE `categories`
  ADD COLUMN IF NOT EXISTS `calc_mode` ENUM('simple','sqft') NOT NULL DEFAULT 'simple' AFTER `description`;
ALTER TABLE `categories`
  ADD COLUMN IF NOT EXISTS `tax_percent` DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER `calc_mode`;
ALTER TABLE `categories`
  ADD COLUMN IF NOT EXISTS `requires_design` TINYINT(1) NOT NULL DEFAULT 1 AFTER `tax_percent`;

CREATE TABLE IF NOT EXISTS `category_options` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `field_key` VARCHAR(60) NOT NULL,
  `field_type` ENUM('text','number','select','radio','checkbox','textarea','date') NOT NULL DEFAULT 'text',
  `is_required` TINYINT(1) NOT NULL DEFAULT 0,
  `help_text` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_catopt` (`category_id`,`field_key`),
  KEY `idx_catopt_cat` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `category_option_values` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `option_id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_catoptval` (`option_id`,`label`),
  KEY `idx_catoptval_opt` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order lines -------------------------------------------------------------------
ALTER TABLE `order_items` MODIFY `item_id` INT UNSIGNED NULL;
ALTER TABLE `order_items`
  ADD COLUMN IF NOT EXISTS `width_ft` DECIMAL(10,2) NULL AFTER `qty`;
ALTER TABLE `order_items`
  ADD COLUMN IF NOT EXISTS `height_ft` DECIMAL(10,2) NULL AFTER `width_ft`;
ALTER TABLE `order_items`
  ADD COLUMN IF NOT EXISTS `total_sqft` DECIMAL(12,2) NULL AFTER `height_ft`;

-- Who took it, who accepted it (designer already lives on the line) --------------
ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `accepted_by_user_id` INT UNSIGNED NULL AFTER `taken_by_user_id`;

-- Reserved for a future commission module. Nothing reads these yet. --------------
ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `commission_percent` DECIMAL(5,2) NULL AFTER `balance_amount`;
ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `commission_amount` DECIMAL(12,2) NULL AFTER `commission_percent`;
ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `commission_user_id` INT UNSIGNED NULL AFTER `commission_amount`;

-- Discount is not part of this shop's workflow — stop applying it. Columns stay so
-- old orders keep their history; new orders are written with zero.
UPDATE `orders` SET `discount_type` = NULL, `discount_value` = 0 WHERE `discount_type` IS NOT NULL AND `total` = 0;

-- Single branch: everything belongs to the lowest-numbered active branch.
UPDATE `orders` o SET o.`branch_id` = (SELECT MIN(b.`id`) FROM `branches` b WHERE b.`is_active` = 1)
WHERE o.`branch_id` NOT IN (SELECT `id` FROM (SELECT MIN(`id`) AS id FROM `branches` WHERE `is_active` = 1) x);
