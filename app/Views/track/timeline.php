<?php
use App\Models\Status;

$title = 'Track ' . $order['job_no'];
$stages = ['design_pending' => 'Order Received', 'design_in_progress' => 'Designing',
    'proof_sent' => 'Proof Sent — Your Approval Needed', 'design_approved' => 'Design Approved',
    'ready_for_print' => 'Ready for Print', 'printing' => 'Printing', 'post_press' => 'Finishing',
    'ready_for_delivery' => 'Ready for ' . ($order['delivery_type'] === 'delivery' ? 'Delivery' : 'Pickup'),
    'delivered' => 'Delivered', 'completed' => 'Completed'];
$currentRank = Status::rank((string)$order['status']);
$isCancelled = (int)$order['is_cancelled'] === 1;
$anyDesign = array_reduce($items, fn($c, $i) => $c || (int)$i['requires_design'] === 1, false);
$waNumber = normalize_phone((string)($branch['whatsapp'] ?: $branch['phone'])) ?? '';
?>
<div class="container py-4" style="max-width:640px">
  <div class="card mb-3"><div class="card-body">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <h4 class="mb-0"><?= e($order['job_no']) ?></h4>
        <small class="text-muted">Hello <?= e($customer['name']) ?> 🙏</small>
      </div>
      <span class="badge fs-6 bg-<?= e(Status::color((string)$order['status'])) ?>"><?= e(Status::label((string)$order['status'])) ?></span>
    </div>
    <?php if (!$isCancelled && $order['due_date']): ?>
      <div class="mt-2"><i class="bi bi-calendar-event"></i> Expected: <strong><?= e(fmt_date($order['due_date'], true)) ?></strong></div>
    <?php endif; ?>
  </div></div>

  <?php if ($isCancelled): ?>
    <div class="alert alert-danger">This order was cancelled<?= $order['cancelled_reason'] ? ': ' . e($order['cancelled_reason']) : '' ?>.
      Please contact us if this is unexpected.</div>
  <?php else: ?>
  <div class="card mb-3"><div class="card-body">
    <h6 class="mb-3">Order progress</h6>
    <ul class="timeline">
      <?php foreach ($stages as $key => $label):
          $rank = Status::rank($key);
          if (!$anyDesign && Status::isDesignStage($key)) continue;
          $done = $currentRank > $rank || in_array($order['status'], ['delivered', 'completed'], true) && $rank <= $currentRank;
          $current = $order['status'] === $key; ?>
      <li class="<?= $current ? 'current' : ($done ? 'done' : '') ?>">
        <span class="dot"><?= $done ? '✓' : ($current ? '●' : '') ?></span>
        <?= e($label) ?>
        <?php if ($current && $key === 'proof_sent'): ?>
          <div class="small text-danger fw-normal">Waiting for you — open the proof link from WhatsApp and tap Approve.</div>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div></div>
  <?php endif; ?>

  <div class="card mb-3"><div class="card-body">
    <h6>Items</h6>
    <?php foreach ($items as $item): ?>
    <div class="d-flex justify-content-between border-bottom py-2">
      <div>
        <?= e($item['item_name_snapshot']) ?> × <?= e(rtrim(rtrim((string)$item['qty'], '0'), '.')) ?>
        <div class="small text-muted"><?= e(Status::label((string)$item['status'])) ?></div>
        <?php if ($item['status'] === 'proof_sent' && $item['latest_proof_token']): ?>
          <a class="btn btn-warning btn-sm mt-1" href="<?= e(base_url('proof/' . $item['latest_proof_token'])) ?>">
            👀 View &amp; approve design</a>
        <?php endif; ?>
      </div>
      <div class="text-end"><?= e(fmt_money($item['line_total'])) ?></div>
    </div>
    <?php endforeach; ?>
    <table class="table table-sm mt-2 mb-0">
      <tr class="fw-bold"><td>Total</td><td class="text-end"><?= e(fmt_money($order['total'])) ?></td></tr>
      <tr class="text-success"><td>Paid</td><td class="text-end"><?= e(fmt_money($order['paid_amount'])) ?></td></tr>
      <tr class="fw-bold <?= (float)$order['balance_amount'] > 0 ? 'text-danger' : 'text-success' ?>">
        <td>Balance</td><td class="text-end"><?= e(fmt_money($order['balance_amount'])) ?></td></tr>
    </table>
  </div></div>

  <div class="card"><div class="card-body text-center">
    <div class="mb-1"><strong><?= e($branch['name']) ?></strong></div>
    <div class="small text-muted"><?= e($branch['address'] ?? '') ?> <?= e($branch['city'] ?? '') ?></div>
    <div class="mt-2 d-flex gap-2 justify-content-center">
      <?php if ($branch['phone']): ?><a class="btn btn-outline-primary btn-sm" href="tel:<?= e($branch['phone']) ?>"><i class="bi bi-telephone"></i> Call</a><?php endif; ?>
      <?php if ($waNumber): ?><a class="btn btn-success btn-sm" target="_blank" href="https://wa.me/<?= e($waNumber) ?>"><i class="bi bi-whatsapp"></i> Chat on WhatsApp</a><?php endif; ?>
    </div>
  </div></div>
</div>
