<?php
use App\Core\Settings;

$businessName = (string)Settings::get('business_name', 'Krishna Print');
$brandColor = (string)Settings::get('brand_color', '#E4002B');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Login') ?> — <?= e($businessName) ?></title>
<link rel="stylesheet" href="<?= e(asset_url('vendor/bootstrap-icons/bootstrap-icons.css')) ?>">
<link rel="stylesheet" href="<?= e(asset_url('css/app.css')) ?>">
<style>:root{--kp-brand:<?= e($brandColor) ?>}</style>
<?php if ($favicon = (string)Settings::get('business_favicon', '')): ?><link rel="icon" href="<?= e(upload_url($favicon)) ?>"><?php endif; ?>
</head>
<body class="auth-body">
  <?= $content ?>
</body>
</html>
