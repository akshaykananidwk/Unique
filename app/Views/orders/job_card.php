<?php
use App\Core\Settings;
use App\Models\Status;

$title = 'Job Card ' . $order['job_no'];
$businessName = (string)Settings::get('business_name', '');
$trackUrl = base_url('track/' . $order['tracking_token']);
$wrapClass = $format === 'thermal' ? 'print-thermal' : 'print-a5';
?>
<div class="<?= e($wrapClass) ?>">
  <div class="print-head">
    <h4><?= e($businessName) ?></h4>
    <div class="print-muted"><?= e($branch['name']) ?> · <?= e($branch['address'] ?? '') ?> · Ph: <?= e($branch['phone'] ?? '') ?></div>
    <?php if ($branch['gstin']): ?><div class="print-muted">GSTIN: <?= e($branch['gstin']) ?></div><?php endif; ?>
    <h3 style="margin:6px 0">JOB CARD — <?= e($order['job_no']) ?></h3>
  </div>
  <table>
    <tr><td><strong>Customer:</strong> <?= e($customer['name']) ?> (<?= e($customer['phone']) ?>)</td>
        <td><strong>Date:</strong> <?= e(fmt_date($order['order_date'], true)) ?></td></tr>
    <tr><td><strong>Priority:</strong> <?php $hot = in_array(strtolower((string)$order['priority']), ['urgent', 'rush'], true); ?>
        <span style="<?= $hot ? 'color:#dc3545;font-weight:700;font-size:1.1em' : '' ?>"><?= e(strtoupper((string)$order['priority'])) ?><?= $hot ? ' ⚡' : '' ?></span></td>
        <td><strong>Due:</strong> <?= e(fmt_date($order['due_date'], true)) ?></td></tr>
    <tr><td colspan="2"><strong>Delivery:</strong> <?= e(ucfirst((string)$order['delivery_type'])) ?>
      <?= $order['delivery_address'] ? '— ' . e($order['delivery_address']) : '' ?></td></tr>
  </table>
  <table style="margin-top:6px">
    <thead><tr><th>#</th><th>Item &amp; specifications</th><th>Qty</th><th>Rate</th><th>Amount</th></tr></thead>
    <tbody>
    <?php foreach ($items as $i => $item): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><strong><?= e($item['item_name_snapshot']) ?></strong>
          <?php if ($item['spec_text']): ?><br><small><?= e($item['spec_text']) ?></small><?php endif; ?>
          <?php if ($item['special_instructions']): ?><br><small>📌 <?= e($item['special_instructions']) ?></small><?php endif; ?>
          <br><small>Status: <?= e(Status::label((string)$item['status'])) ?></small></td>
        <td><?php $n = fn($v) => rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.');
            if ($item['total_sqft'] !== null && (float)$item['total_sqft'] > 0): ?>
              <?= e($n($item['qty'])) ?> × <?= e($n($item['width_ft'])) ?>×<?= e($n($item['height_ft'])) ?>ft<br>
              <strong><?= e($n($item['total_sqft'])) ?> sq.ft</strong>
            <?php else: ?>
              <?= e($n($item['qty'])) ?> <?= e($item['unit']) ?>
            <?php endif; ?></td>
        <td><?= e(number_format((float)$item['rate'], 2)) ?></td>
        <td><?= e(number_format((float)$item['line_total'], 2)) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <table class="totals" style="margin-top:6px">
    <tr><td>Subtotal</td><td style="text-align:right"><?= e(number_format((float)$order['subtotal'], 2)) ?></td></tr>
    <?php if ((float)$order['discount_amount'] > 0): ?><tr><td>Discount</td><td style="text-align:right">− <?= e(number_format((float)$order['discount_amount'], 2)) ?></td></tr><?php endif; ?>
    <?php if ((float)$order['tax_amount'] > 0): ?><tr><td>Tax</td><td style="text-align:right"><?= e(number_format((float)$order['tax_amount'], 2)) ?></td></tr><?php endif; ?>
    <?php if ((float)$order['delivery_charge'] > 0): ?><tr><td>Delivery</td><td style="text-align:right"><?= e(number_format((float)$order['delivery_charge'], 2)) ?></td></tr><?php endif; ?>
    <tr class="grand"><td>TOTAL</td><td style="text-align:right">₹<?= e(number_format((float)$order['total'], 2)) ?></td></tr>
    <tr><td>Advance Paid</td><td style="text-align:right">₹<?= e(number_format((float)$order['paid_amount'], 2)) ?></td></tr>
    <tr class="grand"><td>BALANCE DUE</td><td style="text-align:right">₹<?= e(number_format((float)$order['balance_amount'], 2)) ?></td></tr>
  </table>
  <div class="qr-box">
    <div id="qr"></div>
    <div class="print-muted">Scan to track your order<br><?= e($trackUrl) ?></div>
  </div>
  <p class="print-muted" style="text-align:center;margin-top:8px">Thank you for your business! 🙏</p>
</div>
<script src="<?= e(asset_url('vendor/qrcode/qrcode.min.js')) ?>"></script>
<script>
(function () {
  var qr = qrcode(0, 'M');
  qr.addData(<?= json_encode($trackUrl) ?>);
  qr.make();
  document.getElementById('qr').innerHTML = qr.createImgTag(3, 4);
})();
</script>
