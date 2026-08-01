<?php
use App\Core\Acl;
use App\Core\DB;
use App\Core\Settings;

$businessName = (string)Settings::get('business_name', 'Krishna Print');
$brandColor = (string)Settings::get('brand_color', '#0d6efd');
$unreadCount = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('notifications') . '` WHERE user_id = ? AND is_read = 0', [(int)$user['id']]);
$updateBadge = Settings::getBool('update_available', false) && Acl::can('update.manage');
$nav = [
    ['dashboard.view', 'dashboard', 'speedometer2', 'Dashboard'],
    ['order.view', 'orders', 'receipt', 'Orders'],
    ['order.view', 'orders/kanban', 'kanban', 'Job Board'],
    ['design.view', 'my-jobs', 'palette', 'My Jobs'],
    ['customer.view', 'customers', 'people', 'Customers'],
    ['payment.view', 'cashbook', 'cash-stack', 'Cash Book'],
    ['item.manage', 'items', 'box-seam', 'Items'],
    ['category.manage', 'categories', 'tags', 'Categories'],
    ['report.staff', 'reports', 'graph-up', 'Reports'],
    ['whatsapp.logs', 'whatsapp/logs', 'whatsapp', 'WhatsApp'],
    ['user.manage', 'users', 'person-gear', 'Users'],
    ['role.manage', 'roles', 'shield-lock', 'Roles'],
    ['branch.manage', 'branches', 'shop', 'Branches'],
    ['settings.manage', 'settings', 'gear', 'Settings'],
    ['update.manage', 'system/updates', 'cloud-arrow-down', 'Updates'],
    ['backup.manage', 'system/backups', 'archive', 'Backups'],
];
$currentPath = trim((string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Admin') ?> — <?= e($businessName) ?></title>
<link rel="stylesheet" href="<?= e(asset_url('vendor/bootstrap/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(asset_url('vendor/bootstrap-icons/bootstrap-icons.css')) ?>">
<link rel="stylesheet" href="<?= e(asset_url('css/app.css')) ?>">
<style>:root{--kp-brand:<?= e($brandColor) ?>}</style>
</head>
<body class="admin-body">
<nav class="navbar navbar-expand-lg border-bottom sticky-top bg-body px-2">
  <button class="btn btn-outline-secondary d-lg-none me-2" data-bs-toggle="offcanvas" data-bs-target="#sidebar"><i class="bi bi-list"></i></button>
  <a class="navbar-brand fw-bold text-truncate" style="color:var(--kp-brand)" href="<?= e(admin_url()) ?>"><?= e($businessName) ?></a>
  <div class="ms-auto d-flex align-items-center gap-2">
    <div class="position-relative d-none d-md-block">
      <input id="globalSearch" class="form-control form-control-sm" style="width:260px" placeholder="Search job no / customer / phone…" autocomplete="off">
      <div id="globalSearchResults" class="dropdown-menu shadow" style="max-width:360px"></div>
    </div>
    <button class="btn btn-outline-secondary btn-sm" id="themeToggle" title="Dark mode"><i class="bi bi-moon"></i></button>
    <?php if ($updateBadge): ?>
      <a href="<?= e(admin_url('system/updates')) ?>" class="btn btn-warning btn-sm" title="Update available"><i class="bi bi-cloud-arrow-down"></i></a>
    <?php endif; ?>
    <a href="<?= e(admin_url('notifications')) ?>" class="btn btn-outline-secondary btn-sm position-relative">
      <i class="bi bi-bell"></i>
      <?php if ($unreadCount): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span><?php endif; ?>
    </a>
    <div class="dropdown">
      <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> <span class="d-none d-md-inline"><?= e($user['name']) ?></span></button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><span class="dropdown-item-text small text-muted"><?= e($user['role_name']) ?></span></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="<?= e(admin_url('logout')) ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="d-flex">
  <div class="offcanvas-lg offcanvas-start admin-sidebar border-end" id="sidebar">
    <div class="offcanvas-header d-lg-none"><strong><?= e($businessName) ?></strong>
      <button class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button></div>
    <ul class="nav flex-column py-2">
      <?php foreach ($nav as [$perm, $path, $icon, $label]): if (!Acl::can($perm)) continue; ?>
      <li class="nav-item">
        <a class="nav-link<?= str_contains($currentPath, $path) && ($path !== 'orders' || preg_match('#admin/orders(?!/kanban)#', $currentPath)) ? ' active' : '' ?>" href="<?= e(admin_url($path)) ?>">
          <i class="bi bi-<?= e($icon) ?> me-2"></i><?= e($label) ?>
        </a>
      </li>
      <?php endforeach; ?>
      <?php if (Acl::can('order.create')): ?>
      <li class="nav-item mt-2 px-3">
        <a class="btn btn-primary w-100" href="<?= e(admin_url('orders/create')) ?>"><i class="bi bi-plus-lg"></i> New Order</a>
      </li>
      <?php endif; ?>
    </ul>
  </div>
  <main class="flex-grow-1 p-3 admin-main">
    <?php foreach ((array)flash() as $f): ?>
      <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show" role="alert">
        <?= $f['message'] /* messages are staff-authored; links allowed */ ?>
        <button class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endforeach; ?>
    <?= $content ?>
  </main>
</div>
<script src="<?= e(asset_url('vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(asset_url('vendor/chartjs/chart.umd.min.js')) ?>"></script>
<script>window.KP={adminUrl:'<?= e(admin_url()) ?>',csrf:'<?= e(App\Core\Csrf::token()) ?>'};</script>
<script src="<?= e(asset_url('js/app.js')) ?>"></script>
</body>
</html>
