<?php $admin = ins_data('admin', []); ?>
<h5>Step 6 — Super Admin Account</h5>
<form method="post">
  <input type="hidden" name="_csrf" value="<?= ins_csrf() ?>">
  <input type="hidden" name="step" value="6">
  <div class="row g-2">
    <div class="col-md-6"><label class="form-label">Your name *</label>
      <input name="admin_name" class="form-control" required value="<?= ins_e($admin['name'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Mobile number * <small class="text-muted">(this is your login)</small></label>
      <input name="admin_phone" class="form-control" required maxlength="10" pattern="[0-9]{10}" inputmode="numeric" value="<?= ins_e($admin['phone'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Email</label>
      <input type="email" name="admin_email" class="form-control" value="<?= ins_e($admin['email'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Password * (min 8 chars)</label>
      <input type="password" name="admin_password" id="pw" class="form-control" required minlength="8">
      <div class="progress mt-1" style="height:6px"><div class="progress-bar" id="pwBar" style="width:0%"></div></div></div>
    <div class="col-md-6"><label class="form-label">Confirm password *</label>
      <input type="password" name="admin_password2" class="form-control" required minlength="8"></div>
  </div>
  <div class="d-flex justify-content-between mt-3">
    <a class="btn btn-outline-secondary" href="?step=5">← Back</a>
    <button class="btn btn-primary">Continue →</button>
  </div>
</form>
<script>
document.getElementById('pw').addEventListener('input', function () {
  let s = 0;
  if (this.value.length >= 8) s += 34;
  if (/[A-Z]/.test(this.value) && /[a-z]/.test(this.value)) s += 33;
  if (/\d/.test(this.value) || /[^A-Za-z0-9]/.test(this.value)) s += 33;
  const bar = document.getElementById('pwBar');
  bar.style.width = s + '%';
  bar.className = 'progress-bar bg-' + (s < 40 ? 'danger' : s < 80 ? 'warning' : 'success');
});
</script>
