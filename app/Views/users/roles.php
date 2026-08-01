<?php use App\Core\Csrf; $title = 'Roles & Permissions';
$byModule = [];
foreach ($permissions as $p) { $byModule[$p['module']][] = $p; }
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Roles &amp; Permissions</h4>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#roleModal"><i class="bi bi-plus-lg"></i> New Role</button>
</div>

<div class="accordion" id="rolesAcc">
<?php foreach ($roles as $role): ?>
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#role<?= (int)$role['id'] ?>">
        <strong><?= e($role['name']) ?></strong>
        <span class="badge bg-secondary ms-2"><?= (int)$role['user_count'] ?> users</span>
        <?php if ((int)$role['is_system']): ?><span class="badge bg-info ms-1">built-in</span><?php endif; ?>
      </button>
    </h2>
    <div id="role<?= (int)$role['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#rolesAcc">
      <div class="accordion-body">
        <?php if ($role['slug'] === 'super_admin'): ?>
          <p class="text-muted">Super Admin always has every permission.</p>
        <?php elseif ($role['slug'] === 'customer'): ?>
          <p class="text-muted">Customers never log in — they use signed tracking/proof links only.</p>
        <?php else: ?>
        <form method="post" action="<?= e(admin_url('roles/' . $role['id'] . '/permissions')) ?>">
          <?= Csrf::field() ?>
          <div class="row">
            <?php foreach ($byModule as $module => $perms): ?>
            <div class="col-md-4 col-lg-3 mb-3">
              <div class="fw-semibold small text-uppercase text-muted mb-1"><?= e($module) ?></div>
              <?php foreach ($perms as $p): ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="<?= (int)$p['id'] ?>"
                       id="rp<?= (int)$role['id'] ?>_<?= (int)$p['id'] ?>"
                       <?= isset($matrix[(int)$role['id']][(int)$p['id']]) ? 'checked' : '' ?>>
                <label class="form-check-label small" for="rp<?= (int)$role['id'] ?>_<?= (int)$p['id'] ?>"><?= e($p['label']) ?></label>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm">Save Permissions</button>
        </form>
            <?php if (!(int)$role['is_system']): ?>
            <form method="post" action="<?= e(admin_url('roles/' . $role['id'] . '/delete')) ?>" data-confirm="Delete this role?">
              <?= Csrf::field() ?><button class="btn btn-outline-danger btn-sm">Delete Role</button>
            </form>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="modal fade" id="roleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="post" action="<?= e(admin_url('roles')) ?>">
    <?= Csrf::field() ?>
    <div class="modal-header"><h5 class="modal-title">New Role</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-2"><label class="form-label">Role name *</label><input name="name" class="form-control" required></div>
      <div class="mb-2"><label class="form-label">Description</label><input name="description" class="form-control"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Create Role</button></div>
  </form>
</div></div></div>
