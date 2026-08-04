-- Krishna Print — full product catalogue from the shop's own category/item list.
-- 211 items across 18 categories. Prices are left at 0 and every item is kept OFF the
-- public website (show_on_public = 0) until a real price is entered, so no customer can
-- ever be quoted zero. Counter staff can use them straight away and type the rate.
-- Safe to re-run: categories match on slug, items on sku, options on (item, field_key).

INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Visiting Card','visiting-cards','bi-credit-card-2-front',10,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Paper Bag','paper-bag','bi-bag',11,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Letter Head','letter-head','bi-file-text',12,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Rough Pad','rough-pad','bi-journal',13,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Pamphlet','pamphlet','bi-file-earmark-richtext',14,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Envelope','envelope','bi-envelope',15,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Bill Book','bill-books','bi-receipt',16,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('UV DTF','uv-dtf','bi-stickies',17,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Doctor File','doctor-file','bi-folder2',18,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Display','display','bi-easel',19,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Solvent Printing','solvent-printing','bi-image',20,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Eco Solvent','eco-solvent','bi-droplet',21,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('UV Special','uv-special','bi-brightness-high',22,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('LED Signage','led-signage','bi-lightbulb',23,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Sun Pack','sun-pack','bi-sun',24,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Paper Wristband','paper-wristband','bi-smartwatch',25,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('ID Card','id-card','bi-person-badge',26,1,1,NOW(),NOW());
INSERT IGNORE INTO `categories` (`name`,`slug`,`icon`,`sort_order`,`is_active`,`show_on_public`,`created_at`,`updated_at`)
VALUES ('Garment Tag','garment-tag','bi-tag',27,1,1,NOW(),NOW());

-- ---------- Visiting Card (36 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Tiny Small Card + Single Side + 250 GSM','VCD-001','box','slab',0,100,100,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-001' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-001' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-001' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-001' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'250 GSM + Gloss UV Coated Small Cards','VCD-002','box','slab',0,100,100,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-002' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-002' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-002' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-002' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'250 GSM Tearable Art Card','VCD-003','box','slab',0,100,100,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-003' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-003' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-003' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-003' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'250 GSM + Gloss UV Coated Cards','VCD-004','box','slab',0,100,100,18,1,24,0,1,4,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-004' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-004' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-004' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-004' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'250 GSM + Both Side Gloss Lamination','VCD-005','box','slab',0,100,100,18,1,24,0,1,5,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-005' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-005' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-005' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-005' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Non Tearable + NT','VCD-006','box','slab',0,100,100,18,1,24,0,1,6,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-006' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-006' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-006' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-006' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Non Tearable with Gloss Coated + NT','VCD-007','box','slab',0,100,100,18,1,24,0,1,7,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-007' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-007' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-007' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-007' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Non Tearable Drip Off + NT','VCD-008','box','slab',0,100,100,18,1,24,0,1,8,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-008' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-008' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-008' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-008' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'300 GSM Both Side Print + Tearable Art Card','VCD-009','box','slab',0,100,100,18,1,24,0,1,9,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-009' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-009' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-009' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-009' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'350 GSM Matt Card','VCD-010','box','slab',0,100,100,18,1,24,0,1,10,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-010' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-010' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-010' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-010' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'350 GSM Gloss UV Coated + Texture','VCD-011','box','slab',0,100,100,18,1,24,0,1,11,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-011' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-011' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-011' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-011' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'350 GSM Matt UV Coated + Texture','VCD-012','box','slab',0,100,100,18,1,24,0,1,12,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-012' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-012' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-012' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-012' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-012' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-012' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-012' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'350 GSM + UV Card','VCD-013','box','slab',0,100,100,18,1,24,0,1,13,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-013' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-013' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-013' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-013' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-013' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-013' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-013' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Velvet Card + 350 GSM + UV','VCD-014','box','slab',0,100,100,18,1,24,0,1,14,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-014' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-014' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-014' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-014' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-014' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-014' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-014' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'400 GSM + UV Card','VCD-015','box','slab',0,100,100,18,1,24,0,1,15,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-015' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-015' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-015' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-015' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-015' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-015' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-015' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Matte Lamination + UV 400 GSM','VCD-016','box','slab',0,100,100,18,1,24,0,1,16,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-016' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-016' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-016' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-016' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-016' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-016' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-016' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Horizontal Folded 400 GSM','VCD-017','box','slab',0,100,100,18,1,24,0,1,17,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-017' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-017' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-017' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-017' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-017' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-017' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-017' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Vertical Folded 400 GSM','VCD-018','box','slab',0,100,100,18,1,24,0,1,18,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-018' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-018' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-018' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-018' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-018' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-018' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-018' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Matte Lamination + Foil 1 Side + 400 GSM','VCD-019','box','slab',0,100,100,18,1,24,0,1,19,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-019' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-019' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-019' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-019' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-019' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-019' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-019' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'400 GSM Drip Off','VCD-020','box','slab',0,100,100,18,1,24,0,1,20,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-020' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-020' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-020' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-020' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-020' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-020' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-020' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Velvet Lamination + UV + Foil 1 Side + 500 GSM','VCD-021','box','slab',0,100,100,18,1,24,0,1,21,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-021' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-021' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-021' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-021' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-021' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-021' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-021' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'500 GSM Metallic Printing + Drip Off','VCD-022','box','slab',0,100,100,18,1,24,0,1,22,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-022' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-022' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-022' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-022' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-022' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-022' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-022' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Velvet Card + 500 GSM + UV + Foil','VCD-023','box','slab',0,100,100,18,1,24,0,1,23,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-023' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-023' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-023' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-023' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-023' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-023' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-023' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'500 GSM + Matte + UV + Foil + Round Cut','VCD-024','box','slab',0,100,100,18,1,24,0,1,24,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-024' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-024' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-024' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-024' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-024' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-024' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-024' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'800 GSM + Matte + UV + Foil + Die Cut','VCD-025','box','slab',0,100,100,18,1,24,0,1,25,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-025' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-025' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-025' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-025' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-025' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-025' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-025' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'800 GSM + Velvet + UV + Foil','VCD-026','box','slab',0,100,100,18,1,24,0,1,26,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-026' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-026' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-026' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-026' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-026' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-026' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-026' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'800 GSM Craft Sheet + Foil','VCD-027','box','slab',0,100,100,18,1,24,0,1,27,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-027' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-027' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-027' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-027' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-027' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-027' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-027' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'ATM Pouch Glossy Lamination','VCD-028','box','slab',0,100,100,18,1,24,0,1,28,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-028' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-028' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-028' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-028' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-028' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-028' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-028' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'ATM Pouch Matt Lamination','VCD-029','box','slab',0,100,100,18,1,24,0,1,29,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-029' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-029' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-029' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-029' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-029' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-029' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-029' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'PVC Fusion Card Matte + UV + Foil','VCD-030','box','slab',0,100,100,18,1,24,0,1,30,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-030' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-030' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-030' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-030' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-030' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-030' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-030' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'PVC Fusion Card Velvet + UV + Foil','VCD-031','box','slab',0,100,100,18,1,24,0,1,31,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-031' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-031' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-031' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-031' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-031' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-031' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-031' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'PVC Fusion Card Silver Base','VCD-032','box','slab',0,100,100,18,1,24,0,1,32,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-032' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-032' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-032' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-032' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-032' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-032' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-032' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'PVC Fusion Card Golden Base','VCD-033','box','slab',0,100,100,18,1,24,0,1,33,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-033' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-033' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-033' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-033' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-033' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-033' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-033' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Metal Visiting Card','VCD-034','box','slab',0,100,100,18,1,24,0,1,34,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-034' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-034' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-034' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-034' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-034' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-034' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-034' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Vertical','VCD-035','box','slab',0,100,100,18,1,24,0,1,35,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-035' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-035' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-035' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-035' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-035' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-035' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-035' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Horizontal','VCD-036','box','slab',0,100,100,18,1,24,0,1,36,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='VCD-036' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-036' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-036' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Corner','corner','radio',0,2,0 FROM `items` i WHERE i.`sku`='VCD-036' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='corner');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Normal',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-036' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Normal');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Round Cut',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='VCD-036' AND o.`field_key`='corner' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Round Cut');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='VCD-036' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- Paper Bag (2 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'250 GSM JK Ultima (Matt Lamination)','PBG-001','pcs','per_unit',0,50,10,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='paper-bag';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Bag Size','bag_size','select',1,1,1 FROM `items` i WHERE i.`sku`='PBG-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='bag_size');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Small',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PBG-001' AND o.`field_key`='bag_size' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Small');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Medium',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PBG-001' AND o.`field_key`='bag_size' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Medium');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Large',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PBG-001' AND o.`field_key`='bag_size' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Large');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='PBG-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'170 GSM JK Ultima (Matt Lamination)','PBG-002','pcs','per_unit',0,50,10,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='paper-bag';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Bag Size','bag_size','select',1,1,1 FROM `items` i WHERE i.`sku`='PBG-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='bag_size');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Small',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PBG-002' AND o.`field_key`='bag_size' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Small');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Medium',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PBG-002' AND o.`field_key`='bag_size' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Medium');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Large',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PBG-002' AND o.`field_key`='bag_size' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Large');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='PBG-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- Letter Head (3 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'A4 Affordable Letter Pad','LTH-001','pcs','slab',0,100,100,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='letter-head';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='LTH-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='LTH-001' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='LTH-001' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='LTH-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Premium Letter Head','LTH-002','pcs','slab',0,100,100,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='letter-head';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='LTH-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='LTH-002' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='LTH-002' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='LTH-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'100 GSM Premium Letter Head','LTH-003','pcs','slab',0,100,100,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='letter-head';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='LTH-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='LTH-003' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='LTH-003' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='LTH-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- Rough Pad (5 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'2.75×4.25','RPD-001','pad','per_unit',0,10,1,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='rough-pad';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Pages per pad','pages','select',1,1,1 FROM `items` i WHERE i.`sku`='RPD-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='pages');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'50 Pages',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='RPD-001' AND o.`field_key`='pages' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='50 Pages');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'100 Pages',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='RPD-001' AND o.`field_key`='pages' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='100 Pages');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='RPD-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'4.25×5.25','RPD-002','pad','per_unit',0,10,1,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='rough-pad';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Pages per pad','pages','select',1,1,1 FROM `items` i WHERE i.`sku`='RPD-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='pages');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'50 Pages',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='RPD-002' AND o.`field_key`='pages' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='50 Pages');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'100 Pages',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='RPD-002' AND o.`field_key`='pages' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='100 Pages');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='RPD-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'4.25×7','RPD-003','pad','per_unit',0,10,1,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='rough-pad';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Pages per pad','pages','select',1,1,1 FROM `items` i WHERE i.`sku`='RPD-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='pages');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'50 Pages',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='RPD-003' AND o.`field_key`='pages' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='50 Pages');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'100 Pages',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='RPD-003' AND o.`field_key`='pages' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='100 Pages');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='RPD-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'5.25×8.25','RPD-004','pad','per_unit',0,10,1,18,1,24,0,1,4,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='rough-pad';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Pages per pad','pages','select',1,1,1 FROM `items` i WHERE i.`sku`='RPD-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='pages');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'50 Pages',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='RPD-004' AND o.`field_key`='pages' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='50 Pages');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'100 Pages',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='RPD-004' AND o.`field_key`='pages' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='100 Pages');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='RPD-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Doctor Rough Pad','RPD-005','pad','per_unit',0,10,1,18,1,24,0,1,5,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='rough-pad';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Pages per pad','pages','select',1,1,1 FROM `items` i WHERE i.`sku`='RPD-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='pages');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'50 Pages',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='RPD-005' AND o.`field_key`='pages' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='50 Pages');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'100 Pages',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='RPD-005' AND o.`field_key`='pages' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='100 Pages');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='RPD-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- Pamphlet (4 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'A5 Four Colour','PMP-001','pcs','slab',0,100,100,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='pamphlet';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Paper','paper','select',1,1,1 FROM `items` i WHERE i.`sku`='PMP-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='paper');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'70 GSM',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-001' AND o.`field_key`='paper' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='70 GSM');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'90 GSM',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-001' AND o.`field_key`='paper' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='90 GSM');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'130 GSM Art',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-001' AND o.`field_key`='paper' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='130 GSM Art');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Folding','folding','radio',0,2,0 FROM `items` i WHERE i.`sku`='PMP-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='folding');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'No Fold',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-001' AND o.`field_key`='folding' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='No Fold');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Half Fold',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-001' AND o.`field_key`='folding' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Half Fold');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Z Fold',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-001' AND o.`field_key`='folding' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Z Fold');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='PMP-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'A4 Four Colour','PMP-002','pcs','slab',0,100,100,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='pamphlet';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Paper','paper','select',1,1,1 FROM `items` i WHERE i.`sku`='PMP-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='paper');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'70 GSM',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-002' AND o.`field_key`='paper' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='70 GSM');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'90 GSM',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-002' AND o.`field_key`='paper' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='90 GSM');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'130 GSM Art',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-002' AND o.`field_key`='paper' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='130 GSM Art');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Folding','folding','radio',0,2,0 FROM `items` i WHERE i.`sku`='PMP-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='folding');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'No Fold',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-002' AND o.`field_key`='folding' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='No Fold');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Half Fold',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-002' AND o.`field_key`='folding' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Half Fold');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Z Fold',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-002' AND o.`field_key`='folding' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Z Fold');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='PMP-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'A5 Single Colour','PMP-003','pcs','slab',0,100,100,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='pamphlet';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Paper','paper','select',1,1,1 FROM `items` i WHERE i.`sku`='PMP-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='paper');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'70 GSM',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-003' AND o.`field_key`='paper' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='70 GSM');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'90 GSM',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-003' AND o.`field_key`='paper' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='90 GSM');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'130 GSM Art',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-003' AND o.`field_key`='paper' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='130 GSM Art');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Folding','folding','radio',0,2,0 FROM `items` i WHERE i.`sku`='PMP-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='folding');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'No Fold',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-003' AND o.`field_key`='folding' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='No Fold');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Half Fold',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-003' AND o.`field_key`='folding' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Half Fold');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Z Fold',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-003' AND o.`field_key`='folding' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Z Fold');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='PMP-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'A4 Single Colour','PMP-004','pcs','slab',0,100,100,18,1,24,0,1,4,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='pamphlet';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Paper','paper','select',1,1,1 FROM `items` i WHERE i.`sku`='PMP-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='paper');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'70 GSM',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-004' AND o.`field_key`='paper' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='70 GSM');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'90 GSM',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-004' AND o.`field_key`='paper' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='90 GSM');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'130 GSM Art',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-004' AND o.`field_key`='paper' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='130 GSM Art');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Folding','folding','radio',0,2,0 FROM `items` i WHERE i.`sku`='PMP-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='folding');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'No Fold',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-004' AND o.`field_key`='folding' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='No Fold');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Half Fold',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-004' AND o.`field_key`='folding' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Half Fold');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Z Fold',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='PMP-004' AND o.`field_key`='folding' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Z Fold');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,3,0 FROM `items` i WHERE i.`sku`='PMP-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- Envelope (10 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'9.5×4.25','ENV-001','pcs','slab',0,100,100,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='envelope';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing','printing','radio',1,1,1 FROM `items` i WHERE i.`sku`='ENV-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='printing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-001' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Multi Colour',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-001' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Multi Colour');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='ENV-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'11×4.75','ENV-002','pcs','slab',0,100,100,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='envelope';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing','printing','radio',1,1,1 FROM `items` i WHERE i.`sku`='ENV-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='printing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-002' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Multi Colour',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-002' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Multi Colour');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='ENV-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'10.5×4.25','ENV-003','pcs','slab',0,100,100,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='envelope';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing','printing','radio',1,1,1 FROM `items` i WHERE i.`sku`='ENV-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='printing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-003' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Multi Colour',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-003' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Multi Colour');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='ENV-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'9.40×12.40','ENV-004','pcs','slab',0,100,100,18,1,24,0,1,4,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='envelope';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing','printing','radio',1,1,1 FROM `items` i WHERE i.`sku`='ENV-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='printing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-004' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Multi Colour',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-004' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Multi Colour');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='ENV-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'8.60×10.60','ENV-005','pcs','slab',0,100,100,18,1,24,0,1,5,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='envelope';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing','printing','radio',1,1,1 FROM `items` i WHERE i.`sku`='ENV-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='printing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-005' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Multi Colour',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-005' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Multi Colour');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='ENV-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'9×4','ENV-006','pcs','slab',0,100,100,18,1,24,0,1,6,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='envelope';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing','printing','radio',1,1,1 FROM `items` i WHERE i.`sku`='ENV-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='printing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-006' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Multi Colour',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-006' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Multi Colour');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='ENV-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'9.70×4.20','ENV-007','pcs','slab',0,100,100,18,1,24,0,1,7,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='envelope';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing','printing','radio',1,1,1 FROM `items` i WHERE i.`sku`='ENV-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='printing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-007' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Multi Colour',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-007' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Multi Colour');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='ENV-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'6×8','ENV-008','pcs','slab',0,100,100,18,1,24,0,1,8,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='envelope';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing','printing','radio',1,1,1 FROM `items` i WHERE i.`sku`='ENV-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='printing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-008' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Multi Colour',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-008' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Multi Colour');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='ENV-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'5×7','ENV-009','pcs','slab',0,100,100,18,1,24,0,1,9,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='envelope';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing','printing','radio',1,1,1 FROM `items` i WHERE i.`sku`='ENV-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='printing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-009' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Multi Colour',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-009' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Multi Colour');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='ENV-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'10.75×4.75','ENV-010','pcs','slab',0,100,100,18,1,24,0,1,10,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='envelope';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing','printing','radio',1,1,1 FROM `items` i WHERE i.`sku`='ENV-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='printing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-010' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Multi Colour',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ENV-010' AND o.`field_key`='printing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Multi Colour');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='ENV-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- Bill Book (6 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'A4 2 Copy FC','BBK-001','book','per_unit',0,1,1,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='bill-books';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Copies','copies','radio',1,1,1 FROM `items` i WHERE i.`sku`='BBK-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='copies');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'2 Copy (Duplicate)',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-001' AND o.`field_key`='copies' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='2 Copy (Duplicate)');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'3 Copy (Triplicate)',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-001' AND o.`field_key`='copies' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='3 Copy (Triplicate)');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Colour','colour','radio',1,2,1 FROM `items` i WHERE i.`sku`='BBK-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Full Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-001' AND o.`field_key`='colour' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Full Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour / B&W',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-001' AND o.`field_key`='colour' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour / B&W');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Numbering','numbering','radio',0,3,0 FROM `items` i WHERE i.`sku`='BBK-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='numbering');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Yes',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-001' AND o.`field_key`='numbering' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Yes');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'No',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-001' AND o.`field_key`='numbering' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='No');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Numbering starts from','numbering_start','number',0,4,0 FROM `items` i WHERE i.`sku`='BBK-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='numbering_start');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,5,0 FROM `items` i WHERE i.`sku`='BBK-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'A8 2 Copy FC','BBK-002','book','per_unit',0,1,1,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='bill-books';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Copies','copies','radio',1,1,1 FROM `items` i WHERE i.`sku`='BBK-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='copies');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'2 Copy (Duplicate)',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-002' AND o.`field_key`='copies' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='2 Copy (Duplicate)');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'3 Copy (Triplicate)',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-002' AND o.`field_key`='copies' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='3 Copy (Triplicate)');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Colour','colour','radio',1,2,1 FROM `items` i WHERE i.`sku`='BBK-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Full Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-002' AND o.`field_key`='colour' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Full Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour / B&W',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-002' AND o.`field_key`='colour' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour / B&W');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Numbering','numbering','radio',0,3,0 FROM `items` i WHERE i.`sku`='BBK-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='numbering');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Yes',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-002' AND o.`field_key`='numbering' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Yes');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'No',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-002' AND o.`field_key`='numbering' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='No');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Numbering starts from','numbering_start','number',0,4,0 FROM `items` i WHERE i.`sku`='BBK-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='numbering_start');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,5,0 FROM `items` i WHERE i.`sku`='BBK-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'A4 3 Copy FC','BBK-003','book','per_unit',0,1,1,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='bill-books';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Copies','copies','radio',1,1,1 FROM `items` i WHERE i.`sku`='BBK-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='copies');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'2 Copy (Duplicate)',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-003' AND o.`field_key`='copies' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='2 Copy (Duplicate)');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'3 Copy (Triplicate)',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-003' AND o.`field_key`='copies' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='3 Copy (Triplicate)');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Colour','colour','radio',1,2,1 FROM `items` i WHERE i.`sku`='BBK-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Full Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-003' AND o.`field_key`='colour' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Full Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour / B&W',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-003' AND o.`field_key`='colour' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour / B&W');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Numbering','numbering','radio',0,3,0 FROM `items` i WHERE i.`sku`='BBK-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='numbering');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Yes',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-003' AND o.`field_key`='numbering' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Yes');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'No',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-003' AND o.`field_key`='numbering' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='No');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Numbering starts from','numbering_start','number',0,4,0 FROM `items` i WHERE i.`sku`='BBK-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='numbering_start');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,5,0 FROM `items` i WHERE i.`sku`='BBK-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'A4 2 Copy SC','BBK-004','book','per_unit',0,1,1,18,1,24,0,1,4,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='bill-books';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Copies','copies','radio',1,1,1 FROM `items` i WHERE i.`sku`='BBK-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='copies');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'2 Copy (Duplicate)',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-004' AND o.`field_key`='copies' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='2 Copy (Duplicate)');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'3 Copy (Triplicate)',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-004' AND o.`field_key`='copies' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='3 Copy (Triplicate)');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Colour','colour','radio',1,2,1 FROM `items` i WHERE i.`sku`='BBK-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Full Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-004' AND o.`field_key`='colour' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Full Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour / B&W',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-004' AND o.`field_key`='colour' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour / B&W');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Numbering','numbering','radio',0,3,0 FROM `items` i WHERE i.`sku`='BBK-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='numbering');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Yes',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-004' AND o.`field_key`='numbering' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Yes');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'No',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-004' AND o.`field_key`='numbering' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='No');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Numbering starts from','numbering_start','number',0,4,0 FROM `items` i WHERE i.`sku`='BBK-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='numbering_start');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,5,0 FROM `items` i WHERE i.`sku`='BBK-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'A5 2 Copy SC','BBK-005','book','per_unit',0,1,1,18,1,24,0,1,5,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='bill-books';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Copies','copies','radio',1,1,1 FROM `items` i WHERE i.`sku`='BBK-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='copies');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'2 Copy (Duplicate)',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-005' AND o.`field_key`='copies' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='2 Copy (Duplicate)');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'3 Copy (Triplicate)',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-005' AND o.`field_key`='copies' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='3 Copy (Triplicate)');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Colour','colour','radio',1,2,1 FROM `items` i WHERE i.`sku`='BBK-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Full Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-005' AND o.`field_key`='colour' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Full Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour / B&W',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-005' AND o.`field_key`='colour' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour / B&W');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Numbering','numbering','radio',0,3,0 FROM `items` i WHERE i.`sku`='BBK-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='numbering');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Yes',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-005' AND o.`field_key`='numbering' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Yes');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'No',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-005' AND o.`field_key`='numbering' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='No');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Numbering starts from','numbering_start','number',0,4,0 FROM `items` i WHERE i.`sku`='BBK-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='numbering_start');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,5,0 FROM `items` i WHERE i.`sku`='BBK-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'A8 2 Copy SC','BBK-006','book','per_unit',0,1,1,18,1,24,0,1,6,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='bill-books';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Copies','copies','radio',1,1,1 FROM `items` i WHERE i.`sku`='BBK-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='copies');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'2 Copy (Duplicate)',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-006' AND o.`field_key`='copies' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='2 Copy (Duplicate)');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'3 Copy (Triplicate)',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-006' AND o.`field_key`='copies' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='3 Copy (Triplicate)');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Colour','colour','radio',1,2,1 FROM `items` i WHERE i.`sku`='BBK-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Full Colour',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-006' AND o.`field_key`='colour' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Full Colour');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Colour / B&W',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-006' AND o.`field_key`='colour' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Colour / B&W');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Numbering','numbering','radio',0,3,0 FROM `items` i WHERE i.`sku`='BBK-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='numbering');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Yes',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-006' AND o.`field_key`='numbering' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Yes');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'No',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='BBK-006' AND o.`field_key`='numbering' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='No');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Numbering starts from','numbering_start','number',0,4,0 FROM `items` i WHERE i.`sku`='BBK-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='numbering_start');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,5,0 FROM `items` i WHERE i.`sku`='BBK-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- UV DTF (3 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'11×12','UDT-001','sheet','per_unit',0,1,1,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-dtf';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,1,0 FROM `items` i WHERE i.`sku`='UDT-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'11×16','UDT-002','sheet','per_unit',0,1,1,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-dtf';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,1,0 FROM `items` i WHERE i.`sku`='UDT-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'23×12','UDT-003','sheet','per_unit',0,1,1,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-dtf';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,1,0 FROM `items` i WHERE i.`sku`='UDT-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- Doctor File (3 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'250 GSM','DOC-001','pcs','per_unit',0,10,1,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='doctor-file';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='DOC-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DOC-001' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DOC-001' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DOC-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'300 GSM','DOC-002','pcs','per_unit',0,10,1,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='doctor-file';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='DOC-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DOC-002' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DOC-002' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DOC-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'350 GSM','DOC-003','pcs','per_unit',0,10,1,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='doctor-file';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='DOC-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DOC-003' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DOC-003' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DOC-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- Display (47 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Black Normal Standee','DSP-001','pcs','fixed',0,1,1,18,0,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-001' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-001' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Aluminium Roll Up Standee','DSP-002','pcs','fixed',0,1,1,18,0,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-002' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-002' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Luxury Roll Up Standee','DSP-003','pcs','fixed',0,1,1,18,0,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-003' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-003' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'L Standee','DSP-004','pcs','fixed',0,1,1,18,0,24,0,1,4,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-004' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-004' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'V Shape Roll Up','DSP-005','pcs','fixed',0,1,1,18,0,24,0,1,5,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-005' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-005' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'X Banner','DSP-006','pcs','fixed',0,1,1,18,0,24,0,1,6,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-006' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-006' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Umbrella','DSP-007','pcs','fixed',0,1,1,18,0,24,0,1,7,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-007' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-007' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Canopy','DSP-008','pcs','fixed',0,1,1,18,0,24,0,1,8,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-008' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-008' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Look Walker','DSP-009','pcs','fixed',0,1,1,18,0,24,0,1,9,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-009' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-009' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Velcro Pop-Up','DSP-010','pcs','fixed',0,1,1,18,0,24,0,1,10,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-010' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-010' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'MDF Promotional Table','DSP-011','pcs','fixed',0,1,1,18,0,24,0,1,11,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-011' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-011' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Promotional Table','DSP-012','pcs','fixed',0,1,1,18,0,24,0,1,12,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-012' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-012' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-012' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-012' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Mini Roll Up','DSP-013','pcs','fixed',0,1,1,18,0,24,0,1,13,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-013' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-013' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-013' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-013' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Rotating Round Lollipop','DSP-014','pcs','fixed',0,1,1,18,0,24,0,1,14,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-014' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-014' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-014' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-014' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Round Lollipop','DSP-015','pcs','fixed',0,1,1,18,0,24,0,1,15,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-015' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-015' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-015' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-015' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Square Lollipop','DSP-016','pcs','fixed',0,1,1,18,0,24,0,1,16,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-016' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-016' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-016' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-016' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'LED Tabletop','DSP-017','pcs','fixed',0,1,1,18,0,24,0,1,17,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-017' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-017' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-017' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-017' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Double Side Roll Up','DSP-018','pcs','fixed',0,1,1,18,0,24,0,1,18,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-018' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-018' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-018' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-018' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Exit Frame','DSP-019','pcs','fixed',0,1,1,18,0,24,0,1,19,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-019' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-019' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-019' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-019' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Scrolling Banner','DSP-020','pcs','fixed',0,1,1,18,0,24,0,1,20,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-020' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-020' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-020' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-020' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Black Premium Standee','DSP-021','pcs','fixed',0,1,1,18,0,24,0,1,21,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-021' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-021' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-021' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-021' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Aluminium Standee','DSP-022','pcs','fixed',0,1,1,18,0,24,0,1,22,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-022' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-022' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-022' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-022' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Luxury Roll Up','DSP-023','pcs','fixed',0,1,1,18,0,24,0,1,23,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-023' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-023' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-023' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-023' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Wooden Easel','DSP-024','pcs','fixed',0,1,1,18,0,24,0,1,24,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-024' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-024' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-024' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-024' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Economy Easel','DSP-025','pcs','fixed',0,1,1,18,0,24,0,1,25,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-025' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-025' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-025' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-025' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Premium Easel','DSP-026','pcs','fixed',0,1,1,18,0,24,0,1,26,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-026' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-026' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-026' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-026' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Tripod','DSP-027','pcs','fixed',0,1,1,18,0,24,0,1,27,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-027' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-027' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-027' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-027' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Folding Metal Easel','DSP-028','pcs','fixed',0,1,1,18,0,24,0,1,28,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-028' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-028' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-028' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-028' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'MS Metal Easel','DSP-029','pcs','fixed',0,1,1,18,0,24,0,1,29,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-029' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-029' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-029' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-029' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'MS Brochure','DSP-030','pcs','fixed',0,1,1,18,0,24,0,1,30,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-030' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-030' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-030' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-030' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Zig Zag Brochure','DSP-031','pcs','fixed',0,1,1,18,0,24,0,1,31,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-031' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-031' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-031' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-031' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'White MS Brochure','DSP-032','pcs','fixed',0,1,1,18,0,24,0,1,32,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-032' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-032' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-032' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-032' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Sun Board','DSP-033','pcs','fixed',0,1,1,18,0,24,0,1,33,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-033' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-033' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-033' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-033' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Mini X Banner','DSP-034','pcs','fixed',0,1,1,18,0,24,0,1,34,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-034' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-034' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-034' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-034' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Cut Out','DSP-035','pcs','fixed',0,1,1,18,0,24,0,1,35,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-035' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-035' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-035' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-035' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Poster Stand','DSP-036','pcs','fixed',0,1,1,18,0,24,0,1,36,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-036' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-036' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-036' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-036' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Promotional Umbrella','DSP-037','pcs','fixed',0,1,1,18,0,24,0,1,37,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-037' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-037' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-037' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-037' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Patio Umbrella','DSP-038','pcs','fixed',0,1,1,18,0,24,0,1,38,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-038' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-038' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-038' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-038' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Clip On Board','DSP-039','pcs','fixed',0,1,1,18,0,24,0,1,39,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-039' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-039' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-039' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-039' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Slim Glass LED','DSP-040','pcs','fixed',0,1,1,18,0,24,0,1,40,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-040' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-040' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-040' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-040' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Door Frame','DSP-041','pcs','fixed',0,1,1,18,0,24,0,1,41,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-041' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-041' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-041' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-041' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'V Shape','DSP-042','pcs','fixed',0,1,1,18,0,24,0,1,42,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-042' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-042' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-042' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-042' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'MDF Promotional','DSP-043','pcs','fixed',0,1,1,18,0,24,0,1,43,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-043' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-043' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-043' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-043' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Advertising Tent','DSP-044','pcs','fixed',0,1,1,18,0,24,0,1,44,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-044' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-044' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-044' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-044' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Adjustable Backdrop','DSP-045','pcs','fixed',0,1,1,18,0,24,0,1,45,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-045' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-045' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-045' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-045' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Gazebo Tent','DSP-046','pcs','fixed',0,1,1,18,0,24,0,1,46,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-046' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-046' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-046' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-046' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'A Board','DSP-047','pcs','fixed',0,1,1,18,0,24,0,1,47,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='display';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Print','print_included','radio',1,1,1 FROM `items` i WHERE i.`sku`='DSP-047' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='print_included');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'With Print',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-047' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='With Print');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Stand Only',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='DSP-047' AND o.`field_key`='print_included' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Stand Only');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='DSP-047' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- Solvent Printing (11 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'China Banner','SLV-001','sqft','area',0,1,1,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SLV-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SLV-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SLV-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-001' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-001' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-001' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SLV-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Black Back China','SLV-002','sqft','area',0,1,1,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SLV-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SLV-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SLV-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-002' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-002' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-002' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SLV-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Black Back Star','SLV-003','sqft','area',0,1,1,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SLV-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SLV-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SLV-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-003' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-003' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-003' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SLV-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Star Flex','SLV-004','sqft','area',0,1,1,18,1,24,0,1,4,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SLV-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SLV-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SLV-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-004' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-004' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-004' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SLV-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Vinyl','SLV-005','sqft','area',0,1,1,18,1,24,0,1,5,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SLV-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SLV-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SLV-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-005' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-005' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-005' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SLV-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Vinyl Lamination','SLV-006','sqft','area',0,1,1,18,1,24,0,1,6,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SLV-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SLV-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SLV-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-006' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-006' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-006' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SLV-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Vinyl+3mm','SLV-007','sqft','area',0,1,1,18,1,24,0,1,7,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SLV-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SLV-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SLV-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-007' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-007' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-007' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SLV-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Vinyl+5mm','SLV-008','sqft','area',0,1,1,18,1,24,0,1,8,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SLV-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SLV-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SLV-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-008' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-008' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-008' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SLV-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'One Way Vision','SLV-009','sqft','area',0,1,1,18,1,24,0,1,9,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SLV-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SLV-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SLV-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-009' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-009' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-009' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SLV-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Backlight','SLV-010','sqft','area',0,1,1,18,1,24,0,1,10,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SLV-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SLV-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SLV-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-010' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-010' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-010' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SLV-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Fabric Frontlit','SLV-011','sqft','area',0,1,1,18,1,24,0,1,11,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SLV-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SLV-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SLV-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-011' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-011' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SLV-011' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SLV-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- Eco Solvent (24 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Star','ECO-001','sqft','area',0,1,1,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-001' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-001' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-001' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Black Back','ECO-002','sqft','area',0,1,1,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-002' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-002' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-002' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Gray Back','ECO-003','sqft','area',0,1,1,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-003' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-003' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-003' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Backlit','ECO-004','sqft','area',0,1,1,18,1,24,0,1,4,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-004' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-004' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-004' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Vinyl','ECO-005','sqft','area',0,1,1,18,1,24,0,1,5,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-005' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-005' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-005' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Vinyl Lamination','ECO-006','sqft','area',0,1,1,18,1,24,0,1,6,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-006' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-006' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-006' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Vinyl Gray Back','ECO-007','sqft','area',0,1,1,18,1,24,0,1,7,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-007' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-007' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-007' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Vinyl Gray Lamination','ECO-008','sqft','area',0,1,1,18,1,24,0,1,8,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-008' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-008' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-008' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Vinyl Print Cut','ECO-009','sqft','area',0,1,1,18,1,24,0,1,9,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-009' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-009' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-009' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Vinyl Lami Cut','ECO-010','sqft','area',0,1,1,18,1,24,0,1,10,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-010' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-010' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-010' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Vinyl+3mm','ECO-011','sqft','area',0,1,1,18,1,24,0,1,11,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-011' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-011' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-011' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Vinyl+5mm','ECO-012','sqft','area',0,1,1,18,1,24,0,1,12,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-012' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-012' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-012' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-012' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-012' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-012' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-012' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Vinyl+5mm Cut','ECO-013','sqft','area',0,1,1,18,1,24,0,1,13,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-013' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-013' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-013' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-013' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-013' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-013' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-013' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco One Way','ECO-014','sqft','area',0,1,1,18,1,24,0,1,14,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-014' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-014' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-014' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-014' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-014' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-014' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-014' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Fabric Frontlit','ECO-015','sqft','area',0,1,1,18,1,24,0,1,15,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-015' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-015' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-015' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-015' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-015' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-015' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-015' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Vinyl Sparcal','ECO-016','sqft','area',0,1,1,18,1,24,0,1,16,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-016' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-016' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-016' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-016' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-016' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-016' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-016' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Frosted Glass','ECO-017','sqft','area',0,1,1,18,1,24,0,1,17,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-017' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-017' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-017' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-017' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-017' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-017' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-017' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Clear','ECO-018','sqft','area',0,1,1,18,1,24,0,1,18,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-018' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-018' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-018' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-018' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-018' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-018' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-018' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Retro','ECO-019','sqft','area',0,1,1,18,1,24,0,1,19,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-019' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-019' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-019' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-019' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-019' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-019' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-019' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Retro Flex','ECO-020','sqft','area',0,1,1,18,1,24,0,1,20,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-020' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-020' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-020' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-020' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-020' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-020' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-020' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Eco Translite','ECO-021','sqft','area',0,1,1,18,1,24,0,1,21,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-021' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-021' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-021' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-021' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-021' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-021' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-021' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Radium Print','ECO-022','sqft','area',0,1,1,18,1,24,0,1,22,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-022' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-022' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-022' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-022' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-022' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-022' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-022' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Radium Lamination','ECO-023','sqft','area',0,1,1,18,1,24,0,1,23,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-023' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-023' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-023' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-023' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-023' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-023' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-023' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Radium Lami Cut','ECO-024','sqft','area',0,1,1,18,1,24,0,1,24,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='ECO-024' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='ECO-024' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='ECO-024' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-024' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-024' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='ECO-024' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='ECO-024' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- UV Special (21 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV BB Frontlit','UVS-001','sqft','area',0,1,1,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-001' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-001' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-001' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Vinyl','UVS-002','sqft','area',0,1,1,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-002' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-002' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-002' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Star Frontlit','UVS-003','sqft','area',0,1,1,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-003' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-003' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-003' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Backlit','UVS-004','sqft','area',0,1,1,18,1,24,0,1,4,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-004' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-004' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-004' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Frontlit Clear','UVS-005','sqft','area',0,1,1,18,1,24,0,1,5,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-005' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-005' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-005' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Backlit Clear','UVS-006','sqft','area',0,1,1,18,1,24,0,1,6,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-006' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-006' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-006' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Retro','UVS-007','sqft','area',0,1,1,18,1,24,0,1,7,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-007' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-007' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-007' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Backlit Fabric China','UVS-008','sqft','area',0,1,1,18,1,24,0,1,8,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-008' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-008' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-008' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Backlit 3 Layer','UVS-009','sqft','area',0,1,1,18,1,24,0,1,9,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-009' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-009' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-009' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-009' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Scrimless','UVS-010','sqft','area',0,1,1,18,1,24,0,1,10,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-010' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-010' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-010' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-010' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Radium','UVS-011','sqft','area',0,1,1,18,1,24,0,1,11,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-011' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-011' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-011' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-011' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Highway','UVS-012','sqft','area',0,1,1,18,1,24,0,1,12,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-012' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-012' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-012' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-012' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-012' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-012' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-012' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Backlit Fabric Korea','UVS-013','sqft','area',0,1,1,18,1,24,0,1,13,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-013' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-013' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-013' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-013' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-013' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-013' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-013' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Frontlit Fabric','UVS-014','sqft','area',0,1,1,18,1,24,0,1,14,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-014' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-014' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-014' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-014' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-014' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-014' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-014' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Star Translight','UVS-015','sqft','area',0,1,1,18,1,24,0,1,15,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-015' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-015' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-015' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-015' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-015' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-015' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-015' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Canvas','UVS-016','sqft','area',0,1,1,18,1,24,0,1,16,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-016' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-016' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-016' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-016' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-016' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-016' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-016' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Optical Clear','UVS-017','sqft','area',0,1,1,18,1,24,0,1,17,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-017' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-017' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-017' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-017' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-017' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-017' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-017' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Wallpaper','UVS-018','sqft','area',0,1,1,18,1,24,0,1,18,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-018' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-018' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-018' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-018' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-018' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-018' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-018' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Silver/Golden','UVS-019','sqft','area',0,1,1,18,1,24,0,1,19,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-019' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-019' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-019' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-019' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-019' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-019' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-019' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Vinyl+3mm','UVS-020','sqft','area',0,1,1,18,1,24,0,1,20,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-020' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-020' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-020' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-020' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-020' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-020' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-020' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'UV Vinyl+5mm','UVS-021','sqft','area',0,1,1,18,1,24,0,1,21,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='uv-special';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='UVS-021' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='UVS-021' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='UVS-021' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-021' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-021' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='UVS-021' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='UVS-021' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- LED Signage (7 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Wall Mount','LED-001','pcs','fixed',0,1,1,18,0,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='led-signage';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Size (ft)','size_ft','text',0,1,0 FROM `items` i WHERE i.`sku`='LED-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='size_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='LED-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Easel','LED-002','pcs','fixed',0,1,1,18,0,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='led-signage';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Size (ft)','size_ft','text',0,1,0 FROM `items` i WHERE i.`sku`='LED-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='size_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='LED-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Horizontal','LED-003','pcs','fixed',0,1,1,18,0,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='led-signage';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Size (ft)','size_ft','text',0,1,0 FROM `items` i WHERE i.`sku`='LED-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='size_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='LED-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Vertical','LED-004','pcs','fixed',0,1,1,18,0,24,0,1,4,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='led-signage';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Size (ft)','size_ft','text',0,1,0 FROM `items` i WHERE i.`sku`='LED-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='size_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='LED-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Pole','LED-005','pcs','fixed',0,1,1,18,0,24,0,1,5,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='led-signage';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Size (ft)','size_ft','text',0,1,0 FROM `items` i WHERE i.`sku`='LED-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='size_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='LED-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Video Walker','LED-006','pcs','fixed',0,1,1,18,0,24,0,1,6,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='led-signage';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Size (ft)','size_ft','text',0,1,0 FROM `items` i WHERE i.`sku`='LED-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='size_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='LED-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Totem','LED-007','pcs','fixed',0,1,1,18,0,24,0,1,7,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='led-signage';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Size (ft)','size_ft','text',0,1,0 FROM `items` i WHERE i.`sku`='LED-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='size_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='LED-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- Sun Pack (8 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Vinyl18x24','SNP-001','sqft','area',0,1,1,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='sun-pack';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SNP-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SNP-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SNP-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-001' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-001' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-001' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SNP-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Vinyl18x16','SNP-002','sqft','area',0,1,1,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='sun-pack';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SNP-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SNP-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SNP-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-002' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-002' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-002' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SNP-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Vinyl24x24','SNP-003','sqft','area',0,1,1,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='sun-pack';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SNP-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SNP-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SNP-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-003' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-003' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-003' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SNP-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Vinyl18x12','SNP-004','sqft','area',0,1,1,18,1,24,0,1,4,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='sun-pack';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SNP-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SNP-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SNP-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-004' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-004' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-004' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SNP-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Retro18x24','SNP-005','sqft','area',0,1,1,18,1,24,0,1,5,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='sun-pack';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SNP-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SNP-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SNP-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-005' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-005' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-005' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SNP-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Retro18x16','SNP-006','sqft','area',0,1,1,18,1,24,0,1,6,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='sun-pack';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SNP-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SNP-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SNP-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-006' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-006' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-006' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SNP-006' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Retro24x24','SNP-007','sqft','area',0,1,1,18,1,24,0,1,7,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='sun-pack';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SNP-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SNP-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SNP-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-007' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-007' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-007' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SNP-007' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Retro18x12','SNP-008','sqft','area',0,1,1,18,1,24,0,1,8,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='sun-pack';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Width (ft)','width_ft','number',1,1,0 FROM `items` i WHERE i.`sku`='SNP-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='width_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Height (ft)','height_ft','number',1,2,0 FROM `items` i WHERE i.`sku`='SNP-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='height_ft');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Finishing','finishing','radio',0,3,0 FROM `items` i WHERE i.`sku`='SNP-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='finishing');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Plain',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-008' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Plain');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Eyelets',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-008' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Eyelets');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Rod Pocket',0,'add',0,3 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='SNP-008' AND o.`field_key`='finishing' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Rod Pocket');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,4,0 FROM `items` i WHERE i.`sku`='SNP-008' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- Paper Wristband (1 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Tyvek Wristband','PWB-001','pcs','per_unit',0,50,10,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='paper-wristband';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,1,0 FROM `items` i WHERE i.`sku`='PWB-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- ID Card (5 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'ID Card Cover','IDC-001','pcs','per_unit',0,10,1,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='id-card';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='IDC-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='IDC-001' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='IDC-001' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='IDC-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Premium Satin Lanyard','IDC-002','pcs','per_unit',0,10,1,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='id-card';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='IDC-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='IDC-002' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='IDC-002' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='IDC-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Affordable Lanyard','IDC-003','pcs','per_unit',0,10,1,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='id-card';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='IDC-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='IDC-003' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='IDC-003' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='IDC-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Premium Cover','IDC-004','pcs','per_unit',0,10,1,18,1,24,0,1,4,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='id-card';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='IDC-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='IDC-004' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='IDC-004' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='IDC-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'ID Card','IDC-005','pcs','per_unit',0,10,1,18,1,24,0,1,5,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='id-card';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='IDC-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='IDC-005' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='IDC-005' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='IDC-005' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

-- ---------- Garment Tag (4 items) ----------
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Gloss Coated','GTG-001','pcs','per_unit',0,100,50,18,1,24,0,1,1,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='garment-tag';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='GTG-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='GTG-001' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='GTG-001' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='GTG-001' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Matt Lamination','GTG-002','pcs','per_unit',0,100,50,18,1,24,0,1,2,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='garment-tag';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='GTG-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='GTG-002' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='GTG-002' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='GTG-002' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Matt+UV','GTG-003','pcs','per_unit',0,100,50,18,1,24,0,1,3,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='garment-tag';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='GTG-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='GTG-003' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='GTG-003' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='GTG-003' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');
INSERT IGNORE INTO `items` (`category_id`,`name`,`sku`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`,`updated_at`)
SELECT c.`id`,'Thread Dori','GTG-004','pcs','per_unit',0,100,50,18,1,24,0,1,4,1,NOW(),NOW() FROM `categories` c WHERE c.`slug`='garment-tag';
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Printing Side','side','radio',1,1,1 FROM `items` i WHERE i.`sku`='GTG-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Single Side',0,'add',1,1 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='GTG-004' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Single Side');
INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`)
SELECT o.`id`,'Double Side',0,'add',0,2 FROM `item_options` o JOIN `items` i ON i.`id`=o.`item_id` WHERE i.`sku`='GTG-004' AND o.`field_key`='side' AND NOT EXISTS (SELECT 1 FROM `item_option_values` v WHERE v.`option_id`=o.`id` AND v.`label`='Double Side');
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`,`affects_price`)
SELECT i.`id`,'Matter / text to print','content','textarea',1,2,0 FROM `items` i WHERE i.`sku`='GTG-004' AND NOT EXISTS (SELECT 1 FROM `item_options` o WHERE o.`item_id`=i.`id` AND o.`field_key`='content');

