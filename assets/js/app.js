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

  // "Add row" on the category component editor — clone the last row, blanked.
  document.addEventListener('click', e => {
    const btn = e.target.closest('.kp-comp-add');
    if (!btn) return;
    const wrap = btn.closest('form').querySelector('.kp-comp-rows');
    const last = wrap.querySelector('.kp-comp-row:last-child');
    const row = last.cloneNode(true);
    row.querySelectorAll('input').forEach(i => { i.value = ''; });
    wrap.appendChild(row);
    row.querySelector('input').focus();
  });

  // Selects that save the moment you pick (e.g. the status column on the orders list).
  // requestSubmit keeps any data-confirm on the form working; plain .submit() would skip it.
  document.addEventListener('change', e => {
    const el = e.target;
    if (!el.matches || !el.matches('[data-auto-submit]') || !el.form) return;
    if (el.dataset.submitting) return;          // guard a double fire; never disable the
    el.dataset.submitting = '1';                // control itself or its value is not posted
    if (el.form.requestSubmit) el.form.requestSubmit();
    else el.form.submit();
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
  // A save that came back with an error puts the lines back exactly as they were typed —
  // an order takes minutes to write up and must never be lost to a missing field. This
  // wins over the seed above, because it is the newer of the two.
  if (Array.isArray(window.KP_OLD_ITEMS) && window.KP_OLD_ITEMS.length) {
    state.items = window.KP_OLD_ITEMS.map(l => Object.assign({}, l));
  }

  // Strip formatting (+91, leading 0, spaces, dashes) down to the 10-digit local number.
  const localPhone = raw => {
    let d = String(raw || '').replace(/\D/g, '').replace(/^0+/, ''); // drop leading zeros first
    if (d.length === 12 && d.startsWith('91')) d = d.slice(2);       // +91XXXXXXXXXX
    return d.length === 10 ? d : null;
  };

  // Customer lookup once we have a clean 10-digit number (create page only).
  // A number belongs to a person, and a person belongs to a company — so a known number
  // fills in both, and an unknown one offers to attach itself to a company we already have.
  const phoneInput = document.getElementById('customer_phone');
  const newCustomerFields = document.getElementById('newCustomerFields');
  const customerBadge = document.getElementById('customerBadge');
  const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v; };

  const clearChosenCompany = () => {
    setVal('customer_id', '');
    const chosen = document.getElementById('companyChosen');
    if (chosen) chosen.innerHTML = '';
  };

  if (phoneInput) phoneInput.addEventListener('input', async () => {
    const digits = localPhone(phoneInput.value);
    if (!digits) return;
    const data = await kpFetch(window.KP.adminUrl + '/api/customer-lookup?phone=' + digits);
    if (data.found) {
      state.customerId = data.customer.id;
      setVal('customer_id', data.customer.id);
      setVal('customer_name', data.customer.name);
      setVal('customer_address', data.customer.address || '');
      setVal('customer_gstin', data.customer.gstin || '');
      if (data.contact && data.contact.name) setVal('contact_name', data.contact.name);
      const person = data.contact ? esc(data.contact.name) : '';
      customerBadge.innerHTML = data.customer.is_blocked == 1
        ? '<span class="badge bg-danger">BLOCKED customer</span>'
        : '<span class="badge bg-primary">' + esc(data.customer.name) + '</span> ' +
          (person ? '<span class="badge bg-secondary">' + person + '</span> ' : '') +
          (data.contact_count > 1 ? '<span class="badge bg-light text-dark">' + data.contact_count + ' contacts</span> ' : '') +
          '<span class="badge bg-success">' + data.order_count + ' past orders</span> ' +
          (data.outstanding > 0 ? '<span class="badge bg-warning text-dark">Outstanding ' + money(data.outstanding) + '</span>' : '');
      // Known number: nothing to fill in, so the new-customer block stays out of the way.
      newCustomerFields.classList.add('d-none');
    } else {
      state.customerId = null;
      clearChosenCompany();
      customerBadge.innerHTML = '<span class="badge bg-info">New number</span>';
      newCustomerFields.classList.remove('d-none');
    }
  });

  // Attach a new number to a company that already exists — the Tata case, where one account
  // has many people. Picking one means we do NOT create a second account for the same firm.
  const companySearch = document.getElementById('companySearch');
  const companyResults = document.getElementById('companyResults');
  if (companySearch) {
    let companyTimer;
    const hideResults = () => companyResults.classList.add('d-none');
    companySearch.addEventListener('input', () => {
      clearTimeout(companyTimer);
      const q = companySearch.value.trim();
      if (q.length < 2) { hideResults(); return; }
      companyTimer = setTimeout(async () => {
        const data = await kpFetch(window.KP.adminUrl + '/api/customer-search?q=' + encodeURIComponent(q));
        const rows = (data && data.results) || [];
        if (!rows.length) {
          companyResults.innerHTML = '<div class="list-group-item small text-muted">No match — fill in the new customer below.</div>';
        } else {
          companyResults.innerHTML = rows.map(r =>
            '<button type="button" class="list-group-item list-group-item-action kp-company" ' +
            'data-id="' + r.id + '" data-name="' + esc(r.name) + '">' + esc(r.name) +
            ' <span class="text-muted small">· ' + r.contact_count + ' contact(s)</span></button>'
          ).join('');
        }
        companyResults.classList.remove('d-none');
      }, 250);
    });
    companyResults.addEventListener('click', e => {
      const btn = e.target.closest('.kp-company');
      if (!btn) return;
      setVal('customer_id', btn.dataset.id);
      setVal('customer_name', btn.dataset.name);
      document.getElementById('companyChosen').innerHTML =
        '<span class="badge bg-primary">' + esc(btn.dataset.name) + '</span> ' +
        '<button type="button" class="btn btn-sm btn-link p-0 align-baseline" id="companyClear">change</button>';
      companySearch.value = '';
      hideResults();
    });
    document.addEventListener('click', e => {
      if (e.target.id === 'companyClear') { clearChosenCompany(); return; }
      if (!companySearch.contains(e.target) && !companyResults.contains(e.target)) hideResults();
    });
  }

  // ================================================================ order lines
  // Mirrors App\Models\OrderCalc exactly. The server recalculates on save, so this is
  // only the live preview — but the formulas are kept identical on purpose.
  const round2 = n => Math.round((Number(n) || 0) * 100) / 100;
  const calcLine = l => {
    const mode = l.calc_mode === 'sqft' ? 'sqft' : 'simple';
    const qty = Math.max(0, round2(l.qty));
    const rate = Math.max(0, round2(l.rate));
    const w = Math.max(0, round2(l.width_ft));
    const h = Math.max(0, round2(l.height_ft));
    const sqft = mode === 'sqft' ? round2(qty * w * h) : null;
    const billed = mode === 'sqft' ? sqft : qty;
    const amount = round2(billed * rate);
    const taxPercent = Math.max(0, Number(l.tax_percent) || 0);
    return { sqft, amount, tax: round2(amount * taxPercent / 100) };
  };

  function recalcTotals() {
    let subtotal = 0, tax = 0;
    state.items.forEach(l => { const c = calcLine(l); l.total_sqft = c.sqft; l.amount = c.amount; l.tax_amount = c.tax; subtotal += c.amount; tax += c.tax; });
    subtotal = round2(subtotal); tax = round2(tax);
    const dc = document.getElementById('delivery_charge');
    const delivery = dc ? Math.max(0, round2(dc.value)) : 0;
    const total = Math.round(subtotal + tax + delivery);
    setText('sumSubtotal', money(subtotal));
    setText('sumTax', money(tax));
    setText('sumDelivery', money(delivery));
    setText('sumTotal', money(total));
    const adv = document.getElementById('advance_amount');
    if (adv) setText('sumBalance', money(total - (round2(adv.value) || 0)));
    const paid = Number(window.KP_ALREADY_PAID || 0);
    setText('sumDue', money(total - paid));
  }

  function renderItems() {
    const tbody = document.getElementById('itemsBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!state.items.length) {
      tbody.innerHTML = '<tr><td colspan="9" class="text-muted small text-center py-3">No items yet — tap “Add Item”.</td></tr>';
      recalcTotals();
      return;
    }
    state.items.forEach((line, i) => {
      const sq = line.calc_mode === 'sqft';
      const num = (cls, val, ph) =>
        '<input type="number" step="any" min="0" class="form-control form-control-sm ' + cls + '" data-i="' + i + '" value="' + (val == null ? '' : val) + '"' + (ph ? ' placeholder="' + ph + '"' : '') + '>';
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td data-label="Item"><strong>' + esc(line.item_name) + '</strong>' +
          (line.requires_design == 1 ? ' <span class="badge bg-info badge-status">Design</span>' : '') +
          '<div class="small text-muted">' + esc(line.category_name || '') +
          (line.spec_text ? ' · ' + esc(line.spec_text) : '') + '</div></td>' +
        '<td data-label="Qty">' + num('kp-qty', line.qty) +
          (line.unit ? '<div class="small text-muted text-center">' + esc(line.unit) + '</div>' : '') + '</td>' +
        '<td data-label="Width ft">' + (sq ? num('kp-w', line.width_ft) : '<span class="text-muted">—</span>') + '</td>' +
        '<td data-label="Height ft">' + (sq ? num('kp-h', line.height_ft) : '<span class="text-muted">—</span>') + '</td>' +
        '<td data-label="Sq. Ft." class="kp-sqft fw-semibold">' + (sq ? (line.total_sqft ?? 0) : '—') + '</td>' +
        '<td data-label="Rate ₹">' + num('kp-rate', line.rate) + '</td>' +
        '<td data-label="GST %">' + num('kp-gst', line.tax_percent) + '</td>' +
        '<td data-label="Amount" class="kp-amt fw-semibold text-end">' + money(line.amount || 0) +
          '<div class="small text-muted kp-gstamt">' + (line.tax_amount ? '+ ' + money(line.tax_amount) + ' GST' : '') + '</div></td>' +
        '<td data-label=""><button type="button" class="btn btn-sm btn-outline-danger" data-rm="' + i + '"><i class="bi bi-trash"></i></button></td>';
      tbody.appendChild(tr);
    });
    recalcTotals();
  }

  // Inline editing of every number on a line — recalculates that row and the totals at once.
  const itemsBody = document.getElementById('itemsBody');
  if (itemsBody) {
    itemsBody.addEventListener('input', e => {
      const field = e.target.closest('.kp-qty, .kp-w, .kp-h, .kp-rate, .kp-gst');
      if (!field) return;
      const i = parseInt(field.dataset.i, 10);
      const line = state.items[i];
      if (!line) return;
      const val = cls => { const el = itemsBody.querySelector('.' + cls + '[data-i="' + i + '"]'); return el ? el.value : 0; };
      line.qty = val('kp-qty');
      line.rate = val('kp-rate');
      line.tax_percent = parseFloat(val('kp-gst')) || 0;
      if (line.calc_mode === 'sqft') { line.width_ft = val('kp-w'); line.height_ft = val('kp-h'); }
      const c = calcLine(line);
      line.total_sqft = c.sqft; line.amount = c.amount; line.tax_amount = c.tax;
      const tr = field.closest('tr');
      const sqCell = tr.querySelector('.kp-sqft');
      if (sqCell && line.calc_mode === 'sqft') sqCell.textContent = c.sqft;
      const amtCell = tr.querySelector('.kp-amt');
      if (amtCell) amtCell.childNodes[0].nodeValue = money(c.amount);
      const gstCell = tr.querySelector('.kp-gstamt');
      if (gstCell) gstCell.textContent = c.tax ? '+ ' + money(c.tax) + ' GST' : '';
      recalcTotals();
    });
    itemsBody.addEventListener('click', e => {
      const btn = e.target.closest('[data-rm]');
      if (!btn) return;
      state.items.splice(parseInt(btn.dataset.rm, 10), 1);
      renderItems();
    });
  }

  ['delivery_charge', 'advance_amount'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', recalcTotals);
    el.addEventListener('change', recalcTotals);
  });

  // ================================================================ Add Item modal
  const itemModalEl = document.getElementById('itemModal');
  const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
  const categorySelect = document.getElementById('modalCategory');
  const nameInput = document.getElementById('modalItemName');
  const optionsBox = document.getElementById('modalOptions');
  const el = id => document.getElementById(id);
  let currentCategory = null;

  (window.KP_NAME_SUGGESTIONS || []).forEach(n => {
    const list = document.getElementById('itemNameSuggest');
    if (!list) return;
    const o = document.createElement('option'); o.value = n; list.appendChild(o);
  });

  const showSqftFields = show => document.querySelectorAll('.kp-sqft-only')
    .forEach(n => { n.style.display = show ? '' : 'none'; });

  // The mode in play right now: the chosen component's, else the category's.
  // 'mixed' categories have no mode of their own — each component decides.
  const componentSelect = document.getElementById('modalComponent');
  const unitInput = document.getElementById('modalUnit');
  let currentComponent = null;
  const activeMode = () => {
    if (currentComponent) return currentComponent.calc_mode;
    if (!currentCategory) return 'simple';
    return currentCategory.calc_mode === 'sqft' ? 'sqft' : 'simple';
  };

  function paintComponents(components) {
    if (!componentSelect) return;
    componentSelect.innerHTML = '<option value="">— Custom (type the name) —</option>';
    (components || []).forEach((c, i) => {
      const o = document.createElement('option');
      o.value = String(i);
      o.textContent = c.name + (c.calc_mode === 'sqft' ? '  (ft × ft)' : '  (' + c.unit + ')');
      componentSelect.appendChild(o);
    });
    componentSelect.closest('.kp-component-wrap').style.display = (components && components.length) ? '' : 'none';
  }

  if (componentSelect) componentSelect.addEventListener('change', () => {
    const list = (currentCategory && currentCategory._components) || [];
    currentComponent = componentSelect.value === '' ? null : list[parseInt(componentSelect.value, 10)];
    if (currentComponent) {
      nameInput.value = currentComponent.name;
      if (unitInput) unitInput.value = currentComponent.unit;
    } else if (unitInput) {
      unitInput.value = '';
    }
    showSqftFields(activeMode() === 'sqft');
    modalCalc();
  });

  function modalCalc() {
    if (!currentCategory) return;
    const line = {
      calc_mode: activeMode(),
      qty: el('modalQty').value, rate: el('modalRate').value,
      width_ft: el('modalWidth').value, height_ft: el('modalHeight').value,
      tax_percent: el('modalGst') ? el('modalGst').value : currentCategory.tax_percent
    };
    const c = calcLine(line);
    if (el('modalSqft')) el('modalSqft').value = c.sqft == null ? '0' : c.sqft;
    setText('modalAmount', money(c.amount));
  }

  if (categorySelect) categorySelect.addEventListener('change', async () => {
    if (!categorySelect.value) { optionsBox.innerHTML = ''; currentCategory = null; showSqftFields(false); return; }
    optionsBox.innerHTML = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm"></div></div>';
    const data = await kpFetch(window.KP.adminUrl + '/api/category-options/' + categorySelect.value);
    if (!data.ok) { optionsBox.innerHTML = '<div class="alert alert-danger">Could not load this category.</div>'; return; }
    currentCategory = data.category;
    currentCategory._components = data.components || [];
    currentComponent = null;
    optionsBox.innerHTML = data.html;
    paintComponents(currentCategory._components);
    // Prefill GST from the category (which already falls back to the shop default).
    if (el('modalGst')) el('modalGst').value = currentCategory.tax_percent || 0;
    showSqftFields(activeMode() === 'sqft');
    const dw = el('modalDesignerWrap');
    if (dw) dw.style.display = currentCategory.requires_design == 1 ? '' : 'none';
    modalCalc();
  });

  ['modalQty', 'modalWidth', 'modalHeight', 'modalRate'].forEach(id => {
    const node = el(id);
    if (node) node.addEventListener('input', modalCalc);
  });

  function collectSpec() {
    const spec = {};
    optionsBox.querySelectorAll('[data-spec-key]').forEach(node => {
      const key = node.getAttribute('data-spec-key');
      if (node.type === 'radio') { if (node.checked) spec[key] = node.value; }
      else if (node.type === 'checkbox') { if (node.checked) (spec[key] = spec[key] || []).push(node.value); }
      else if (node.value !== '') spec[key] = node.value;
    });
    return spec;
  }

  function specSummary(spec) {
    return Object.keys(spec).map(k => {
      const node = optionsBox.querySelector('[data-spec-key="' + k + '"]');
      const label = node ? (node.closest('.mb-1') || node.closest('div')).querySelector('label') : null;
      const name = label ? label.textContent.replace(/\s*\*$/, '').trim() : k;
      return name + ': ' + (Array.isArray(spec[k]) ? spec[k].join(', ') : spec[k]);
    }).join(' | ');
  }

  const addBtn = document.getElementById('modalAdd');
  if (addBtn) addBtn.addEventListener('click', () => {
    if (!currentCategory) { kpToast('danger', 'Pick a category first.'); return; }
    const name = (nameInput.value || '').trim();
    if (!name) { kpToast('danger', 'Type the item name.'); return; }
    let missing = null;
    const spec = collectSpec();
    optionsBox.querySelectorAll('[data-required="1"]').forEach(node => {
      const key = node.getAttribute('data-spec-key');
      if (!(key in spec)) {
        const lab = (node.closest('.mb-1') || node.closest('div')).querySelector('label');
        missing = lab ? lab.textContent.replace(/\s*\*$/, '').trim() : key;
      }
    });
    if (missing) { kpToast('danger', 'Please fill: ' + missing); return; }

    const mode = activeMode();
    const line = {
      category_id: currentCategory.id,
      category_name: currentCategory.name,
      calc_mode: mode,
      unit: unitInput ? (unitInput.value || '').trim() : '',
      item_name: name,
      qty: round2(el('modalQty').value) || 0,
      width_ft: mode === 'sqft' ? round2(el('modalWidth').value) : null,
      height_ft: mode === 'sqft' ? round2(el('modalHeight').value) : null,
      rate: round2(el('modalRate').value) || 0,
      tax_percent: el('modalGst') ? (parseFloat(el('modalGst').value) || 0) : (currentCategory.tax_percent || 0),
      spec: spec,
      spec_text: specSummary(spec),
      due_date: el('modalDue').value,
      designer_id: el('modalDesigner') ? (el('modalDesigner').value || null) : null,
      special_instructions: el('modalInstructions').value,
      requires_design: currentCategory.requires_design
    };
    const c = calcLine(line);
    line.total_sqft = c.sqft; line.amount = c.amount; line.tax_amount = c.tax;
    state.items.push(line);
    renderItems();
    itemModal.hide();
  });

  if (itemModalEl) itemModalEl.addEventListener('hidden.bs.modal', () => {
    if (nameInput) nameInput.value = '';
    ['modalQty'].forEach(id => { if (el(id)) el(id).value = 1; });
    ['modalWidth', 'modalHeight', 'modalRate'].forEach(id => { if (el(id)) el(id).value = 0; });
    if (el('modalInstructions')) el('modalInstructions').value = '';
    if (optionsBox) optionsBox.innerHTML = '';
    if (categorySelect) categorySelect.value = '';
    if (componentSelect) { componentSelect.innerHTML = ''; componentSelect.closest('.kp-component-wrap').style.display = 'none'; }
    if (unitInput) unitInput.value = '';
    currentCategory = null;
    currentComponent = null;
    showSqftFields(false);
    setText('modalAmount', money(0));
  });

  // Show how many reference files were picked
  const fileInput = document.getElementById('referenceFiles');
  if (fileInput) fileInput.addEventListener('change', () => {
    const names = Array.from(fileInput.files).map(f => f.name);
    document.getElementById('fileList').innerHTML = names.length
      ? '<i class="bi bi-paperclip"></i> ' + names.length + ' file(s): ' + names.map(esc).join(', ')
      : '';
  });

  // Removing an existing reference file only stages it — nothing is deleted until the
  // order is saved, so a mis-click is undone by leaving the page.
  const removeBox = document.getElementById('removeFilesBox');
  if (removeBox) document.addEventListener('click', e => {
    const btn = e.target.closest('.kp-file-remove');
    if (!btn) return;
    const id = btn.dataset.id;
    const card = btn.closest('.col-6, .col-md-3, .col-lg-2') || btn.parentElement;
    const staged = removeBox.querySelector('input[value="' + id + '"]');
    if (staged) {                                   // clicked again — put it back
      staged.remove();
      card.classList.remove('opacity-50');
      btn.className = 'btn btn-sm btn-outline-danger mt-auto kp-file-remove';
      btn.innerHTML = '<i class="bi bi-trash"></i> Remove';
      return;
    }
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'remove_attachments[]';
    input.value = id;
    removeBox.appendChild(input);
    card.classList.add('opacity-50');
    btn.className = 'btn btn-sm btn-outline-secondary mt-auto kp-file-remove';
    btn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> Undo';
  });

  // ================================================================ submit
  const waModalEl = document.getElementById('waConfirmModal');
  const waModal = waModalEl ? new bootstrap.Modal(waModalEl) : null;
  let waConfirmed = false;

  const fillAndGo = notify => {
    if (document.getElementById('notify_customer')) document.getElementById('notify_customer').value = notify ? '1' : '0';
    document.getElementById('items_json').value = JSON.stringify(state.items);
    const btn = orderForm.querySelector('button[type=submit]');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving…'; }
  };

  /**
   * Everything that must be filled in before the order can be saved.
   * Returns a list of problems; empty means good to go.
   */
  const problems = () => {
    const out = [];
    const bad = (id, msg) => {
      const el = document.getElementById(id);
      if (el) el.classList.add('is-invalid');
      out.push({ id: id, msg: msg });
    };
    orderForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    if (!isEdit) {
      const phone = document.getElementById('customer_phone');
      const digits = localPhone(phone ? phone.value : '');
      if (!digits) {
        bad('customer_phone', 'Mobile number is missing.');
      } else if (digits.length !== 10) {
        bad('customer_phone', 'Mobile number must be 10 digits — “' + phone.value.trim() + '” is not.');
      }
      // A number nobody recognises means a new customer, and a new customer needs a name.
      const idField = document.getElementById('customer_id');
      const nameField = document.getElementById('customer_name');
      if (idField && !idField.value && nameField && !nameField.value.trim()) {
        const box = document.getElementById('newCustomerFields');
        if (box) box.classList.remove('d-none');
        bad('customer_name', 'This is a new customer — enter their name.');
      }
    }
    if (!state.items.length) {
      out.push({ id: null, msg: 'Add at least one item.' });
    }
    state.items.forEach((l, i) => {
      if (!String(l.name || '').trim()) out.push({ id: null, msg: 'Item ' + (i + 1) + ' has no name.' });
      if (!(Number(l.qty) > 0)) out.push({ id: null, msg: 'Item ' + (i + 1) + ' needs a quantity.' });
    });
    return out;
  };

  /** Put the problems at the top of the page, and take the user to the first one. */
  const showProblems = list => {
    const box = document.getElementById('formErrors');
    if (box) {
      box.innerHTML = '<strong>Please fix ' + (list.length === 1 ? 'this' : 'these ' + list.length) + ' before saving:</strong>'
        + '<ul class="mb-0 mt-1">' + list.map(p => '<li>' + esc(p.msg) + '</li>').join('') + '</ul>';
      box.classList.remove('d-none');
      box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
      kpToast('danger', list[0].msg);
    }
    const first = list.find(p => p.id);
    if (first) {
      const el = document.getElementById(first.id);
      if (el) setTimeout(() => el.focus(), 400);
    }
  };

  /** One gate for every route into saving — the button and both modal answers. */
  const guard = () => {
    const list = problems();
    if (list.length) { showProblems(list); return false; }
    const box = document.getElementById('formErrors');
    if (box) box.classList.add('d-none');
    return true;
  };

  orderForm.addEventListener('submit', e => {
    if (!guard()) { e.preventDefault(); return; }
    if (isEdit || waConfirmed || !waModal) { fillAndGo(true); return; }
    e.preventDefault();
    waModal.show();
  });

  if (waModal) {
    // .submit() skips the form's own submit handler, so the gate is checked here too.
    const answer = notify => {
      waModal.hide();
      if (!guard()) return;
      waConfirmed = true;
      fillAndGo(notify);
      orderForm.submit();
    };
    document.getElementById('waYes').addEventListener('click', () => answer(true));
    document.getElementById('waNo').addEventListener('click', () => answer(false));
  }

  // Clear the red outline as soon as the field is being corrected.
  orderForm.addEventListener('input', e => {
    if (e.target.classList && e.target.classList.contains('is-invalid')) {
      e.target.classList.remove('is-invalid');
    }
  });

  renderItems();
})();
