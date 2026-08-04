<?php use App\Core\Csrf; $title = 'New Order'; ?>
<h4 class="mb-3">New Order</h4>
<form id="orderCreateForm" method="post" action="<?= e(admin_url('orders')) ?>" enctype="multipart/form-data">
  <?= Csrf::field() ?>
  <input type="hidden" name="items_json" id="items_json">
  <input type="hidden" name="customer_id" id="customer_id">
  <input type="hidden" name="notify_customer" id="notify_customer" value="1">
  <input type="hidden" name="branch_id" value="<?= (int)($branches[0]['id'] ?? 0) ?>">

  <!-- Step 1: Customer -->
  <div class="card mb-3"><div class="card-body">
    <h6 class="text-uppercase text-muted small">Step 1 — Customer</h6>
    <div class="row g-2">
      <div class="col-md-3">
        <label class="form-label">Mobile Number *</label>
        <input type="tel" id="customer_phone" name="customer_phone" class="form-control"
               inputmode="tel" required placeholder="e.g. 98765 43210 / +91…" autofocus>
        <div class="form-text">Spaces, +91 or a leading 0 are fine.</div>
        <div id="customerBadge" class="mt-1 small"></div>
      </div>
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
      <div class="col-md-3">
        <label class="form-label">Accepted By</label>
        <select name="accepted_by_user_id" class="form-select">
          <option value="">— <?= e($user['name']) ?> (me) —</option>
          <?php foreach ($staff as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Who accepted this order.</div>
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
      <table class="table table-sm align-middle table-mobile kp-lines">
        <thead><tr>
          <th style="min-width:190px">Item</th>
          <th style="width:90px">Qty</th>
          <th style="width:90px">Width ft</th>
          <th style="width:90px">Height ft</th>
          <th style="width:95px">Sq. Ft.</th>
          <th style="width:105px">Rate ₹</th>
          <th style="width:85px">GST %</th>
          <th style="width:120px" class="text-end">Amount</th>
          <th style="width:44px"></th>
        </tr></thead>
        <tbody id="itemsBody"></tbody>
      </table>
    </div>
    <div class="form-text">Foot × foot items: Qty × Width × Height = Sq. Ft., then × Rate = Amount. Everything recalculates as you type.</div>
  </div></div>

  <!-- Step 3: Files -->
  <div class="card mb-3"><div class="card-body">
    <h6 class="text-uppercase text-muted small">Step 3 — Reference Files <span class="text-muted">(optional)</span></h6>
    <input type="file" name="reference_files[]" id="referenceFiles" class="form-control" multiple
           accept=".pdf,.jpg,.jpeg,.png,.webp,.cdr,.ai,.psd,.eps,.zip,.rar">
    <div class="form-text">PDF, JPG, PNG, CDR, AI, PSD, EPS, ZIP, RAR — pick as many as you like.</div>
    <div id="fileList" class="small mt-2"></div>
  </div></div>

  <!-- Step 4: Summary & Payment -->
  <div class="card mb-3"><div class="card-body">
    <h6 class="text-uppercase text-muted small">Step 4 — Summary &amp; Payment</h6>
    <div class="row g-3">
      <div class="col-md-6">
        <div class="row g-2">
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
          <tr><td>GST</td><td class="text-end" id="sumTax">₹0.00</td></tr>
          <tr><td>Delivery</td><td class="text-end" id="sumDelivery">₹0.00</td></tr>
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
      <div class="modal-header"><h5 class="modal-title" id="itemModalTitle">Add Item</h5>
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
            <datalist id="itemNameSuggest"></datalist>
            <div class="form-text">Type any name — you are not limited to a list.</div></div>
        </div>
        <div class="row g-2 mb-2 kp-component-wrap" style="display:none">
          <div class="col-md-7"><label class="form-label">Component</label>
            <select id="modalComponent" class="form-select"></select>
            <div class="form-text">Pick a ready component, or choose Custom and type any name.</div></div>
          <div class="col-md-5"><label class="form-label">Unit</label>
            <input id="modalUnit" class="form-control" placeholder="pcs / sqft / ft / mtr / job"></div>
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
          <div class="col-4 col-md-2"><label class="form-label">GST %</label>
            <input type="number" step="any" min="0" max="100" id="modalGst" class="form-control" value="0"
                   title="Optional — leave 0 for no GST"></div>
          <div class="col-4 col-md-2"><label class="form-label">Due Date</label>
            <input type="datetime-local" id="modalDue" class="form-control"></div>
          <div class="col-12 col-md-6" id="modalDesignerWrap"><label class="form-label">Designer (optional)</label>
            <select id="modalDesigner" class="form-select">
              <option value="">— Assign later / auto —</option>
              <?php foreach ($designers as $d): ?>
                <option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?> (<?= (int)$d['open_jobs'] ?> open)</option>
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

<script>window.KP_NAME_SUGGESTIONS = <?= json_encode($nameSuggestions ?? [], JSON_UNESCAPED_UNICODE) ?>;</script>
