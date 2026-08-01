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
  <div class="sticky-actions d-flex gap-2">
    <button class="btn btn-primary flex-grow-1">Save Changes</button>
    <a href="<?= e(admin_url('orders/' . $order['id'])) ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>
<p class="text-muted small mt-2">Item lines cannot be edited after creation — cancel the order and re-create it if the items are wrong (this keeps the audit trail honest).</p>
