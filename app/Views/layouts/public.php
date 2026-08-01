<?php
use App\Core\Settings;

$businessName = (string)Settings::get('business_name', 'Krishna Print');
$brandColor = (string)Settings::get('brand_color', '#0d6efd');
$waNumber = normalize_phone((string)Settings::get('business_whatsapp', '')) ?? '';
$logo = (string)Settings::get('business_logo', '');
$cartCount = count($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? $businessName) ?></title>
<link rel="stylesheet" href="<?= e(asset_url('vendor/bootstrap/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(asset_url('vendor/bootstrap-icons/bootstrap-icons.css')) ?>">
<link rel="stylesheet" href="<?= e(asset_url('css/app.css')) ?>">
<style>:root{--kp-brand:<?= e($brandColor) ?>}</style>
<?php if ($favicon = (string)Settings::get('business_favicon', '')): ?><link rel="icon" href="<?= e(upload_url($favicon)) ?>"><?php endif; ?>
</head>
<body class="public-body">
<nav class="navbar navbar-expand-md bg-body border-bottom sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" style="color:var(--kp-brand)" href="<?= e(base_url()) ?>">
      <?php if ($logo): ?><img src="<?= e(upload_url($logo)) ?>" alt="" height="32"><?php endif; ?>
      <?= e($businessName) ?>
    </a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#pubnav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="pubnav">
      <ul class="navbar-nav ms-auto align-items-md-center gap-md-2">
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('products')) ?>">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('my-orders')) ?>">Track Order</a></li>
        <li class="nav-item"><a class="btn btn-outline-primary btn-sm position-relative" href="<?= e(base_url('cart')) ?>">
          <i class="bi bi-cart3"></i> Cart
          <?php if ($cartCount): ?><span class="badge bg-danger rounded-pill"><?= $cartCount ?></span><?php endif; ?>
        </a></li>
        <li class="nav-item"><a class="btn btn-primary btn-sm" href="<?= e(base_url('products')) ?>">Order Now</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(admin_url('login')) ?>" title="Staff login"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
      </ul>
    </div>
  </div>
</nav>
<main class="public-main">
  <div class="container py-3">
    <?php foreach ((array)flash() as $f): ?>
      <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($f['message']) ?>
        <button class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endforeach; ?>
  </div>
  <?= $content ?>
</main>
<footer class="border-top py-4 mt-5">
  <div class="container small text-muted d-md-flex justify-content-between">
    <div>
      <strong><?= e($businessName) ?></strong> — <?= e((string)Settings::get('business_tagline', '')) ?><br>
      <?= e((string)Settings::get('business_address', '')) ?> <?= e((string)Settings::get('business_city', '')) ?><br>
      Phone: <?= e((string)Settings::get('business_phone', '')) ?>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="<?= e(base_url('track')) ?>">Track your order</a>
    </div>
  </div>
</footer>
<?php if ($waNumber): ?>
<a class="wa-float" target="_blank" rel="noopener" href="https://wa.me/<?= e($waNumber) ?>" title="Chat on WhatsApp"><i class="bi bi-whatsapp"></i></a>
<?php endif; ?>
<script src="<?= e(asset_url('vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
<script>window.KP={csrf:'<?= e(App\Core\Csrf::token()) ?>'};</script>
<script src="<?= e(asset_url('js/public.js')) ?>"></script>
</body>
</html>
