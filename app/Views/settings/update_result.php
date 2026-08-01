<?php $title = 'Update Result'; ?>
<h4 class="mb-3">Update Result</h4>
<?php if ($result['ok']): ?>
  <div class="alert alert-success">
    <h5 class="alert-heading">✔ Update installed successfully</h5>
    <p><?= e($result['message']) ?></p>
    <?php if (!empty($result['changelog'])): ?>
      <strong>What changed:</strong>
      <ul><?php foreach ((array)$result['changelog'] as $line): ?><li><?= e($line) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
  </div>
<?php elseif (!empty($result['rolled_back'])): ?>
  <div class="alert alert-danger">
    <h5 class="alert-heading">✖ Update failed — rollback completed</h5>
    <p><?= e($result['message']) ?></p>
    <p class="mb-0">Your files and database were restored from <code><?= e($result['backup_path'] ?? '') ?></code>. The site is running the previous version.</p>
  </div>
<?php else: ?>
  <div class="alert alert-danger">
    <h5 class="alert-heading">✖ Update could not start</h5>
    <p class="mb-0"><?= e($result['message']) ?></p>
  </div>
<?php endif; ?>
<?php if (!empty($result['log'])): ?>
<div class="card"><div class="card-body">
  <h6>Update log</h6>
  <pre class="small mb-0" style="white-space:pre-wrap"><?= e($result['log']) ?></pre>
</div></div>
<?php endif; ?>
<a class="btn btn-primary mt-3" href="<?= e(admin_url('system/updates')) ?>">Back to Updates</a>
