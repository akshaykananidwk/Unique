<?php $db = ins_data('db', []); ?>
<h5>Step 2 — Database</h5>
<p class="text-muted small">Create an empty MySQL database in aaPanel first (Databases → Add database), then enter its details.</p>
<form method="post">
  <input type="hidden" name="_csrf" value="<?= ins_csrf() ?>">
  <input type="hidden" name="step" value="2">
  <div class="row g-2">
    <div class="col-md-8"><label class="form-label">Host</label>
      <input name="db_host" class="form-control" value="<?= ins_e($db['host'] ?? '127.0.0.1') ?>"></div>
    <div class="col-md-4"><label class="form-label">Port</label>
      <input type="number" name="db_port" class="form-control" value="<?= ins_e($db['port'] ?? 3306) ?>"></div>
    <div class="col-md-4"><label class="form-label">Database name *</label>
      <input name="db_name" class="form-control" required value="<?= ins_e($db['name'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Username *</label>
      <input name="db_user" class="form-control" required value="<?= ins_e($db['user'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Password</label>
      <input type="password" name="db_pass" class="form-control" value="<?= ins_e($db['pass'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Table prefix (optional)</label>
      <input name="db_prefix" class="form-control" value="<?= ins_e($db['prefix'] ?? '') ?>" placeholder="leave blank for none"></div>
  </div>
  <?php if (ins_data('db_existing_tables')): ?>
  <div class="form-check mt-3">
    <input class="form-check-input" type="checkbox" name="wipe_confirm" value="1" id="wipe">
    <label class="form-check-label text-danger" for="wipe">
      Wipe the <?= (int)ins_data('db_existing_tables') ?> existing tables (ALL DATA in this database will be lost)</label>
  </div>
  <?php endif; ?>
  <div class="d-flex justify-content-between mt-3">
    <a class="btn btn-outline-secondary" href="?step=1">← Back</a>
    <button class="btn btn-primary">Test Connection &amp; Continue →</button>
  </div>
</form>
