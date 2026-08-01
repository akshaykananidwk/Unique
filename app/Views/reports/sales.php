<?php use App\Core\View; $title = 'Sales Register';
$extraFilters = '<div class="col-6 col-md-2"><select name="group" class="form-select form-select-sm">'
    . '<option value="order"' . ($group === 'order' ? ' selected' : '') . '>Order-wise</option>'
    . '<option value="item"' . ($group === 'item' ? ' selected' : '') . '>Item-wise</option>'
    . '<option value="category"' . ($group === 'category' ? ' selected' : '') . '>Category-wise</option>'
    . '</select></div>';
?>
<h4 class="mb-3">Sales Register <small class="text-muted fs-6"><?= e(fmt_date($from)) ?> – <?= e(fmt_date($to)) ?></small></h4>
<?= View::partial('reports/_filters', compact('branches', 'selectedBranch', 'extraFilters')) ?>
<?php if (!$rows): ?><div class="empty-state"><i class="bi bi-graph-up"></i>No sales in this period.</div>
<?php else: ?>
<div class="table-responsive"><table class="table table-sm table-bordered" style="font-size:.85rem">
  <thead class="table-light"><tr><?php foreach ($headers as $h): ?><th><?= e($h) ?></th><?php endforeach; ?></tr></thead>
  <tbody>
  <?php $sums = []; foreach ($rows as $r): ?>
    <tr><?php $i = 0; foreach ($r as $k => $v): ?>
      <td class="<?= is_numeric($v) && $i > 1 ? 'text-end' : '' ?>"><?= is_numeric($v) && !in_array($k, ['job_no'], true) && $i > 1 ? e(number_format((float)$v, 2)) : e($v) ?></td>
    <?php if (is_numeric($v)) { $sums[$k] = ($sums[$k] ?? 0) + (float)$v; } $i++; endforeach; ?></tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<p class="small text-muted"><?= count($rows) ?> row(s).</p>
<?php endif; ?>
