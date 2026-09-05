<?php use App\Models\Status; $title = 'Work Done'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
  <h4 class="mb-0">Work Done <small class="text-muted fs-6"><?= e(fmt_date($from)) ?> — <?= e(fmt_date($to)) ?></small></h4>
  <a class="btn btn-success btn-sm no-print"
     href="?<?= e(http_build_query(array_merge($_GET, ['export' => 'csv']))) ?>">
    <i class="bi bi-file-earmark-excel"></i> Excel / CSV
  </a>
</div>
<p class="text-muted small">
  One row per job: who took the order, who made it and who took the money. Export it and the
  file opens straight in Excel.
</p>
<?php include __DIR__ . '/_filters.php'; ?>

<?php if (!$rows): ?>
  <div class="empty-state"><i class="bi bi-clipboard-check"></i>No work in this period.</div>
<?php else: ?>

<!-- Totals per person first: the question is usually "how much did each of them do". -->
<div class="card mb-3"><div class="card-body">
  <h6>Per person</h6>
  <div class="table-responsive"><table class="table table-sm align-middle table-mobile mb-0">
    <thead><tr>
      <th>Person</th>
      <th class="text-end">Orders taken</th><th class="text-end">Value</th>
      <th class="text-end">Jobs prepared</th><th class="text-end">Value</th>
    </tr></thead>
    <tbody>
    <?php foreach ($byPerson as $who => $n): ?>
      <tr>
        <td data-label="Person"><?= e($who) ?></td>
        <td data-label="Orders taken" class="text-end"><?= (int)($n['order_by'] ?? 0) ?></td>
        <td data-label="Value" class="text-end"><?= e(fmt_money($n['order_by_value'] ?? 0)) ?></td>
        <td data-label="Jobs prepared" class="text-end"><?= (int)($n['prepared_by'] ?? 0) ?></td>
        <td data-label="Value" class="text-end"><?= e(fmt_money($n['prepared_by_value'] ?? 0)) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div></div>

<div class="table-responsive"><table class="table table-sm table-hover align-middle table-mobile">
  <thead><tr>
    <th>Job No</th><th>Date</th><th>Customer</th><th>Contact</th><th>Item</th>
    <th class="text-end">Qty</th><th class="text-end">Amount</th>
    <th>Order By</th><th>Prepared By</th><th>Prepaid By</th><th>Status</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td data-label="Job No"><?= e($r['job_no']) ?></td>
      <td data-label="Date" class="small"><?= e(fmt_date($r['order_date'])) ?></td>
      <td data-label="Customer"><?= e($r['customer']) ?></td>
      <td data-label="Contact" class="small">
        <?= e($r['contact_person'] ?: '—') ?>
        <?php if ($r['contact_phone']): ?><div class="text-muted"><?= e($r['contact_phone']) ?></div><?php endif; ?>
      </td>
      <td data-label="Item" class="small"><?= e($r['item']) ?>
        <?php if ($r['category']): ?><div class="text-muted"><?= e($r['category']) ?></div><?php endif; ?></td>
      <td data-label="Qty" class="text-end"><?= e(rtrim(rtrim((string)$r['qty'], '0'), '.')) ?></td>
      <td data-label="Amount" class="text-end"><?= e(fmt_money($r['line_total'])) ?></td>
      <td data-label="Order By" class="small"><?= e($r['order_by'] ?: '—') ?></td>
      <td data-label="Prepared By" class="small"><?= e($r['prepared_by'] ?: '—') ?></td>
      <td data-label="Prepaid By" class="small"><?= e($r['prepaid_by'] ?: '—') ?></td>
      <td data-label="Status"><span class="badge bg-<?= e(Status::color((string)$r['item_status'])) ?>"><?= e(Status::label((string)$r['item_status'])) ?></span></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
