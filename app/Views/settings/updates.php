<?php use App\Core\Csrf; $title = 'System Updates'; ?>
<h4 class="mb-3">System Updates</h4>
<div class="row g-3">
  <div class="col-lg-6">
    <div class="card mb-3"><div class="card-body">
      <h6>GitHub repository</h6>
      <form method="post" action="<?= e(admin_url('system/updates/settings')) ?>">
        <?= Csrf::field() ?>
        <div class="row g-2">
          <div class="col-6"><label class="form-label">Repository owner</label>
            <input name="upd_repo_owner" class="form-control" value="<?= e($settings['upd_repo_owner']) ?>"></div>
          <div class="col-6"><label class="form-label">Repository name</label>
            <input name="upd_repo_name" class="form-control" value="<?= e($settings['upd_repo_name']) ?>"></div>
          <div class="col-6"><label class="form-label">Branch</label>
            <input name="upd_branch" class="form-control" value="<?= e($settings['upd_branch']) ?>"></div>
          <div class="col-6"><label class="form-label">Personal Access Token <small class="text-muted">(encrypted)</small></label>
            <input name="upd_token" class="form-control" autocomplete="off"
                   placeholder="<?= $settings['upd_token'] !== '' ? '••••••••  (saved — type to replace)' : 'ghp_…' ?>"></div>
          <div class="col-6"><label class="form-label">Keep last N backups</label>
            <input type="number" min="1" max="30" name="upd_keep_backups" class="form-control" value="<?= (int)$settings['upd_keep_backups'] ?>"></div>
          <div class="col-6 d-flex flex-column justify-content-end">
            <div class="form-check"><input class="form-check-input" type="checkbox" name="upd_auto_check" value="1" id="uac" <?= $settings['upd_auto_check'] ? 'checked' : '' ?>>
              <label class="form-check-label small" for="uac">Auto-check daily</label></div>
            <div class="form-check"><input class="form-check-input" type="checkbox" name="upd_include_uploads" value="1" id="uiu" <?= $settings['upd_include_uploads'] ? 'checked' : '' ?>>
              <label class="form-check-label small" for="uiu">Include uploads/ in backups</label></div>
          </div>
        </div>
        <button class="btn btn-primary btn-sm mt-2">Save</button>
      </form>
      <div class="d-flex gap-2 mt-2">
        <form method="post" action="<?= e(admin_url('system/updates/test')) ?>"><?= Csrf::field() ?>
          <button class="btn btn-outline-secondary btn-sm">Test Connection</button></form>
        <form method="post" action="<?= e(admin_url('system/updates/check')) ?>"><?= Csrf::field() ?>
          <button class="btn btn-outline-primary btn-sm">Check for Update</button></form>
        <a class="btn btn-outline-secondary btn-sm" href="<?= e(admin_url('system/update-history')) ?>">Update History</a>
        <a class="btn btn-outline-secondary btn-sm" href="<?= e(admin_url('system/backups')) ?>">Backups</a>
      </div>
    </div></div>
  </div>
  <div class="col-lg-6">
    <?php
    // Server checks. These cover files an update is deliberately not allowed to overwrite
    // (anything under uploads/), so when one of them goes wrong only a repair can fix it.
    $badChecks = array_values(array_filter($checks ?? [], fn($c) => $c['state'] !== 'ok'));
    ?>
    <div class="card mb-3 <?= $badChecks ? 'border-danger' : '' ?>"><div class="card-body">
      <h6>Server checks</h6>
      <?php foreach (($checks ?? []) as $c): ?>
        <div class="d-flex gap-2 align-items-start mb-1">
          <span class="badge bg-<?= $c['state'] === 'ok' ? 'success' : 'danger' ?>">
            <i class="bi bi-<?= $c['state'] === 'ok' ? 'check-lg' : 'exclamation-triangle' ?>"></i>
          </span>
          <div>
            <div class="fw-semibold"><?= e($c['label']) ?></div>
            <div class="small text-muted"><?= e($c['detail']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
      <form method="post" action="<?= e(admin_url('system/repair')) ?>" class="mt-2">
        <?= Csrf::field() ?>
        <button class="btn btn-sm <?= $badChecks ? 'btn-danger' : 'btn-outline-secondary' ?>">
          <i class="bi bi-wrench-adjustable"></i> Repair now
        </button>
      </form>
      <?php if ($badChecks): ?>
        <div class="small text-muted mt-2">
          If Repair now cannot fix it, the file belongs to another user. In aaPanel open
          <strong>File Manager → uploads → .htaccess</strong> and delete the line
          <code>php_flag engine off</code> that is <em>not</em> inside an
          <code>&lt;IfModule&gt;</code> block.
        </div>
      <?php endif; ?>
    </div></div>

    <div class="card"><div class="card-body">
      <h6>Status</h6>
      <table class="table table-sm">
        <tr><td>Current version</td><td><strong><?= e($settings['current_version']) ?></strong></td></tr>
        <tr><td>Current commit</td><td><code><?= e(substr($settings['current_commit'], 0, 10) ?: '— (set after first update)') ?></code></td></tr>
        <tr><td>Last check</td><td><?= e($settings['last_update_check'] ? fmt_date($settings['last_update_check'], true) : 'Never') ?></td></tr>
      </table>

      <?php if ($meta && !empty($meta['available'])): ?>
        <div class="alert alert-warning">
          <h5 class="alert-heading">Update available: <?= e($meta['latest_version']) ?></h5>
          <table class="table table-sm mb-2">
            <tr><td>Current → New</td><td><?= e($meta['current_version']) ?> → <strong><?= e($meta['latest_version']) ?></strong></td></tr>
            <tr><td>Commit</td><td><code><?= e(substr((string)$meta['latest_commit'], 0, 10)) ?></code> — <?= e(mb_substr((string)$meta['commit_message'], 0, 120)) ?></td></tr>
            <tr><td>Author / date</td><td><?= e($meta['commit_author']) ?> · <?= e($meta['commit_date']) ?> IST</td></tr>
            <?php if (!empty($meta['files_changed'])): ?>
            <tr><td>Files</td><td><?= (int)$meta['files_changed']['total'] ?> changed
              (+<?= (int)$meta['files_changed']['added'] ?> / −<?= (int)$meta['files_changed']['removed'] ?> / ~<?= (int)$meta['files_changed']['modified'] ?>)</td></tr>
            <?php endif; ?>
            <?php if (!empty($meta['migrations_included'])): ?>
            <tr><td colspan="2"><span class="badge bg-warning text-dark">⚠ Includes new database migrations</span></td></tr>
            <?php endif; ?>
          </table>
          <?php if (!empty($meta['changelog'])): ?>
            <div class="small"><strong>Changelog:</strong><ul class="mb-2">
              <?php foreach ((array)$meta['changelog'] as $line): ?><li><?= e($line) ?></li><?php endforeach; ?>
            </ul></div>
          <?php endif; ?>
          <form method="post" action="<?= e(admin_url('system/updates/run')) ?>"
                data-confirm="Run the update now? A full backup is taken first and everything is rolled back automatically if anything fails.">
            <?= Csrf::field() ?>
            <button class="btn btn-warning btn-lg w-100"><i class="bi bi-cloud-arrow-down"></i> Update Now</button>
          </form>
        </div>
      <?php elseif ($meta): ?>
        <div class="alert alert-success mb-0">✔ You are running the latest version — checked <?= e(fmt_date($meta['checked_at'] ?? '', true)) ?>.</div>
      <?php else: ?>
        <p class="text-muted">Save the repository details and press “Check for Update”.</p>
      <?php endif; ?>
    </div></div>
  </div>
</div>
