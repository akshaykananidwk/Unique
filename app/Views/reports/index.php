<?php use App\Core\Acl; $title = 'Reports'; ?>
<h4 class="mb-3">Reports</h4>
<div class="row g-3">
<?php
$reports = [
    ['report.staff', 'reports/users', 'person-badge', 'User Performance', 'Orders taken and their value, advance, recovered and pending per user'],
    ['report.staff', 'reports/designers', 'palette2', 'Designer Performance', 'Designs made, jobs and orders handled, total value handled'],
    ['report.work', 'reports/work', 'clipboard-check', 'Work Done', 'Every job with who took it, who made it and who took the money — exports to Excel'],
    ['report.staff', 'reports/staff', 'people', 'Staff Performance (detailed)', 'Turnaround, revisions, first-time approval and on-time %'],
    ['report.sales', 'reports/sales', 'graph-up', 'Sales Register', 'Order-wise, item-wise and category-wise sales'],
    ['report.outstanding', 'reports/outstanding', 'cash-coin', 'Outstanding', 'Pending balances by age bucket, bulk WhatsApp reminders'],
    ['payment.view', 'cashbook', 'cash-stack', 'Daily Cash Book', 'Opening, collections by mode, day list'],
    ['report.sales', 'reports/gst', 'percent', 'GST Summary', 'Taxable value and tax grouped by rate'],
    ['report.sales', 'reports/revisions', 'arrow-repeat', 'Design Revision Analysis', 'Which items cause the most rework'],
    ['report.sales', 'reports/cancelled', 'x-circle', 'Cancelled Orders', 'Cancelled orders with reasons'],
    ['log.view', 'reports/activity', 'journal-text', 'Activity Log', 'Who changed what, when, from which IP'],
];
foreach ($reports as [$perm, $path, $icon, $name, $desc]): if (!Acl::can($perm)) continue; ?>
  <div class="col-md-6 col-lg-4">
    <a class="card h-100 text-decoration-none" href="<?= e(admin_url($path)) ?>"><div class="card-body">
      <h6><i class="bi bi-<?= e($icon) ?> me-2 text-primary"></i><?= e($name) ?></h6>
      <p class="small text-muted mb-0"><?= e($desc) ?></p>
    </div></a>
  </div>
<?php endforeach; ?>
</div>
