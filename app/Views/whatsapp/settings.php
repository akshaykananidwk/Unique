<?php use App\Core\Csrf; use App\Core\View; $title = 'WhatsApp Settings'; ?>
<h4 class="mb-3">WhatsApp</h4>
<?= View::partial('whatsapp/_nav', ['waActive' => 'settings']) ?>
<div class="row g-3">
  <div class="col-lg-7">
    <form method="post" action="<?= e(admin_url('whatsapp/settings')) ?>">
      <?= Csrf::field() ?>
      <div class="card mb-3"><div class="card-body row g-2">
        <div class="col-12 form-check form-switch ms-2">
          <input class="form-check-input" type="checkbox" name="wa_enabled" value="1" id="wae" <?= $settings['wa_enabled'] === '1' ? 'checked' : '' ?>>
          <label class="form-check-label fw-semibold" for="wae">Enable WhatsApp sending (master switch)</label>
        </div>
        <div class="col-12 form-check form-switch ms-2">
          <input class="form-check-input" type="checkbox" name="wa_auto_send" value="1" id="waauto" <?= $settings['wa_auto_send'] === '1' ? 'checked' : '' ?>>
          <label class="form-check-label fw-semibold" for="waauto">Send messages immediately (recommended — no cron needed)</label>
          <div class="form-text ms-1">When on, every message goes out right after the action — you never press “Send pending now”. Leave it on unless you specifically want cron-only sending.</div>
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
    <!-- Diagnostics: "are my settings correct / why aren't messages going?" -->
    <div class="card mb-3 border-<?= $diagnostics['ok'] ? 'success' : 'warning' ?>"><div class="card-body">
      <h6 class="d-flex justify-content-between align-items-center">
        <span>Status check</span>
        <span class="badge bg-<?= $diagnostics['ok'] ? 'success' : 'warning text-dark' ?>">
          <?= $diagnostics['ok'] ? 'Ready to send' : 'Needs attention' ?>
        </span>
      </h6>
      <ul class="list-unstyled small mb-2">
        <?php foreach ($diagnostics['checks'] as $c): ?>
        <li class="mb-1">
          <span class="text-<?= e($c['level']) ?>"><?= $c['ok'] ? '✔' : ($c['level'] === 'warning' ? '⚠' : '✖') ?></span>
          <strong><?= e($c['label']) ?></strong>
          <?php if ($c['detail']): ?><div class="text-muted ms-3"><?= e($c['detail']) ?></div><?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
      <div class="d-flex gap-2 flex-wrap small mb-2">
        <span class="badge bg-secondary">Pending: <?= (int)$diagnostics['counts']['pending'] ?></span>
        <span class="badge bg-success">Sent: <?= (int)$diagnostics['counts']['sent'] ?></span>
        <span class="badge bg-danger">Failed: <?= (int)$diagnostics['counts']['failed'] ?></span>
      </div>
      <div class="d-flex gap-2">
        <form method="post" action="<?= e(admin_url('whatsapp/process-now')) ?>">
          <?= Csrf::field() ?>
          <button class="btn btn-primary btn-sm"><i class="bi bi-send"></i> Send pending now</button>
        </form>
        <a class="btn btn-outline-secondary btn-sm" href="<?= e(admin_url('whatsapp/logs')) ?>">View logs</a>
      </div>
      <p class="small text-muted mt-2 mb-0">
        Messages are queued and sent by the per-minute cron
        (<code>cron/whatsapp_worker.php</code>). If the cron isn't set up yet, use
        <strong>Send pending now</strong> to send them from here.
      </p>
    </div></div>

    <div class="card"><div class="card-body">
      <h6>Test Send</h6>
      <form method="post" action="<?= e(admin_url('whatsapp/test')) ?>" class="d-flex gap-2">
        <?= Csrf::field() ?>
        <input name="test_number" class="form-control" placeholder="10-digit mobile" required>
        <button class="btn btn-success">Send Test</button>
      </form>
      <p class="small text-muted mt-2">Sends immediately (bypasses the queue) and shows the raw API response — the quickest way to confirm the API key and session are correct.</p>
    </div></div>
    <div class="alert alert-warning mt-3 small">
      <strong>Security note:</strong> if your API key is the same as the phone number, anyone who knows the number can
      send messages from your account. Generate a long random key (32+ characters) in the provider panel and rotate it
      if it has ever been shared in a chat or screenshot.
    </div>
  </div>
</div>
