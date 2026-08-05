<?php $title = 'Designer Performance'; $branches = []; $selectedBranch = null; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
  <h4 class="mb-0">Designer Performance <small class="text-muted fs-6"><?= e(fmt_date($from)) ?> — <?= e(fmt_date($to)) ?></small></h4>
  <a class="btn btn-outline-secondary btn-sm no-print" href="?<?= e(http_build_query(array_merge($_GET, ['export' => 'csv']))) ?>"><i class="bi bi-download"></i> CSV</a>
</div>
<?php include __DIR__ . '/_filters.php'; ?>

<?php if (!$rows): ?>
  <div class="empty-state"><i class="bi bi-palette2"></i>No designers, or no design work in this period.</div>
<?php else: ?>

<div class="row g-2 mb-3">
  <div class="col-6 col-lg-3"><div class="stat-card"><div class="text-muted small">Designs Made</div>
    <div class="fs-4 fw-bold"><?= (int)$totals['designs_made'] ?></div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-card"><div class="text-muted small">Jobs Handled</div>
    <div class="fs-4 fw-bold"><?= (int)$totals['jobs_handled'] ?></div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-card"><div class="text-muted small">Orders Handled</div>
    <div class="fs-4 fw-bold"><?= (int)$totals['orders_handled'] ?></div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-card"><div class="text-muted small">Value Handled</div>
    <div class="fs-4 fw-bold"><?= e(fmt_money($totals['value_handled'])) ?></div></div></div>
</div>

<div class="table-responsive"><table class="table table-sm table-hover align-middle table-mobile">
  <thead><tr>
    <th>Designer</th>
    <th class="text-end">Designs Made</th><th class="text-end">Approved</th>
    <th class="text-end">Jobs Handled</th><th class="text-end">Orders Handled</th>
    <th class="text-end">Value Handled</th><th class="text-end">Open Now</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td data-label="Designer" class="fw-semibold"><?= e($r['name']) ?></td>
      <td data-label="Designs Made" class="text-end"><?= (int)$r['designs_made'] ?></td>
      <td data-label="Approved" class="text-end text-success"><?= (int)$r['designs_approved'] ?></td>
      <td data-label="Jobs Handled" class="text-end"><?= (int)$r['jobs_handled'] ?></td>
      <td data-label="Orders Handled" class="text-end"><?= (int)$r['orders_handled'] ?></td>
      <td data-label="Value Handled" class="text-end fw-semibold"><?= e(fmt_money($r['value_handled'])) ?></td>
      <td data-label="Open Now" class="text-end <?= (int)$r['open_now'] > 0 ? 'fw-semibold' : 'text-muted' ?>"><?= (int)$r['open_now'] ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot class="table-light fw-bold"><tr>
    <td>Total</td>
    <td class="text-end"><?= (int)$totals['designs_made'] ?></td><td></td>
    <td class="text-end"><?= (int)$totals['jobs_handled'] ?></td>
    <td class="text-end"><?= (int)$totals['orders_handled'] ?></td>
    <td class="text-end"><?= e(fmt_money($totals['value_handled'])) ?></td><td></td>
  </tr></tfoot>
</table></div>
<p class="text-muted small">Designs Made counts every proof version uploaded. Value Handled is the total of the job lines assigned
  to that designer. Open Now is what is on their desk right now, regardless of the date range.</p>
<?php endif; ?>

<?php // Month by month — the report the shop actually reads at the end of a month.
if (!empty($monthly)):
  $byMonth = [];
  foreach ($monthly as $m) { $byMonth[$m['ym']][] = $m; }
?>
<div class="card mt-3"><div class="card-body">
  <h6>Designs accepted, month by month</h6>
  <?php foreach ($byMonth as $ym => $mrows): ?>
    <div class="fw-semibold mt-2"><?= e(date('F Y', strtotime($ym . '-01'))) ?></div>
    <div class="table-responsive"><table class="table table-sm align-middle table-mobile mb-1">
      <thead><tr><th>Designer</th><th class="text-end">Jobs Accepted</th><th class="text-end">Proofs Uploaded</th><th class="text-end">Value</th></tr></thead>
      <tbody>
      <?php foreach ($mrows as $m): ?>
        <tr><td data-label="Designer"><?= e($m['name']) ?></td><td data-label="Accepted" class="text-end"><?= (int)$m['jobs_accepted'] ?></td><td data-label="Proofs" class="text-end"><?= (int)$m['proofs_uploaded'] ?></td><td data-label="Value" class="text-end"><?= e(fmt_money($m['value_handled'])) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endforeach; ?>
</div></div>
<?php else: ?>
<div class="card mt-3"><div class="card-body text-muted small">No design jobs were accepted in this period.</div></div>
<?php endif; ?>
