<?php use App\Core\Csrf; $title = 'New Order'; ?>
<h4 class="mb-3">New Order</h4>
<form id="orderCreateForm" method="post" action="<?= e(admin_url('orders')) ?>" enctype="multipart/form-data">
  <?= Csrf::field() ?>
  <input type="hidden" name="items_json" id="items_json">
  <input type="hidden" name="customer_id" id="customer_id">
  <input type="hidden" name="notify_customer" id="notify_customer" value="1">

  <!-- Step 1: Customer -->
  <div class="card mb-3"><div class="card-body">
    <h6 class="text-uppercase text-muted small">Step 1 — Customer</h6>
    <div class="row g-2">
      <div class="col-md-3">
        <label class="form-label">Mobile Number *</label>
        <input type="tel" id="customer_phone" name="customer_phone" class="form-control"
               inputmode="tel" required placeholder="e.g. 98765 43210 / +91…" autofocus>
        <div class="form-text">Spaces, +91 or a leading 0 are fine — we keep the 10-digit number automatically.</div>
        <div id="customerBadge" class="mt-1 small"></div>
      </div>
      <?php if (count($branches) > 1): ?>
      <div class="col-md-3">
        <label class="form-label">Branch *</label>
        <select name="branch_id" class="form-select" required>
          <?php foreach ($branches as $b): ?>
            <option value="<?= (int)$b['id'] ?>" <?= (int)$user['primary_branch_id'] === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php else: ?>
        <input type="hidden" name="branch_id" value="<?= (int)($branches[0]['id'] ?? 0) ?>">
      <?php endif; ?>
      <div class="col-md-3">
        <label class="form-label">Job No</label>
        <input name="job_no" class="form-control" placeholder="Leave blank — auto">
        <div class="form-text">Type your own (e.g. to match a GST bill) or leave blank.</div>
      </div>
      <div class="col-md-3">
        <label class="form-label">Order Date</label>
        <input type="datetime-local" name="order_date" class="form-control" value="<?= e(date('Y-m-d\TH:i')) ?>">
        <div class="form-text">Change it to enter an older order.</div>
      </div>
    </div>
    <div id="newCustomerFields" class="row g-2 mt-1 d-none">
      <div class="col-md-3"><label class="form-label">Name *</label><input id="customer_name" name="customer_name" class="form-control"></div>
      <div class="col-md-3"><label class="form-label">Address</label><input id="customer_address" name="customer_address" class="form-control"></div>
      <div class="col-md-2"><label class="form-label">City</label><input id="customer_city" name="customer_city" class="form-control"></div>
      <div class="col-md-2"><label class="form-label">WhatsApp</label><input name="customer_whatsapp" class="form-control" inputmode="tel" placeholder="Same as phone"></div>
      <div class="col-md-2"><label class="form-label">GSTIN</label><input id="customer_gstin" name="customer_gstin" class="form-control"></div>
    </div>
  </div></div>

  <!-- Step 2: Items -->
  <div class="card mb-3"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center">
      <h6 class="text-uppercase text-muted small mb-0">Step 2 — Items</h6>
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal"><i class="bi bi-plus-lg"></i> Add Item</button>
    </div>
    <div class="table-responsive mt-2">
      <table class="table table-sm align-middle table-mobile">
        <thead><tr><th>Item</th><th>Qty</th><th>Rate</th><th>Amount</th><th></th></tr></thead>
        <tbody id="itemsBody"><tr><td colspan="5" class="text-muted small text-center py-3">No items added yet — tap “Add Item”.</td></tr></tbody>
      </table>
    </div>
  </div></div>

  <!-- Step 3: Summary & Payment -->
  <div class="card mb-3"><div class="card-body">
    <h6 class="text-uppercase text-muted small">Step 3 — Summary &amp; Payment</h6>
    <div class="row g-3">
      <div class="col-md-6">
        <div class="row g-2">
          <div class="col-6"><label class="form-label">Discount Type</label>
            <select id="discount_type" name="discount_type" class="form-select">
              <option value="">None</option><option value="flat">Flat ₹</option><option value="percent">Percent %</option>
            </select></div>
          <div class="col-6"><label class="form-label">Discount Value</label>
            <input type="number" step="0.01" min="0" id="discount_value" name="discount_value" class="form-control" value="0"></div>
          <div class="col-6"><label class="form-label">Delivery Charge</label>
            <input type="number" step="0.01" min="0" id="delivery_charge" name="delivery_charge" class="form-control" value="0"></div>
          <div class="col-6"><label class="form-label">Priority</label>
            <select name="priority" class="form-select">
              <option value="normal">Normal</option><option value="urgent">Urgent</option><option value="rush">Rush</option>
            </select></div>
          <div class="col-6"><label class="form-label">Delivery Type</label>
            <select name="delivery_type" class="form-select" id="delivery_type">
              <option value="pickup">Pickup</option><option value="delivery">Delivery</option>
            </select></div>
          <div class="col-6"><label class="form-label">Delivery Address</label>
            <input name="delivery_address" class="form-control" placeholder="If delivery"></div>
          <div class="col-12"><label class="form-label">Order Note (shown to customer)</label>
            <input name="customer_note" class="form-control"></div>
          <div class="col-12"><label class="form-label">Internal Note (staff only)</label>
            <input name="internal_note" class="form-control"></div>
        </div>
      </div>
      <div class="col-md-6">
        <table class="table table-sm">
          <tr><td>Subtotal</td><td class="text-end" id="sumSubtotal">₹0.00</td></tr>
          <tr><td>Discount</td><td class="text-end" id="sumDiscount">− ₹0.00</td></tr>
          <tr><td>Tax</td><td class="text-end" id="sumTax">₹0.00</td></tr>
          <tr class="fw-bold fs-5"><td>Total</td><td class="text-end" id="sumTotal">₹0.00</td></tr>
        </table>
        <div class="row g-2">
          <div class="col-4"><label class="form-label">Advance ₹</label>
            <input type="number" step="0.01" min="0" id="advance_amount" name="advance_amount" class="form-control" value="0"></div>
          <div class="col-4"><label class="form-label">Mode</label>
            <select name="advance_mode" class="form-select">
              <option value="cash">Cash</option><option value="upi">UPI</option><option value="card">Card</option>
              <option value="bank">Bank</option><option value="cheque">Cheque</option>
            </select></div>
          <div class="col-4"><label class="form-label">Reference</label>
            <input name="advance_reference" class="form-control" placeholder="UPI ref / cheque no"></div>
        </div>
        <div class="alert alert-warning mt-3 mb-0 d-flex justify-content-between fs-5">
          <span>Balance Due</span><strong id="sumBalance">₹0.00</strong>
        </div>
      </div>
    </div>
  </div></div>

  <div class="sticky-actions d-flex gap-2">
    <button type="submit" class="btn btn-primary btn-lg flex-grow-1"><i class="bi bi-check-lg"></i> Save Order</button>
    <a href="<?= e(admin_url('orders')) ?>" class="btn btn-outline-secondary btn-lg">Cancel</a>
  </div>
</form>

<!-- Add Item modal -->
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
          <div class="col-12" id="modalFileWrap"><label class="form-label">Customer artwork / reference (optional)</label>
            <input type="file" id="modalFile" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf,.cdr,.ai,.psd,.zip"></div>
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

<!-- WhatsApp confirmation choice on save -->
<div class="modal fade" id="waConfirmModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title"><i class="bi bi-whatsapp text-success"></i> Send WhatsApp to customer?</h5>
      </div>
      <div class="modal-body">
        <p class="mb-1">Send the order-confirmation WhatsApp message to this customer now?</p>
        <p class="text-muted small mb-0">Choose <strong>No</strong> to save the order silently — staff and designers are still notified either way.</p>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-outline-secondary" id="waNo"><i class="bi bi-x-lg"></i> No, don’t send</button>
        <button type="button" class="btn btn-success" id="waYes"><i class="bi bi-whatsapp"></i> Yes, send it</button>
      </div>
    </div>
  </div>
</div>

<script>window.KP_ITEMS = <?= json_encode(array_map(fn($i) => [
    'id' => (int)$i['id'], 'category_id' => (int)$i['category_id'], 'name' => $i['name'],
    'unit' => $i['unit'], 'base_price' => $i['base_price'], 'pricing_type' => $i['pricing_type'], 'min_qty' => (int)$i['min_qty'],
], $items), JSON_UNESCAPED_UNICODE) ?>;</script>
