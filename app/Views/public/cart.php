<?php use App\Core\Csrf; $title = 'Your Order'; ?>
<div class="container section" style="max-width:760px">
  <h1 class="section-title">Your Order</h1>
  <p class="section-sub">Review your items, then continue to place the order.</p>

  <?php if (!$cart): ?>
    <div class="empty-state"><i class="bi bi-cart"></i>Your order is empty.<br>
      <a class="btn btn-primary mt-2 rounded-pill" href="<?= e(base_url('products')) ?>">Browse products</a></div>
  <?php else: ?>
  <div class="track-box mb-3 p-2 p-sm-3">
    <?php foreach ($cart as $line): ?>
      <div class="d-flex justify-content-between align-items-start gap-2 border-bottom py-2">
        <div>
          <div class="fw-semibold"><?= e($line['name']) ?>
            <span class="badge bg-light text-dark border ms-1"><?= e(rtrim(rtrim((string)$line['qty'], '0'), '.')) ?> <?= e($line['unit']) ?></span>
          </div>
          <?php if ($line['spec_text']): ?><div class="small text-muted"><?= e($line['spec_text']) ?></div><?php endif; ?>
          <?php if ($line['file']): ?><div class="small text-success"><i class="bi bi-paperclip"></i> <?= e($line['file']['original']) ?></div><?php endif; ?>
        </div>
        <form method="post" action="<?= e(base_url('cart/remove')) ?>">
          <?= Csrf::field() ?><input type="hidden" name="key" value="<?= e($line['key']) ?>">
          <button class="btn btn-sm btn-outline-danger" title="Remove"><i class="bi bi-trash"></i></button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="d-flex gap-2 flex-column flex-sm-row">
    <a class="btn btn-outline-secondary rounded-pill" href="<?= e(base_url('products')) ?>"><i class="bi bi-plus-lg"></i> Add more items</a>
    <a class="btn btn-primary btn-lg flex-grow-1 rounded-pill" href="<?= e(base_url('checkout')) ?>">Continue to Checkout →</a>
  </div>
  <p class="small text-muted mt-3 text-center">You will get a confirmation and price on WhatsApp after our team reviews your order.</p>
  <?php endif; ?>
</div>
