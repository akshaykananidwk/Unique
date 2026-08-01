<?php use App\Core\Csrf; $title = $item['name'];
$optionsJs = [];
foreach ($options as $o) {
    $values = [];
    foreach ($o['values'] as $v) {
        $values[(string)$v['id']] = ['delta' => $v['price_delta'], 'mode' => $v['price_mode']];
    }
    $optionsJs[$o['field_key']] = ['values' => $values];
}
?>
<div class="container py-4">
  <nav class="small mb-2"><a href="<?= e(base_url('products')) ?>">Products</a> › <?= e($item['category_name']) ?></nav>
  <div class="row g-4">
    <div class="col-md-5">
      <?php if ($item['image']): ?>
        <img src="<?= e(upload_url($item['image'])) ?>" class="img-fluid rounded" alt="<?= e($item['name']) ?>">
      <?php else: ?>
        <div class="bg-body-tertiary rounded d-flex align-items-center justify-content-center" style="height:240px">
          <i class="bi bi-image fs-1 text-muted"></i></div>
      <?php endif; ?>
      <h2 class="mt-3"><?= e($item['name']) ?></h2>
      <p class="text-muted"><?= e($item['description'] ?: $item['short_description']) ?></p>
      <?php if ($slabs): ?>
      <h6>Quantity pricing</h6>
      <table class="table table-sm" style="max-width:300px">
        <?php foreach ($slabs as $s): ?>
        <tr><td><?= (int)$s['min_qty'] ?><?= $s['max_qty'] ? '–' . (int)$s['max_qty'] : '+' ?> <?= e($item['unit']) ?></td>
            <td class="text-end"><?= e(fmt_money($s['rate'])) ?>/<?= e($item['unit']) ?></td></tr>
        <?php endforeach; ?>
      </table>
      <?php endif; ?>
    </div>
    <div class="col-md-7">
      <div class="card"><div class="card-body">
        <h5>Order this product</h5>
        <form id="productOrderForm" method="post" action="<?= e(base_url('cart/add')) ?>" enctype="multipart/form-data">
          <?= Csrf::field() ?>
          <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
          <div class="mb-3">
            <label class="form-label">Quantity (<?= e($item['unit']) ?>) *</label>
            <input type="number" name="qty" class="form-control" value="<?= (int)$item['min_qty'] ?>"
                   min="<?= (int)$item['min_qty'] ?>" step="<?= (int)$item['step_qty'] ?>" required>
          </div>
          <?php foreach ($options as $o): $name = 'opt_' . $o['field_key']; ?>
          <div class="mb-3">
            <label class="form-label"><?= e($o['label']) ?><?= (int)$o['is_required'] ? ' *' : '' ?></label>
            <?php if ($o['help_text']): ?><div class="form-text mt-0 mb-1"><?= e($o['help_text']) ?></div><?php endif; ?>
            <?php switch ($o['field_type']):
                case 'select': ?>
              <select name="<?= e($name) ?>" class="form-select" data-opt-key="<?= e($o['field_key']) ?>" <?= (int)$o['is_required'] ? 'required' : '' ?>>
                <option value="">— Select —</option>
                <?php foreach ($o['values'] as $v): ?>
                  <option value="<?= (int)$v['id'] ?>" <?= (int)$v['is_default'] ? 'selected' : '' ?>><?= e($v['label']) ?></option>
                <?php endforeach; ?>
              </select>
            <?php break; case 'radio': ?>
              <?php foreach ($o['values'] as $v): ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="<?= e($name) ?>" id="<?= e($name . $v['id']) ?>"
                       value="<?= (int)$v['id'] ?>" data-opt-key="<?= e($o['field_key']) ?>" <?= (int)$v['is_default'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="<?= e($name . $v['id']) ?>"><?= e($v['label']) ?></label>
              </div>
              <?php endforeach; ?>
            <?php break; case 'checkbox': ?>
              <?php foreach ($o['values'] as $v): ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="<?= e($name) ?>[]" id="<?= e($name . $v['id']) ?>"
                       value="<?= (int)$v['id'] ?>" data-opt-key="<?= e($o['field_key']) ?>" <?= (int)$v['is_default'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="<?= e($name . $v['id']) ?>"><?= e($v['label']) ?></label>
              </div>
              <?php endforeach; ?>
            <?php break; case 'textarea': ?>
              <textarea name="<?= e($name) ?>" class="form-control" rows="3" <?= (int)$o['is_required'] ? 'required' : '' ?>></textarea>
            <?php break; case 'number': ?>
              <input type="number" step="any" name="<?= e($name) ?>" class="form-control" <?= (int)$o['is_required'] ? 'required' : '' ?>>
            <?php break; case 'date': ?>
              <input type="date" name="<?= e($name) ?>" class="form-control" <?= (int)$o['is_required'] ? 'required' : '' ?>>
            <?php break; case 'color': ?>
              <input type="color" name="<?= e($name) ?>" class="form-control form-control-color">
            <?php break; case 'file': ?>
              <div class="form-text">Upload in the artwork box below.</div>
            <?php break; default: ?>
              <input type="text" name="<?= e($name) ?>" class="form-control" <?= (int)$o['is_required'] ? 'required' : '' ?>>
            <?php endswitch; ?>
          </div>
          <?php endforeach; ?>
          <?php if ((int)$item['allow_customer_file_upload']): ?>
          <div class="mb-3">
            <label class="form-label">Your artwork / reference (optional)</label>
            <input type="file" name="artwork" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf,.cdr,.ai,.psd,.zip">
          </div>
          <?php endif; ?>
          <div class="alert alert-info d-flex justify-content-between align-items-center">
            <span>Estimated price</span><span id="livePrice" class="fs-5">—</span>
          </div>
          <button class="btn btn-primary btn-lg w-100"><i class="bi bi-cart-plus"></i> Add to Order</button>
          <p class="small text-muted mt-2 mb-0">Final price is confirmed by our team before printing. Design proof approval happens on WhatsApp.</p>
        </form>
      </div></div>
    </div>
  </div>
</div>
<script>window.KP_PRODUCT = <?= json_encode([
    'base_price' => $item['base_price'],
    'pricing_type' => $item['pricing_type'],
    'tax_percent' => $item['tax_percent'],
    'min_qty' => (int)$item['min_qty'],
    'slabs' => array_map(fn($s) => ['min' => (int)$s['min_qty'], 'max' => $s['max_qty'] ? (int)$s['max_qty'] : null, 'rate' => $s['rate']], $slabs),
    'options' => $optionsJs,
], JSON_UNESCAPED_UNICODE) ?>;</script>
