<?php use App\Core\View; $title = 'Staff Performance'; ?>
<h4 class="mb-3">Staff Performance <small class="text-muted fs-6"><?= e(fmt_date($from)) ?> – <?= e(fmt_date($to)) ?></small></h4>
<?= View::partial('reports/_filters', compact('branches', 'selectedBranch')) ?>

<?php if (!$rows): ?><div class="empty-state"><i class="bi bi-people"></i>No activity in this period.</div>
<?php else: ?>
<div class="table-responsive">
<table class="table table-sm table-bordered align-middle" style="font-size:.82rem">
  <thead class="table-light"><tr>
    <th>Staff</th><th>Role</th><th class="text-end">Orders</th><th class="text-end">Order Value</th>
    <th class="text-end">Advance</th><th class="text-end">Collections</th><th class="text-end">Designs Done</th>
    <th class="text-end">Avg TAT (h)</th><th class="text-end">Avg Rev.</th><th class="text-end">1st-time %</th>
    <th class="text-end">On-time %</th><th class="text-end text-danger">Pending</th><th class="text-end text-danger">Overdue</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td class="fw-semibold"><?= e($r['name']) ?></td>
      <td><?= e($r['role']) ?></td>
      <td class="text-end"><?= (int)$r['orders_taken'] ?></td>
      <td class="text-end"><?= e(fmt_money($r['order_value'])) ?></td>
      <td class="text-end"><?= e(fmt_money($r['advance_collected'])) ?></td>
      <td class="text-end"><?= e(fmt_money($r['total_collections'])) ?></td>
      <td class="text-end"><?= (int)$r['designs_completed'] ?></td>
      <td class="text-end"><?= $r['avg_turnaround_hours'] !== null ? e($r['avg_turnaround_hours']) : '—' ?></td>
      <td class="text-end"><?= $r['avg_revisions'] !== null ? e($r['avg_revisions']) : '—' ?></td>
      <td class="text-end"><?= $r['first_time_approval'] !== null ? e($r['first_time_approval']) . '%' : '—' ?></td>
      <td class="text-end"><?= $r['on_time_percent'] !== null ? e($r['on_time_percent']) . '%' : '—' ?></td>
      <td class="text-end <?= $r['pending_now'] > 0 ? 'text-danger fw-bold' : '' ?>"><?= (int)$r['pending_now'] ?></td>
      <td class="text-end <?= $r['overdue_now'] > 0 ? 'text-danger fw-bold' : '' ?>"><?= (int)$r['overdue_now'] ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<div class="card mt-4 no-print"><div class="card-body">
  <h6>Month-by-month comparison (last 12 months, order value taken)</h6>
  <canvas id="monthlyChart" height="110"></canvas>
</div></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const raw = <?= json_encode($monthly, JSON_UNESCAPED_UNICODE) ?>;
  const months = [...new Set(raw.map(r => r.ym))].sort();
  const staff = [...new Set(raw.map(r => r.name))];
  const palette = ['#0d6efd','#dc3545','#198754','#fd7e14','#6f42c1','#20c997','#e83e8c','#6c757d'];
  const datasets = staff.map((s, i) => ({
    label: s,
    data: months.map(m => { const row = raw.find(r => r.ym === m && r.name === s); return row ? parseFloat(row.value) : 0; }),
    backgroundColor: palette[i % palette.length]
  }));
  new Chart(document.getElementById('monthlyChart'), {
    type: 'bar', data: { labels: months, datasets: datasets },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
  });
});
</script>
<?php endif; ?>
