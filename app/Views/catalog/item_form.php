<?php use App\Core\Csrf; $title = $item ? 'Edit Item' : 'Add Item'; ?>
<h4 class="mb-3"><?= e($title) ?><?= $item ? ' — ' . e($item['name']) : '' ?></h4>

<form method="post" enctype="multipart/form-data"
      action="<?= e($item ? admin_url('items/' . $item['id'] . '/update') : admin_url('items')) ?>">
  <?= Csrf::field() ?>
  <div class="card mb-3"><div class="card-body row g-2">
    <div class="col-md-4"><label class="form-label">Name *</label>
      <input name="name" class="form-control" required value="<?= e($item['name'] ?? '') ?>"></div>
    <div class="col-md-2"><label class="form-label">SKU *</label>
      <input name="sku" class="form-control" required value="<?= e($item['sku'] ?? '') ?>"></div>
    <div class="col-md-3"><label class="form-label">Category *</label>
      <select name="category_id" class="form-select" required>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= (int)($item['category_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-3"><label class="form-label">Unit</label>
      <input name="unit" class="form-control" value="<?= e($item['unit'] ?? 'pcs') ?>" placeholder="pcs / box / sheet / sqft / kg"></div>
    <div class="col-md-3"><label class="form-label">Pricing Type</label>
      <select name="pricing_type" class="form-select">
        <?php foreach (['per_unit' => 'Per unit', 'fixed' => 'Fixed price', 'slab' => 'Quantity slabs', 'area' => 'Area (width × height)'] as $v => $l): ?>
          <option value="<?= $v ?>" <?= ($item['pricing_type'] ?? 'per_unit') === $v ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-2"><label class="form-label">Base Price ₹</label>
      <input type="number" step="0.01" min="0" name="base_price" class="form-control" value="<?= e($item['base_price'] ?? '0') ?>"></div>
    <div class="col-md-2"><label class="form-label">Min Qty</label>
      <input type="number" min="1" name="min_qty" class="form-control" value="<?= e($item['min_qty'] ?? '1') ?>"></div>
    <div class="col-md-2"><label class="form-label">Step Qty</label>
      <input type="number" min="1" name="step_qty" class="form-control" value="<?= e($item['step_qty'] ?? '1') ?>"></div>
    <div class="col-md-1"><label class="form-label">Tax %</label>
      <input type="number" step="0.01" min="0" name="tax_percent" class="form-control" value="<?= e($item['tax_percent'] ?? '0') ?>"></div>
    <div class="col-md-2"><label class="form-label">Turnaround (hours)</label>
      <input type="number" min="1" name="default_turnaround_hours" class="form-control" value="<?= e($item['default_turnaround_hours'] ?? '24') ?>"></div>
    <div class="col-md-6"><label class="form-label">Short description (public site)</label>
      <input name="short_description" class="form-control" value="<?= e($item['short_description'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Image</label>
      <input type="file" name="image" class="form-control" accept="image/*"></div>
    <div class="col-12 d-flex flex-wrap gap-3">
      <?php foreach (['requires_design' => 'Requires design (proof loop)', 'show_on_public' => 'Show on public website',
                     'allow_customer_file_upload' => 'Allow customer file upload', 'is_active' => 'Active'] as $key => $label): ?>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="<?= $key ?>" value="1" id="f_<?= $key ?>"
          <?= ($item === null && in_array($key, ['requires_design', 'show_on_public', 'allow_customer_file_upload', 'is_active'], true)) || !empty($item[$key]) ? 'checked' : '' ?>>
        <label class="form-check-label" for="f_<?= $key ?>"><?= $label ?></label>
      </div>
      <?php endforeach; ?>
    </div>
  </div></div>
  <button class="btn btn-primary mb-4"><?= $item ? 'Save Item' : 'Create Item' ?></button>
</form>

<?php if ($item): ?>
<!-- Options editor -->
<div class="card mb-4"><div class="card-body">
  <h6>Order-form options <small class="text-muted">— these fields appear on staff & public order forms instantly</small></h6>
  <form method="post" action="<?= e(admin_url('items/' . $item['id'] . '/options')) ?>" id="optionsForm">
    <?= Csrf::field() ?>
    <input type="hidden" name="options_json" id="options_json">
    <div id="optionsEditor"></div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="addOption"><i class="bi bi-plus"></i> Add Option Field</button>
    <button class="btn btn-sm btn-success">Save Options</button>
  </form>
</div></div>

<!-- Slabs editor -->
<div class="card mb-4"><div class="card-body">
  <h6>Quantity price slabs <small class="text-muted">— used when pricing type is “slab”</small></h6>
  <form method="post" action="<?= e(admin_url('items/' . $item['id'] . '/slabs')) ?>" id="slabsForm">
    <?= Csrf::field() ?>
    <input type="hidden" name="slabs_json" id="slabs_json">
    <div id="slabsEditor"></div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="addSlab"><i class="bi bi-plus"></i> Add Slab</button>
    <button class="btn btn-sm btn-success">Save Slabs</button>
  </form>
</div></div>

<script>
(function () {
  const fieldTypes = ['text','number','select','radio','checkbox','textarea','date','file','color'];
  let options = <?= json_encode(array_map(fn($o) => [
      'label' => $o['label'], 'field_key' => $o['field_key'], 'field_type' => $o['field_type'],
      'is_required' => (int)$o['is_required'], 'help_text' => $o['help_text'], 'affects_price' => (int)$o['affects_price'],
      'values' => array_map(fn($v) => [
          'label' => $v['label'], 'price_delta' => $v['price_delta'], 'price_mode' => $v['price_mode'],
          'is_default' => (int)$v['is_default'],
      ], $o['values']),
  ], $options), JSON_UNESCAPED_UNICODE) ?>;
  let slabs = <?= json_encode(array_map(fn($s) => [
      'min_qty' => (int)$s['min_qty'], 'max_qty' => $s['max_qty'] !== null ? (int)$s['max_qty'] : '', 'rate' => $s['rate'],
  ], $slabs), JSON_UNESCAPED_UNICODE) ?>;

  const editor = document.getElementById('optionsEditor');
  function esc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }

  function renderOptions() {
    editor.innerHTML = '';
    options.forEach((o, i) => {
      const needsValues = ['select','radio','checkbox'].includes(o.field_type);
      const div = document.createElement('div');
      div.className = 'border rounded p-2 mb-2';
      div.innerHTML =
        '<div class="row g-2 align-items-end">' +
        '<div class="col-md-3"><label class="form-label small">Label</label><input class="form-control form-control-sm" value="' + esc(o.label) + '" data-f="label" data-i="' + i + '"></div>' +
        '<div class="col-md-2"><label class="form-label small">Field key</label><input class="form-control form-control-sm" value="' + esc(o.field_key) + '" data-f="field_key" data-i="' + i + '"></div>' +
        '<div class="col-md-2"><label class="form-label small">Type</label><select class="form-select form-select-sm" data-f="field_type" data-i="' + i + '">' +
          fieldTypes.map(t => '<option ' + (o.field_type === t ? 'selected' : '') + '>' + t + '</option>').join('') + '</select></div>' +
        '<div class="col-md-2"><label class="form-label small">Help text</label><input class="form-control form-control-sm" value="' + esc(o.help_text) + '" data-f="help_text" data-i="' + i + '"></div>' +
        '<div class="col-md-3">' +
        '<div class="form-check form-check-inline"><input type="checkbox" class="form-check-input" ' + (o.is_required ? 'checked' : '') + ' data-f="is_required" data-i="' + i + '"><label class="form-check-label small">Required</label></div>' +
        '<div class="form-check form-check-inline"><input type="checkbox" class="form-check-input" ' + (o.affects_price ? 'checked' : '') + ' data-f="affects_price" data-i="' + i + '"><label class="form-check-label small">Affects price</label></div>' +
        '<button type="button" class="btn btn-sm btn-outline-danger" data-del="' + i + '"><i class="bi bi-trash"></i></button>' +
        '</div></div>' +
        (needsValues ? '<div class="mt-2 ps-3" data-values="' + i + '">' +
          (o.values || []).map((v, j) =>
            '<div class="row g-1 mb-1">' +
            '<div class="col-4"><input class="form-control form-control-sm" placeholder="Choice label" value="' + esc(v.label) + '" data-vf="label" data-i="' + i + '" data-j="' + j + '"></div>' +
            '<div class="col-2"><input type="number" step="0.01" class="form-control form-control-sm" placeholder="₹ delta" value="' + esc(v.price_delta) + '" data-vf="price_delta" data-i="' + i + '" data-j="' + j + '"></div>' +
            '<div class="col-2"><select class="form-select form-select-sm" data-vf="price_mode" data-i="' + i + '" data-j="' + j + '">' +
              ['add','multiply','replace'].map(m => '<option ' + (v.price_mode === m ? 'selected' : '') + '>' + m + '</option>').join('') + '</select></div>' +
            '<div class="col-2 form-check ms-2"><input type="checkbox" class="form-check-input" ' + (v.is_default ? 'checked' : '') + ' data-vf="is_default" data-i="' + i + '" data-j="' + j + '"><label class="form-check-label small">Default</label></div>' +
            '<div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger" data-delval="' + i + ':' + j + '">×</button></div>' +
            '</div>').join('') +
          '<button type="button" class="btn btn-sm btn-outline-secondary" data-addval="' + i + '">+ choice</button></div>' : '');
      editor.appendChild(div);
    });
  }

  editor.addEventListener('input', e => {
    const el = e.target;
    if (el.dataset.f !== undefined && el.dataset.i !== undefined) {
      const v = el.type === 'checkbox' ? (el.checked ? 1 : 0) : el.value;
      options[el.dataset.i][el.dataset.f] = v;
      if (el.dataset.f === 'field_type') renderOptions();
    }
    if (el.dataset.vf !== undefined) {
      const v = el.type === 'checkbox' ? (el.checked ? 1 : 0) : el.value;
      options[el.dataset.i].values[el.dataset.j][el.dataset.vf] = v;
    }
  });
  editor.addEventListener('click', e => {
    const b = e.target.closest('button');
    if (!b) return;
    if (b.dataset.del !== undefined) { options.splice(b.dataset.del, 1); renderOptions(); }
    if (b.dataset.addval !== undefined) { (options[b.dataset.addval].values = options[b.dataset.addval].values || []).push({label:'',price_delta:0,price_mode:'add',is_default:0}); renderOptions(); }
    if (b.dataset.delval !== undefined) { const [i,j] = b.dataset.delval.split(':'); options[i].values.splice(j,1); renderOptions(); }
  });
  document.getElementById('addOption').addEventListener('click', () => {
    options.push({label:'',field_key:'',field_type:'text',is_required:0,help_text:'',affects_price:0,values:[]});
    renderOptions();
  });
  document.getElementById('optionsForm').addEventListener('submit', () => {
    document.getElementById('options_json').value = JSON.stringify(options);
  });
  renderOptions();

  // Slabs
  const slabsEditor = document.getElementById('slabsEditor');
  function renderSlabs() {
    slabsEditor.innerHTML = slabs.map((s, i) =>
      '<div class="row g-1 mb-1">' +
      '<div class="col-3"><input type="number" class="form-control form-control-sm" placeholder="Min qty" value="' + esc(s.min_qty) + '" data-sf="min_qty" data-i="' + i + '"></div>' +
      '<div class="col-3"><input type="number" class="form-control form-control-sm" placeholder="Max qty (blank = ∞)" value="' + esc(s.max_qty) + '" data-sf="max_qty" data-i="' + i + '"></div>' +
      '<div class="col-3"><input type="number" step="0.01" class="form-control form-control-sm" placeholder="Rate ₹" value="' + esc(s.rate) + '" data-sf="rate" data-i="' + i + '"></div>' +
      '<div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger" data-delslab="' + i + '">×</button></div>' +
      '</div>').join('');
  }
  slabsEditor.addEventListener('input', e => {
    if (e.target.dataset.sf !== undefined) slabs[e.target.dataset.i][e.target.dataset.sf] = e.target.value;
  });
  slabsEditor.addEventListener('click', e => {
    const b = e.target.closest('button');
    if (b && b.dataset.delslab !== undefined) { slabs.splice(b.dataset.delslab, 1); renderSlabs(); }
  });
  document.getElementById('addSlab').addEventListener('click', () => { slabs.push({min_qty:'',max_qty:'',rate:''}); renderSlabs(); });
  document.getElementById('slabsForm').addEventListener('submit', () => {
    document.getElementById('slabs_json').value = JSON.stringify(slabs);
  });
  renderSlabs();
})();
</script>
<?php endif; ?>
