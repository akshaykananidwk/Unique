<?php
use App\Core\Csrf;
use App\Core\Settings;

$businessName = (string)Settings::get('business_name', 'Krishna Print');
$tagline = (string)Settings::get('business_tagline', 'Printing | Branding | Innovation');
$logo = (string)Settings::get('business_logo', '');

$title = 'Login';
?>
<div class="auth-sheet">
  <?= kp_cubes_svg('auth-cubes') ?>

  <div class="auth-grid">
    <!-- Left: form -->
    <div class="auth-left">
      <?php foreach ((array)flash() as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?> py-2 small mb-2"><?= e($f['message']) ?></div>
      <?php endforeach; ?>

      <h1 class="auth-welcome">Welcome Back!</h1>
      <div class="auth-underline"></div>
      <p class="auth-sub">Sign in to continue</p>

      <form class="auth-form" method="post" action="<?= e(admin_url('login')) ?>">
        <?= Csrf::field() ?>
        <label class="fld">Username / Email</label>
        <div class="auth-input">
          <span class="ico"><i class="bi bi-person"></i></span>
          <input type="text" name="identifier" placeholder="Enter your username or email" required autofocus autocomplete="username">
        </div>

        <label class="fld">Password</label>
        <div class="auth-input">
          <span class="ico"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" id="pw" placeholder="Enter your password" required autocomplete="current-password">
          <button type="button" class="eye" id="eye" tabindex="-1" aria-label="Show password"><i class="bi bi-eye-slash"></i></button>
        </div>

        <div class="auth-meta">
          <label class="d-flex align-items-center gap-2 m-0">
            <input type="checkbox" name="remember" value="1"> Remember Me
          </label>
          <a href="#" onclick="alert('Please contact your administrator to reset your password.');return false;">Forgot Password?</a>
        </div>

        <button class="auth-btn" type="submit">Login <i class="bi bi-box-arrow-in-right"></i></button>
        <p class="auth-signup">Staff access only · contact the admin for an account</p>
      </form>

      <div class="auth-features">
        <div class="auth-feature"><div class="fi"><i class="bi bi-shield-check"></i></div><b>Secure &amp; Safe</b><small>Your data is 100% secure</small></div>
        <div class="auth-feature"><div class="fi"><i class="bi bi-speedometer2"></i></div><b>Fast &amp; Reliable</b><small>Quick access anytime</small></div>
        <div class="auth-feature"><div class="fi"><i class="bi bi-headset"></i></div><b>24/7 Support</b><small>We're here to help</small></div>
      </div>

      <div class="auth-brand">
        <?php if ($logo): ?>
          <img src="<?= e(upload_url($logo)) ?>" alt="<?= e($businessName) ?>">
        <?php else: ?>
          <span class="drop"><i class="bi bi-droplet-fill"></i></span>
          <div>
            <div class="bn"><?= e($businessName) ?></div>
            <div class="tg"><?= e($tagline) ?></div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right: vibrant printing panel -->
    <div class="auth-right">
      <span class="glow floaty" style="width:120px;height:120px;background:#00aeef;top:12%;left:14%"></span>
      <span class="glow floaty d" style="width:80px;height:80px;background:#ffd400;bottom:18%;left:22%"></span>
      <span class="glow floaty" style="width:60px;height:60px;background:#39b54a;top:22%;right:16%"></span>
      <div class="auth-poster floaty">
        <div class="l l1">WE</div>
        <div class="l l2">PRINT</div>
        <div class="l l3">WHAT YOU</div>
        <div class="l l4">THINK</div>
      </div>
      <div class="tag"><?= e($businessName) ?></div>
    </div>
  </div>
</div>

<script>
(function () {
  var pw = document.getElementById('pw'), eye = document.getElementById('eye');
  eye && eye.addEventListener('click', function () {
    var show = pw.type === 'password';
    pw.type = show ? 'text' : 'password';
    eye.innerHTML = show ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
  });
})();
</script>
