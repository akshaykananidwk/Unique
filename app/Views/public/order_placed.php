<?php $title = 'Order Placed'; ?>
<div class="container py-5 text-center" style="max-width:560px">
  <div class="card"><div class="card-body p-4">
    <div class="display-3">🎉</div>
    <h3>Order placed successfully!</h3>
    <p class="fs-5">Your job number is<br><strong class="fs-3"><?= e($order['job_no']) ?></strong></p>
    <p class="text-muted">We've sent the job number and tracking link to your WhatsApp.
      Our team will confirm the final price and start work shortly.</p>
    <a class="btn btn-primary btn-lg" href="<?= e(base_url('track/' . $order['tracking_token'])) ?>">
      <i class="bi bi-geo"></i> Track this order</a>
    <div class="mt-3"><a href="<?= e(base_url('products')) ?>">← Order something else</a></div>
  </div></div>
</div>
