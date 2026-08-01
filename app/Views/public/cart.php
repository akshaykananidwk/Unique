<?php use App\Core\Csrf; $title = 'Your Order'; ?>
<div class="container py-4">
  <h2 class="mb-4">Your Order</h2>
  <?php if (!$cart): ?>
    <div class="empty-state"><i class="bi bi-cart"></i>Your cart is empty.<br>
      <a class="btn btn-primary mt-2" href="<?= e(base_url('products')) ?>">Browse products</a></div>
  <?php else: ?>
  <div class="table-responsive"><table class="table align-middle table-mobile">
    <thead><tr><th>Item</th><th>Qty</th><th class="text-end">Estimated</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($cart as $line): ?>
      <tr>
        <td data-label="Item"><strong><?= e($line['name']) ?></strong>
          <?php if ($line['spec_text']): ?><div class="small text-muted"><?= e($line['spec_text']) ?></div><?php endif; ?>
          <?php if ($line['file']): ?><div class="small text-success">📎 <?= e($line['file']['original']) ?></div><?php endif; ?></td>
        <td data-label="Qty"><?= e(rtrim(rtrim((string)$line['qty'], '0'), '.')) ?> <?= e($line['unit']) ?></td>
        <td data-label="Estimated" class="text-end"><?= e(fmt_money($line['amount'])) ?></td>
        <td data-label="">
          <form method="post" action="<?= e(base_url('cart/remove')) ?>">
            <?= Csrf::field() ?><input type="hidden" name="key" value="<?= e($line['key']) ?>">
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr class="fw-bold"><td colspan="2">Estimated total (before tax)</td>
      <td class="text-end"><?= e(fmt_money($total)) ?></td><td></td></tr></tfoot>
  </table></div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= e(base_url('products')) ?>">+ Add more items</a>
    <a class="btn btn-primary btn-lg flex-grow-1" href="<?= e(base_url('checkout')) ?>">Continue to Checkout →</a>
  </div>
  <?php endif; ?>
</div>
