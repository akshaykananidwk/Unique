<?php
use App\Core\Settings;
use App\Models\Status;

$title = 'Order List';
$businessName = (string)Settings::get('business_name', '');

// Same shop line as the job card: fall back to Settings, never print a stray separator.
$shopPhone = trim((string)(($branch['phone'] ?? '') ?: Settings::get('business_phone', '')));
$shopAddress = trim((string)(($branch['address'] ?? '') ?: Settings::get('business_address', '')));
$headBits = array_filter([
    trim((string)($branch['name'] ?? '')),
    $shopAddress,
    $shopPhone !== '' ? 'Ph: ' . $shopPhone : '',
], fn($v) => $v !== '');

$pending = 0;
$value = 0.0;
$due = 0.0;
foreach ($orders as $o) {
    if (!in_array($o['status'], ['delivered', 'completed', 'cancelled'], true)) {
        $pending++;
    }
    $value += (float)$o['total'];
    $due += (float)$o['balance_amount'];
}
?>
<div class="print-a4">
  <div class="print-head">
    <h4><?= e($businessName) ?></h4>
    <div class="print-muted"><?= e(implode(' · ', $headBits)) ?></div>
    <h3 style="margin:6px 0">
      <?= $personName ? e($personName) . ' — Work List' : 'ORDER LIST' ?>
    </h3>
    <div class="print-muted"><?= e($filterText) ?></div>
    <div class="print-muted">Printed <?= e(fmt_date(now(), true)) ?></div>
  </div>

  <table style="margin-top:8px">
    <thead>
      <tr>
        <th>#</th><th>Job No</th><th>Customer</th><th>Item</th>
        <th>Due</th><th>Status</th><th>Total</th><th>Balance</th><th style="width:70px">Done ✓</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $i => $o):
        $overdue = Status::isOverdue($o['due_date'], (string)$o['status']); ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($o['job_no']) ?>
          <?php if (in_array(strtolower((string)$o['priority']), ['urgent', 'rush'], true)): ?>
            <strong style="color:#dc3545">⚡</strong>
          <?php endif; ?>
        </td>
        <td><?= e($o['customer_name']) ?>
          <div class="print-muted"><?= e($o['contact_name'] ?: $o['customer_phone']) ?></div></td>
        <td><?= e(mb_substr((string)$o['items'], 0, 60)) ?>
          <?php if ($o['designers']): ?><div class="print-muted"><?= e($o['designers']) ?></div><?php endif; ?></td>
        <td<?= $overdue ? ' style="color:#dc3545;font-weight:700"' : '' ?>><?= e(fmt_date($o['due_date'])) ?></td>
        <td><?= e(Status::label((string)$o['status'])) ?></td>
        <td style="text-align:right"><?= e(fmt_money($o['total'])) ?></td>
        <td style="text-align:right"><?= e(fmt_money($o['balance_amount'])) ?></td>
        <!-- An empty box: this sheet is meant to be ticked off by hand during the day. -->
        <td></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$orders): ?>
      <tr><td colspan="9" style="text-align:center">Nothing matches this filter.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>

  <table style="margin-top:8px">
    <tr>
      <td><strong>Jobs on this sheet:</strong> <?= count($orders) ?></td>
      <td><strong>Still pending:</strong> <?= $pending ?></td>
      <td style="text-align:right"><strong>Total:</strong> <?= e(fmt_money($value)) ?></td>
      <td style="text-align:right"><strong>Balance due:</strong> <?= e(fmt_money($due)) ?></td>
    </tr>
  </table>

  <table style="margin-top:14px">
    <tr>
      <td>Given to: ____________________</td>
      <td>Signature: ____________________</td>
      <td style="text-align:right">Date: <?= e(fmt_date(now())) ?></td>
    </tr>
  </table>
</div>
