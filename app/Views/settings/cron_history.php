<?php
$title = 'Cron History';
$runColors = ['success' => 'success', 'failed' => 'danger', 'running' => 'info', 'skipped' => 'secondary'];
$qs = static function (array $extra) use ($filter, $status): string {
    return http_build_query(array_merge(['job' => $filter, 'status' => $status], $extra));
};
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h4 class="mb-0">Cron History <small class="text-muted fs-6">(<?= number_format($total) ?>)</small></h4>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(admin_url('system/cron')) ?>"><i class="bi bi-arrow-left"></i> Back to Cron Jobs</a>
</div>

<form method="get" class="d-flex flex-wrap gap-2 mb-3">
  <select name="job" class="form-select form-select-sm" style="max-width:260px">
    <option value="">All jobs</option>
    <?php foreach ($jobs as $j): ?>
      <option value="<?= e($j['job_key']) ?>" <?= $filter === $j['job_key'] ? 'selected' : '' ?>><?= e($j['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="status" class="form-select form-select-sm" style="max-width:150px">
    <option value="">All statuses</option>
    <?php foreach (array_keys($runColors) as $s): ?>
      <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-outline-primary btn-sm">Filter</button>
</form>

<?php if (!$runs): ?>
  <div class="empty-state"><i class="bi bi-clock-history"></i>Nothing matches.</div>
<?php else: ?>
<div class="table-responsive"><table class="table table-sm table-mobile align-middle">
  <thead><tr><th>#</th><th>Started</th><th>Finished</th><th>Job</th><th>Status</th><th>Took</th><th>Items</th><th>By</th><th>Message</th></tr></thead>
  <tbody>
  <?php foreach ($runs as $r): ?>
    <tr class="<?= $r['status'] === 'failed' ? 'table-danger' : '' ?>">
      <td data-label="#" class="small"><?= (int)$r['id'] ?></td>
      <td data-label="Started" class="small text-nowrap"><?= e(fmt_date($r['started_at'], true)) ?></td>
      <td data-label="Finished" class="small text-nowrap"><?= e($r['finished_at'] ? fmt_date($r['finished_at'], true) : '—') ?></td>
      <td data-label="Job" class="small"><code><?= e($r['job_key']) ?></code></td>
      <td data-label="Status"><span class="badge bg-<?= e($runColors[$r['status']] ?? 'secondary') ?>"><?= e($r['status']) ?></span></td>
      <td data-label="Took" class="small"><?= $r['duration_ms'] !== null ? (int)$r['duration_ms'] . ' ms' : '—' ?></td>
      <td data-label="Items" class="small"><?= (int)$r['items'] ?></td>
      <td data-label="By" class="small"><?= e($r['triggered_by']) ?></td>
      <td data-label="Message" class="small" style="max-width:460px"><?= e((string)$r['message']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>

<?php if ($pages > 1): ?>
<nav><ul class="pagination pagination-sm">
  <?php for ($p = max(1, $page - 4); $p <= min($pages, $page + 4); $p++): ?>
    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
      <a class="page-link" href="<?= e(admin_url('system/cron/history?' . $qs(['page' => $p]))) ?>"><?= $p ?></a>
    </li>
  <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php endif; ?>
