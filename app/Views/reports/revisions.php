<?php use App\Core\View; $title = 'Design Revision Analysis'; ?>
<h4 class="mb-3">Design Revision Analysis <small class="text-muted fs-6"><?= e(fmt_date($from)) ?> – <?= e(fmt_date($to)) ?></small></h4>
<?= View::partial('reports/_filters', compact('branches', 'selectedBranch')) ?>
<?php if (!$rows): ?><div class="empty-state"><i class="bi bi-arrow-repeat"></i>No revisions recorded in this period — excellent!</div>
<?php else: ?>
<div class="table-responsive"><table class="table table-sm table-bordered" style="max-width:720px">
  <thead class="table-light"><tr><th>Item</th><th class="text-end">Design Jobs</th><th class="text-end">Total Revisions</th>
    <th class="text-end">Avg / Job</th><th class="text-end">Worst</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr class="<?= (float)$r['avg_revisions'] >= 2 ? 'row-amber' : '' ?>">
      <td><?= e($r['item']) ?></td>
      <td class="text-end"><?= (int)$r['jobs'] ?></td>
      <td class="text-end"><?= (int)$r['revisions'] ?></td>
      <td class="text-end"><?= e(number_format((float)$r['avg_revisions'], 2)) ?></td>
      <td class="text-end"><?= (int)$r['max_revisions'] ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<p class="small text-muted">High averages usually mean the order form is missing a field customers care about — add it under Items → Options.</p>
<?php endif; ?>
