<?php
use App\Core\Acl;
use App\Core\DB;
use App\Core\Settings;

$businessName = (string)Settings::get('business_name', 'Krishna Print');
$brandColor = (string)Settings::get('brand_color', '#E4002B');
$logo = (string)Settings::get('business_logo', '');
$unreadCount = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('notifications') . '` WHERE user_id = ? AND is_read = 0', [(int)$user['id']]);
$updateBadge = Settings::getBool('update_available', false) && Acl::can('update.manage');
$waPath = Acl::can('whatsapp.settings') ? 'whatsapp/settings' : 'whatsapp/logs';
$waPerm = Acl::can('whatsapp.settings') ? 'whatsapp.settings' : 'whatsapp.logs';

$navGroups = [
    'Main' => [
        ['dashboard.view', 'dashboard', 'speedometer2', 'Dashboard'],
        ['order.view', 'orders', 'receipt-cutoff', 'Orders'],
        ['order.view', 'orders/kanban', 'kanban', 'Job Board'],
        ['design.view', 'my-jobs', 'palette2', 'My Jobs'],
    ],
    // Item Master is gone — item names are typed free-hand and the CATEGORY carries the
    // question set, so Categories is the only catalogue screen that matters now.
    'Customers & Catalog' => [
        ['customer.view', 'customers', 'people', 'Customers'],
        ['category.manage', 'categories', 'tags', 'Categories'],
    ],
    'Money & Reports' => [
        ['payment.view', 'cashbook', 'cash-stack', 'Cash Book'],
        ['report.staff', 'reports', 'graph-up-arrow', 'Reports'],
    ],
    'Communication' => [
        [$waPerm, $waPath, 'whatsapp', 'WhatsApp'],
    ],
    // Single shop, many users — no branch switching.
    'Administration' => [
        ['user.manage', 'users', 'person-gear', 'Users'],
        ['role.manage', 'roles', 'shield-lock', 'Roles'],
    ],
    'System' => [
        ['settings.manage', 'settings', 'gear', 'Settings'],
        ['settings.manage', 'system/cron', 'clock-history', 'Cron Jobs'],
        ['update.manage', 'system/updates', 'cloud-arrow-down', 'Updates'],
        ['backup.manage', 'system/backups', 'hdd-stack', 'Backups'],
    ],
];
$currentPath = trim((string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/');
$isActive = static function (string $path) use ($currentPath): bool {
    if ($path === 'orders') {
        return (bool)preg_match('#/admin/orders(/\d+|/create|$)#', '/' . $currentPath) && !str_contains($currentPath, 'kanban');
    }
    return str_contains($currentPath, '/admin/' . $path) || str_ends_with($currentPath, '/admin/' . $path);
};
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
<?php if ($favicon = (string)Settings::get('business_favicon', '')): ?><link rel="icon" href="<?= e(upload_url($favicon)) ?>"><?php endif; ?>
</head>
<body class="admin-body">

<!-- Sidebar (offcanvas on < lg) -->
<aside class="kp-sidebar offcanvas-lg offcanvas-start" id="sidebar" tabindex="-1">
  <div class="kp-sidebar-head">
    <a class="kp-brand" href="<?= e(admin_url()) ?>">
      <?php if ($logo): ?>
        <img src="<?= e(upload_url($logo)) ?>" alt="">
      <?php else: ?>
        <span class="kp-brand-mark"><?= kp_cubes_svg('kp-mini-cubes') ?></span>
      <?php endif; ?>
      <span class="kp-brand-name"><?= e($businessName) ?></span>
    </a>
    <button class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button>
  </div>

  <nav class="kp-nav">
    <?php foreach ($navGroups as $group => $items):
        $visible = array_filter($items, fn($i) => Acl::can($i[0]));
        if (!$visible) continue; ?>
      <div class="kp-nav-group"><?= e($group) ?></div>
      <?php foreach ($visible as [$perm, $path, $icon, $label]): ?>
        <a class="kp-nav-link<?= $isActive($path) ? ' active' : '' ?>" href="<?= e(admin_url($path)) ?>">
          <i class="bi bi-<?= e($icon) ?>"></i><span><?= e($label) ?></span>
          <?php if ($path === $waPath && $updateBadge === false): ?><?php endif; ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>

  <?php if (Acl::can('order.create')): ?>
  <div class="kp-sidebar-foot">
    <a class="btn btn-brand w-100" href="<?= e(admin_url('orders/create')) ?>"><i class="bi bi-plus-lg"></i> New Order</a>
  </div>
  <?php endif; ?>
</aside>

<!-- Top bar -->
<header class="kp-topbar">
  <button class="btn btn-light d-lg-none border" data-bs-toggle="offcanvas" data-bs-target="#sidebar"><i class="bi bi-list"></i></button>
  <div class="kp-search d-none d-md-block">
    <i class="bi bi-search"></i>
    <input id="globalSearch" class="form-control" placeholder="Search job no, customer or phone…" autocomplete="off">
    <div id="globalSearchResults" class="dropdown-menu shadow"></div>
  </div>
  <div class="ms-auto d-flex align-items-center gap-2">
    <button class="btn btn-icon" id="themeToggle" title="Toggle theme"><i class="bi bi-moon-stars"></i></button>
    <?php if ($updateBadge): ?>
      <a href="<?= e(admin_url('system/updates')) ?>" class="btn btn-warning btn-sm rounded-pill" title="Update available"><i class="bi bi-cloud-arrow-down"></i> Update</a>
    <?php endif; ?>
    <a href="<?= e(admin_url('notifications')) ?>" class="btn btn-icon position-relative" title="Notifications">
      <i class="bi bi-bell"></i>
      <?php if ($unreadCount): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span><?php endif; ?>
    </a>
    <div class="dropdown">
      <button class="btn kp-user dropdown-toggle" data-bs-toggle="dropdown">
        <span class="kp-avatar"><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></span>
        <span class="d-none d-md-inline"><?= e($user['name']) ?></span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow">
        <li><span class="dropdown-item-text small text-muted"><?= e($user['role_name']) ?></span></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="<?= e(admin_url('logout')) ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</header>

<main class="kp-main">
  <?php foreach ((array)flash() as $f): ?>
    <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show" role="alert">
      <?= $f['message'] /* staff-authored messages; links allowed */ ?>
      <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endforeach; ?>
  <?= $content ?>
</main>

<script src="<?= e(asset_url('vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(asset_url('vendor/chartjs/chart.umd.min.js')) ?>"></script>
<script>window.KP={adminUrl:'<?= e(admin_url()) ?>',csrf:'<?= e(App\Core\Csrf::token()) ?>'};</script>
<script src="<?= e(asset_url('js/app.js')) ?>"></script>
</body>
</html>
