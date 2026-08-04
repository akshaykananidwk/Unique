<?php $title = 'User Performance'; $branches = []; $selectedBranch = null; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
  <h4 class="mb-0">User Performance <small class="text-muted fs-6"><?= e(fmt_date($from)) ?> — <?= e(fmt_date($to)) ?></small></h4>
  <a class="btn btn-outline-secondary btn-sm no-print" href="?<?= e(http_build_query(array_merge($_GET, ['export' => 'csv']))) ?>"><i class="bi bi-download"></i> CSV</a>
</div>
<?php include __DIR__ . '/_filters.php'; ?>

<?php if (!$rows): ?>
  <div class="empty-state"><i class="bi bi-people"></i>No activity in this period.</div>
<?php else: ?>

<div class="row g-2 mb-3">
  <div class="col-6 col-lg-3"><div class="stat-card"><div class="text-muted small">Orders Taken</div>
    <div class="fs-4 fw-bold"><?= (int)$totals['orders_taken'] ?></div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-card"><div class="text-muted small">Order Value</div>
    <div class="fs-4 fw-bold"><?= e(fmt_money($totals['order_value'])) ?></div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-card"><div class="text-muted small">Collected</div>
    <div class="fs-4 fw-bold text-success"><?= e(fmt_money($totals['collected_total'])) ?></div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-card"><div class="text-muted small">Pending</div>
    <div class="fs-4 fw-bold text-danger"><?= e(fmt_money($totals['pending_amount'])) ?></div></div></div>
</div>

<div class="table-responsive"><table class="table table-sm table-hover align-middle table-mobile">
  <thead><tr>
    <th>User</th><th>Role</th>
    <th class="text-end">Orders Taken</th><th class="text-end">Order Value</th>
    <th class="text-end">Accepted</th>
    <th class="text-end">Advance</th><th class="text-end">Recovered</th>
    <th class="text-end">Total Collected</th><th class="text-end">Pending</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td data-label="User" class="fw-semibold"><?= e($r['name']) ?></td>
      <td data-label="Role"><span class="badge bg-secondary badge-status"><?= e($r['role_name']) ?></span></td>
      <td data-label="Orders Taken" class="text-end"><?= (int)$r['orders_taken'] ?></td>
      <td data-label="Order Value" class="text-end fw-semibold"><?= e(fmt_money($r['order_value'])) ?></td>
      <td data-label="Accepted" class="text-end"><?= (int)$r['orders_accepted'] ?></td>
      <td data-label="Advance" class="text-end"><?= e(fmt_money($r['advance_taken'])) ?></td>
      <td data-label="Recovered" class="text-end"><?= e(fmt_money($r['recovered'])) ?></td>
      <td data-label="Total Collected" class="text-end text-success fw-semibold"><?= e(fmt_money($r['collected_total'])) ?></td>
      <td data-label="Pending" class="text-end <?= (float)$r['pending_amount'] > 0 ? 'text-danger fw-semibold' : 'text-muted' ?>"><?= e(fmt_money($r['pending_amount'])) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot class="table-light fw-bold"><tr>
    <td colspan="2">Total</td>
    <td class="text-end"><?= (int)$totals['orders_taken'] ?></td>
    <td class="text-end"><?= e(fmt_money($totals['order_value'])) ?></td>
    <td></td>
    <td class="text-end"><?= e(fmt_money($totals['advance_taken'])) ?></td>
    <td class="text-end"><?= e(fmt_money($totals['recovered'])) ?></td>
    <td class="text-end text-success"><?= e(fmt_money($totals['collected_total'])) ?></td>
    <td class="text-end text-danger"><?= e(fmt_money($totals['pending_amount'])) ?></td>
  </tr></tfoot>
</table></div>
<p class="text-muted small">Advance and Recovered are money this person actually took in the period. Pending is what is still owed
  on every order they took, whenever it was taken.</p>
<?php endif; ?>
