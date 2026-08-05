<?php use App\Core\Acl; use App\Core\Csrf; use App\Models\Status;
$title = 'Orders';
$backUrl = admin_url('orders') . ($_GET ? '?' . http_build_query($_GET) : '');
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h4 class="mb-0">Orders <span class="text-muted fs-6">(<?= (int)$total ?>)</span></h4>
  <a href="<?= e(admin_url('orders/create')) ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New Order</a>
</div>

<form method="get" class="row g-2 mb-3">
  <div class="col-6 col-md-3"><input name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Job no / customer / phone"></div>
  <div class="col-6 col-md-2">
    <select name="status" class="form-select form-select-sm">
      <option value="">All statuses</option>
      <option value="overdue" <?= $status === 'overdue' ? 'selected' : '' ?>>⚠ Overdue</option>
      <option value="needs_review" <?= $status === 'needs_review' ? 'selected' : '' ?>>🆕 Needs review</option>
      <?php foreach (array_merge(array_keys(Status::RANKS), Status::SPECIAL) as $s): ?>
        <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(Status::label($s)) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-6 col-md-2"><input type="date" name="from" value="<?= e($_GET['from'] ?? '') ?>" class="form-control form-control-sm"></div>
  <div class="col-6 col-md-2"><input type="date" name="to" value="<?= e($_GET['to'] ?? '') ?>" class="form-control form-control-sm"></div>
  <div class="col-6 col-md-1"><button class="btn btn-outline-primary btn-sm w-100">Filter</button></div>
</form>

<?php if (!$orders): ?>
  <div class="empty-state"><i class="bi bi-receipt"></i>No orders match. <a href="<?= e(admin_url('orders/create')) ?>">Create the first one</a>.</div>
<?php else: ?>
<div class="table-responsive">
<table class="table table-sm table-hover align-middle table-mobile">
  <thead><tr><th>Job No</th><th>Customer</th><th>Date / Due</th><th>Status</th><th>Total</th><th>Balance</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($orders as $o):
      $overdue = Status::isOverdue($o['due_date'], (string)$o['status']); ?>
    <tr class="<?= $overdue ? 'row-overdue' : e(priority_class($o['priority'])) ?>">
      <td data-label="Job No">
        <a href="<?= e(admin_url('orders/' . $o['id'])) ?>" class="fw-semibold"><?= e($o['job_no']) ?></a>
        <?php if ((int)$o['needs_review']): ?><span class="badge bg-info badge-status">New — needs review</span><?php endif; ?>
        <?= priority_badge($o['priority']) ?>
      </td>
      <td data-label="Customer"><?= e($o['customer_name']) ?><div class="small text-muted"><?= e($o['customer_phone']) ?></div></td>
      <td data-label="Date / Due"><span class="small"><?= e(fmt_date($o['order_date'])) ?></span>
        <div class="small <?= $overdue ? 'text-overdue fw-bold' : 'text-muted' ?>">Due <?= e(fmt_date($o['due_date'], true)) ?></div></td>
      <td data-label="Status">
        <?php if (Acl::can('order.change_status')): ?>
          <form method="post" action="<?= e(admin_url('orders/' . $o['id'] . '/status')) ?>" class="d-inline">
            <?= Csrf::field() ?>
            <input type="hidden" name="back" value="<?= e($backUrl) ?>">
            <select name="status" data-auto-submit
                    class="form-select form-select-sm kp-status-pick text-bg-<?= e(Status::color((string)$o['status'])) ?>"
                    title="Change status — applies to every job in this order">
              <?php foreach (array_keys(Status::RANKS) as $s): ?>
                <option value="<?= e($s) ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= e(Status::label($s)) ?></option>
              <?php endforeach; ?>
              <?php if (!isset(Status::RANKS[$o['status']])): ?>
                <option value="<?= e($o['status']) ?>" selected><?= e(Status::label((string)$o['status'])) ?></option>
              <?php endif; ?>
            </select>
          </form>
        <?php else: ?>
          <span class="badge bg-<?= e(Status::color((string)$o['status'])) ?>"><?= e(Status::label((string)$o['status'])) ?></span>
        <?php endif; ?>
      </td>
      <td data-label="Total"><?= e(fmt_money($o['total'])) ?></td>
      <td data-label="Balance" class="<?= (float)$o['balance_amount'] > 0 ? 'text-danger fw-semibold' : 'text-success' ?>"><?= e(fmt_money($o['balance_amount'])) ?></td>
      <td data-label="Actions">
        <div class="btn-group btn-group-sm">
          <a class="btn btn-outline-secondary" title="Open" href="<?= e(admin_url('orders/' . $o['id'])) ?>"><i class="bi bi-eye"></i></a>
          <a class="btn btn-outline-secondary" title="Job card" target="_blank" href="<?= e(admin_url('orders/' . $o['id'] . '/job-card')) ?>"><i class="bi bi-printer"></i></a>
          <a class="btn btn-outline-success" title="WhatsApp customer" target="_blank" href="https://wa.me/<?= e(normalize_phone($o['customer_phone']) ?? '') ?>"><i class="bi bi-whatsapp"></i></a>
          <?php if ($user['role_slug'] === 'super_admin'): ?>
          <form method="post" class="d-inline" action="<?= e(admin_url('orders/' . $o['id'] . '/delete')) ?>" data-confirm="Delete order <?= e($o['job_no']) ?>? It will be removed from all lists.">
            <?= Csrf::field() ?><button class="btn btn-outline-danger" title="Delete order"><i class="bi bi-trash"></i></button>
          </form>
          <?php endif; ?>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php $pages = (int)ceil($total / $perPage); if ($pages > 1): ?>
<nav><ul class="pagination pagination-sm">
  <?php for ($p = 1; $p <= $pages; $p++): $qs = $_GET; $qs['page'] = $p; ?>
    <li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query($qs)) ?>"><?= $p ?></a></li>
  <?php endfor; ?>
</ul></nav>
<?php endif; endif; ?>
