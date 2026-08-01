<?php use App\Models\Status; $title = 'Dashboard'; ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <h4 class="mb-0">Dashboard</h4>
  <?php if (count($branches) > 1): ?>
  <form method="get" class="d-flex gap-2">
    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
      <option value="">All Branches</option>
      <?php foreach ($branches as $b): ?>
        <option value="<?= (int)$b['id'] ?>" <?= $selectedBranch === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
  <?php endif; ?>
</div>

<div class="row g-2 mb-4">
  <?php
  $cardDefs = [
    ['Today\'s Orders', (string)$cards['orders_today'], '', ''],
    ['Today\'s Value', fmt_money($cards['value_today']), '', ''],
    ['Today\'s Collection', fmt_money($cards['collection_today']), '', ''],
    ['Pending Balance', fmt_money($cards['pending_balance']), '', ''],
    ['Jobs In Progress', (string)$cards['in_progress'], admin_url('orders'), ''],
    ['Overdue', (string)$cards['overdue'], admin_url('orders?status=overdue'), $cards['overdue'] > 0 ? 'stat-danger' : ''],
    ['Awaiting Approval', (string)$cards['awaiting_approval'], admin_url('orders?status=proof_sent'), ''],
    ['Ready for Delivery', (string)$cards['ready_for_delivery'], admin_url('orders?status=ready_for_delivery'), ''],
  ];
  foreach ($cardDefs as [$label, $value, $link, $cls]): ?>
  <div class="col-6 col-md-3">
    <?php if ($link): ?><a href="<?= e($link) ?>" class="text-decoration-none text-reset"><?php endif; ?>
    <div class="stat-card <?= e($cls) ?>">
      <div class="stat-value"><?= e($value) ?></div>
      <div class="stat-label"><?= e($label) ?></div>
    </div>
    <?php if ($link): ?></a><?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-7">
    <div class="card h-100"><div class="card-body">
      <h6>Revenue — last 30 days</h6>
      <canvas id="trendChart" height="120"></canvas>
    </div></div>
  </div>
  <div class="col-lg-5">
    <div class="card h-100"><div class="card-body">
      <h6>Orders by stage</h6>
      <canvas id="stageChart" height="150"></canvas>
    </div></div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-6">
    <div class="card h-100"><div class="card-body">
      <h6>Needs attention</h6>
      <?php $any = false; foreach ($attention as $group => $rows) { if ($rows) { $any = true; } } ?>
      <?php if (!$any): ?><div class="empty-state"><i class="bi bi-check2-circle"></i>All clear — nothing needs attention.</div><?php endif; ?>
      <?php $labels = ['overdue' => ['Overdue jobs', 'danger'], 'unviewed_proofs' => ['Proofs unviewed > 24h', 'warning'],
                       'stale_changes' => ['Changes pending > 48h', 'warning'], 'needs_review' => ['Public orders to confirm', 'info']]; ?>
      <?php foreach ($attention as $group => $rows): if (!$rows) continue; [$glabel, $gcolor] = $labels[$group]; ?>
        <div class="mb-2"><span class="badge bg-<?= e($gcolor) ?>"><?= e($glabel) ?></span></div>
        <ul class="list-unstyled small mb-3">
          <?php foreach ($rows as $r): ?>
          <li class="mb-1"><a href="<?= e(admin_url('orders/' . $r['id'])) ?>" class="<?= $group === 'overdue' ? 'text-overdue' : '' ?>">
            <?= e($r['job_no']) ?></a> — <?= e($r['customer']) ?>
            <span class="text-muted"><?= e(fmt_date($r['due_date'] ?? $r['sent_at'] ?? $r['updated_at'] ?? $r['created_at'] ?? null, true)) ?></span></li>
          <?php endforeach; ?>
        </ul>
      <?php endforeach; ?>
    </div></div>
  </div>
  <div class="col-lg-6">
    <div class="card mb-3"><div class="card-body">
      <h6>Designer workload</h6>
      <?php if (!$designerLoad): ?><div class="empty-state"><i class="bi bi-palette"></i>No designers yet. Add one under Users.</div><?php endif; ?>
      <?php foreach ($designerLoad as $d): ?>
        <div class="d-flex justify-content-between border-bottom py-1 small">
          <span><?= e($d['name']) ?></span><strong><?= (int)$d['open_jobs'] ?> open</strong>
        </div>
      <?php endforeach; ?>
    </div></div>
    <div class="card"><div class="card-body">
      <h6>Top items this month</h6>
      <?php if (!$topItems): ?><div class="empty-state"><i class="bi bi-box"></i>No sales yet this month.</div><?php endif; ?>
      <?php foreach ($topItems as $t): ?>
        <div class="d-flex justify-content-between border-bottom py-1 small">
          <span><?= e($t['name']) ?> <span class="text-muted">× <?= (int)$t['c'] ?></span></span>
          <strong><?= e(fmt_money($t['v'])) ?></strong>
        </div>
      <?php endforeach; ?>
    </div></div>
  </div>
</div>

<?php if ($branchComparison): ?>
<div class="card mb-4"><div class="card-body">
  <h6>Branch comparison — this month</h6>
  <canvas id="branchChart" height="90"></canvas>
</div></div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const trend = <?= json_encode(array_map(fn($r) => ['d' => $r['d'], 'v' => (float)$r['v']], $trend)) ?>;
  new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: { labels: trend.map(r => r.d.slice(5)), datasets: [{ label: 'Revenue', data: trend.map(r => r.v),
      borderColor: getComputedStyle(document.documentElement).getPropertyValue('--kp-brand') || '#0d6efd', tension: .3, fill: false }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
  });
  const stages = <?= json_encode(array_map(fn($r) => ['s' => Status::label((string)$r['status']), 'c' => (int)$r['c']], $byStage)) ?>;
  new Chart(document.getElementById('stageChart'), {
    type: 'bar',
    data: { labels: stages.map(r => r.s), datasets: [{ data: stages.map(r => r.c), backgroundColor: '#6ea8fe' }] },
    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { ticks: { precision: 0 } } } }
  });
  <?php if ($branchComparison): ?>
  const branches = <?= json_encode(array_map(fn($r) => ['n' => $r['name'], 'v' => (float)$r['value']], $branchComparison)) ?>;
  new Chart(document.getElementById('branchChart'), {
    type: 'bar',
    data: { labels: branches.map(r => r.n), datasets: [{ label: 'Value', data: branches.map(r => r.v), backgroundColor: '#75b798' }] },
    options: { plugins: { legend: { display: false } } }
  });
  <?php endif; ?>
});
</script>
