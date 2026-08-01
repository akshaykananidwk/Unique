# Krishna Print — Order & Job Management System

A multi-branch order and job-tracking platform for a printing press, covering the full lifecycle:
order → job number → WhatsApp notify → designer → proof approval loop → printing → delivery → payment.

**Stack:** PHP 8.1+ (runs on 8.3/8.4) · MySQL/MariaDB · Bootstrap 5.3 + vanilla JS · PDO prepared statements only.
Every configurable value lives in the database and is editable from the admin panel — **no file editing after install**.

## Highlights

- **Two order paths:** staff counter entry (any of 3 branches) and a public customer website.
- **Dynamic catalogue:** the entire order form is generated from `items` + `item_options` + `item_option_values` + `price_slabs`. Adding a product is data entry, not code.
- **Design proof loop:** watermarked proofs, version history, customer approve (double-confirm, audit-logged) or request changes (typed **or** voice, with an iPhone-safe audio fallback).
- **WhatsApp engine:** 16 editable templates, queue + cron worker with rate-limit/quiet-hours/retry, inbound webhook, full logs with resend.
- **Payments & reports:** advance/part/final payments, A5 + thermal receipts with QR, outstanding by age bucket, staff performance, GST summary, cash book — all CSV/print exportable.
- **One-click installer** (`/install`) and a **GitHub auto-update engine** with automatic backup + rollback.
- **Security:** role/permission ACL enforced server-side, CSRF on every POST, output escaping, encrypted secrets (AES-256-CBC), login rate-limiting, hardened uploads.

## Install

1. Upload the files to your web root (aaPanel: `/www/wwwroot/{domain}`).
2. Create an empty MySQL database.
3. Open `https://your-domain/install` and follow the 8 steps.
4. Paste the 4 cron commands shown on the final screen into aaPanel → Cron.
5. Done — click **Go to Admin Panel**.

No config files to edit. The installer writes `config/config.php`, generates the encryption key, detects the base URL, creates your branches + super admin, and locks itself.

## Layout

```
index.php / admin/ / install/   entry points
app/Core        Router, DB, Auth, Acl, Csrf, Crypt, Settings, Uploader,
                Whatsapp, WaEvents, Backup, Migrator, Updater, Logger, View
app/Controllers Admin\* and Site\* controllers
app/Models      OrderService, ProofService, Pricing, Status (state machine)
app/Views       admin panel + public site + print templates
cron/           whatsapp_worker, reminders, update_check, auto_backup
database/       schema.sql, seed.sql, migrations/
assets/         bundled Bootstrap, Bootstrap Icons, Chart.js, QRCode (no CDN)
```

## Verification

See `VERIFICATION_REPORT.md` for the full test log (fresh install, 8 roles, order/proof/payment
flows, concurrency, security spot-checks, backup/restore, update engine) and
`WHATSAPP_TEMPLATES_RENDERED.md` for all 16 templates rendered with real data.

## Requirements

PHP extensions: `pdo_mysql, curl, mbstring, gd` (or imagick)`, zip, openssl, fileinfo, json`.
Writable: `config/`, `uploads/`, `storage/`, `backups/`, web root (for `.htaccess`).
