<?php use App\Core\Csrf; $title = 'Backups'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Backups</h4>
  <form method="post" action="<?= e(admin_url('system/backups/create')) ?>"><?= Csrf::field() ?>
    <button class="btn btn-primary btn-sm"><i class="bi bi-archive"></i> Create Backup Now</button>
  </form>
</div>
<?php if (!$backups): ?><div class="empty-state"><i class="bi bi-archive"></i>No backups yet. One is taken automatically before every update and nightly by cron.</div><?php endif; ?>
<div class="table-responsive"><table class="table table-sm table-mobile align-middle">
  <thead><tr><th>Backup</th><th>Version</th><th>Reason</th><th>Size</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($backups as $b): ?>
    <tr>
      <td data-label="Backup"><code><?= e($b['name']) ?></code>
        <div class="small text-muted"><?= e($b['manifest']['created_at'] ?? '') ?></div></td>
      <td data-label="Version"><?= e($b['manifest']['version'] ?? '?') ?></td>
      <td data-label="Reason"><?= e($b['manifest']['reason'] ?? '—') ?>
        <?= !empty($b['manifest']['includes_uploads']) ? '<span class="badge bg-info badge-status">+uploads</span>' : '' ?></td>
      <td data-label="Size"><?= e(number_format($b['size'] / 1048576, 1)) ?> MB</td>
      <td data-label="Actions">
        <div class="d-flex gap-1">
          <form method="post" action="<?= e(admin_url('system/backups/restore')) ?>"
                data-confirm="RESTORE from this backup? Current files AND database will be replaced with this snapshot.">
            <?= Csrf::field() ?><input type="hidden" name="backup_dir" value="<?= e($b['dir']) ?>">
            <button class="btn btn-sm btn-warning">Restore</button>
          </form>
          <form method="post" action="<?= e(admin_url('system/backups/delete')) ?>" data-confirm="Delete this backup permanently?">
            <?= Csrf::field() ?><input type="hidden" name="backup_dir" value="<?= e($b['dir']) ?>">
            <button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
