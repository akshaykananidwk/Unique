<?php use App\Core\Acl; use App\Core\Csrf; $title = 'Settings'; ?>
<h4 class="mb-3">Settings</h4>
<?php if (Acl::can('whatsapp.settings') || Acl::can('update.manage')): ?>
<div class="d-flex flex-wrap gap-2 mb-3">
  <?php if (Acl::can('whatsapp.settings')): ?>
    <a class="btn btn-outline-success btn-sm" href="<?= e(admin_url('whatsapp/settings')) ?>"><i class="bi bi-whatsapp"></i> WhatsApp Settings</a>
    <a class="btn btn-outline-success btn-sm" href="<?= e(admin_url('whatsapp/templates')) ?>"><i class="bi bi-chat-square-text"></i> WhatsApp Templates</a>
  <?php endif; ?>
  <?php if (Acl::can('update.manage')): ?>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(admin_url('system/updates')) ?>"><i class="bi bi-cloud-arrow-down"></i> System Updates</a>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(admin_url('system/backups')) ?>"><i class="bi bi-archive"></i> Backups</a>
  <?php endif; ?>
</div>
<?php endif; ?>
<form method="post" action="<?= e(admin_url('settings')) ?>" enctype="multipart/form-data">
  <?= Csrf::field() ?>
  <div class="card mb-3"><div class="card-body">
    <h6 class="text-uppercase text-muted small">Business</h6>
    <div class="row g-2">
      <div class="col-md-4"><label class="form-label">Business name</label><input name="business_name" class="form-control" value="<?= e($values['business_name']) ?>"></div>
      <div class="col-md-4"><label class="form-label">Tagline</label><input name="business_tagline" class="form-control" value="<?= e($values['business_tagline']) ?>"></div>
      <div class="col-md-4"><label class="form-label">Brand colour</label><input type="color" name="brand_color" class="form-control form-control-color w-100" value="<?= e($values['brand_color'] ?: '#0d6efd') ?>"></div>
      <div class="col-md-6"><label class="form-label">Address</label><input name="business_address" class="form-control" value="<?= e($values['business_address']) ?>"></div>
      <div class="col-md-2"><label class="form-label">City</label><input name="business_city" class="form-control" value="<?= e($values['business_city']) ?>"></div>
      <div class="col-md-2"><label class="form-label">State</label><input name="business_state" class="form-control" value="<?= e($values['business_state']) ?>"></div>
      <div class="col-md-2"><label class="form-label">Pincode</label><input name="business_pincode" class="form-control" value="<?= e($values['business_pincode']) ?>"></div>
      <div class="col-md-3"><label class="form-label">Phone</label><input name="business_phone" class="form-control" value="<?= e($values['business_phone']) ?>"></div>
      <div class="col-md-3"><label class="form-label">WhatsApp</label><input name="business_whatsapp" class="form-control" value="<?= e($values['business_whatsapp']) ?>"></div>
      <div class="col-md-3"><label class="form-label">Email</label><input name="business_email" class="form-control" value="<?= e($values['business_email']) ?>"></div>
      <div class="col-md-3"><label class="form-label">GSTIN</label><input name="business_gstin" class="form-control" value="<?= e($values['business_gstin']) ?>"></div>
      <div class="col-md-3"><label class="form-label">Logo <?= $values['business_logo'] ? '<a target="_blank" href="' . e(upload_url($values['business_logo'])) . '">(current)</a>' : '' ?></label>
        <input type="file" name="business_logo" class="form-control" accept="image/*"></div>
      <div class="col-md-3"><label class="form-label">Favicon</label><input type="file" name="business_favicon" class="form-control" accept="image/*,.ico"></div>
    </div>
  </div></div>

  <div class="card mb-3"><div class="card-body">
    <h6 class="text-uppercase text-muted small">Locale &amp; format</h6>
    <div class="row g-2">
      <div class="col-md-3"><label class="form-label">Currency symbol</label><input name="currency_symbol" class="form-control" value="<?= e($values['currency_symbol'] ?: '₹') ?>"></div>
      <div class="col-md-3"><label class="form-label">Timezone</label><input name="timezone" class="form-control" value="<?= e($values['timezone'] ?: 'Asia/Kolkata') ?>"></div>
      <div class="col-md-3"><label class="form-label">Date format (PHP)</label><input name="date_format" class="form-control" value="<?= e($values['date_format'] ?: 'd-m-Y') ?>"></div>
      <div class="col-md-3"><label class="form-label">Base URL</label><input name="base_url" class="form-control" value="<?= e($values['base_url']) ?>"></div>
    </div>
  </div></div>

  <div class="card mb-3"><div class="card-body">
    <h6 class="text-uppercase text-muted small">Workflow</h6>
    <div class="row g-2">
      <div class="col-md-2"><label class="form-label">Job no. prefix</label><input name="job_prefix" class="form-control" value="<?= e($values['job_prefix'] ?: 'JOB') ?>"></div>
      <div class="col-md-2"><label class="form-label">Idle timeout (min)</label><input type="number" name="idle_timeout_minutes" class="form-control" value="<?= e($values['idle_timeout_minutes'] ?: '120') ?>"></div>
      <div class="col-md-2"><label class="form-label">Revision flag at</label><input type="number" name="revision_flag_threshold" class="form-control" value="<?= e($values['revision_flag_threshold'] ?: '3') ?>"></div>
      <div class="col-md-2"><label class="form-label">Proof max MB</label><input type="number" name="proof_max_upload_mb" class="form-control" value="<?= e($values['proof_max_upload_mb'] ?: '15') ?>"></div>
      <div class="col-md-2"><label class="form-label">Upload max MB</label><input type="number" name="upload_max_mb" class="form-control" value="<?= e($values['upload_max_mb'] ?: '15') ?>"></div>
      <div class="col-md-2"><label class="form-label">OTP expiry (min)</label><input type="number" name="otp_expiry_minutes" class="form-control" value="<?= e($values['otp_expiry_minutes'] ?: '10') ?>"></div>
      <div class="col-md-3"><label class="form-label">Balance reminder after (days)</label><input type="number" name="balance_reminder_days" class="form-control" value="<?= e($values['balance_reminder_days'] ?: '7') ?>"></div>
      <div class="col-md-3"><label class="form-label">Proof reminder after (hours)</label><input type="number" name="proof_reminder_hours" class="form-control" value="<?= e($values['proof_reminder_hours'] ?: '24') ?>"></div>
      <div class="col-12 d-flex flex-wrap gap-4 mt-2">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="auto_assign_designer" value="1" id="aad" <?= $values['auto_assign_designer'] === '1' ? 'checked' : '' ?>>
          <label class="form-check-label" for="aad">Auto-assign designers (round-robin)</label></div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="public_otp_required" value="1" id="por" <?= $values['public_otp_required'] === '1' ? 'checked' : '' ?>>
          <label class="form-check-label" for="por">Require WhatsApp OTP on public orders</label></div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="public_order_auto_confirm" value="1" id="pac" <?= $values['public_order_auto_confirm'] === '1' ? 'checked' : '' ?>>
          <label class="form-check-label" for="pac">Auto-confirm public orders (skip review)</label></div>
      </div>
    </div>
  </div></div>
  <div class="sticky-actions"><button class="btn btn-primary">Save Settings</button></div>
</form>
