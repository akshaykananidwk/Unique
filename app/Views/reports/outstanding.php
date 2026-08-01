<?php use App\Core\Csrf; $title = 'Outstanding Report';
$totalOutstanding = array_sum(array_map(fn($r) => (float)$r['balance_amount'], $rows));
?>
<h4 class="mb-3">Outstanding <small class="text-muted fs-6">total <?= e(fmt_money($totalOutstanding)) ?></small></h4>
<form method="get" class="row g-2 mb-3 no-print">
  <div class="col-6 col-md-2">
    <select name="bucket" class="form-select form-select-sm">
      <option value="">All ages</option>
      <?php foreach (['0-7' => '0–7 days', '8-15' => '8–15 days', '16-30' => '16–30 days', '30+' => '30+ days'] as $v => $l): ?>
        <option value="<?= $v ?>" <?= $bucket === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php if (count($branches) > 1): ?>
  <div class="col-6 col-md-2">
    <select name="branch_id" class="form-select form-select-sm">
      <option value="">All branches</option>
      <?php foreach ($branches as $b): ?>
        <option value="<?= (int)$b['id'] ?>" <?= $selectedBranch === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <div class="col-6 col-md-4 d-flex gap-1">
    <button class="btn btn-outline-primary btn-sm">Apply</button>
    <button class="btn btn-outline-secondary btn-sm" name="export" value="csv"><i class="bi bi-filetype-csv"></i> CSV</button>
    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="bi bi-printer"></i> Print / PDF</button>
  </div>
</form>

<?php if (!$rows): ?><div class="empty-state"><i class="bi bi-cash-coin"></i>Nothing outstanding — well done!</div>
<?php else: ?>
<form method="post" action="<?= e(admin_url('reports/outstanding/remind')) ?>">
  <?= Csrf::field() ?>
  <div class="mb-2 no-print">
    <button class="btn btn-success btn-sm" data-confirm-inline="1"><i class="bi bi-whatsapp"></i> Send reminder to selected</button>
    <label class="ms-2 small"><input type="checkbox" id="checkAll"> Select all</label>
  </div>
  <div class="table-responsive"><table class="table table-sm table-mobile align-middle">
    <thead><tr><th class="no-print"></th><th>Job No</th><th>Customer</th><th>Branch</th><th>Order Date</th>
      <th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Balance</th><th class="text-end">Age</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr class="<?= (int)$r['age_days'] > 30 ? 'row-overdue' : ((int)$r['age_days'] > 15 ? 'row-amber' : '') ?>">
        <td class="no-print" data-label="Select"><input type="checkbox" class="rowcheck" name="order_ids[]" value="<?= (int)$r['id'] ?>"></td>
        <td data-label="Job No"><a href="<?= e(admin_url('orders/' . $r['id'])) ?>"><?= e($r['job_no']) ?></a></td>
        <td data-label="Customer"><?= e($r['customer']) ?> <span class="text-muted small"><?= e($r['phone']) ?></span></td>
        <td data-label="Branch"><?= e($r['branch']) ?></td>
        <td data-label="Date"><?= e(fmt_date($r['order_date'])) ?></td>
        <td data-label="Total" class="text-end"><?= e(fmt_money($r['total'])) ?></td>
        <td data-label="Paid" class="text-end"><?= e(fmt_money($r['paid_amount'])) ?></td>
        <td data-label="Balance" class="text-end fw-bold text-danger"><?= e(fmt_money($r['balance_amount'])) ?></td>
        <td data-label="Age" class="text-end"><?= (int)$r['age_days'] ?>d</td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</form>
<script>document.getElementById('checkAll').addEventListener('change', function () {
  document.querySelectorAll('.rowcheck').forEach(c => c.checked = this.checked);
});</script>
<?php endif; ?>
