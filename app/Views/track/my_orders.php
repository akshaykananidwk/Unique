<?php
use App\Models\Status;
$title = 'My Orders';
$active = array_filter($orders, fn($o) => !in_array($o['status'], ['completed', 'cancelled'], true));
$done = array_filter($orders, fn($o) => $o['status'] === 'completed');
?>
<div class="container section" style="max-width:760px">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <h1 class="section-title mb-0">My Orders</h1>
      <p class="section-sub mb-0">Hello <?= e($customer['name']) ?> 🙏 · <?= e($customer['phone']) ?></p>
    </div>
    <a href="<?= e(base_url('my-orders/exit')) ?>" class="btn btn-outline-secondary btn-sm">Not you? Exit</a>
  </div>

  <div class="row g-2 mb-3">
    <div class="col-4"><div class="stat-card text-center"><div class="stat-value"><?= count($orders) ?></div><div class="stat-label">Total Orders</div></div></div>
    <div class="col-4"><div class="stat-card text-center"><div class="stat-value text-primary"><?= count($active) ?></div><div class="stat-label">In Progress</div></div></div>
    <div class="col-4"><div class="stat-card text-center"><div class="stat-value text-success"><?= count($done) ?></div><div class="stat-label">Completed</div></div></div>
  </div>

  <?php if (!$orders): ?>
    <div class="empty-state"><i class="bi bi-inbox"></i>No orders found for this number.</div>
  <?php endif; ?>

  <?php foreach ($orders as $o):
      $pct = Status::progressPercent((string)$o['status']);
      $color = Status::color((string)$o['status']); ?>
  <div class="track-box mb-3 p-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <div class="fw-bold fs-5"><?= e($o['job_no']) ?>
          <span class="badge bg-<?= e($color) ?> align-middle"><?= e(Status::label((string)$o['status'])) ?></span>
          <?php if ($o['is_overdue']): ?><span class="badge bg-danger align-middle">Delayed</span><?php endif; ?>
        </div>
        <div class="small text-muted">Ordered <?= e(fmt_date($o['order_date'], true)) ?></div>
      </div>
      <div class="text-end small">
        <?php if ($o['due_date'] && !in_array($o['status'], ['completed', 'cancelled'], true)): ?>
          <div><i class="bi bi-calendar-event"></i> Expected <strong><?= e(fmt_date($o['due_date'])) ?></strong></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- progress -->
    <?php if ($o['status'] !== 'cancelled'): ?>
    <div class="progress mt-2" style="height:8px" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
      <div class="progress-bar bg-<?= e($color) ?>" style="width:<?= $pct ?>%"></div>
    </div>
    <div class="small text-muted mt-1"><?= $pct ?>% — <?= e(Status::label((string)$o['status'])) ?></div>
    <?php else: ?>
    <div class="small text-danger mt-2">Cancelled<?= $o['cancelled_reason'] ? ': ' . e($o['cancelled_reason']) : '' ?></div>
    <?php endif; ?>

    <!-- items -->
    <div class="small mt-2">
      <?php foreach ($o['items'] as $it): ?>
        <div class="d-flex justify-content-between border-top py-1">
          <span><?= e($it['item_name_snapshot']) ?> × <?= e(rtrim(rtrim((string)$it['qty'], '0'), '.')) ?></span>
          <span class="badge bg-<?= e(Status::color((string)$it['status'])) ?> badge-status"><?= e(Status::label((string)$it['status'])) ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
      <span class="small <?= (float)$o['balance_amount'] > 0 ? 'text-danger' : 'text-success' ?>">
        <?php if ((float)$o['balance_amount'] > 0): ?>Balance due: <strong><?= e(fmt_money($o['balance_amount'])) ?></strong><?php else: ?>Fully paid<?php endif; ?>
      </span>
      <a class="btn btn-sm btn-outline-primary rounded-pill" href="<?= e(base_url('track/' . $o['tracking_token'])) ?>">
        View full timeline →
      </a>
    </div>
  </div>
  <?php endforeach; ?>

  <div class="text-center mt-4">
    <a class="btn btn-primary rounded-pill" href="<?= e(base_url('products')) ?>"><i class="bi bi-bag-plus"></i> Place a new order</a>
  </div>
</div>
