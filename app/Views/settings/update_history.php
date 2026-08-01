<?php $title = 'Update History'; ?>
<h4 class="mb-3">Update History</h4>
<?php if (!$rows): ?><div class="empty-state"><i class="bi bi-clock-history"></i>No updates have been run yet.</div><?php endif; ?>
<div class="table-responsive"><table class="table table-sm table-mobile">
  <thead><tr><th>#</th><th>From → To</th><th>Status</th><th>Started</th><th>Backup</th><th>Log</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td data-label="#"><?= (int)$r['id'] ?></td>
      <td data-label="From → To"><?= e($r['from_version']) ?> → <?= e($r['to_version'] ?? '—') ?>
        <div class="small text-muted"><code><?= e(substr((string)$r['from_commit'], 0, 8)) ?></code> → <code><?= e(substr((string)$r['to_commit'] ?? '', 0, 8)) ?></code></div></td>
      <td data-label="Status"><span class="badge bg-<?= e(['success' => 'success', 'failed' => 'danger', 'rolled_back' => 'warning'][$r['status']] ?? 'secondary') ?>"><?= e($r['status']) ?></span></td>
      <td data-label="Started" class="small"><?= e(fmt_date($r['started_at'], true)) ?></td>
      <td data-label="Backup" class="small"><code><?= e($r['backup_path'] ?? '—') ?></code></td>
      <td data-label="Log"><details><summary class="small">view</summary><pre class="small" style="white-space:pre-wrap;max-height:300px;overflow:auto"><?= e($r['log_text']) ?></pre></details></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<a class="btn btn-outline-secondary btn-sm" href="<?= e(admin_url('system/updates')) ?>">← Back</a>
