<?php
use App\Core\Settings;

$title = 'Receipt ' . $payment['receipt_no'];
$businessName = (string)Settings::get('business_name', '');
$trackUrl = base_url('track/' . $order['tracking_token']);
$wrapClass = $format === 'thermal' ? 'print-thermal' : 'print-a5';
$modeLabels = ['cash' => 'Cash', 'upi' => 'UPI', 'card' => 'Card', 'bank' => 'Bank Transfer', 'cheque' => 'Cheque', 'credit' => 'Credit'];
?>
<div class="<?= e($wrapClass) ?>">
  <div class="print-head">
    <h4><?= e($businessName) ?></h4>
    <div class="print-muted"><?= e($branch['name']) ?> · <?= e($branch['address'] ?? '') ?> · Ph: <?= e($branch['phone'] ?? '') ?></div>
    <?php if ($branch['gstin']): ?><div class="print-muted">GSTIN: <?= e($branch['gstin']) ?></div><?php endif; ?>
    <h3 style="margin:6px 0">PAYMENT RECEIPT</h3>
  </div>
  <table>
    <tr><td><strong>Receipt No:</strong> <?= e($payment['receipt_no']) ?></td>
        <td><strong>Date:</strong> <?= e(fmt_date($payment['paid_at'], true)) ?></td></tr>
    <tr><td><strong>Job No:</strong> <?= e($order['job_no']) ?></td>
        <td><strong>Type:</strong> <?= e(ucfirst((string)$payment['type'])) ?></td></tr>
    <tr><td colspan="2"><strong>Received from:</strong> <?= e($customer['name']) ?> (<?= e($customer['phone']) ?>)</td></tr>
    <tr><td><strong>Mode:</strong> <?= e($modeLabels[$payment['mode']] ?? $payment['mode']) ?></td>
        <td><strong>Reference:</strong> <?= e($payment['reference'] ?: '—') ?></td></tr>
  </table>
  <table class="totals" style="margin-top:8px">
    <tr class="grand"><td>AMOUNT RECEIVED</td><td style="text-align:right">₹<?= e(number_format((float)$payment['amount'], 2)) ?></td></tr>
    <tr><td>Order Total</td><td style="text-align:right">₹<?= e(number_format((float)$order['total'], 2)) ?></td></tr>
    <tr><td>Total Paid</td><td style="text-align:right">₹<?= e(number_format((float)$order['paid_amount'], 2)) ?></td></tr>
    <tr class="grand"><td>BALANCE</td><td style="text-align:right">₹<?= e(number_format((float)$order['balance_amount'], 2)) ?></td></tr>
  </table>
  <div class="qr-box">
    <div id="qr"></div>
    <div class="print-muted">Scan to view your order status</div>
  </div>
  <p class="print-muted" style="text-align:center;margin-top:8px">Thank you! 🙏 — <?= e($businessName) ?></p>
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
