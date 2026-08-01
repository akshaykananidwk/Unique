-- Krishna Print — schema
-- All tables InnoDB / utf8mb4_unicode_ci

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------- Core

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_key` VARCHAR(50) NOT NULL DEFAULT 'general',
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` LONGTEXT NULL,
  `is_encrypted` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`setting_key`),
  KEY `idx_settings_group` (`group_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `branches` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(10) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('shop','godown','office') NOT NULL DEFAULT 'shop',
  `address` VARCHAR(255) NULL,
  `city` VARCHAR(100) NULL,
  `phone` VARCHAR(20) NULL,
  `whatsapp` VARCHAR(20) NULL,
  `gstin` VARCHAR(20) NULL,
  `invoice_prefix` VARCHAR(10) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_branches_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(60) NOT NULL,
  `slug` VARCHAR(60) NOT NULL,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `description` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(60) NOT NULL,
  `module` VARCHAR(40) NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_permissions_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150) NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `primary_branch_id` INT UNSIGNED NULL,
  `avatar` VARCHAR(255) NULL,
  `designer_capacity` INT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME NULL,
  `last_login_ip` VARCHAR(45) NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_phone` (`phone`),
  KEY `idx_users_role` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_branches` (
  `user_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`,`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Customers

CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `whatsapp` VARCHAR(20) NULL,
  `email` VARCHAR(150) NULL,
  `address` VARCHAR(255) NULL,
  `city` VARCHAR(100) NULL,
  `pincode` VARCHAR(10) NULL,
  `gstin` VARCHAR(20) NULL,
  `customer_type` ENUM('retail','dealer','corporate') NOT NULL DEFAULT 'retail',
  `price_group_id` INT UNSIGNED NULL,
  `notes` TEXT NULL,
  `source` ENUM('counter','public','import') NOT NULL DEFAULT 'counter',
  `created_by` INT UNSIGNED NULL,
  `branch_id` INT UNSIGNED NULL,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `is_blocked` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customers_phone` (`phone`),
  KEY `idx_customers_branch` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `customer_otps` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone` VARCHAR(20) NOT NULL,
  `otp_hash` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `attempts` INT NOT NULL DEFAULT 0,
  `verified_at` DATETIME NULL,
  `ip` VARCHAR(45) NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_otp_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Catalog

CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `image` VARCHAR(255) NULL,
  `icon` VARCHAR(40) NULL,
  `description` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `show_on_public` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `sku` VARCHAR(60) NOT NULL,
  `short_description` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `image` VARCHAR(255) NULL,
  `unit` VARCHAR(20) NOT NULL DEFAULT 'pcs',
  `pricing_type` ENUM('fixed','per_unit','slab','area') NOT NULL DEFAULT 'per_unit',
  `base_price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `min_qty` INT NOT NULL DEFAULT 1,
  `step_qty` INT NOT NULL DEFAULT 1,
  `tax_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `requires_design` TINYINT(1) NOT NULL DEFAULT 1,
  `default_turnaround_hours` INT NOT NULL DEFAULT 24,
  `show_on_public` TINYINT(1) NOT NULL DEFAULT 1,
  `allow_customer_file_upload` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_items_sku` (`sku`),
  KEY `idx_items_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `item_options` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `field_key` VARCHAR(60) NOT NULL,
  `field_type` ENUM('text','number','select','radio','checkbox','textarea','date','file','color') NOT NULL DEFAULT 'text',
  `is_required` TINYINT(1) NOT NULL DEFAULT 0,
  `help_text` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `affects_price` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_item_options_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `item_option_values` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `option_id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `price_delta` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `price_mode` ENUM('add','multiply','replace') NOT NULL DEFAULT 'add',
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_iov_option` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `price_slabs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` INT UNSIGNED NOT NULL,
  `min_qty` INT NOT NULL,
  `max_qty` INT NULL,
  `rate` DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_slabs_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `price_groups` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(60) NOT NULL,
  `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Orders

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_no` VARCHAR(40) NOT NULL,
  `tracking_token` VARCHAR(64) NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `source` ENUM('counter','public','whatsapp','phone') NOT NULL DEFAULT 'counter',
  `taken_by_user_id` INT UNSIGNED NULL,
  `order_date` DATETIME NOT NULL,
  `due_date` DATETIME NULL,
  `priority` ENUM('normal','urgent','rush') NOT NULL DEFAULT 'normal',
  `status` VARCHAR(40) NOT NULL DEFAULT 'pending',
  `needs_review` TINYINT(1) NOT NULL DEFAULT 0,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount_type` ENUM('flat','percent') NULL,
  `discount_value` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `tax_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `delivery_charge` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `round_off` DECIMAL(6,2) NOT NULL DEFAULT 0,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `balance_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `delivery_type` ENUM('pickup','delivery') NOT NULL DEFAULT 'pickup',
  `delivery_address` VARCHAR(255) NULL,
  `customer_note` TEXT NULL,
  `internal_note` TEXT NULL,
  `is_cancelled` TINYINT(1) NOT NULL DEFAULT 0,
  `cancelled_reason` VARCHAR(255) NULL,
  `cancelled_by` INT UNSIGNED NULL,
  `cancelled_at` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `delivered_at` DATETIME NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_job_no` (`job_no`),
  UNIQUE KEY `uq_orders_token` (`tracking_token`),
  KEY `idx_orders_branch` (`branch_id`),
  KEY `idx_orders_customer` (`customer_id`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_due` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_sequences` (
  `branch_id` INT UNSIGNED NOT NULL,
  `yymm` CHAR(4) NOT NULL,
  `last_seq` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`branch_id`,`yymm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `receipt_sequences` (
  `branch_id` INT UNSIGNED NOT NULL,
  `yy` CHAR(2) NOT NULL,
  `last_seq` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`branch_id`,`yy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `item_id` INT UNSIGNED NULL,
  `item_name_snapshot` VARCHAR(150) NOT NULL,
  `category_name_snapshot` VARCHAR(100) NULL,
  `qty` DECIMAL(12,2) NOT NULL DEFAULT 1,
  `unit` VARCHAR(20) NULL,
  `rate` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `rate_overridden` TINYINT(1) NOT NULL DEFAULT 0,
  `spec_json` LONGTEXT NULL,
  `spec_text` TEXT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `tax_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `tax_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `line_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `requires_design` TINYINT(1) NOT NULL DEFAULT 1,
  `assigned_designer_id` INT UNSIGNED NULL,
  `designer_assigned_at` DATETIME NULL,
  `assigned_production_id` INT UNSIGNED NULL,
  `status` VARCHAR(40) NOT NULL DEFAULT 'pending',
  `revision_count` INT NOT NULL DEFAULT 0,
  `due_date` DATETIME NULL,
  `special_instructions` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_oi_order` (`order_id`),
  KEY `idx_oi_designer` (`assigned_designer_id`),
  KEY `idx_oi_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_attachments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `order_item_id` INT UNSIGNED NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NULL,
  `mime` VARCHAR(100) NULL,
  `size_bytes` BIGINT UNSIGNED NULL,
  `kind` ENUM('customer_artwork','reference','final_output','other') NOT NULL DEFAULT 'other',
  `uploaded_by` INT UNSIGNED NULL,
  `uploaded_by_customer` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_oa_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `order_item_id` INT UNSIGNED NULL,
  `from_status` VARCHAR(40) NULL,
  `to_status` VARCHAR(40) NOT NULL,
  `changed_by_user_id` INT UNSIGNED NULL,
  `changed_by_customer` TINYINT(1) NOT NULL DEFAULT 0,
  `note` VARCHAR(500) NULL,
  `ip` VARCHAR(45) NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_osh_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Design proofs

CREATE TABLE IF NOT EXISTS `design_proofs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_item_id` INT UNSIGNED NOT NULL,
  `version` INT NOT NULL DEFAULT 1,
  `file_path` VARCHAR(255) NOT NULL,
  `watermarked_path` VARCHAR(255) NULL,
  `thumb_path` VARCHAR(255) NULL,
  `file_type` VARCHAR(20) NULL,
  `proof_token` VARCHAR(64) NOT NULL,
  `uploaded_by_user_id` INT UNSIGNED NULL,
  `designer_note` TEXT NULL,
  `status` ENUM('pending','approved','change_requested','superseded') NOT NULL DEFAULT 'pending',
  `sent_at` DATETIME NULL,
  `viewed_at` DATETIME NULL,
  `responded_at` DATETIME NULL,
  `response_ip` VARCHAR(45) NULL,
  `response_user_agent` VARCHAR(255) NULL,
  `approval_confirmed` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_proof_token` (`proof_token`),
  KEY `idx_proofs_item` (`order_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `proof_feedback` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `proof_id` INT UNSIGNED NOT NULL,
  `feedback_text` TEXT NULL,
  `input_method` ENUM('typed','voice','both') NOT NULL DEFAULT 'typed',
  `voice_file_path` VARCHAR(255) NULL,
  `reference_file_path` VARCHAR(255) NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pf_proof` (`proof_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Payments

CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `receipt_no` VARCHAR(40) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `type` ENUM('advance','part','final','refund') NOT NULL DEFAULT 'part',
  `mode` ENUM('cash','upi','card','bank','cheque','credit') NOT NULL DEFAULT 'cash',
  `reference` VARCHAR(100) NULL,
  `received_by_user_id` INT UNSIGNED NULL,
  `paid_at` DATETIME NOT NULL,
  `note` VARCHAR(255) NULL,
  `created_at` DATETIME NULL,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payments_receipt` (`receipt_no`),
  KEY `idx_payments_order` (`order_id`),
  KEY `idx_payments_customer` (`customer_id`),
  KEY `idx_payments_branch` (`branch_id`),
  KEY `idx_payments_paid_at` (`paid_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- WhatsApp

CREATE TABLE IF NOT EXISTS `whatsapp_templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(60) NOT NULL,
  `event_key` VARCHAR(60) NOT NULL,
  `title` VARCHAR(120) NOT NULL,
  `body` TEXT NOT NULL,
  `media_url` VARCHAR(255) NULL,
  `recipient` ENUM('customer','designer','production','branch_manager','admin','custom') NOT NULL DEFAULT 'customer',
  `custom_numbers` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `delay_minutes` INT NOT NULL DEFAULT 0,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wt_code` (`code`),
  KEY `idx_wt_event` (`event_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `whatsapp_queue` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_code` VARCHAR(60) NULL,
  `event_key` VARCHAR(60) NULL,
  `to_number` VARCHAR(20) NOT NULL,
  `message` LONGTEXT NOT NULL,
  `media_url` VARCHAR(255) NULL,
  `ref_type` VARCHAR(40) NULL,
  `ref_id` INT UNSIGNED NULL,
  `priority` INT NOT NULL DEFAULT 5,
  `status` ENUM('pending','processing','sent','failed','cancelled') NOT NULL DEFAULT 'pending',
  `attempts` INT NOT NULL DEFAULT 0,
  `max_attempts` INT NOT NULL DEFAULT 3,
  `last_error` TEXT NULL,
  `api_response` LONGTEXT NULL,
  `scheduled_at` DATETIME NULL,
  `sent_at` DATETIME NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_wq_status` (`status`,`scheduled_at`),
  KEY `idx_wq_ref` (`ref_type`,`ref_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `whatsapp_inbound` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `from_number` VARCHAR(20) NOT NULL,
  `message` LONGTEXT NULL,
  `raw_json` LONGTEXT NULL,
  `matched_customer_id` INT UNSIGNED NULL,
  `matched_order_id` INT UNSIGNED NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_wi_from` (`from_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- System

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `module` VARCHAR(40) NOT NULL,
  `action` VARCHAR(40) NOT NULL,
  `ref_type` VARCHAR(40) NULL,
  `ref_id` INT UNSIGNED NULL,
  `description` VARCHAR(500) NULL,
  `ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_al_user` (`user_id`),
  KEY `idx_al_module` (`module`),
  KEY `idx_al_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `body` VARCHAR(500) NULL,
  `url` VARCHAR(255) NULL,
  `icon` VARCHAR(60) NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`,`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL DEFAULT 1,
  `executed_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_migrations_file` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `update_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `from_version` VARCHAR(20) NULL,
  `to_version` VARCHAR(20) NULL,
  `from_commit` VARCHAR(64) NULL,
  `to_commit` VARCHAR(64) NULL,
  `status` ENUM('success','failed','rolled_back') NOT NULL,
  `log_text` LONGTEXT NULL,
  `backup_path` VARCHAR(255) NULL,
  `started_at` DATETIME NULL,
  `finished_at` DATETIME NULL,
  `triggered_by` INT UNSIGNED NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `identifier` VARCHAR(150) NOT NULL,
  `ip` VARCHAR(45) NOT NULL,
  `success` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_la_identifier` (`identifier`,`created_at`),
  KEY `idx_la_ip` (`ip`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `public_order_throttle` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) NOT NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pot_ip` (`ip`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
