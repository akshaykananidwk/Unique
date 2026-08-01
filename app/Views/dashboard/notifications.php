<?php use App\Core\Csrf; $title = 'Notifications'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Notifications</h4>
  <form method="post" action="<?= e(admin_url('notifications/read')) ?>"><?= Csrf::field() ?>
    <button class="btn btn-sm btn-outline-secondary">Mark all read</button>
  </form>
</div>
<?php if (!$rows): ?>
  <div class="empty-state"><i class="bi bi-bell-slash"></i>No notifications yet.</div>
<?php endif; ?>
<div class="list-group">
<?php foreach ($rows as $n): ?>
  <a class="list-group-item list-group-item-action <?= (int)$n['is_read'] === 0 ? 'fw-semibold' : '' ?>"
     href="<?= e($n['url'] ?: '#') ?>">
    <i class="bi bi-<?= e($n['icon'] ?: 'bell') ?> me-2"></i><?= e($n['title']) ?>
    <div class="small text-muted"><?= e($n['body']) ?> · <?= e(fmt_date($n['created_at'], true)) ?></div>
  </a>
<?php endforeach; ?>
</div>
