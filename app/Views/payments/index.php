<?php use App\Core\Acl; $title = 'Payments'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h4 class="mb-0">Payments <span class="text-muted fs-6">(<?= (int)$total ?>)</span>
    <?php if ($customer): ?>
      <small class="text-muted fs-6">— <?= e($customer['name']) ?></small>
    <?php endif; ?>
  </h4>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(admin_url('cashbook')) ?>"><i class="bi bi-cash-stack"></i> Cash Book</a>
</div>

<div class="row g-2 mb-3">
  <div class="col-6 col-lg-3"><div class="stat-card">
    <div class="text-muted small">Received</div>
    <div class="fs-4 fw-bold text-success"><?= e(fmt_money($sums['received'])) ?></div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-card">
    <div class="text-muted small">Discount allowed</div>
    <div class="fs-4 fw-bold text-warning-emphasis"><?= e(fmt_money($sums['discount'])) ?></div></div></div>
  <?php if ($customer): ?>
  <div class="col-6 col-lg-3"><div class="stat-card">
    <div class="text-muted small">Still outstanding</div>
    <div class="fs-4 fw-bold text-danger">
      <?= e(fmt_money($customerOutstanding)) ?></div></div></div>
  <?php endif; ?>
</div>

<form method="get" class="row g-2 mb-3">
  <?php if ($customerId): ?><input type="hidden" name="customer" value="<?= (int)$customerId ?>"><?php endif; ?>
  <div class="col-6 col-md-3"><input name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Receipt / job no / customer"></div>
  <div class="col-6 col-md-2">
    <select name="mode" class="form-select form-select-sm">
      <option value="">Any mode</option>
      <?php foreach (['cash', 'upi', 'card', 'bank', 'cheque', 'credit'] as $m): ?>
        <option value="<?= $m ?>" <?= $mode === $m ? 'selected' : '' ?>><?= ucfirst($m) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-6 col-md-2">
    <select name="type" class="form-select form-select-sm">
      <option value="">Any type</option>
      <?php foreach (['advance', 'part', 'final', 'refund'] as $t): ?>
        <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-6 col-md-2"><input type="date" name="from" value="<?= e($_GET['from'] ?? '') ?>" class="form-control form-control-sm"></div>
  <div class="col-6 col-md-2"><input type="date" name="to" value="<?= e($_GET['to'] ?? '') ?>" class="form-control form-control-sm"></div>
  <div class="col-6 col-md-1"><button class="btn btn-outline-primary btn-sm w-100">Filter</button></div>
</form>

<?php if (!$rows): ?>
  <div class="empty-state"><i class="bi bi-cash-coin"></i>No payments match.</div>
<?php else: ?>
<div class="table-responsive"><table class="table table-sm table-hover align-middle table-mobile">
  <thead><tr>
    <th>Receipt</th><th>Date</th><th>Customer</th><th>Job No</th>
    <th class="text-end">Received</th><th class="text-end">Discount</th>
    <th>Mode</th><th>Type</th><th>By</th><th></th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr class="<?= (float)$r['amount'] < 0 ? 'table-warning' : '' ?>">
      <td data-label="Receipt"><code class="small"><?= e($r['receipt_no']) ?></code></td>
      <td data-label="Date" class="small text-nowrap"><?= e(fmt_date($r['paid_at'], true)) ?></td>
      <td data-label="Customer">
        <a href="<?= e(admin_url('customers/' . $r['cust_id'])) ?>"><?= e($r['customer_name']) ?></a>
        <div class="small text-muted"><?= e($r['customer_phone']) ?></div>
      </td>
      <td data-label="Job No"><a href="<?= e(admin_url('orders/' . $r['order_id'])) ?>"><?= e($r['job_no']) ?></a>
        <?php if ((float)$r['balance_amount'] > 0): ?>
          <div class="small text-danger">still <?= e(fmt_money($r['balance_amount'])) ?></div>
        <?php endif; ?>
      </td>
      <td data-label="Received" class="text-end fw-semibold"><?= e(fmt_money($r['amount'])) ?></td>
      <td data-label="Discount" class="text-end"><?= (float)$r['discount_amount'] > 0 ? e(fmt_money($r['discount_amount'])) : '—' ?></td>
      <td data-label="Mode"><span class="badge bg-secondary"><?= e($r['mode']) ?></span></td>
      <td data-label="Type" class="small"><?= e($r['type']) ?></td>
      <td data-label="By" class="small"><?= e($r['received_by'] ?: '—') ?></td>
      <td data-label="" class="text-nowrap">
        <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
           href="<?= e(admin_url('payments/' . $r['id'] . '/receipt')) ?>" title="Receipt"><i class="bi bi-printer"></i></a>
        <?php if (Acl::can('payment.edit')): ?>
          <a class="btn btn-sm btn-outline-primary" href="<?= e(admin_url('payments/' . $r['id'] . '/edit')) ?>" title="Edit"><i class="bi bi-pencil"></i></a>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>

<?php if ($pages > 1): ?>
<nav><ul class="pagination pagination-sm">
  <?php for ($p = max(1, $page - 4); $p <= min($pages, $page + 4); $p++): $qs = $_GET; $qs['page'] = $p; ?>
    <li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query($qs)) ?>"><?= $p ?></a></li>
  <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php endif; ?>
