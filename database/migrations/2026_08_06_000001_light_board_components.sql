-- Light Board and any other build-up job: one order line per component, where each
-- component decides for itself whether it is sold by the square foot or by the piece.
--
-- Until now the calculation mode lived on the CATEGORY, so a category was all-sqft or
-- all-simple. A light board mixes both (Acrylic by sq.ft, LED Module by piece), so the
-- mode moves onto the line, and each category gets a manageable list of preset components.

-- A line remembers how it was worked out, independent of its category.
ALTER TABLE `order_items`
  ADD COLUMN IF NOT EXISTS `calc_mode` ENUM('simple','sqft') NOT NULL DEFAULT 'simple' AFTER `category_name_snapshot`;

-- Backfill from the category the line belongs to, so existing orders keep their maths.
UPDATE `order_items` oi
JOIN `categories` c ON c.`name` = oi.`category_name_snapshot`
SET oi.`calc_mode` = c.`calc_mode`
WHERE oi.`calc_mode` = 'simple' AND c.`calc_mode` = 'sqft';

-- A category can now be a mix of both.
ALTER TABLE `categories` MODIFY `calc_mode` ENUM('simple','sqft','mixed') NOT NULL DEFAULT 'simple';

-- Preset components per category. Fully data-driven: add, rename, reorder or retire a
-- component from the Categories screen without touching any code.
CREATE TABLE IF NOT EXISTS `category_components` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `calc_mode` ENUM('simple','sqft') NOT NULL DEFAULT 'simple',
  `unit` VARCHAR(20) NOT NULL DEFAULT 'pcs',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_catcomp` (`category_id`,`name`),
  KEY `idx_catcomp_cat` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Light Board
INSERT IGNORE INTO `categories`
  (`name`,`slug`,`icon`,`description`,`calc_mode`,`tax_percent`,`requires_design`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Light Board','light-board','bi-lightbulb','Built up from components — acrylic, letters, LED, frame, labour',
        'mixed',0,1,5,1,0,NOW(),NOW());

-- Components. Acrylic and ACP are sold by the square foot; everything else by the piece,
-- metre, foot or as a flat charge. Any of these can be switched over in the admin later.
INSERT IGNORE INTO `category_components` (`category_id`,`name`,`calc_mode`,`unit`,`sort_order`)
SELECT c.`id`, v.`name`, v.`mode`, v.`unit`, v.`so` FROM `categories` c JOIN (
    SELECT 'Acrylic'                     AS name, 'sqft'   AS mode, 'sqft' AS unit,  1 AS so
    UNION ALL SELECT 'ACP Sheet',              'sqft',   'sqft',  2
    UNION ALL SELECT 'Steel Letter',           'simple', 'pcs',   3
    UNION ALL SELECT 'SS Letter',              'simple', 'pcs',   4
    UNION ALL SELECT 'LED Module',             'simple', 'pcs',   5
    UNION ALL SELECT 'Power Supply',           'simple', 'pcs',   6
    UNION ALL SELECT 'Wire',                   'simple', 'mtr',   7
    UNION ALL SELECT 'Pipe & Frame (P&F)',     'simple', 'ft',    8
    UNION ALL SELECT 'MS Frame',               'simple', 'ft',    9
    UNION ALL SELECT 'Installation / Fitting Charge', 'simple', 'job', 10
    UNION ALL SELECT 'Transport Charge',       'simple', 'job',  11
    UNION ALL SELECT 'Labour Charge',          'simple', 'job',  12
) v WHERE c.`slug` = 'light-board';

-- Light board questions.
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Board Size / Notes','board_size','text',0,1 FROM `categories` c WHERE c.`slug`='light-board';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,2 FROM `categories` c WHERE c.`slug`='light-board';

-- The existing wide-format categories keep working: give them a sensible preset list too so
-- the component picker is useful everywhere, not just on light boards.
INSERT IGNORE INTO `category_components` (`category_id`,`name`,`calc_mode`,`unit`,`sort_order`)
SELECT c.`id`, v.`name`, v.`mode`, v.`unit`, v.`so` FROM `categories` c JOIN (
    SELECT 'Printing'   AS name, 'sqft'   AS mode, 'sqft' AS unit, 1 AS so
    UNION ALL SELECT 'Pipe & Frame (P&F)', 'simple', 'ft',  2
    UNION ALL SELECT 'Installation / Fitting Charge', 'simple', 'job', 3
    UNION ALL SELECT 'Transport Charge',   'simple', 'job', 4
    UNION ALL SELECT 'Labour Charge',      'simple', 'job', 5
) v WHERE c.`slug` IN ('solvent-printing','eco-solvent','uv-special','sun-pack','flex-banners','boards-signage');

UPDATE `categories` SET `calc_mode` = 'mixed'
WHERE `slug` IN ('solvent-printing','eco-solvent','uv-special','sun-pack','flex-banners','boards-signage');
