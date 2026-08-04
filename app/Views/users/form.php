<?php use App\Core\Csrf; $title = $staff ? 'Edit User' : 'Add User'; ?>
<h4 class="mb-3"><?= e($title) ?></h4>
<form method="post" action="<?= e($staff ? admin_url('users/' . $staff['id'] . '/update') : admin_url('users')) ?>">
  <?= Csrf::field() ?>
  <div class="card mb-3"><div class="card-body row g-2">
    <div class="col-md-4"><label class="form-label">Name *</label>
      <input name="name" class="form-control" required value="<?= e($staff['name'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Phone * <small class="text-muted">(login id)</small></label>
      <input name="phone" class="form-control" required inputmode="tel" placeholder="10-digit mobile" value="<?= e($staff['phone'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= e($staff['email'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Role *</label>
      <select name="role_id" class="form-select" required id="roleSelect">
        <?php foreach ($roles as $r): ?>
          <option value="<?= (int)$r['id'] ?>" data-slug="<?= e($r['slug']) ?>" <?= (int)($staff['role_id'] ?? 0) === (int)$r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-4"><label class="form-label">Primary Branch</label>
      <select name="primary_branch_id" class="form-select">
        <option value="">— None —</option>
        <?php foreach ($branches as $b): ?>
          <option value="<?= (int)$b['id'] ?>" <?= (int)($staff['primary_branch_id'] ?? 0) === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-4"><label class="form-label">Extra branches (multi-branch access)</label>
      <div class="d-flex flex-wrap gap-2 pt-1">
        <?php foreach ($branches as $b): ?>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="branch_ids[]" value="<?= (int)$b['id'] ?>"
                 id="b<?= (int)$b['id'] ?>" <?= in_array((int)$b['id'], $userBranches, true) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="b<?= (int)$b['id'] ?>"><?= e($b['name']) ?></label>
        </div>
        <?php endforeach; ?>
      </div></div>
    <div class="col-md-4"><label class="form-label">Password <?= $staff ? '<small class="text-muted">(leave blank to keep)</small>' : '* (min 8 chars)' ?></label>
      <input type="password" name="password" class="form-control" minlength="8" <?= $staff ? '' : 'required' ?> autocomplete="new-password" id="pwField">
      <div class="progress mt-1" style="height:5px"><div class="progress-bar" id="pwMeter" style="width:0%"></div></div></div>
    <div class="col-md-4"><label class="form-label">Designer capacity <small class="text-muted">(open jobs, blank = unlimited)</small></label>
      <input type="number" min="1" name="designer_capacity" class="form-control" value="<?= e($staff['designer_capacity'] ?? '') ?>"></div>
    <div class="col-md-4 d-flex align-items-end">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="ua" <?= ($staff === null || !empty($staff['is_active'])) ? 'checked' : '' ?>>
        <label class="form-check-label" for="ua">Active</label>
      </div></div>
  </div></div>
  <div class="sticky-actions d-flex gap-2">
    <button class="btn btn-primary flex-grow-1">Save User</button>
    <a href="<?= e(admin_url('users')) ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>
<script>
document.getElementById('pwField').addEventListener('input', function () {
  let score = 0;
  if (this.value.length >= 8) score += 34;
  if (/[A-Z]/.test(this.value) && /[a-z]/.test(this.value)) score += 33;
  if (/\d/.test(this.value) || /[^A-Za-z0-9]/.test(this.value)) score += 33;
  const bar = document.getElementById('pwMeter');
  bar.style.width = score + '%';
  bar.className = 'progress-bar bg-' + (score < 40 ? 'danger' : score < 80 ? 'warning' : 'success');
});
</script>
