<?php $log = ins_data('import_log'); ?>
<h5>Step 3 — Import Database</h5>
<?php if (ins_data('imported')): ?>
  <div class="alert alert-success">✔ Import complete.</div>
  <pre class="small bg-body-tertiary p-2 rounded"><?= ins_e(implode("\n", (array)$log)) ?></pre>
  <div class="d-flex justify-content-end">
    <a class="btn btn-primary" href="?step=4">Continue →</a>
  </div>
<?php else: ?>
  <p>This imports <code>schema.sql</code> (all tables), <code>seed.sql</code> (roles, permissions,
     default settings, the 16 WhatsApp templates and a sample catalogue) and any migrations —
     each recorded so the auto-updater never re-runs them.</p>
  <form method="post">
    <input type="hidden" name="_csrf" value="<?= ins_csrf() ?>">
    <input type="hidden" name="step" value="3">
    <div class="d-flex justify-content-between">
      <a class="btn btn-outline-secondary" href="?step=2">← Back</a>
      <button class="btn btn-primary">Run Import</button>
    </div>
  </form>
<?php endif; ?>
