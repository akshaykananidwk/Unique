<?php use App\Core\View; $title = 'GST Summary'; ?>
<h4 class="mb-3">GST Summary <small class="text-muted fs-6"><?= e(fmt_date($from)) ?> – <?= e(fmt_date($to)) ?></small></h4>
<?= View::partial('reports/_filters', compact('branches', 'selectedBranch')) ?>
<?php if (!$rows): ?><div class="empty-state"><i class="bi bi-percent"></i>No taxable sales in this period.</div>
<?php else: $tt = 0; $tx = 0; ?>
<table class="table table-sm table-bordered" style="max-width:640px">
  <thead class="table-light"><tr><th>Tax Rate</th><th class="text-end">Lines</th><th class="text-end">Taxable Value</th><th class="text-end">Tax</th><th class="text-end">Total</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): $tt += (float)$r['taxable']; $tx += (float)$r['tax']; ?>
    <tr>
      <td><?= e($r['tax_percent']) ?>%</td>
      <td class="text-end"><?= (int)$r['lines'] ?></td>
      <td class="text-end"><?= e(number_format((float)$r['taxable'], 2)) ?></td>
      <td class="text-end"><?= e(number_format((float)$r['tax'], 2)) ?></td>
      <td class="text-end"><?= e(number_format((float)$r['total'], 2)) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot><tr class="fw-bold"><td colspan="2">Total</td><td class="text-end"><?= e(number_format($tt, 2)) ?></td>
    <td class="text-end"><?= e(number_format($tx, 2)) ?></td><td class="text-end"><?= e(number_format($tt + $tx, 2)) ?></td></tr></tfoot>
</table>
<?php endif; ?>
