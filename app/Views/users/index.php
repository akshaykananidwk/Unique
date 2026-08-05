<?php use App\Core\Csrf; $title = 'Users'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Users</h4>
  <a class="btn btn-primary btn-sm" href="<?= e(admin_url('users/create')) ?>"><i class="bi bi-plus-lg"></i> Add User</a>
</div>
<div class="table-responsive"><table class="table table-sm table-hover table-mobile align-middle">
  <thead><tr><th>Name</th><th>Phone</th><th>Role</th><th>Last Login</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <tr class="<?= (int)$u['is_active'] ? '' : 'opacity-50' ?>">
      <td data-label="Name"><a class="fw-semibold" href="<?= e(admin_url('users/' . $u['id'] . '/edit')) ?>"><?= e($u['name']) ?></a></td>
      <td data-label="Phone"><?= e($u['phone']) ?></td>
      <td data-label="Role"><span class="badge bg-secondary"><?= e($u['role_name']) ?></span></td>
      <td data-label="Last Login" class="small"><?= e(fmt_date($u['last_login_at'], true)) ?></td>
      <td data-label="Status"><?= (int)$u['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
      <td data-label="">
        <div class="d-flex gap-1">
          <a class="btn btn-sm btn-outline-primary" href="<?= e(admin_url('users/' . $u['id'] . '/edit')) ?>"><i class="bi bi-pencil"></i></a>
          <form method="post" action="<?= e(admin_url('users/' . $u['id'] . '/delete')) ?>" data-confirm="Delete this user?">
            <?= Csrf::field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
