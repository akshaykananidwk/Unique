<?php use App\Models\Status; $title = 'Job Board'; ?>
<h4 class="mb-3">Job Board <small class="text-muted fs-6">all active job items by stage</small></h4>
<div class="kanban-wrap">
  <?php foreach ($columns as $statusKey => $cards): ?>
  <div class="kanban-col">
    <h6 class="d-flex justify-content-between">
      <span><?= e(Status::label($statusKey)) ?></span>
      <span class="badge bg-secondary"><?= count($cards) ?></span>
    </h6>
    <?php foreach ($cards as $card):
        $overdue = Status::isOverdue($card['due_date'], (string)$card['status']); ?>
    <div class="kanban-card <?= $overdue ? 'overdue' : '' ?> <?= e(priority_class($card['priority'])) ?>">
      <?php // Customer name on top and clickable — that is how a job is recognised at a
      // glance. The job number goes underneath rather than away. ?>
      <a href="<?= e(admin_url('orders/' . $card['order_id'])) ?>" class="fw-semibold"><?= e($card['customer_name']) ?></a>
      <?= priority_badge($card['priority']) ?>
      <div><?= e($card['item_name_snapshot']) ?></div>
      <div class="text-muted small"><?= e($card['job_no']) ?></div>
      <?php if ($card['designer_name']): ?><div class="text-muted"><i class="bi bi-palette"></i> <?= e($card['designer_name']) ?></div><?php endif; ?>
      <div class="<?= $overdue ? 'text-overdue fw-bold' : 'text-muted' ?>"><i class="bi bi-clock"></i> <?= e(fmt_date($card['due_date'], true)) ?></div>
    </div>
    <?php endforeach; ?>
    <?php if (!$cards): ?><div class="text-muted small text-center py-2">—</div><?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
