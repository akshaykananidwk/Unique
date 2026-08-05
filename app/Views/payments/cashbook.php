<?php $title = 'Cash Book'; $dayTotal = array_sum(array_map(fn($m) => (float)$m['total'], $byMode)); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h4 class="mb-0">Daily Cash Book</h4>
  <form method="get" class="d-flex gap-2">
    <input type="date" name="date" value="<?= e($date) ?>" class="form-control form-control-sm">

    <button class="btn btn-outline-primary btn-sm">Go</button>
    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="bi bi-printer"></i></button>
  </form>
</div>

<div class="row g-2 mb-3">
  <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value"><?= e(fmt_money($opening)) ?></div><div class="stat-label">Cumulative before <?= e(fmt_date($date)) ?></div></div></div>
  <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value text-success"><?= e(fmt_money($dayTotal)) ?></div><div class="stat-label">Collected on <?= e(fmt_date($date)) ?></div></div></div>
  <?php foreach ($byMode as $m): ?>
  <div class="col-6 col-md-2"><div class="stat-card"><div class="stat-value"><?= e(fmt_money($m['total'])) ?></div><div class="stat-label"><?= e(strtoupper((string)$m['mode'])) ?> (<?= (int)$m['cnt'] ?>)</div></div></div>
  <?php endforeach; ?>
</div>

<?php if (!$rows): ?>
  <div class="empty-state"><i class="bi bi-cash-stack"></i>No payments recorded on this day.</div>
<?php else: ?>
<div class="table-responsive"><table class="table table-sm table-mobile">
  <thead><tr><th>Time</th><th>Receipt</th><th>Job</th><th>Customer</th><th>Type / Mode</th><th>By</th><th class="text-end">Amount</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $p): ?>
    <tr>
      <td data-label="Time"><?= e(date('h:i A', strtotime((string)$p['paid_at']))) ?></td>
      <td data-label="Receipt"><?= e($p['receipt_no']) ?></td>
      <td data-label="Job"><a href="<?= e(admin_url('orders/' . $p['order_id'])) ?>"><?= e($p['job_no']) ?></a></td>
      <td data-label="Customer"><?= e($p['customer_name']) ?></td>
      <td data-label="Type"><?= e($p['type']) ?> / <?= e($p['mode']) ?></td>
      <td data-label="By"><?= e($p['received_by'] ?? '—') ?></td>
      <td data-label="Amount" class="text-end fw-semibold"><?= e(fmt_money($p['amount'])) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot><tr class="fw-bold"><td colspan="6">Total</td><td class="text-end"><?= e(fmt_money($dayTotal)) ?></td></tr></tfoot>
</table></div>
<?php endif; ?>
