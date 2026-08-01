<?php
use App\Core\Settings;

$businessName = (string)Settings::get('business_name', 'Krishna Print');
$brandColor = (string)Settings::get('brand_color', '#0d6efd');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? $businessName) ?></title>
<link rel="stylesheet" href="<?= e(asset_url('vendor/bootstrap/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(asset_url('vendor/bootstrap-icons/bootstrap-icons.css')) ?>">
<link rel="stylesheet" href="<?= e(asset_url('css/app.css')) ?>">
<style>:root{--kp-brand:<?= e($brandColor) ?>}</style>
</head>
<body class="bg-body-tertiary">
<div class="container d-flex align-items-center justify-content-center" style="min-height:100vh">
  <div style="width:100%;max-width:400px">
    <?php foreach ((array)flash() as $f): ?>
      <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
  </div>
</div>
<script src="<?= e(asset_url('vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
</body>
</html>
