<?php $biz = ins_data('business', []); ?>
<h5>Step 4 — Business Setup</h5>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="_csrf" value="<?= ins_csrf() ?>">
  <input type="hidden" name="step" value="4">
  <div class="row g-2">
    <div class="col-md-6"><label class="form-label">Business name *</label>
      <input name="business_name" class="form-control" required value="<?= ins_e($biz['business_name'] ?? 'Krishna Print') ?>"></div>
    <div class="col-md-6"><label class="form-label">Tagline</label>
      <input name="business_tagline" class="form-control" value="<?= ins_e($biz['business_tagline'] ?? 'Quality printing, on time') ?>"></div>
    <div class="col-md-4"><label class="form-label">Logo (jpg/png/webp)</label>
      <input type="file" name="business_logo" class="form-control" accept=".jpg,.jpeg,.png,.webp"></div>
    <div class="col-md-4"><label class="form-label">Favicon</label>
      <input type="file" name="business_favicon" class="form-control" accept=".jpg,.jpeg,.png,.webp,.ico"></div>
    <div class="col-md-4"><label class="form-label">Brand colour</label>
      <input type="color" name="brand_color" class="form-control form-control-color w-100" value="<?= ins_e($biz['brand_color'] ?? '#0d6efd') ?>"></div>
    <div class="col-md-8"><label class="form-label">Address</label>
      <input name="business_address" class="form-control" value="<?= ins_e($biz['business_address'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">City</label>
      <input name="business_city" class="form-control" value="<?= ins_e($biz['business_city'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">State</label>
      <input name="business_state" class="form-control" value="<?= ins_e($biz['business_state'] ?? 'Gujarat') ?>"></div>
    <div class="col-md-4"><label class="form-label">Pincode</label>
      <input name="business_pincode" class="form-control" value="<?= ins_e($biz['business_pincode'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Phone</label>
      <input name="business_phone" class="form-control" value="<?= ins_e($biz['business_phone'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">WhatsApp number</label>
      <input name="business_whatsapp" class="form-control" value="<?= ins_e($biz['business_whatsapp'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Email</label>
      <input name="business_email" class="form-control" value="<?= ins_e($biz['business_email'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">GSTIN</label>
      <input name="business_gstin" class="form-control" value="<?= ins_e($biz['business_gstin'] ?? '') ?>"></div>
    <div class="col-md-3"><label class="form-label">Currency symbol</label>
      <input name="currency_symbol" class="form-control" value="<?= ins_e($biz['currency_symbol'] ?? '₹') ?>"></div>
    <div class="col-md-3"><label class="form-label">Timezone</label>
      <input name="timezone" class="form-control" value="<?= ins_e($biz['timezone'] ?? 'Asia/Kolkata') ?>"></div>
    <div class="col-md-3"><label class="form-label">Date format</label>
      <input name="date_format" class="form-control" value="<?= ins_e($biz['date_format'] ?? 'd-m-Y') ?>"></div>
    <div class="col-md-3"><label class="form-label">Job number prefix</label>
      <input name="job_prefix" class="form-control" value="<?= ins_e($biz['job_prefix'] ?? 'JOB') ?>"></div>
  </div>
  <div class="d-flex justify-content-between mt-3">
    <a class="btn btn-outline-secondary" href="?step=3">← Back</a>
    <button class="btn btn-primary">Continue →</button>
  </div>
</form>
