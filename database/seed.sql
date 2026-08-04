-- Krishna Print — seed data
SET NAMES utf8mb4;

-- ---------------------------------------------------------------- Roles
INSERT INTO `roles` (`name`,`slug`,`is_system`,`description`) VALUES
('Super Admin','super_admin',1,'Full access to every branch and every module'),
('Branch Manager','branch_manager',1,'Manages orders, staff and reports of own branch'),
('Counter Staff','counter_staff',1,'Creates orders and takes payments at the counter'),
('Designer','designer',1,'Works on assigned design jobs and uploads proofs'),
('Production Staff','production',1,'Moves jobs through printing and post-press'),
('Delivery Staff','delivery',1,'Delivers ready jobs and collects balance'),
('Accountant','accountant',1,'Payments, ledgers and financial reports'),
('Customer','customer',1,'Public tracking and proof approval via signed links');

-- ---------------------------------------------------------------- Permissions
INSERT INTO `permissions` (`code`,`module`,`label`) VALUES
('dashboard.view','dashboard','View dashboard'),
('dashboard.view_all_branches','dashboard','View all branches on dashboard'),
('order.create','order','Create orders'),
('order.view','order','View orders (own branch)'),
('order.view_all','order','View orders of all branches'),
('order.edit','order','Edit orders'),
('order.delete','order','Delete orders'),
('order.cancel','order','Cancel orders'),
('order.change_status','order','Change order status'),
('order.override_approval','order','Mark design approved in person (skip online approval)'),
('order.assign','order','Assign designer / production'),
('customer.create','customer','Create customers'),
('customer.view','customer','View customers'),
('customer.edit','customer','Edit customers'),
('customer.delete','customer','Delete customers'),
('item.manage','catalog','Manage items'),
('category.manage','catalog','Manage categories'),
('price.manage','catalog','Manage pricing'),
('design.upload','design','Upload design proofs'),
('design.view','design','View design jobs'),
('design.approve_internal','design','Internal proof approval'),
('payment.create','payment','Record payments'),
('payment.view','payment','View payments'),
('payment.refund','payment','Record refunds'),
('payment.delete','payment','Delete payments'),
('branch.manage','admin','Manage branches'),
('user.manage','admin','Manage users'),
('role.manage','admin','Manage roles & permissions'),
('report.staff','report','Staff performance report'),
('report.branch','report','Branch reports'),
('report.sales','report','Sales reports'),
('report.outstanding','report','Outstanding report'),
('report.export','report','Export reports'),
('whatsapp.settings','whatsapp','WhatsApp settings'),
('whatsapp.templates','whatsapp','WhatsApp templates'),
('whatsapp.logs','whatsapp','WhatsApp logs'),
('whatsapp.resend','whatsapp','Resend WhatsApp messages'),
('settings.manage','system','Manage settings'),
('update.manage','system','Run system updates'),
('backup.manage','system','Manage backups'),
('log.view','system','View activity logs');

-- Super Admin: everything
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p WHERE r.slug='super_admin';

-- Branch Manager
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p WHERE r.slug='branch_manager' AND p.code IN
('dashboard.view','order.create','order.view','order.edit','order.cancel','order.change_status','order.override_approval','order.assign',
 'customer.create','customer.view','customer.edit','design.view','design.approve_internal',
 'payment.create','payment.view','payment.refund','report.staff','report.branch','report.sales',
 'report.outstanding','report.export','whatsapp.logs','whatsapp.resend','log.view');

-- Counter Staff
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p WHERE r.slug='counter_staff' AND p.code IN
('dashboard.view','order.create','order.view','order.edit','customer.create','customer.view','customer.edit',
 'payment.create','payment.view');

-- Designer
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p WHERE r.slug='designer' AND p.code IN
('dashboard.view','design.view','design.upload');

-- Production Staff
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p WHERE r.slug='production' AND p.code IN
('dashboard.view','order.view','order.change_status','design.view');

-- Delivery Staff
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p WHERE r.slug='delivery' AND p.code IN
('dashboard.view','order.view','order.change_status','payment.create','payment.view');

-- Accountant
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p WHERE r.slug='accountant' AND p.code IN
('dashboard.view','dashboard.view_all_branches','order.view','order.view_all','customer.view',
 'payment.create','payment.view','payment.refund','report.branch','report.sales','report.outstanding',
 'report.export','report.staff');

-- ---------------------------------------------------------------- Settings defaults
INSERT INTO `settings` (`group_key`,`setting_key`,`setting_value`,`is_encrypted`) VALUES
('general','business_name','Krishna Print',0),
('general','business_tagline','Printing | Branding | Innovation',0),
('general','business_logo','',0),
('general','business_favicon','',0),
('general','business_address','',0),
('general','business_city','',0),
('general','business_state','Gujarat',0),
('general','business_pincode','',0),
('general','business_phone','',0),
('general','business_whatsapp','',0),
('general','business_email','',0),
('general','business_gstin','',0),
('general','currency_symbol','₹',0),
('general','timezone','Asia/Kolkata',0),
('general','date_format','d-m-Y',0),
('general','brand_color','#0d6efd',0),
('app','base_url','',0),
('app','job_prefix','JOB',0),
('app','idle_timeout_minutes','120',0),
('app','asset_version','1',0),
('app','auto_assign_designer','0',0),
('app','revision_flag_threshold','3',0),
('app','proof_max_upload_mb','15',0),
('app','upload_max_mb','15',0),
('app','public_order_auto_confirm','0',0),
('app','public_otp_required','1',0),
('app','otp_expiry_minutes','10',0),
('app','balance_reminder_days','7',0),
('app','proof_reminder_hours','24',0),
('app','feedback_stale_hours','48',0),
('whatsapp','wa_enabled','0',0),
('whatsapp','wa_auto_send','1',0),
('whatsapp','wa_api_url','https://bulk.akdwk.in/api.php',0),
('whatsapp','wa_api_key','',1),
('whatsapp','wa_session_id','',0),
('whatsapp','wa_country_code','91',0),
('whatsapp','wa_rate_limit_per_min','12',0),
('whatsapp','wa_max_attempts','3',0),
('whatsapp','wa_quiet_start','22:00',0),
('whatsapp','wa_quiet_end','08:00',0),
('whatsapp','wa_webhook_secret','',0),
('whatsapp','wa_last_worker_run','',0),
('whatsapp','wa_last_worker_sent_at','',0),
('update','upd_repo_owner','',0),
('update','upd_repo_name','',0),
('update','upd_branch','main',0),
('update','upd_token','',1),
('update','upd_auto_check','1',0),
('update','upd_include_uploads','0',0),
('update','upd_keep_backups','5',0),
('update','current_version','1.0.0',0),
('update','current_commit','',0),
('update','last_update_check','',0),
('update','update_available','0',0),
('update','latest_version','',0),
('update','latest_commit','',0),
('update','latest_update_meta','',0);

-- ---------------------------------------------------------------- WhatsApp templates
INSERT INTO `whatsapp_templates` (`code`,`event_key`,`title`,`body`,`recipient`,`is_active`,`delay_minutes`) VALUES
('order_created_customer','order.created','Order confirmation to customer',
'Namaste {customer_name} 🙏\nYour order is confirmed at {business_name}.\n\nJob No: *{job_no}*\n{items_list}\nTotal: ₹{total}\nAdvance Paid: ₹{advance}\nBalance Due: ₹{balance}\nExpected Delivery: {due_date}\n\nTrack your order anytime:\n{tracking_link}\n\nThank you!\n{branch_name} | {branch_phone}','customer',1,0),
('order_created_designer','order.designer_assigned','New work assigned to designer',
'New work assigned to you 🎨\nJob: *{job_no}*\nItem: {item_name} (Qty {qty})\nCustomer: {customer_name}\nDue: {due_date}\nPriority: {priority}\n\nOpen your job board:\n{designer_link}','designer',1,0),
('order_created_manager','order.created','New order alert to branch manager',
'New order at {branch_name} 📋\nJob: *{job_no}*\nCustomer: {customer_name} ({customer_phone})\nTotal: ₹{total} | Advance: ₹{advance} | Balance: ₹{balance}\nTaken by: {staff_name}\nDue: {due_date}','branch_manager',1,0),
('proof_ready_customer','proof.sent','Design proof ready',
'Namaste {customer_name} 🙏\nYour design for Job *{job_no}* ({item_name}) is ready to view.\n\nPlease check it and tap APPROVE or REQUEST CHANGES:\n{proof_link}\n\nPlease check spelling, phone numbers and logo carefully before approving.\n{business_name}','customer',1,0),
('proof_reminder_customer','proof.reminder','Proof pending reminder',
'Gentle reminder 🙏 {customer_name}\nYour design proof for Job *{job_no}* is waiting for your approval.\nPrinting starts only after you approve.\n\n{proof_link}\n\n{business_name} | {branch_phone}','customer',1,0),
('change_requested_designer','proof.change_requested','Change requested to designer',
'Change requested ✏️\nJob: *{job_no}* ({item_name}) — Revision #{revision_no}\nCustomer: {customer_name}\n\nFeedback:\n{feedback_text}\n\nOpen your job board:\n{designer_link}','designer',1,0),
('proof_approved_designer','proof.approved','Proof approved to designer',
'Design approved ✅\nJob: *{job_no}* ({item_name})\nCustomer {customer_name} has given final approval. Great work!','designer',1,0),
('proof_approved_production','proof.approved','New job for production',
'New job ready to print 🖨️\nJob: *{job_no}*\nItem: {item_name} (Qty {qty})\nDue: {due_date}\nPriority: {priority}\nBranch: {branch_name}','production',1,0),
('printing_started_customer','status.printing','Printing started',
'Update from {business_name} 🖨️\nYour Job *{job_no}* is now being printed.\nExpected delivery: {due_date}\n\nTrack: {tracking_link}','customer',1,0),
('ready_for_delivery_customer','status.ready_for_delivery','Order ready',
'Good news {customer_name}! 🎉\nYour order *{job_no}* is READY.\nBalance due: ₹{balance}\n\nCollect from: {branch_name}, {branch_address}\nPhone: {branch_phone}','customer',1,0),
('delivered_customer','status.delivered','Order delivered',
'Thank you {customer_name} 🙏\nYour order *{job_no}* has been delivered.\nBalance due: ₹{balance}\n\nWe would love to serve you again!\n{business_name} | {branch_phone}','customer',1,0),
('payment_received_customer','payment.received','Payment receipt',
'Payment received ✅\nJob: *{job_no}*\nAmount: ₹{paid}\nTotal Paid: ₹{advance}\nBalance: ₹{balance}\n\nReceipt: {receipt_link}\nThank you!\n{business_name}','customer',1,0),
('balance_reminder_customer','payment.balance_reminder','Balance reminder',
'Namaste {customer_name} 🙏\nA gentle reminder from {business_name}.\nJob *{job_no}* has a pending balance of ₹{balance}.\n\nYou can pay at the counter or on UPI.\n{branch_name} | {branch_phone}','customer',1,0),
('order_cancelled_customer','order.cancelled','Order cancelled',
'Namaste {customer_name},\nYour order *{job_no}* at {business_name} has been cancelled.\nIf this is unexpected, please call {branch_phone}.','customer',1,0),
('daily_summary_admin','system.daily_summary','Daily summary to admin',
'📊 {business_name} — Daily Summary {today}\n{items_list}','admin',1,0),
('overdue_alert_manager','order.overdue','Overdue job alert',
'⚠️ Overdue job at {branch_name}\nJob: *{job_no}* ({item_name})\nCustomer: {customer_name}\nWas due: {due_date}\nCurrent status: {priority}','branch_manager',1,0);

-- ---------------------------------------------------------------- Price groups
INSERT INTO `price_groups` (`name`,`discount_percent`) VALUES
('Dealer',10.00),
('Corporate',5.00);

-- ---------------------------------------------------------------- Sample catalog
INSERT INTO `categories` (`name`,`slug`,`description`,`sort_order`,`is_active`,`show_on_public`,`created_at`) VALUES
('Visiting Cards','visiting-cards','Business & visiting cards in all finishes',1,1,1,NOW()),
('Flex Banners','flex-banners','Flex printing, banners and hoardings',2,1,1,NOW()),
('Bill Books','bill-books','Bill books, letterheads and office stationery',3,1,1,NOW()),
('Stickers & Labels','stickers-labels','Stickers, labels and product branding',4,1,1,NOW()),
('Boards & Signage','boards-signage','Sunboards, ACP boards and shop signage',5,1,1,NOW());

INSERT INTO `items` (`category_id`,`name`,`sku`,`short_description`,`unit`,`pricing_type`,`base_price`,`min_qty`,`step_qty`,`tax_percent`,`requires_design`,`default_turnaround_hours`,`show_on_public`,`allow_customer_file_upload`,`sort_order`,`is_active`,`created_at`) VALUES
((SELECT id FROM categories WHERE slug='visiting-cards'),'Standard Visiting Card','VC-STD','300gsm art card, single/double side','box','slab',250.00,100,100,18.00,1,24,1,1,1,1,NOW()),
((SELECT id FROM categories WHERE slug='visiting-cards'),'Premium Laminated Card','VC-PRM','Matt/gloss lamination, rounded corners optional','box','slab',400.00,100,100,18.00,1,48,1,1,2,1,NOW()),
((SELECT id FROM categories WHERE slug='flex-banners'),'Normal Flex Banner','FLX-NRM','Standard flex, per square foot','sqft','area',12.00,1,1,18.00,1,24,1,1,1,1,NOW()),
((SELECT id FROM categories WHERE slug='flex-banners'),'Star Flex Banner','FLX-STAR','High quality star flex, per square foot','sqft','area',18.00,1,1,18.00,1,24,1,1,2,1,NOW()),
((SELECT id FROM categories WHERE slug='bill-books'),'Bill Book (Duplicate)','BB-DUP','2-copy carbonless bill book, 50 sets','pcs','per_unit',180.00,1,1,18.00,1,72,1,1,1,1,NOW()),
((SELECT id FROM categories WHERE slug='stickers-labels'),'Vinyl Sticker Sheet','ST-VNL','A3 vinyl stickers, cut to shape','sheet','per_unit',60.00,5,5,18.00,1,48,1,1,1,1,NOW()),
((SELECT id FROM categories WHERE slug='boards-signage'),'Sunboard Print 3mm','BRD-SUN3','Direct print on 3mm sunboard, per sqft','sqft','area',45.00,1,1,18.00,1,72,1,1,1,1,NOW()),
((SELECT id FROM categories WHERE slug='bill-books'),'Rubber Stamp','RS-STD','Self-ink rubber stamp — ready product, no design loop','pcs','fixed',250.00,1,1,18.00,0,24,1,0,2,1,NOW());

-- Options: Standard Visiting Card
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`help_text`,`sort_order`,`affects_price`) VALUES
((SELECT id FROM items WHERE sku='VC-STD'),'Printing Side','side','radio',1,NULL,1,1),
((SELECT id FROM items WHERE sku='VC-STD'),'Paper Type','paper','select',1,NULL,2,1),
((SELECT id FROM items WHERE sku='VC-STD'),'Text / Content for the card','content','textarea',1,'Name, designation, phone, address exactly as it should print',3,0),
((SELECT id FROM items WHERE sku='VC-STD'),'Corner Style','corner','radio',0,NULL,4,0);

INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`) VALUES
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='VC-STD') AND field_key='side'),'Single Side',0,'add',1,1),
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='VC-STD') AND field_key='side'),'Double Side',100,'add',0,2),
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='VC-STD') AND field_key='paper'),'Art Card 300gsm',0,'add',1,1),
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='VC-STD') AND field_key='paper'),'Ivory 350gsm',50,'add',0,2),
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='VC-STD') AND field_key='corner'),'Square',0,'add',1,1),
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='VC-STD') AND field_key='corner'),'Rounded',0,'add',0,2);

-- Slabs: Standard Visiting Card (rate per box of 100)
INSERT INTO `price_slabs` (`item_id`,`min_qty`,`max_qty`,`rate`) VALUES
((SELECT id FROM items WHERE sku='VC-STD'),100,400,250.00),
((SELECT id FROM items WHERE sku='VC-STD'),500,900,220.00),
((SELECT id FROM items WHERE sku='VC-STD'),1000,NULL,200.00),
((SELECT id FROM items WHERE sku='VC-PRM'),100,400,400.00),
((SELECT id FROM items WHERE sku='VC-PRM'),500,NULL,350.00);

-- Options: Premium card
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`help_text`,`sort_order`,`affects_price`) VALUES
((SELECT id FROM items WHERE sku='VC-PRM'),'Lamination','lamination','select',1,NULL,1,1),
((SELECT id FROM items WHERE sku='VC-PRM'),'Text / Content for the card','content','textarea',1,NULL,2,0);

INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`) VALUES
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='VC-PRM') AND field_key='lamination'),'Matt',0,'add',1,1),
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='VC-PRM') AND field_key='lamination'),'Gloss',0,'add',0,2),
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='VC-PRM') AND field_key='lamination'),'Velvet + Spot UV',150,'add',0,3);

-- Options: Flex banners (both)
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`help_text`,`sort_order`,`affects_price`) VALUES
((SELECT id FROM items WHERE sku='FLX-NRM'),'Width (ft)','width_ft','number',1,'Banner width in feet',1,0),
((SELECT id FROM items WHERE sku='FLX-NRM'),'Height (ft)','height_ft','number',1,'Banner height in feet',2,0),
((SELECT id FROM items WHERE sku='FLX-NRM'),'Text / Matter on banner','content','textarea',1,NULL,3,0),
((SELECT id FROM items WHERE sku='FLX-NRM'),'Eyelets (rings)','eyelets','radio',0,NULL,4,0),
((SELECT id FROM items WHERE sku='FLX-STAR'),'Width (ft)','width_ft','number',1,'Banner width in feet',1,0),
((SELECT id FROM items WHERE sku='FLX-STAR'),'Height (ft)','height_ft','number',1,'Banner height in feet',2,0),
((SELECT id FROM items WHERE sku='FLX-STAR'),'Text / Matter on banner','content','textarea',1,NULL,3,0);

INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`) VALUES
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='FLX-NRM') AND field_key='eyelets'),'Yes',0,'add',1,1),
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='FLX-NRM') AND field_key='eyelets'),'No',0,'add',0,2);

-- Options: Bill book
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`help_text`,`sort_order`,`affects_price`) VALUES
((SELECT id FROM items WHERE sku='BB-DUP'),'Book Size','size','select',1,NULL,1,1),
((SELECT id FROM items WHERE sku='BB-DUP'),'Numbering Start','numbering_start','number',0,'First bill number, default 1',2,0),
((SELECT id FROM items WHERE sku='BB-DUP'),'Details to print (firm name, GSTIN, columns)','content','textarea',1,NULL,3,0);

INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`) VALUES
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='BB-DUP') AND field_key='size'),'1/8 (small)',0,'add',1,1),
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='BB-DUP') AND field_key='size'),'1/6',30,'add',0,2),
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='BB-DUP') AND field_key='size'),'1/4 (big)',60,'add',0,3);

-- Options: Sticker
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`help_text`,`sort_order`,`affects_price`) VALUES
((SELECT id FROM items WHERE sku='ST-VNL'),'Finish','finish','radio',1,NULL,1,1),
((SELECT id FROM items WHERE sku='ST-VNL'),'Sticker text / design brief','content','textarea',1,NULL,2,0);

INSERT INTO `item_option_values` (`option_id`,`label`,`price_delta`,`price_mode`,`is_default`,`sort_order`) VALUES
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='ST-VNL') AND field_key='finish'),'Glossy',0,'add',1,1),
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='ST-VNL') AND field_key='finish'),'Matt',0,'add',0,2),
((SELECT id FROM item_options WHERE item_id=(SELECT id FROM items WHERE sku='ST-VNL') AND field_key='finish'),'Transparent',10,'add',0,3);

-- Options: Sunboard
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`help_text`,`sort_order`,`affects_price`) VALUES
((SELECT id FROM items WHERE sku='BRD-SUN3'),'Width (ft)','width_ft','number',1,NULL,1,0),
((SELECT id FROM items WHERE sku='BRD-SUN3'),'Height (ft)','height_ft','number',1,NULL,2,0),
((SELECT id FROM items WHERE sku='BRD-SUN3'),'Board matter / design brief','content','textarea',1,NULL,3,0);

-- Options: Rubber stamp (no design loop)
INSERT INTO `item_options` (`item_id`,`label`,`field_key`,`field_type`,`is_required`,`help_text`,`sort_order`,`affects_price`) VALUES
((SELECT id FROM items WHERE sku='RS-STD'),'Stamp text','content','textarea',1,'Exact text for the stamp',1,0);
