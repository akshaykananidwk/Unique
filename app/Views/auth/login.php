<?php use App\Core\Csrf; use App\Core\Settings; ?>
<div class="card shadow-sm">
  <div class="card-body p-4">
    <div class="text-center mb-4">
      <?php if ($logo = (string)Settings::get('business_logo', '')): ?>
        <img src="<?= e(upload_url($logo)) ?>" alt="" style="max-height:60px" class="mb-2">
      <?php endif; ?>
      <h4 class="mb-0"><?= e((string)Settings::get('business_name', 'Krishna Print')) ?></h4>
      <small class="text-muted">Staff Login</small>
    </div>
    <form method="post" action="<?= e(admin_url('login')) ?>">
      <?= Csrf::field() ?>
      <div class="mb-3">
        <label class="form-label">Phone or Email</label>
        <input type="text" name="identifier" class="form-control form-control-lg" required autofocus autocomplete="username">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control form-control-lg" required autocomplete="current-password">
      </div>
      <button class="btn btn-primary btn-lg w-100"><i class="bi bi-box-arrow-in-right"></i> Login</button>
    </form>
  </div>
</div>
