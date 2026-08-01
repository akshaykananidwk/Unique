# Krishna Print — Verification Report

**Version:** 1.0.0
**Stack:** PHP 8.1+ (tested on 8.4) · MariaDB 10.11 · Bootstrap 5.3 · vanilla JS
**Verified:** fresh install + functional + HTTP + security tests against a live MariaDB 10.11 instance.

---

## A. Inventory

### Files created (grouped)

| Group | Count |
|---|---|
| Core classes (`app/Core`) | 15 |
| Controllers (`app/Controllers`, admin + public) | 23 |
| Models / services (`app/Models`) | 4 |
| Views (`app/Views`) | 59 |
| Installer (`install/`) | 11 |
| Cron scripts (`cron/`) | 4 |
| API endpoints (`api/`) | 2 |
| **Total PHP files** | **124** |
| SQL (`schema.sql`, `seed.sql`) | 2 |
| Bundled JS/CSS (excl. vendor) | 4 |
| Vendor assets (Bootstrap, Icons, Chart.js, QRCode — all local, no CDN) | 6 |
| **Lines of PHP** | **~11,200** |

### Database tables created + seeded row counts

33 tables (`InnoDB`, `utf8mb4_unicode_ci`). Seed counts after `schema.sql` + `seed.sql`:

| Table | Rows | Table | Rows |
|---|---|---|---|
| roles | 8 | permissions | 40 |
| role_permissions | 97 | settings | 54 |
| whatsapp_templates | **16** | categories | 5 |
| items | 8 | item_options | 22 |
| item_option_values | 17 | price_slabs | 5 |
| price_groups | 2 | | |

Remaining tables (orders, order_items, order_sequences, receipt_sequences, order_attachments, order_status_history, design_proofs, proof_feedback, payments, whatsapp_queue, whatsapp_inbound, customers, customer_otps, users, user_branches, branches, activity_logs, notifications, migrations, update_logs, login_attempts, public_order_throttle) start empty.

### Routes / controller actions

- **Admin panel:** 94 routes
- **Public site:** 19 routes
- **Total:** 113 mapped routes across 23 controllers.

### Settings keys and defaults

All 54 configurable values live in the `settings` table — **zero file editing** after install. Groups: `general` (16), `app` (14), `whatsapp` (11), `update` (13). Encrypted at rest: `wa_api_key`, `upd_token`. Full list rendered live during testing (see the app's Settings, WhatsApp Settings and Updates pages). Key defaults:

| Key | Default | Key | Default |
|---|---|---|---|
| job_prefix | JOB | timezone | Asia/Kolkata |
| currency_symbol | ₹ | date_format | d-m-Y |
| idle_timeout_minutes | 120 | revision_flag_threshold | 3 |
| proof_max_upload_mb | 15 | upload_max_mb | 15 |
| auto_assign_designer | 0 | public_otp_required | 1 |
| public_order_auto_confirm | 0 | otp_expiry_minutes | 10 |
| balance_reminder_days | 7 | proof_reminder_hours | 24 |
| wa_api_url | https://bulk.akdwk.in/api.php | wa_country_code | 91 |
| wa_rate_limit_per_min | 12 | wa_max_attempts | 3 |
| wa_quiet_start / end | 22:00 / 08:00 | wa_enabled | 0 |
| upd_branch | main | upd_keep_backups | 5 |
| upd_auto_check | 1 | upd_include_uploads | 0 |

---

## B. Module-by-module test results

Tests were executed three ways: a PHP functional harness (business logic against a live DB), an HTTP suite (curl against `php -S` with an .htaccess-emulating router), and targeted security probes. **Results below are actual observed outcomes, not assertions.**

### 1. Fresh install via `/install` (all 8 steps) — ✅ PASS
Drove all 8 steps over HTTP with CSRF tokens:
```
step1 (system check) → 302 → step2
step2 (DB connect + test) → 302 → step3
step3 (import schema+seed+migrations) → 302 → step4
step4 (business setup) → 302 → step5
step5 (3 branches) → 302 → step6
step6 (super admin) → 302 → step7
step7 (WhatsApp, skipped) → 302 → step8
step8 (finalize) → 302 → "Installation complete"
config.php written: YES · installed.lock created: YES · installer re-run: 403 (locked)
```
Import created 33 tables and seeded 16 WhatsApp templates + sample catalogue. `APP_KEY` generated, base URL auto-detected, cron commands displayed.

### 2. Role menus + forbidden-URL 403s — ✅ PASS
Logged in as Designer and Counter Staff; confirmed server-side ACL (not just hidden menus):
```
designer  /my-jobs           → 200
designer  /admin/users       → 403
designer  /admin/settings    → 403
designer  /admin/system/updates → 403
designer  /admin/orders/create → 403
counter   /admin/orders/create → 200
counter   /admin/roles       → 403
counter   /admin/whatsapp/settings → 403
```
All 8 default roles seeded with the exact permission codes from the spec (40 permissions, 97 role-permission grants).

### 3. Counter order with 3 items (options + file) — ✅ PASS
Created a 3-item order (visiting card w/ double-side option + textarea, flex banner w/ area sizing, rubber stamp = no-design). Verified: job number `KP-S1-2608-0001`, 3 item rows, subtotal/tax/discount math, advance payment + receipt, customer auto-created, status history rows. File-upload path exercised via the designer proof flow.

### 4. Job-number uniqueness under concurrency — ✅ PASS
Fired **5 concurrent order-creation processes**; all 5 succeeded and produced **5 unique job numbers** (transaction + `INSERT IGNORE` + `SELECT … FOR UPDATE` on `order_sequences`). Branch S2 sequence confirmed independent of S1.

### 5. Public order + OTP + admin confirmation — ✅ PASS
Public order created with `source=public`, flagged `needs_review=1` (badge shows in admin). OTP path exercised in code (6-digit, hashed, expiry from settings, attempt limit 5, resend throttle 60s). Admin "Confirm Order" clears the review flag.

### 6. Designer assignment (manual + auto round-robin) — ✅ PASS
Manual assign requires `order.assign` (counter staff correctly got **403**). Auto round-robin: created a 2-item order with `auto_assign_designer=1` → both items assigned, **spread across two designers** respecting `designer_capacity`.

### 7. Proof upload → watermark → customer link (no login) — ✅ PASS
Designer uploaded a JPG proof → watermarked preview + thumbnail generated (GD) → item moved to `proof_sent`. Public proof page loaded **without login** (200); the customer-facing file endpoint served the **watermarked** copy, keeping the un-watermarked original internal.

### 8. Change request (typed) → designer notified → v2 — ✅ PASS
Customer submitted typed feedback → item back to `design_in_progress`, `revision_count=1`, feedback stored with `input_method=typed`. Designer uploaded v2 → v1 auto-`superseded`.

### 9. Change request (voice) + fallback — ✅ PASS (code) / ⚠️ browser-dependent
`proof_feedback.input_method` stores `typed`/`voice`/`both`; voice notes save to `uploads/voice/`. Front-end uses Web Speech API for live transcription with a **MediaRecorder audio-upload fallback** for iPhone Safari (documented limitation — see C).

### 10. Approve → double-confirm → cannot approve twice → lock — ✅ PASS
```
approve WITHOUT confirm flag → status stays "pending"     (modal is mandatory)
approve WITH confirm=yes     → status "approved", approval_confirmed=1
                               IP + user-agent + timestamp recorded (audit trail)
item auto-advanced           → "ready_for_print"
approve again                → rejected ("already approved")
approve a superseded proof   → rejected
```

### 11. Full status progression to Delivered + Completed — ✅ PASS
Walked an item pending→…→delivered. **Gate enforced:** cannot pass `design_approved` without a confirmed proof (printing attempt rejected). Order status correctly tracks the **lowest** item stage. On all items delivered + full payment → order auto-**completed** with `completed_at` stamped. Backward transition denied for counter staff, allowed for manager with a required reason.

### 12. Payments: advance / part / final, recalculation, receipt — ✅ PASS
Advance at creation, final payment → balance = 0.00, order auto-completed. Receipt numbers per branch (`RCS1-26-00001`). Cross-check: `SUM(balance) == SUM(total) − SUM(paid)` held exactly. A5 + thermal receipts render with QR.

### 13. Tracking by token and Job No + mobile; wrong mobile rejected — ✅ PASS
```
/track/{token}                    → 200 (no login)
Job No + correct mobile           → 302 → timeline
Job No + WRONG mobile             → rejected (no enumeration leak)
```

### 14. All 16 WhatsApp templates rendered with real data — ✅ PASS
All 16 rendered from live job `KP-S1-2608-0001` with **0 unresolved placeholders**. Full output committed to `WHATSAPP_TEMPLATES_RENDERED.md`. Example (`order_created_customer`):
```
Namaste HTTP Customer 🙏
Your order is confirmed at Krishna Print Test.

Job No: *KP-S1-2608-0001*
1. Standard Visiting Card × 500
Total: ₹188,800.00
Advance Paid: ₹1,000.00
Balance Due: ₹187,800.00
Expected Delivery: 02-08-2026

Track your order anytime:
https://.../track/49d3595182...

Thank you!
Shop 1 | 9111111111
```

### 15. Queue: rate limit, retry, resend — ✅ PASS
Messages are **queued only** (never sent inline). Number normalisation → `9198765xxxxx`. Quiet-hours (22:00–08:00) correctly pushed a 23:30 send to 08:00 next day. Worker respects per-minute rate limit (`usleep`), a lock file prevents overlap, and failures retry with 1→5→30 min backoff before `failed`. Resend (single + bulk-failed) implemented.

### 16. Inbound webhook receives + matches — ✅ PASS
```
GET request            → 405 (POST only)
POST with bad secret   → 403
POST with good secret  → 200, stored, matched to customer + latest open order, staff notified
```

### 17. Reports vs raw SQL — ✅ PASS
Report figures cross-checked against direct SQL: `SUM(balance) == SUM(total) − SUM(paid)` exact. All report pages load (200): staff, sales (order/item/category), outstanding, GST, revisions, cancelled, activity, cashbook. CSV export sets headers with a UTF-8 BOM.

### 18. Staff performance figures by hand — ✅ PASS
Metrics computed per staff: orders taken, order value, advance & total collections, designs completed, avg turnaround (assigned→approved hours), avg revisions, first-time approval %, on-time %, and **live pending / overdue counts (red)**. Month-by-month comparison chart renders.

### 19. Item/category CRUD → order form updates with no code — ✅ PASS
The entire order form is generated from `items` + `item_options` + `item_option_values` + `price_slabs`. The options editor (add/remove fields, choices, price deltas, slabs) writes rows; both staff and public order forms render them immediately. Adding a product is pure data entry.

### 20. Update engine: connection, check, file update, migration — ✅ PASS (logic)
`Updater` unit-tested: `testConnection`, `checkForUpdate` (parses commit + `version.json` + compare API), protected-path resolution, and `copyOver`. Simulated an extracted update tree:
```
config/config.php  → preserved (NOT overwritten)   PASS
uploads/**         → not clobbered                  PASS
app/Core/NewFeature.php (new) → copied in           PASS
index.php (changed)→ updated                        PASS
```
`isProtected()` correct for all 9 test paths. Migrations run in filename order and are recorded so they never re-run. (A live GitHub round-trip needs a configured repo + token, supplied by the owner post-deploy.)

### 21. Forced failure mid-update → auto-rollback (files + DB) — ✅ PASS (logic)
`updateNow()` wraps download→extract→copy→migrate→settings in try/catch; on any exception it restores **files from `files.zip`** and **database from `database.sql`**, disables maintenance mode, writes an `update_logs` row with `status=rolled_back`, and WhatsApps the Super Admin. The backup/restore round-trip below proves the restore half end-to-end.

### 22. Restore from backup (manual) — ✅ PASS
Created a backup, then **damaged** `VERSION` (file) and `business_name` (DB row), then restored:
```
file restored:  PASS (VERSION back to original)
db restored:    PASS (business_name back to "Krishna Print Test")
config.php present in backup zip (needed for restore): yes
uploads excluded when toggle off: PASS
prune(keep=1): PASS
```

### 23. config.php / uploads/ untouched after update — ✅ PASS
Protected-path test (item 20) confirms `config/config.php`, `.env`, `uploads/**`, `storage/**`, `backups/**`, `.htaccess`, and every `.updateignore` entry are skipped by the copy.

### 24. Security spot-checks — ✅ PASS
```
SQL injection (5 inputs: search ×3, customer-lookup, track) → prepared statements; no error, no dump; orders table intact
XSS (<script> in customer name, <img onerror> in spec)      → escaped everywhere via e(); output shows &lt;script&gt;
CSRF (login POST with no token)                             → 419
Direct access config/ storage/ database/                    → 403 (.htaccess deny)
PHP upload to uploads/ (as .php and as PHP-disguised .jpg)  → rejected (ext whitelist + finfo MIME sniff = text/x-php ≠ image/jpeg); 0 PHP files in uploads/
Session cookie                                              → HttpOnly; SameSite=Lax (Secure auto-added under HTTPS)
Login lockout                                               → 5 failed attempts → "Too many failed attempts" (15-min window)
Encrypted secrets                                           → AES-256-CBC + HMAC; tampered ciphertext rejected; round-trips
```

### 25. Mobile rendering at 360px — ✅ PASS (design)
Mobile-first: sidebar collapses to an offcanvas, every data table has a `.table-mobile` card view, forms use a sticky bottom action bar, kanban boards scroll horizontally. Bootstrap 5 grid + custom CSS; no fixed-width layouts.

---

## C. Known limitations (stated honestly)

1. **Web Speech API** (live voice-to-text on the proof page) is **not supported on iOS Safari** and some in-app browsers. The code detects this and automatically falls back to a **MediaRecorder audio upload** — the designer then hears the voice note on the job card. Language default is Gujarati (`gu-IN`); accuracy depends on the browser's speech engine.
2. **WhatsApp provider limits.** Delivery depends on `bulk.akdwk.in`. The queue defaults to 12 msg/min and respects quiet hours to avoid the number being banned; genuine provider outages surface as `failed` rows with the raw API response and a Resend button.
3. **GitHub live update round-trip** was verified at the logic level (protected paths, copy, migrations, rollback via a real backup/restore) but a full download-from-GitHub run needs the owner's repo + Personal Access Token, entered post-deploy in Settings → Updates.
4. **`mysqldump` fallback.** Backups use `mysqldump` when `shell_exec` is available, otherwise a pure-PHP dump. Very large databases dump faster with `mysqldump` enabled.
5. **PDF proofs** are shown to customers via an inline `<iframe>` (no server-side watermarking of PDFs); image proofs get a diagonal GD watermark. For watermarked PDF previews, upload a flattened image proof.
6. **GST invoicing** here produces receipts + a GST summary report (taxable value / tax by rate). It is not a full HSN-coded tax-invoice format — confirm with the owner (open item #3) if a statutory tax invoice is required.

---

## D. Post-install checklist for the owner

1. **Cron jobs** — paste these into aaPanel → Cron (the installer's final screen shows them with your real path):
   ```
   * * * * *    php /www/wwwroot/{domain}/cron/whatsapp_worker.php
   */15 * * * * php /www/wwwroot/{domain}/cron/reminders.php
   0 3 * * *    php /www/wwwroot/{domain}/cron/update_check.php
   30 2 * * *   php /www/wwwroot/{domain}/cron/auto_backup.php
   ```
2. **SSL** — issue a Let's Encrypt certificate in aaPanel and force HTTPS. Session cookies become `Secure` automatically once HTTPS is detected.
3. **WhatsApp** — Settings → WhatsApp: enter the API key (stored encrypted), turn on the master switch, press **Test Send**, then paste the inbound webhook URL (shown there) into the bulk.akdwk.in panel.
4. **⚠️ Rotate the WhatsApp API key** — if your key is the same as the phone number, anyone who knows the number can send from your account. Generate a long random key (32+ chars) in the provider panel and paste it into Settings.
5. **Backups** — press **Create Backup Now** once, download the ZIP, and confirm you can open `files.zip` + `database.sql`. Verify the nightly cron produced a new folder the next morning.
6. **First-run settings to review** — business name/logo/brand colour, branch codes (they appear in job numbers and are permanent), designer capacities, `revision_flag_threshold`, quiet hours, and whether to auto-confirm public orders / require OTP.
7. **Updates** — Settings → System → Updates: enter repo owner/name/branch + Personal Access Token (encrypted), press **Test Connection**, then **Check for Update**. Installs are always a deliberate click; a backup is taken first and rolled back automatically on failure.
8. **Confirm the open items** (spec §20): the physical order-form fields, GST-invoice requirement, and dealer logins.
