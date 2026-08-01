<?php $wa = ins_data('whatsapp') ?: []; ?>
<h5>Step 7 — WhatsApp <small class="text-muted">(optional — you can set this up later in Settings)</small></h5>
<form method="post">
  <input type="hidden" name="_csrf" value="<?= ins_csrf() ?>">
  <input type="hidden" name="step" value="7">
  <div class="row g-2">
    <div class="col-md-8"><label class="form-label">API URL</label>
      <input name="wa_api_url" class="form-control" value="<?= ins_e($wa['wa_api_url'] ?? 'https://bulk.akdwk.in/api.php') ?>"></div>
    <div class="col-md-4"><label class="form-label">Country code</label>
      <input name="wa_country_code" class="form-control" value="<?= ins_e($wa['wa_country_code'] ?? '91') ?>"></div>
    <div class="col-md-6"><label class="form-label">API key <small class="text-muted">(stored encrypted)</small></label>
      <input name="wa_api_key" class="form-control" value="<?= ins_e($wa['wa_api_key'] ?? '') ?>" autocomplete="off"></div>
    <div class="col-md-6"><label class="form-label">Session ID / device</label>
      <input name="wa_session_id" class="form-control" value="<?= ins_e($wa['wa_session_id'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Test number (for Test Send)</label>
      <input name="test_number" class="form-control" maxlength="10" placeholder="10-digit mobile"></div>
    <div class="col-md-6 d-flex align-items-end gap-2">
      <button name="test_send" value="1" class="btn btn-outline-success">📤 Test Send</button>
    </div>
  </div>
  <div class="d-flex justify-content-between mt-3">
    <a class="btn btn-outline-secondary" href="?step=6">← Back</a>
    <div class="d-flex gap-2">
      <button name="skip" value="1" class="btn btn-outline-secondary">Skip for now</button>
      <button class="btn btn-primary">Save &amp; Continue →</button>
    </div>
  </div>
</form>
