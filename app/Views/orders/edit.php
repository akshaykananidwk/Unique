<?php use App\Core\Csrf; $title = 'Edit ' . $order['job_no']; ?>
<h4 class="mb-1">Edit <?= e($order['job_no']) ?> <small class="text-muted fs-6"><?= e($customer['name']) ?> · <?= e($customer['phone']) ?></small></h4>
<p class="text-muted small">Every field is editable — add or remove items, change the name, quantity, size or rate. Amounts and the grand total update as you type, and are recalculated on the server when you save.</p>

<form id="orderEditForm" method="post" action="<?= e(admin_url('orders/' . $order['id'] . '/update')) ?>">
  <?= Csrf::field() ?>
  <input type="hidden" name="items_json" id="items_json">

  <!-- Order-level fields -->
  <div class="card mb-3"><div class="card-body row g-2">
    <div class="col-md-3"><label class="form-label">Job No</label>
      <input name="job_no" class="form-control" value="<?= e($order['job_no']) ?>" required>
      <div class="form-text">Editable any time — must stay unique.</div></div>
    <div class="col-md-3"><label class="form-label">Order Date</label>
      <input type="datetime-local" name="order_date" class="form-control"
             value="<?= e($order['order_date'] ? date('Y-m-d\TH:i', strtotime($order['order_date'])) : '') ?>"></div>
    <div class="col-md-3"><label class="form-label">Due Date</label>
      <input type="datetime-local" name="due_date" class="form-control"
             value="<?= e($order['due_date'] ? date('Y-m-d\TH:i', strtotime($order['due_date'])) : '') ?>"></div>
    <div class="col-md-3"><label class="form-label">Priority</label>
      <select name="priority" class="form-select">
        <?php foreach (['normal', 'urgent', 'rush'] as $p): ?>
          <option value="<?= $p ?>" <?= $order['priority'] === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-3"><label class="form-label">Accepted By</label>
      <select name="accepted_by_user_id" class="form-select">
        <option value="">— not set —</option>
        <?php foreach ($staff as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= (int)($order['accepted_by_user_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-3"><label class="form-label">Delivery Charge</label>
      <input type="number" step="0.01" min="0" id="delivery_charge" name="delivery_charge" class="form-control" value="<?= e($order['delivery_charge']) ?>"></div>
    <div class="col-md-3"><label class="form-label">Delivery Type</label>
      <select name="delivery_type" class="form-select">
        <option value="pickup" <?= $order['delivery_type'] === 'pickup' ? 'selected' : '' ?>>Pickup</option>
        <option value="delivery" <?= $order['delivery_type'] === 'delivery' ? 'selected' : '' ?>>Delivery</option>
      </select></div>
    <div class="col-md-3"><label class="form-label">Delivery Address</label>
      <input name="delivery_address" class="form-control" value="<?= e($order['delivery_address']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Customer Note</label>
      <input name="customer_note" class="form-control" value="<?= e($order['customer_note']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Internal Note</label>
      <input name="internal_note" class="form-control" value="<?= e($order['internal_note']) ?>"></div>
  </div></div>

  <!-- Items -->
  <div class="card mb-3"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center">
      <h6 class="text-uppercase text-muted small mb-0">Items</h6>
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal"><i class="bi bi-plus-lg"></i> Add Item</button>
    </div>
    <div class="table-responsive mt-2">
      <table class="table table-sm align-middle table-mobile kp-lines">
        <thead><tr>
          <th style="min-width:190px">Item</th>
          <th style="width:90px">Qty</th>
          <th style="width:90px">Width ft</th>
          <th style="width:90px">Height ft</th>
          <th style="width:95px">Sq. Ft.</th>
          <th style="width:105px">Rate ₹</th>
          <th style="width:110px" class="text-end">Amount</th>
          <th style="width:44px"></th>
        </tr></thead>
        <tbody id="itemsBody"></tbody>
      </table>
    </div>
    <div class="form-text">Foot × foot items: Qty × Width × Height = Sq. Ft., then × Rate = Amount.</div>
  </div></div>

  <!-- Totals -->
  <div class="card mb-3"><div class="card-body">
    <div class="row justify-content-end">
      <div class="col-md-5">
        <table class="table table-sm mb-0">
          <tr><td>Subtotal</td><td class="text-end" id="sumSubtotal">₹0.00</td></tr>
          <tr><td>Tax</td><td class="text-end" id="sumTax">₹0.00</td></tr>
          <tr><td>Delivery</td><td class="text-end" id="sumDelivery">₹0.00</td></tr>
          <tr class="fw-bold fs-5"><td>Total</td><td class="text-end" id="sumTotal">₹0.00</td></tr>
          <tr><td>Already Paid</td><td class="text-end text-success"><?= e(fmt_money($order['paid_amount'])) ?></td></tr>
          <tr class="fw-semibold"><td>Balance</td><td class="text-end" id="sumDue">₹0.00</td></tr>
        </table>
      </div>
    </div>
  </div></div>

  <div class="sticky-actions d-flex gap-2">
    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-check-lg"></i> Save Changes</button>
    <a href="<?= e(admin_url('orders/' . $order['id'])) ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>

<!-- Add Item modal (identical to New Order) -->
<div class="modal fade" id="itemModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-fullscreen-md-down">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Item</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-2 mb-2">
          <div class="col-md-5"><label class="form-label">Category *</label>
            <select id="modalCategory" class="form-select">
              <option value="">— Select category —</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= (int)$c['id'] ?>" data-mode="<?= e($c['calc_mode'] ?? 'simple') ?>"><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="col-md-7"><label class="form-label">Item Name *</label>
            <input id="modalItemName" class="form-control" list="itemNameSuggest" autocomplete="off"
                   placeholder="e.g. Flex Banner, Star Flex, Vinyl, Sunboard, PVC…">
            <datalist id="itemNameSuggest"></datalist></div>
        </div>
        <div id="modalOptions" class="border-top pt-2"></div>
        <div class="row g-2 border-top pt-3 align-items-end">
          <div class="col-4 col-md-2"><label class="form-label">Qty *</label>
            <input type="number" step="any" min="0" id="modalQty" class="form-control" value="1"></div>
          <div class="col-4 col-md-2 kp-sqft-only"><label class="form-label">Width ft</label>
            <input type="number" step="any" min="0" id="modalWidth" class="form-control" value="0"></div>
          <div class="col-4 col-md-2 kp-sqft-only"><label class="form-label">Height ft</label>
            <input type="number" step="any" min="0" id="modalHeight" class="form-control" value="0"></div>
          <div class="col-4 col-md-2 kp-sqft-only"><label class="form-label">Sq. Ft.</label>
            <input id="modalSqft" class="form-control-plaintext fw-semibold ps-2" readonly value="0"></div>
          <div class="col-4 col-md-2"><label class="form-label">Rate ₹ *</label>
            <input type="number" step="any" min="0" id="modalRate" class="form-control" value="0"></div>
          <div class="col-4 col-md-2"><label class="form-label">Due Date</label>
            <input type="datetime-local" id="modalDue" class="form-control"></div>
          <div class="col-12 col-md-6" id="modalDesignerWrap"><label class="form-label">Designer (optional)</label>
            <select id="modalDesigner" class="form-select">
              <option value="">— Assign later / auto —</option>
              <?php foreach ($designers as $d): ?>
                <option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="col-12 col-md-6"><label class="form-label">Special instructions</label>
            <input id="modalInstructions" class="form-control"></div>
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
window.KP_ALREADY_PAID = <?= (float)$order['paid_amount'] ?>;
window.KP_NAME_SUGGESTIONS = <?= json_encode($nameSuggestions ?? [], JSON_UNESCAPED_UNICODE) ?>;
window.KP_EDIT_ITEMS = <?= json_encode(array_map(function ($oi) {
    $spec = json_decode((string)($oi['spec_json'] ?? '[]'), true);
    return [
        'id' => (int)$oi['id'],
        'category_id' => (int)($oi['category_id'] ?? 0),
        'category_name' => (string)$oi['category_name_snapshot'],
        'calc_mode' => (string)($oi['calc_mode'] ?? 'simple'),
        'item_name' => (string)$oi['item_name_snapshot'],
        'qty' => (float)$oi['qty'],
        'width_ft' => $oi['width_ft'] !== null ? (float)$oi['width_ft'] : null,
        'height_ft' => $oi['height_ft'] !== null ? (float)$oi['height_ft'] : null,
        'total_sqft' => $oi['total_sqft'] !== null ? (float)$oi['total_sqft'] : null,
        'rate' => (float)$oi['rate'],
        'tax_percent' => (float)$oi['tax_percent'],
        'amount' => (float)$oi['amount'],
        'spec' => is_array($spec) ? $spec : [],
        'spec_text' => (string)$oi['spec_text'],
        'requires_design' => (int)$oi['requires_design'],
        'due_date' => $oi['due_date'] ? date('Y-m-d\TH:i', strtotime((string)$oi['due_date'])) : '',
        'designer_id' => $oi['assigned_designer_id'] ? (int)$oi['assigned_designer_id'] : null,
        'special_instructions' => (string)($oi['special_instructions'] ?? ''),
    ];
}, array_values(array_filter($items, fn($oi) => $oi['status'] !== 'cancelled'))), JSON_UNESCAPED_UNICODE) ?>;
</script>
