<?php use App\Core\Csrf; $title = 'Edit ' . $order['job_no']; ?>
<h4 class="mb-1">Edit <?= e($order['job_no']) ?> <small class="text-muted fs-6"><?= e($customer['name']) ?> · <?= e($customer['phone']) ?></small></h4>
<p class="text-muted small">Add missed items, change quantity or rate, or remove a line — the whole order is editable. Totals, tax and balance are recalculated when you save.</p>

<form id="orderEditForm" method="post" action="<?= e(admin_url('orders/' . $order['id'] . '/update')) ?>">
  <?= Csrf::field() ?>
  <input type="hidden" name="items_json" id="items_json">

  <!-- Order-level fields -->
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
      <select id="discount_type" name="discount_type" class="form-select">
        <option value="">None</option>
        <option value="flat" <?= $order['discount_type'] === 'flat' ? 'selected' : '' ?>>Flat ₹</option>
        <option value="percent" <?= $order['discount_type'] === 'percent' ? 'selected' : '' ?>>Percent %</option>
      </select></div>
    <div class="col-md-3"><label class="form-label">Discount Value</label>
      <input type="number" step="0.01" min="0" id="discount_value" name="discount_value" class="form-control" value="<?= e($order['discount_value']) ?>"></div>
    <div class="col-md-3"><label class="form-label">Delivery Charge</label>
      <input type="number" step="0.01" min="0" id="delivery_charge" name="delivery_charge" class="form-control" value="<?= e($order['delivery_charge']) ?>"></div>
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

  <!-- Items -->
  <div class="card mb-3"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center">
      <h6 class="text-uppercase text-muted small mb-0">Items &amp; Prices</h6>
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal"><i class="bi bi-plus-lg"></i> Add Item</button>
    </div>
    <div class="table-responsive mt-2">
      <table class="table table-sm align-middle table-mobile">
        <thead><tr><th>Item</th><th style="width:110px">Qty</th><th style="width:130px">Rate ₹</th><th>Amount</th><th></th></tr></thead>
        <tbody id="itemsBody"></tbody>
      </table>
    </div>
    <div class="text-muted small">Rate and quantity can be fine-tuned inline. To change an item's options, remove the line and add it again. Final totals are confirmed on save.</div>
  </div></div>

  <!-- Totals -->
  <div class="card mb-3"><div class="card-body">
    <div class="row justify-content-end">
      <div class="col-md-5">
        <table class="table table-sm mb-0">
          <tr><td>Subtotal</td><td class="text-end" id="sumSubtotal">₹0.00</td></tr>
          <tr><td>Discount</td><td class="text-end" id="sumDiscount">− ₹0.00</td></tr>
          <tr><td>Tax</td><td class="text-end" id="sumTax">₹0.00</td></tr>
          <tr class="fw-bold fs-5"><td>Total</td><td class="text-end" id="sumTotal">₹0.00</td></tr>
          <tr><td>Already Paid</td><td class="text-end text-success"><?= e(fmt_money($order['paid_amount'])) ?></td></tr>
        </table>
      </div>
    </div>
  </div></div>

  <div class="sticky-actions d-flex gap-2">
    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-check-lg"></i> Save Changes</button>
    <a href="<?= e(admin_url('orders/' . $order['id'])) ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>

<!-- Add Item modal (same builder as the New Order page) -->
<div class="modal fade" id="itemModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-fullscreen-md-down">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Item</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-2 mb-3">
          <div class="col-md-6"><label class="form-label">Category</label>
            <select id="modalCategory" class="form-select">
              <option value="">— Select category —</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?> (<?= (int)$c['item_count'] ?>)</option>
              <?php endforeach; ?>
            </select></div>
          <div class="col-md-6"><label class="form-label">Item</label>
            <select id="modalItem" class="form-select"><option value="">— Select item —</option></select></div>
        </div>
        <div id="modalOptions"></div>
        <div class="row g-2 border-top pt-3">
          <div class="col-4 col-md-2"><label class="form-label">Qty</label>
            <input type="number" id="modalQty" class="form-control" value="1" min="1"></div>
          <div class="col-4 col-md-2"><label class="form-label">Rate ₹</label>
            <input type="number" step="0.01" id="modalRate" class="form-control"></div>
          <div class="col-4 col-md-3"><label class="form-label">Due Date</label>
            <input type="datetime-local" id="modalDue" class="form-control"></div>
          <div class="col-12 col-md-5" id="modalDesignerWrap"><label class="form-label">Designer (optional)</label>
            <select id="modalDesigner" class="form-select">
              <option value="">— Assign later / auto —</option>
              <?php foreach ($designers as $d): ?>
                <option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?> (<?= (int)$d['open_jobs'] ?> open)</option>
              <?php endforeach; ?>
            </select></div>
          <div class="col-12" id="modalFileWrap" style="display:none"></div>
          <div class="col-12"><label class="form-label">Special instructions</label>
            <textarea id="modalInstructions" class="form-control" rows="2"></textarea></div>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <div class="fs-5">Amount: <strong id="modalAmount">₹0.00</strong></div>
        <button type="button" id="modalAdd" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add to Order</button>
      </div>
    </div>
  </div>
</div>

<script>
window.KP_ITEMS = <?= json_encode(array_map(fn($i) => [
    'id' => (int)$i['id'], 'category_id' => (int)$i['category_id'], 'name' => $i['name'],
    'unit' => $i['unit'], 'base_price' => $i['base_price'], 'pricing_type' => $i['pricing_type'], 'min_qty' => (int)$i['min_qty'],
], $catalog), JSON_UNESCAPED_UNICODE) ?>;
window.KP_EDIT_ITEMS = <?= json_encode(array_map(function ($oi) {
    $spec = json_decode((string)($oi['spec_json'] ?? '[]'), true);
    return [
        'id' => (int)$oi['id'],
        'item_id' => (int)$oi['item_id'],
        'name' => $oi['item_name_snapshot'],
        'fixed' => ($oi['pricing_type'] ?? '') === 'fixed',
        'qty' => (float)$oi['qty'],
        'rate' => (float)$oi['rate'],
        'spec' => is_array($spec) ? $spec : [],
        'spec_text' => (string)$oi['spec_text'],
        'amount' => (float)$oi['amount'],
        'tax_percent' => (float)$oi['tax_percent'],
        'requires_design' => (int)$oi['requires_design'],
        'due_date' => $oi['due_date'] ? date('Y-m-d\TH:i', strtotime((string)$oi['due_date'])) : '',
        'designer_id' => $oi['assigned_designer_id'] ? (int)$oi['assigned_designer_id'] : null,
        'special_instructions' => (string)($oi['special_instructions'] ?? ''),
    ];
}, array_values(array_filter($items, fn($oi) => $oi['status'] !== 'cancelled'))), JSON_UNESCAPED_UNICODE) ?>;
</script>
