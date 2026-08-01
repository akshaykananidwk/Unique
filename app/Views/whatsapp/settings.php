<?php use App\Core\Csrf; $title = 'WhatsApp Settings'; ?>
<h4 class="mb-3">WhatsApp Settings</h4>
<div class="row g-3">
  <div class="col-lg-7">
    <form method="post" action="<?= e(admin_url('whatsapp/settings')) ?>">
      <?= Csrf::field() ?>
      <div class="card mb-3"><div class="card-body row g-2">
        <div class="col-12 form-check form-switch ms-2">
          <input class="form-check-input" type="checkbox" name="wa_enabled" value="1" id="wae" <?= $settings['wa_enabled'] === '1' ? 'checked' : '' ?>>
          <label class="form-check-label fw-semibold" for="wae">Enable WhatsApp sending (master switch)</label>
        </div>
        <div class="col-md-8"><label class="form-label">API URL</label>
          <input name="wa_api_url" class="form-control" value="<?= e($settings['wa_api_url']) ?>"></div>
        <div class="col-md-4"><label class="form-label">Session ID / Device</label>
          <input name="wa_session_id" class="form-control" value="<?= e($settings['wa_session_id']) ?>"></div>
        <div class="col-md-8"><label class="form-label">API Key <small class="text-muted">(stored encrypted)</small></label>
          <input name="wa_api_key" class="form-control" placeholder="<?= $settings['wa_api_key'] !== '' ? '••••••••  (saved — type to replace)' : 'Enter API key' ?>" autocomplete="off"></div>
        <div class="col-md-4"><label class="form-label">Country Code</label>
          <input name="wa_country_code" class="form-control" value="<?= e($settings['wa_country_code']) ?>"></div>
        <div class="col-md-4"><label class="form-label">Rate limit (msgs/minute)</label>
          <input type="number" min="1" max="60" name="wa_rate_limit_per_min" class="form-control" value="<?= e($settings['wa_rate_limit_per_min']) ?>">
          <div class="form-text">Keep ≤ 12 — faster risks the number being banned.</div></div>
        <div class="col-md-4"><label class="form-label">Retry attempts</label>
          <input type="number" min="1" max="10" name="wa_max_attempts" class="form-control" value="<?= e($settings['wa_max_attempts']) ?>"></div>
        <div class="col-md-4"><label class="form-label">Quiet hours</label>
          <div class="input-group">
            <input type="time" name="wa_quiet_start" class="form-control" value="<?= e($settings['wa_quiet_start']) ?>">
            <span class="input-group-text">to</span>
            <input type="time" name="wa_quiet_end" class="form-control" value="<?= e($settings['wa_quiet_end']) ?>">
          </div>
          <div class="form-text">Messages inside this window queue until it ends.</div></div>
        <div class="col-12">
          <label class="form-label">Inbound webhook URL <small class="text-muted">(paste in the bulk.akdwk.in panel)</small></label>
          <input class="form-control" readonly value="<?= e(base_url('api/webhook_inbound.php') . ($settings['wa_webhook_secret'] !== '' ? '?secret=' . $settings['wa_webhook_secret'] : ' — save with “regenerate secret” first')) ?>">
          <div class="form-check mt-1">
            <input class="form-check-input" type="checkbox" name="regenerate_secret" value="1" id="regen">
            <label class="form-check-label" for="regen">Regenerate webhook secret on save</label>
          </div>
        </div>
      </div></div>
      <button class="btn btn-primary">Save Settings</button>
    </form>
  </div>
  <div class="col-lg-5">
    <div class="card"><div class="card-body">
      <h6>Test Send</h6>
      <form method="post" action="<?= e(admin_url('whatsapp/test')) ?>" class="d-flex gap-2">
        <?= Csrf::field() ?>
        <input name="test_number" class="form-control" placeholder="10-digit mobile" required>
        <button class="btn btn-success">Send Test</button>
      </form>
      <p class="small text-muted mt-2">Sends immediately (bypasses the queue) and shows the raw API response.</p>
    </div></div>
    <div class="alert alert-warning mt-3 small">
      <strong>Security note:</strong> if your API key is the same as the phone number, anyone who knows the number can
      send messages from your account. Generate a long random key (32+ characters) in the provider panel and rotate it
      if it has ever been shared in a chat or screenshot.
    </div>
  </div>
</div>
