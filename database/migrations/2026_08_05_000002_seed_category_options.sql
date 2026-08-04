-- Per-category question sets and calculation mode. Safe to re-run.

UPDATE `categories` SET `calc_mode`='simple', `tax_percent`=0, `requires_design`=1 WHERE `slug`='visiting-cards';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Printing Side','side','radio',1,1 FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Single Side',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='visiting-cards' AND o.`field_key`='side';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Double Side',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='visiting-cards' AND o.`field_key`='side';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Paper Type','paper_type','text',0,2 FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Lamination','lamination','select',0,3 FROM `categories` c WHERE c.`slug`='visiting-cards';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'None',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='visiting-cards' AND o.`field_key`='lamination';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Matt',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='visiting-cards' AND o.`field_key`='lamination';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Gloss',3 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='visiting-cards' AND o.`field_key`='lamination';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Velvet',4 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='visiting-cards' AND o.`field_key`='lamination';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'UV',5 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='visiting-cards' AND o.`field_key`='lamination';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,4 FROM `categories` c WHERE c.`slug`='visiting-cards';

UPDATE `categories` SET `calc_mode`='simple', `tax_percent`=0, `requires_design`=1 WHERE `slug`='bill-books';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Copies','copies','radio',1,1 FROM `categories` c WHERE c.`slug`='bill-books';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Duplicate (2 Copy)',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='bill-books' AND o.`field_key`='copies';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Triplicate (3 Copy)',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='bill-books' AND o.`field_key`='copies';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Colour','colour','radio',1,2 FROM `categories` c WHERE c.`slug`='bill-books';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Four Colour',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='bill-books' AND o.`field_key`='colour';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Single Colour',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='bill-books' AND o.`field_key`='colour';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Numbering','numbering','radio',0,3 FROM `categories` c WHERE c.`slug`='bill-books';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='bill-books' AND o.`field_key`='numbering';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='bill-books' AND o.`field_key`='numbering';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Numbering starts from','numbering_start','number',0,4 FROM `categories` c WHERE c.`slug`='bill-books';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Book Size','book_size','text',0,5 FROM `categories` c WHERE c.`slug`='bill-books';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,6 FROM `categories` c WHERE c.`slug`='bill-books';

UPDATE `categories` SET `calc_mode`='sqft', `tax_percent`=0, `requires_design`=1 WHERE `slug`='flex-banners';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Pipe','pipe','radio',0,1 FROM `categories` c WHERE c.`slug`='flex-banners';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='flex-banners' AND o.`field_key`='pipe';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='flex-banners' AND o.`field_key`='pipe';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Frame','frame','radio',0,2 FROM `categories` c WHERE c.`slug`='flex-banners';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='flex-banners' AND o.`field_key`='frame';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='flex-banners' AND o.`field_key`='frame';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Eyelets','eyelets','radio',0,3 FROM `categories` c WHERE c.`slug`='flex-banners';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='flex-banners' AND o.`field_key`='eyelets';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='flex-banners' AND o.`field_key`='eyelets';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Finishing','finishing','text',0,4 FROM `categories` c WHERE c.`slug`='flex-banners';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,5 FROM `categories` c WHERE c.`slug`='flex-banners';

UPDATE `categories` SET `calc_mode`='sqft', `tax_percent`=0, `requires_design`=1 WHERE `slug`='solvent-printing';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Pipe','pipe','radio',0,1 FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='solvent-printing' AND o.`field_key`='pipe';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='solvent-printing' AND o.`field_key`='pipe';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Frame','frame','radio',0,2 FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='solvent-printing' AND o.`field_key`='frame';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='solvent-printing' AND o.`field_key`='frame';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Eyelets','eyelets','radio',0,3 FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='solvent-printing' AND o.`field_key`='eyelets';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='solvent-printing' AND o.`field_key`='eyelets';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Finishing','finishing','text',0,4 FROM `categories` c WHERE c.`slug`='solvent-printing';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,5 FROM `categories` c WHERE c.`slug`='solvent-printing';

UPDATE `categories` SET `calc_mode`='sqft', `tax_percent`=0, `requires_design`=1 WHERE `slug`='eco-solvent';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Pipe','pipe','radio',0,1 FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='eco-solvent' AND o.`field_key`='pipe';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='eco-solvent' AND o.`field_key`='pipe';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Frame','frame','radio',0,2 FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='eco-solvent' AND o.`field_key`='frame';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='eco-solvent' AND o.`field_key`='frame';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Eyelets','eyelets','radio',0,3 FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='eco-solvent' AND o.`field_key`='eyelets';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='eco-solvent' AND o.`field_key`='eyelets';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Finishing','finishing','text',0,4 FROM `categories` c WHERE c.`slug`='eco-solvent';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,5 FROM `categories` c WHERE c.`slug`='eco-solvent';

UPDATE `categories` SET `calc_mode`='sqft', `tax_percent`=0, `requires_design`=1 WHERE `slug`='uv-special';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Pipe','pipe','radio',0,1 FROM `categories` c WHERE c.`slug`='uv-special';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='uv-special' AND o.`field_key`='pipe';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='uv-special' AND o.`field_key`='pipe';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Frame','frame','radio',0,2 FROM `categories` c WHERE c.`slug`='uv-special';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='uv-special' AND o.`field_key`='frame';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='uv-special' AND o.`field_key`='frame';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Eyelets','eyelets','radio',0,3 FROM `categories` c WHERE c.`slug`='uv-special';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='uv-special' AND o.`field_key`='eyelets';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='uv-special' AND o.`field_key`='eyelets';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Finishing','finishing','text',0,4 FROM `categories` c WHERE c.`slug`='uv-special';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,5 FROM `categories` c WHERE c.`slug`='uv-special';

UPDATE `categories` SET `calc_mode`='sqft', `tax_percent`=0, `requires_design`=1 WHERE `slug`='sun-pack';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Pipe','pipe','radio',0,1 FROM `categories` c WHERE c.`slug`='sun-pack';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='sun-pack' AND o.`field_key`='pipe';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='sun-pack' AND o.`field_key`='pipe';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Frame','frame','radio',0,2 FROM `categories` c WHERE c.`slug`='sun-pack';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='sun-pack' AND o.`field_key`='frame';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='sun-pack' AND o.`field_key`='frame';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Eyelets','eyelets','radio',0,3 FROM `categories` c WHERE c.`slug`='sun-pack';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='sun-pack' AND o.`field_key`='eyelets';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='sun-pack' AND o.`field_key`='eyelets';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Finishing','finishing','text',0,4 FROM `categories` c WHERE c.`slug`='sun-pack';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,5 FROM `categories` c WHERE c.`slug`='sun-pack';

UPDATE `categories` SET `calc_mode`='sqft', `tax_percent`=0, `requires_design`=1 WHERE `slug`='boards-signage';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Pipe','pipe','radio',0,1 FROM `categories` c WHERE c.`slug`='boards-signage';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='boards-signage' AND o.`field_key`='pipe';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='boards-signage' AND o.`field_key`='pipe';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Frame','frame','radio',0,2 FROM `categories` c WHERE c.`slug`='boards-signage';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='boards-signage' AND o.`field_key`='frame';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='boards-signage' AND o.`field_key`='frame';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Eyelets','eyelets','radio',0,3 FROM `categories` c WHERE c.`slug`='boards-signage';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='boards-signage' AND o.`field_key`='eyelets';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='boards-signage' AND o.`field_key`='eyelets';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Finishing','finishing','text',0,4 FROM `categories` c WHERE c.`slug`='boards-signage';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,5 FROM `categories` c WHERE c.`slug`='boards-signage';

UPDATE `categories` SET `calc_mode`='sqft', `tax_percent`=0, `requires_design`=0 WHERE `slug`='led-signage';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Size','size_ft','text',0,1 FROM `categories` c WHERE c.`slug`='led-signage';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,2 FROM `categories` c WHERE c.`slug`='led-signage';

UPDATE `categories` SET `calc_mode`='simple', `tax_percent`=0, `requires_design`=0 WHERE `slug`='display';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Print','print_included','radio',1,1 FROM `categories` c WHERE c.`slug`='display';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'With Print',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='display' AND o.`field_key`='print_included';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Stand Only',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='display' AND o.`field_key`='print_included';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Size','size_ft','text',0,2 FROM `categories` c WHERE c.`slug`='display';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,3 FROM `categories` c WHERE c.`slug`='display';

UPDATE `categories` SET `calc_mode`='sqft', `tax_percent`=0, `requires_design`=1 WHERE `slug`='stickers-labels';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Finish','finish','select',0,1 FROM `categories` c WHERE c.`slug`='stickers-labels';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Gloss',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='stickers-labels' AND o.`field_key`='finish';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Matt',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='stickers-labels' AND o.`field_key`='finish';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Transparent',3 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='stickers-labels' AND o.`field_key`='finish';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,2 FROM `categories` c WHERE c.`slug`='stickers-labels';

UPDATE `categories` SET `calc_mode`='simple', `tax_percent`=0, `requires_design`=1 WHERE `slug`='letter-head';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Printing Side','side','radio',1,1 FROM `categories` c WHERE c.`slug`='letter-head';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Single Side',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='letter-head' AND o.`field_key`='side';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Double Side',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='letter-head' AND o.`field_key`='side';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Paper Type','paper_type','text',0,2 FROM `categories` c WHERE c.`slug`='letter-head';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,3 FROM `categories` c WHERE c.`slug`='letter-head';

UPDATE `categories` SET `calc_mode`='simple', `tax_percent`=0, `requires_design`=1 WHERE `slug`='pamphlet';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Paper','paper_type','text',0,1 FROM `categories` c WHERE c.`slug`='pamphlet';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Colour','colour','radio',0,2 FROM `categories` c WHERE c.`slug`='pamphlet';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Four Colour',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='pamphlet' AND o.`field_key`='colour';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Single Colour',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='pamphlet' AND o.`field_key`='colour';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Folding','folding','select',0,3 FROM `categories` c WHERE c.`slug`='pamphlet';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No Fold',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='pamphlet' AND o.`field_key`='folding';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Half Fold',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='pamphlet' AND o.`field_key`='folding';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Z Fold',3 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='pamphlet' AND o.`field_key`='folding';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,4 FROM `categories` c WHERE c.`slug`='pamphlet';

UPDATE `categories` SET `calc_mode`='simple', `tax_percent`=0, `requires_design`=1 WHERE `slug`='envelope';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Size','size_text','text',0,1 FROM `categories` c WHERE c.`slug`='envelope';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Printing','printing','radio',0,2 FROM `categories` c WHERE c.`slug`='envelope';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Single Colour',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='envelope' AND o.`field_key`='printing';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Multi Colour',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='envelope' AND o.`field_key`='printing';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,3 FROM `categories` c WHERE c.`slug`='envelope';

UPDATE `categories` SET `calc_mode`='simple', `tax_percent`=0, `requires_design`=1 WHERE `slug`='rough-pad';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Pages per pad','pages','text',0,1 FROM `categories` c WHERE c.`slug`='rough-pad';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Size','size_text','text',0,2 FROM `categories` c WHERE c.`slug`='rough-pad';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,3 FROM `categories` c WHERE c.`slug`='rough-pad';

UPDATE `categories` SET `calc_mode`='simple', `tax_percent`=0, `requires_design`=1 WHERE `slug`='paper-bag';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Bag Size','bag_size','text',0,1 FROM `categories` c WHERE c.`slug`='paper-bag';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Lamination','lamination','select',0,2 FROM `categories` c WHERE c.`slug`='paper-bag';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'None',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='paper-bag' AND o.`field_key`='lamination';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Matt',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='paper-bag' AND o.`field_key`='lamination';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Gloss',3 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='paper-bag' AND o.`field_key`='lamination';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Velvet',4 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='paper-bag' AND o.`field_key`='lamination';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'UV',5 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='paper-bag' AND o.`field_key`='lamination';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,3 FROM `categories` c WHERE c.`slug`='paper-bag';

UPDATE `categories` SET `calc_mode`='simple', `tax_percent`=0, `requires_design`=1 WHERE `slug`='doctor-file';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Printing Side','side','radio',1,1 FROM `categories` c WHERE c.`slug`='doctor-file';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Single Side',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='doctor-file' AND o.`field_key`='side';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Double Side',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='doctor-file' AND o.`field_key`='side';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Paper Type','paper_type','text',0,2 FROM `categories` c WHERE c.`slug`='doctor-file';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,3 FROM `categories` c WHERE c.`slug`='doctor-file';

UPDATE `categories` SET `calc_mode`='simple', `tax_percent`=0, `requires_design`=1 WHERE `slug`='uv-dtf';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Sheet Size','size_text','text',0,1 FROM `categories` c WHERE c.`slug`='uv-dtf';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,2 FROM `categories` c WHERE c.`slug`='uv-dtf';

UPDATE `categories` SET `calc_mode`='simple', `tax_percent`=0, `requires_design`=1 WHERE `slug`='id-card';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Printing Side','side','radio',1,1 FROM `categories` c WHERE c.`slug`='id-card';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Single Side',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='id-card' AND o.`field_key`='side';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Double Side',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='id-card' AND o.`field_key`='side';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Lanyard','lanyard','radio',0,2 FROM `categories` c WHERE c.`slug`='id-card';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='id-card' AND o.`field_key`='lanyard';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='id-card' AND o.`field_key`='lanyard';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,3 FROM `categories` c WHERE c.`slug`='id-card';

UPDATE `categories` SET `calc_mode`='simple', `tax_percent`=0, `requires_design`=1 WHERE `slug`='garment-tag';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Printing Side','side','radio',1,1 FROM `categories` c WHERE c.`slug`='garment-tag';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Single Side',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='garment-tag' AND o.`field_key`='side';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Double Side',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='garment-tag' AND o.`field_key`='side';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Thread / Dori','dori','radio',0,2 FROM `categories` c WHERE c.`slug`='garment-tag';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'No',1 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='garment-tag' AND o.`field_key`='dori';
INSERT IGNORE INTO `category_option_values` (`option_id`,`label`,`sort_order`)
SELECT o.`id`,'Yes',2 FROM `category_options` o JOIN `categories` c ON c.`id`=o.`category_id` WHERE c.`slug`='garment-tag' AND o.`field_key`='dori';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,3 FROM `categories` c WHERE c.`slug`='garment-tag';

UPDATE `categories` SET `calc_mode`='simple', `tax_percent`=0, `requires_design`=1 WHERE `slug`='paper-wristband';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Size','size_text','text',0,1 FROM `categories` c WHERE c.`slug`='paper-wristband';
INSERT IGNORE INTO `category_options` (`category_id`,`label`,`field_key`,`field_type`,`is_required`,`sort_order`)
SELECT c.`id`,'Matter / text to print','content','textarea',0,2 FROM `categories` c WHERE c.`slug`='paper-wristband';

