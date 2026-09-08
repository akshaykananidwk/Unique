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
    <th class="text-end">Cash In Hand</th>
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
      <?php // Shop money still in this person's pocket — a live figure, not a period one. ?>
      <td data-label="Cash In Hand" class="text-end <?= (float)$r['cash_in_hand'] > 0 ? 'text-success fw-semibold' : 'text-muted' ?>">
        <a class="text-decoration-none" href="<?= e(admin_url('cash') . '?user=' . (int)$r['id']) ?>"><?= e(fmt_money($r['cash_in_hand'])) ?></a></td>
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
    <td class="text-end text-success"><?= e(fmt_money($totals['cash_in_hand'])) ?></td>
  </tr></tfoot>
</table></div>
<p class="text-muted small">Advance and Recovered are money this person actually took in the period. Pending is what is still owed
  on every order they took, whenever it was taken.</p>
<?php endif; ?>

<?php // Month by month — the report the shop actually reads at the end of a month.
if (!empty($monthly)):
  $byMonth = [];
  foreach ($monthly as $m) { $byMonth[$m['ym']][] = $m; }
?>
<div class="card mt-3"><div class="card-body">
  <h6>Orders taken, month by month</h6>
  <?php foreach ($byMonth as $ym => $mrows): ?>
    <div class="fw-semibold mt-2"><?= e(date('F Y', strtotime($ym . '-01'))) ?></div>
    <div class="table-responsive"><table class="table table-sm align-middle table-mobile mb-1">
      <thead><tr><th>User</th><th class="text-end">Orders Taken</th><th class="text-end">Order Value</th></tr></thead>
      <tbody>
      <?php foreach ($mrows as $m): ?>
        <tr><td data-label="User"><?= e($m['name']) ?></td><td data-label="Orders" class="text-end"><?= (int)$m['orders_taken'] ?></td><td data-label="Value" class="text-end"><?= e(fmt_money($m['order_value'])) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endforeach; ?>
</div></div>
<?php else: ?>
<div class="card mt-3"><div class="card-body text-muted small">No orders were taken in this period.</div></div>
<?php endif; ?>
