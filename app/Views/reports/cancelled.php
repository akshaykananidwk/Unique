<?php use App\Core\View; $title = 'Cancelled Orders'; ?>
<h4 class="mb-3">Cancelled Orders <small class="text-muted fs-6"><?= e(fmt_date($from)) ?> – <?= e(fmt_date($to)) ?></small></h4>
<?= View::partial('reports/_filters', compact('branches', 'selectedBranch')) ?>
<?php if (!$rows): ?><div class="empty-state"><i class="bi bi-x-circle"></i>No cancellations in this period.</div>
<?php else: ?>
<div class="table-responsive"><table class="table table-sm table-mobile">
  <thead><tr><th>Job No</th><th>Customer</th><th>Branch</th><th>Ordered</th><th>Cancelled</th><th>By</th><th>Reason</th><th class="text-end">Value</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td data-label="Job No"><?= e($r['job_no']) ?></td>
      <td data-label="Customer"><?= e($r['customer']) ?></td>
      <td data-label="Branch"><?= e($r['branch']) ?></td>
      <td data-label="Ordered"><?= e(fmt_date($r['order_date'])) ?></td>
      <td data-label="Cancelled"><?= e(fmt_date($r['cancelled_at'], true)) ?></td>
      <td data-label="By"><?= e($r['cancelled_by_name'] ?? '—') ?></td>
      <td data-label="Reason"><?= e($r['cancelled_reason']) ?></td>
      <td data-label="Value" class="text-end"><?= e(fmt_money($r['total'])) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
