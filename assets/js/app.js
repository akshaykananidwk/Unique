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

  // ---------------------------------------------------------------- order create page
  const orderForm = document.getElementById('orderCreateForm');
  if (!orderForm) return;

  const state = { items: [], customerId: null };
  const money = n => '₹' + (Math.round(n * 100) / 100).toLocaleString('en-IN', { minimumFractionDigits: 2 });

  // Customer lookup on 10 digits
  const phoneInput = document.getElementById('customer_phone');
  const newCustomerFields = document.getElementById('newCustomerFields');
  const customerBadge = document.getElementById('customerBadge');
  phoneInput.addEventListener('input', async () => {
    const digits = phoneInput.value.replace(/\D/g, '');
    if (digits.length !== 10) return;
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
      spec: collectSpec(),
      spec_text: amountEl.dataset.specText || '',
      amount: currentItem.pricing_type === 'fixed' ? rate : rate * billedQty,
      tax_percent: parseFloat(currentItem.tax_percent || 0),
      due_date: document.getElementById('modalDue').value,
      designer_id: document.getElementById('modalDesigner').value || null,
      special_instructions: document.getElementById('modalInstructions').value,
      requires_design: currentItem.requires_design
    };
    // Per-item file: move the modal file input into the form (renamed by index)
    const fileInput = document.getElementById('modalFile');
    const idx = editIndex !== null ? editIndex : state.items.length;
    if (fileInput.files.length) {
      const clone = fileInput.cloneNode(true);
      clone.name = 'item_file_' + idx;
      clone.classList.add('d-none');
      clone.id = '';
      orderForm.appendChild(clone);
    }
    if (editIndex !== null) { state.items[editIndex] = line; editIndex = null; }
    else state.items.push(line);
    fileInput.value = '';
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
    tbody.innerHTML = '';
    state.items.forEach((line, i) => {
      const tr = document.createElement('tr');
      tr.innerHTML = '<td data-label="Item"><strong>' + line.name + '</strong><br><small class="text-muted">' +
        (line.spec_text || '') + '</small>' +
        (line.requires_design == 1 ? ' <span class="badge bg-info badge-status">Design</span>' : '') + '</td>' +
        '<td data-label="Qty">' + line.qty + '</td>' +
        '<td data-label="Rate">' + money(line.rate) + '</td>' +
        '<td data-label="Amount">' + money(line.amount) + '</td>' +
        '<td data-label=""><button type="button" class="btn btn-sm btn-outline-danger" onclick="kpRemoveItem(' + i + ')"><i class="bi bi-trash"></i></button></td>';
      tbody.appendChild(tr);
    });
    recalcTotals();
  }

  function recalcTotals() {
    const subtotal = state.items.reduce((s, l) => s + l.amount, 0);
    const tax = state.items.reduce((s, l) => s + l.amount * (l.tax_percent || 0) / 100, 0);
    const discType = document.getElementById('discount_type').value;
    const discValue = parseFloat(document.getElementById('discount_value').value) || 0;
    const discount = discType === 'percent' ? subtotal * discValue / 100 : (discType === 'flat' ? Math.min(discValue, subtotal) : 0);
    const delivery = parseFloat(document.getElementById('delivery_charge').value) || 0;
    const total = Math.round(subtotal - discount + tax + delivery);
    const advance = parseFloat(document.getElementById('advance_amount').value) || 0;
    document.getElementById('sumSubtotal').textContent = money(subtotal);
    document.getElementById('sumDiscount').textContent = '− ' + money(discount);
    document.getElementById('sumTax').textContent = money(tax);
    document.getElementById('sumTotal').textContent = money(total);
    document.getElementById('sumBalance').textContent = money(total - advance);
  }
  ['discount_type', 'discount_value', 'delivery_charge', 'advance_amount'].forEach(id => {
    document.getElementById(id).addEventListener('input', recalcTotals);
    document.getElementById(id).addEventListener('change', recalcTotals);
  });

  orderForm.addEventListener('submit', e => {
    if (!state.items.length) {
      e.preventDefault();
      kpToast('danger', 'Add at least one item.');
      return;
    }
    document.getElementById('items_json').value = JSON.stringify(state.items);
    const btn = orderForm.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving…';
  });

  // Enter in the qty field adds the item (keyboard-friendly entry)
  document.getElementById('modalQty').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('modalAdd').click(); }
  });
})();
