<?php
use App\Core\Csrf;
use App\Core\Settings;

$businessName = (string)Settings::get('business_name', 'Krishna Print');
$tagline = (string)Settings::get('business_tagline', 'Printing | Branding | Innovation');
$logo = (string)Settings::get('business_logo', '');
$loginImage = (string)Settings::get('login_image', '');
$peopleUrl = $loginImage !== '' ? upload_url($loginImage) : asset_url('img/login-people.jpg');

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

      <!-- People shown here on phones/tablets (hidden on desktop where the side panel shows them) -->
      <div class="auth-people-mobile">
        <img src="<?= e($peopleUrl) ?>" alt="" loading="lazy">
      </div>

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

    <!-- Right: the business's own Navratri banner models (configurable in Settings) -->
    <div class="auth-people" role="img" aria-label="<?= e($businessName) ?>" style="background-image:url('<?= e($peopleUrl) ?>')"></div>
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
