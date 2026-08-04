/* Krishna Print — admin panel JS */
(function () {
  'use strict';

  // ---------------------------------------------------------------- theme
  const themeBtn = document.getElementById('themeToggle');
  const applyTheme = t => {
    document.documentElement.setAttribute('data-bs-theme', t);
    if (themeBtn) themeBtn.innerHTML = t === 'dark' ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon"></i>';
  };
  applyTheme(localStorage.getItem('kp-theme') || 'light');
  themeBtn && themeBtn.addEventListener('click', () => {
    const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    localStorage.setItem('kp-theme', next);
    applyTheme(next);
  });

  // ---------------------------------------------------------------- helpers
  window.kpToast = function (type, message) {
    const div = document.createElement('div');
    div.className = 'alert alert-' + type + ' position-fixed top-0 start-50 translate-middle-x mt-3 shadow';
    div.style.zIndex = 2000;
    div.textContent = message;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 4000);
  };

  window.kpFetch = async function (url, options = {}) {
    options.headers = Object.assign({
      'X-CSRF-Token': window.KP.csrf,
      'X-Requested-With': 'XMLHttpRequest'
    }, options.headers || {});
    const res = await fetch(url, options);
    return res.json();
  };

  document.addEventListener('submit', e => {
    const form = e.target;
    if (form.matches('[data-confirm]') && !confirm(form.getAttribute('data-confirm'))) {
      e.preventDefault();
    }
  });

  // ---------------------------------------------------------------- global search
  const search = document.getElementById('globalSearch');
  const results = document.getElementById('globalSearchResults');
  let searchTimer;
  if (search) {
    search.addEventListener('input', () => {
      clearTimeout(searchTimer);
      const q = search.value.trim();
      if (q.length < 2) { results.classList.remove('show'); return; }
      searchTimer = setTimeout(async () => {
        const data = await kpFetch(window.KP.adminUrl + '/api/search?q=' + encodeURIComponent(q));
        results.innerHTML = '';
        (data.results || []).forEach(r => {
          const a = document.createElement('a');
          a.className = 'dropdown-item';
          a.href = r.url;
          a.textContent = r.label + ' [' + r.status + ']';
          results.appendChild(a);
        });
        if (!data.results || !data.results.length) {
          results.innerHTML = '<span class="dropdown-item text-muted">No matches</span>';
        }
        results.classList.add('show');
      }, 250);
    });
    document.addEventListener('click', e => {
      if (!search.contains(e.target)) results.classList.remove('show');
    });
  }

  // ---------------------------------------------------------------- order create / edit page
  const orderForm = document.getElementById('orderCreateForm') || document.getElementById('orderEditForm');
  if (!orderForm) return;
  const isEdit = orderForm.id === 'orderEditForm';

  const state = { items: [], customerId: null };
  const money = n => '₹' + (Math.round(n * 100) / 100).toLocaleString('en-IN', { minimumFractionDigits: 2 });
  const esc = s => String(s == null ? '' : s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };

  // On the edit page, seed the basket with the order's current lines (each carries its DB id).
  if (isEdit && Array.isArray(window.KP_EDIT_ITEMS)) {
    state.items = window.KP_EDIT_ITEMS.map(l => Object.assign({}, l));
  }

  // Strip formatting (+91, leading 0, spaces, dashes) down to the 10-digit local number.
  const localPhone = raw => {
    let d = String(raw || '').replace(/\D/g, '').replace(/^0+/, ''); // drop leading zeros first
    if (d.length === 12 && d.startsWith('91')) d = d.slice(2);       // +91XXXXXXXXXX
    return d.length === 10 ? d : null;
  };

  // Customer lookup once we have a clean 10-digit number (create page only)
  const phoneInput = document.getElementById('customer_phone');
  const newCustomerFields = document.getElementById('newCustomerFields');
  const customerBadge = document.getElementById('customerBadge');
  if (phoneInput) phoneInput.addEventListener('input', async () => {
    const digits = localPhone(phoneInput.value);
    if (!digits) return;
    const data = await kpFetch(window.KP.adminUrl + '/api/customer-lookup?phone=' + digits);
    if (data.found) {
      state.customerId = data.customer.id;
      document.getElementById('customer_id').value = data.customer.id;
      document.getElementById('customer_name').value = data.customer.name;
      document.getElementById('customer_address').value = data.customer.address || '';
      document.getElementById('customer_city').value = data.customer.city || '';
      document.getElementById('customer_gstin').value = data.customer.gstin || '';
      customerBadge.innerHTML = data.customer.is_blocked == 1
        ? '<span class="badge bg-danger">BLOCKED customer</span>'
        : '<span class="badge bg-success">' + data.order_count + ' past orders</span> ' +
          (data.outstanding > 0 ? '<span class="badge bg-warning text-dark">Outstanding ' + money(data.outstanding) + '</span>' : '');
      newCustomerFields.classList.remove('d-none');
    } else {
      state.customerId = null;
      document.getElementById('customer_id').value = '';
      customerBadge.innerHTML = '<span class="badge bg-info">New customer — enter details</span>';
      newCustomerFields.classList.remove('d-none');
    }
  });

  // Item modal: category → item → options
  const itemModalEl = document.getElementById('itemModal');
  const itemModal = new bootstrap.Modal(itemModalEl);
  const categorySelect = document.getElementById('modalCategory');
  const itemSelect = document.getElementById('modalItem');
  const optionsBox = document.getElementById('modalOptions');
  let currentItem = null;
  let editIndex = null;

  categorySelect.addEventListener('change', () => {
    const catId = categorySelect.value;
    itemSelect.innerHTML = '<option value="">— Select item —</option>';
    (window.KP_ITEMS || []).filter(i => i.category_id == catId).forEach(i => {
      const opt = document.createElement('option');
      opt.value = i.id;
      opt.textContent = i.name + ' (' + money(parseFloat(i.base_price)) + '/' + i.unit + ')';
      itemSelect.appendChild(opt);
    });
    optionsBox.innerHTML = '';
    currentItem = null;
  });

  itemSelect.addEventListener('change', async () => {
    if (!itemSelect.value) { optionsBox.innerHTML = ''; currentItem = null; return; }
    optionsBox.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> Loading options…</div>';
    const data = await kpFetch(window.KP.adminUrl + '/api/item-options/' + itemSelect.value);
    if (!data.ok) { optionsBox.innerHTML = '<div class="alert alert-danger">Could not load item.</div>'; return; }
    currentItem = data.item;
    optionsBox.innerHTML = data.html;
    document.getElementById('modalQty').value = currentItem.min_qty;
    document.getElementById('modalQty').min = currentItem.min_qty;
    document.getElementById('modalQty').step = currentItem.step_qty;
    const due = new Date(Date.now() + currentItem.turnaround_hours * 3600000);
    document.getElementById('modalDue').value = due.toISOString().slice(0, 16);
    document.getElementById('modalDesignerWrap').style.display = currentItem.requires_design == 1 ? '' : 'none';
    document.getElementById('modalFileWrap').style.display = currentItem.allow_upload == 1 ? '' : 'none';
    recalcModal();
  });

  function collectSpec() {
    const spec = {};
    optionsBox.querySelectorAll('[data-spec-key]').forEach(el => {
      const key = el.getAttribute('data-spec-key');
      if (el.type === 'radio') { if (el.checked) spec[key] = el.value; }
      else if (el.type === 'checkbox') { if (el.checked) { (spec[key] = spec[key] || []).push(el.value); } }
      else if (el.type === 'file') { /* handled separately */ }
      else if (el.value !== '') spec[key] = el.value;
    });
    return spec;
  }

  async function recalcModal() {
    if (!currentItem) return;
    const qty = parseFloat(document.getElementById('modalQty').value) || currentItem.min_qty;
    const data = await kpFetch(window.KP.adminUrl + '/api/calc-price', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ item_id: currentItem.id, qty: qty, spec: collectSpec(), customer_id: state.customerId })
    });
    if (data.ok) {
      const rateField = document.getElementById('modalRate');
      if (!rateField.dataset.touched) rateField.value = data.rate.toFixed(2);
      const rate = parseFloat(rateField.value) || data.rate;
      const amount = currentItem.pricing_type === 'fixed' ? rate : rate * data.billed_qty;
      document.getElementById('modalAmount').textContent = money(amount) +
        (data.tax_percent > 0 ? ' + ' + data.tax_percent + '% tax' : '');
      document.getElementById('modalAmount').dataset.billedQty = data.billed_qty;
      document.getElementById('modalAmount').dataset.specText = data.spec_text;
    }
  }
  optionsBox.addEventListener('change', recalcModal);
  optionsBox.addEventListener('input', recalcModal);
  document.getElementById('modalQty').addEventListener('input', recalcModal);
  document.getElementById('modalRate').addEventListener('input', function () { this.dataset.touched = '1'; recalcModal(); });

  document.getElementById('modalAdd').addEventListener('click', () => {
    if (!currentItem) { kpToast('danger', 'Pick an item first.'); return; }
    // Required-option validation
    let missing = null;
    optionsBox.querySelectorAll('[data-required="1"]').forEach(el => {
      const key = el.getAttribute('data-spec-key');
      const spec = collectSpec();
      if (!(key in spec) && el.type !== 'file') missing = el.closest('.mb-3')?.querySelector('label')?.textContent || key;
    });
    if (missing) { kpToast('danger', 'Please fill: ' + missing); return; }

    const qty = parseFloat(document.getElementById('modalQty').value) || currentItem.min_qty;
    const rate = parseFloat(document.getElementById('modalRate').value) || 0;
    const amountEl = document.getElementById('modalAmount');
    const billedQty = parseFloat(amountEl.dataset.billedQty || qty);
    const line = {
      item_id: currentItem.id,
      name: currentItem.name,
      qty: qty,
      rate: rate,
      fixed: currentItem.pricing_type === 'fixed',
      spec: collectSpec(),
      spec_text: amountEl.dataset.specText || '',
      amount: currentItem.pricing_type === 'fixed' ? rate : rate * billedQty,
      tax_percent: parseFloat(currentItem.tax_percent || 0),
      due_date: document.getElementById('modalDue').value,
      designer_id: document.getElementById('modalDesigner').value || null,
      special_instructions: document.getElementById('modalInstructions').value,
      requires_design: currentItem.requires_design
    };
    // Per-item file: move the modal file input into the form (renamed by index). Create page only.
    const fileInput = document.getElementById('modalFile');
    const idx = editIndex !== null ? editIndex : state.items.length;
    if (fileInput && fileInput.files.length) {
      const clone = fileInput.cloneNode(true);
      clone.name = 'item_file_' + idx;
      clone.classList.add('d-none');
      clone.id = '';
      orderForm.appendChild(clone);
    }
    if (editIndex !== null) { state.items[editIndex] = line; editIndex = null; }
    else state.items.push(line);
    if (fileInput) fileInput.value = '';
    renderItems();
    itemModal.hide();
  });

  itemModalEl.addEventListener('hidden.bs.modal', () => {
    editIndex = null;
    document.getElementById('modalRate').dataset.touched = '';
    document.getElementById('modalInstructions').value = '';
  });

  window.kpRemoveItem = function (i) { state.items.splice(i, 1); renderItems(); };

  function renderItems() {
    const tbody = document.getElementById('itemsBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!state.items.length) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-muted small text-center py-3">No items yet — tap “Add Item”.</td></tr>';
      recalcTotals();
      return;
    }
    state.items.forEach((line, i) => {
      const design = line.requires_design == 1 ? ' <span class="badge bg-info badge-status">Design</span>' : '';
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td data-label="Item"><strong>' + esc(line.name) + '</strong>' + design +
          (line.spec_text ? '<br><small class="text-muted">' + esc(line.spec_text) + '</small>' : '') + '</td>' +
        '<td data-label="Qty"><input type="number" step="0.01" min="0" class="form-control form-control-sm kp-qty" data-i="' + i + '" value="' + line.qty + '"></td>' +
        '<td data-label="Rate"><input type="number" step="0.01" min="0" class="form-control form-control-sm kp-rate" data-i="' + i + '" value="' + line.rate + '"></td>' +
        '<td data-label="Amount" class="kp-amt fw-semibold">' + money(line.amount) + '</td>' +
        '<td data-label=""><button type="button" class="btn btn-sm btn-outline-danger" data-rm="' + i + '"><i class="bi bi-trash"></i></button></td>';
      tbody.appendChild(tr);
    });
    recalcTotals();
  }

  // Inline qty/rate editing + row removal (event delegation)
  const itemsBody = document.getElementById('itemsBody');
  if (itemsBody) {
    itemsBody.addEventListener('input', e => {
      const field = e.target.closest('.kp-qty, .kp-rate');
      if (!field) return;
      const i = parseInt(field.dataset.i, 10);
      const line = state.items[i];
      if (!line) return;
      const qEl = itemsBody.querySelector('.kp-qty[data-i="' + i + '"]');
      const rEl = itemsBody.querySelector('.kp-rate[data-i="' + i + '"]');
      line.qty = parseFloat(qEl.value) || 0;
      line.rate = parseFloat(rEl.value) || 0;
      line.amount = line.fixed ? line.rate : line.rate * line.qty;
      const amtCell = field.closest('tr').querySelector('.kp-amt');
      if (amtCell) amtCell.textContent = money(line.amount);
      recalcTotals();
    });
    itemsBody.addEventListener('click', e => {
      const btn = e.target.closest('[data-rm]');
      if (!btn) return;
      state.items.splice(parseInt(btn.dataset.rm, 10), 1);
      renderItems();
    });
  }

  function recalcTotals() {
    const subtotal = state.items.reduce((s, l) => s + (parseFloat(l.amount) || 0), 0);
    const tax = state.items.reduce((s, l) => s + (parseFloat(l.amount) || 0) * (l.tax_percent || 0) / 100, 0);
    const dt = document.getElementById('discount_type');
    const dv = document.getElementById('discount_value');
    const discType = dt ? dt.value : '';
    const discValue = dv ? (parseFloat(dv.value) || 0) : 0;
    const discount = discType === 'percent' ? subtotal * discValue / 100 : (discType === 'flat' ? Math.min(discValue, subtotal) : 0);
    const dc = document.getElementById('delivery_charge');
    const delivery = dc ? (parseFloat(dc.value) || 0) : 0;
    const total = Math.round(subtotal - discount + tax + delivery);
    setText('sumSubtotal', money(subtotal));
    setText('sumDiscount', '− ' + money(discount));
    setText('sumTax', money(tax));
    setText('sumTotal', money(total));
    const adv = document.getElementById('advance_amount');
    if (adv) setText('sumBalance', money(total - (parseFloat(adv.value) || 0)));
  }
  ['discount_type', 'discount_value', 'delivery_charge', 'advance_amount'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', recalcTotals);
    el.addEventListener('change', recalcTotals);
  });

  renderItems(); // initial paint (seeded lines on edit, empty state on create)

  // WhatsApp Yes/No popup before the order is actually saved
  const waModalEl = document.getElementById('waConfirmModal');
  const waModal = waModalEl ? new bootstrap.Modal(waModalEl) : null;
  let waConfirmed = false;

  const submitOrder = notify => {
    document.getElementById('notify_customer').value = notify ? '1' : '0';
    document.getElementById('items_json').value = JSON.stringify(state.items);
    const btn = orderForm.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving…';
    waConfirmed = true;
    orderForm.submit();
  };

  orderForm.addEventListener('submit', e => {
    if (!state.items.length) {
      e.preventDefault();
      kpToast('danger', 'Add at least one item.');
      return;
    }
    if (isEdit) {
      // No WhatsApp popup on edit — just persist the reconciled item set.
      document.getElementById('items_json').value = JSON.stringify(state.items);
      const btn = orderForm.querySelector('button[type=submit]');
      if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving…'; }
      return;
    }
    if (waConfirmed || !waModal) return; // already answered — let it through
    e.preventDefault();
    waModal.show();
  });

  if (waModal) {
    document.getElementById('waYes').addEventListener('click', () => { waModal.hide(); submitOrder(true); });
    document.getElementById('waNo').addEventListener('click', () => { waModal.hide(); submitOrder(false); });
  }

  // Enter in the qty field adds the item (keyboard-friendly entry)
  document.getElementById('modalQty').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('modalAdd').click(); }
  });
})();
