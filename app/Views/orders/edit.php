<?php use App\Core\Csrf; $title = 'Edit ' . $order['job_no']; ?>
<h4 class="mb-3">Edit <?= e($order['job_no']) ?> <small class="text-muted fs-6"><?= e($customer['name']) ?></small></h4>
<form method="post" action="<?= e(admin_url('orders/' . $order['id'] . '/update')) ?>">
  <?= Csrf::field() ?>
  <div class="card mb-3"><div class="card-body row g-2">
    <div class="col-md-3"><label class="form-label">Priority</label>
      <select name="priority" class="form-select">
        <?php foreach (['normal', 'urgent', 'rush'] as $p): ?>
          <option value="<?= $p ?>" <?= $order['priority'] === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-3"><label class="form-label">Due Date</label>
      <input type="datetime-local" name="due_date" class="form-control"
             value="<?= e($order['due_date'] ? date('Y-m-d\TH:i', strtotime($order['due_date'])) : '') ?>"></div>
    <div class="col-md-3"><label class="form-label">Discount Type</label>
      <select name="discount_type" class="form-select">
        <option value="">None</option>
        <option value="flat" <?= $order['discount_type'] === 'flat' ? 'selected' : '' ?>>Flat ₹</option>
        <option value="percent" <?= $order['discount_type'] === 'percent' ? 'selected' : '' ?>>Percent %</option>
      </select></div>
    <div class="col-md-3"><label class="form-label">Discount Value</label>
      <input type="number" step="0.01" min="0" name="discount_value" class="form-control" value="<?= e($order['discount_value']) ?>"></div>
    <div class="col-md-3"><label class="form-label">Delivery Charge</label>
      <input type="number" step="0.01" min="0" name="delivery_charge" class="form-control" value="<?= e($order['delivery_charge']) ?>"></div>
    <div class="col-md-3"><label class="form-label">Delivery Type</label>
      <select name="delivery_type" class="form-select">
        <option value="pickup" <?= $order['delivery_type'] === 'pickup' ? 'selected' : '' ?>>Pickup</option>
        <option value="delivery" <?= $order['delivery_type'] === 'delivery' ? 'selected' : '' ?>>Delivery</option>
      </select></div>
    <div class="col-md-6"><label class="form-label">Delivery Address</label>
      <input name="delivery_address" class="form-control" value="<?= e($order['delivery_address']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Customer Note</label>
      <input name="customer_note" class="form-control" value="<?= e($order['customer_note']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Internal Note</label>
      <input name="internal_note" class="form-control" value="<?= e($order['internal_note']) ?>"></div>
  </div></div>

  <div class="card mb-3"><div class="card-body">
    <h6 class="text-uppercase text-muted small">Items &amp; Prices</h6>
    <p class="text-muted small">Edit the quantity or rate below to correct the price. Totals, tax and balance are recalculated when you save.</p>
    <div class="table-responsive">
      <table class="table table-sm align-middle table-mobile">
        <thead><tr><th>Item</th><th style="width:120px">Qty</th><th style="width:150px">Rate ₹</th><th class="text-end">Line Total</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): $cancelled = $it['status'] === 'cancelled'; ?>
          <tr class="<?= $cancelled ? 'text-muted' : '' ?>">
            <td data-label="Item">
              <strong><?= e($it['item_name_snapshot']) ?></strong>
              <?php if (!empty($it['spec_text'])): ?><div class="small text-muted"><?= e($it['spec_text']) ?></div><?php endif; ?>
              <?php if ($cancelled): ?><span class="badge bg-secondary">Cancelled</span><?php endif; ?>
            </td>
            <td data-label="Qty">
              <input type="number" step="0.01" min="0" name="item_qty[<?= (int)$it['id'] ?>]"
                     class="form-control form-control-sm" value="<?= e(rtrim(rtrim(number_format((float)$it['qty'], 2, '.', ''), '0'), '.')) ?>"
                     <?= $cancelled ? 'disabled' : '' ?>>
            </td>
            <td data-label="Rate ₹">
              <input type="number" step="0.01" min="0" name="item_rate[<?= (int)$it['id'] ?>]"
                     class="form-control form-control-sm" value="<?= e(number_format((float)$it['rate'], 2, '.', '')) ?>"
                     <?= $cancelled ? 'disabled' : '' ?>>
            </td>
            <td data-label="Line Total" class="text-end fw-semibold"><?= e(fmt_money($it['line_total'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div></div>

  <div class="sticky-actions d-flex gap-2">
    <button class="btn btn-primary flex-grow-1">Save Changes</button>
    <a href="<?= e(admin_url('orders/' . $order['id'])) ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>
<p class="text-muted small mt-2">To add or remove whole item lines, cancel the order and re-create it — this keeps the job history honest. Prices and quantities can be corrected here any time before delivery.</p>
