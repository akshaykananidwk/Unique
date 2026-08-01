<?php $title = 'Activity Log'; ?>
<h4 class="mb-3">Activity Log <span class="text-muted fs-6">(<?= (int)$total ?>)</span></h4>
<form method="get" class="row g-2 mb-3 no-print">
  <div class="col-6 col-md-2">
    <select name="range" class="form-select form-select-sm">
      <?php foreach (['today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'this_month' => 'This Month',
                      'last_month' => 'Last Month', 'this_year' => 'This Year'] as $v => $l): ?>
        <option value="<?= $v ?>" <?= ($_GET['range'] ?? 'this_month') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-6 col-md-2"><input name="module" value="<?= e($module) ?>" class="form-control form-control-sm" placeholder="Module (order, auth…)"></div>
  <div class="col-6 col-md-2">
    <select name="user_id" class="form-select form-select-sm">
      <option value="">All users</option>
      <?php foreach ($users as $u): ?>
        <option value="<?= (int)$u['id'] ?>" <?= (int)($_GET['user_id'] ?? 0) === (int)$u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-6 col-md-2"><button class="btn btn-outline-primary btn-sm">Filter</button></div>
</form>
<div class="table-responsive"><table class="table table-sm" style="font-size:.82rem">
  <thead><tr><th>When</th><th>User</th><th>Module.Action</th><th>Description</th><th>IP</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td class="text-nowrap"><?= e(fmt_date($r['created_at'], true)) ?></td>
      <td><?= e($r['user_name'] ?? 'System/Public') ?></td>
      <td><code><?= e($r['module']) ?>.<?= e($r['action']) ?></code></td>
      <td><?= e($r['description']) ?></td>
      <td class="text-muted"><?= e($r['ip']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php $pages = (int)ceil($total / $perPage); if ($pages > 1): ?>
<nav><ul class="pagination pagination-sm">
  <?php for ($p = max(1, $page - 5); $p <= min($pages, $page + 5); $p++): $qs = $_GET; $qs['page'] = $p; ?>
    <li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query($qs)) ?>"><?= $p ?></a></li>
  <?php endfor; ?>
</ul></nav>
<?php endif; ?>
