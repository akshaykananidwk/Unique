<?php use App\Core\Csrf; $title = $item['name']; ?>
<div class="container section">
  <nav class="small mb-3"><a href="<?= e(base_url('products')) ?>" class="text-decoration-none">← All products</a> · <?= e($item['category_name']) ?></nav>
  <div class="row g-4">
    <div class="col-md-5">
      <div class="prod-thumb rounded-4" style="aspect-ratio:1/1">
        <?php if ($item['image']): ?>
          <img src="<?= e(upload_url($item['image'])) ?>" alt="<?= e($item['name']) ?>">
        <?php else: ?>
          <i class="bi bi-<?= e(category_icon(['name' => $item['category_name']])) ?>" style="font-size:4rem"></i>
        <?php endif; ?>
      </div>
      <h2 class="mt-3"><?= e($item['name']) ?></h2>
      <p class="text-muted"><?= e($item['description'] ?: $item['short_description']) ?></p>
      <div class="d-flex flex-wrap gap-2 small">
        <span class="badge bg-light text-dark border"><i class="bi bi-rulers"></i> Unit: <?= e($item['unit']) ?></span>
        <?php if ((int)$item['requires_design']): ?><span class="badge bg-light text-dark border"><i class="bi bi-palette"></i> Design proof on WhatsApp</span><?php endif; ?>
        <span class="badge bg-light text-dark border"><i class="bi bi-clock"></i> Approx <?= (int)$item['default_turnaround_hours'] ?> hrs</span>
      </div>
    </div>
    <div class="col-md-7">
      <div class="track-box">
        <h5 class="mb-3"><i class="bi bi-bag-plus text-primary"></i> Place your order</h5>
        <form id="productOrderForm" method="post" action="<?= e(base_url('cart/add')) ?>" enctype="multipart/form-data">
          <?= Csrf::field() ?>
          <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
          <div class="mb-3">
            <label class="form-label fw-semibold">Quantity (<?= e($item['unit']) ?>)</label>
            <input type="number" name="qty" class="form-control form-control-lg" value="<?= (int)$item['min_qty'] ?>"
                   min="<?= (int)$item['min_qty'] ?>" step="<?= (int)$item['step_qty'] ?>" required>
          </div>
          <?php foreach ($options as $o): $name = 'opt_' . $o['field_key']; ?>
          <div class="mb-3">
            <label class="form-label fw-semibold"><?= e($o['label']) ?><?= (int)$o['is_required'] ? ' *' : '' ?></label>
            <?php if ($o['help_text']): ?><div class="form-text mt-0 mb-1"><?= e($o['help_text']) ?></div><?php endif; ?>
            <?php switch ($o['field_type']):
                case 'select': ?>
              <select name="<?= e($name) ?>" class="form-select" <?= (int)$o['is_required'] ? 'required' : '' ?>>
                <option value="">— Select —</option>
                <?php foreach ($o['values'] as $v): ?>
                  <option value="<?= (int)$v['id'] ?>" <?= (int)$v['is_default'] ? 'selected' : '' ?>><?= e($v['label']) ?></option>
                <?php endforeach; ?>
              </select>
            <?php break; case 'radio': ?>
              <div class="d-flex flex-wrap gap-2">
              <?php foreach ($o['values'] as $v): ?>
                <input type="radio" class="btn-check" name="<?= e($name) ?>" id="<?= e($name . $v['id']) ?>"
                       value="<?= (int)$v['id'] ?>" <?= (int)$v['is_default'] ? 'checked' : '' ?> autocomplete="off">
                <label class="btn btn-outline-primary btn-sm" for="<?= e($name . $v['id']) ?>"><?= e($v['label']) ?></label>
              <?php endforeach; ?>
              </div>
            <?php break; case 'checkbox': ?>
              <?php foreach ($o['values'] as $v): ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="<?= e($name) ?>[]" id="<?= e($name . $v['id']) ?>"
                       value="<?= (int)$v['id'] ?>" <?= (int)$v['is_default'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="<?= e($name . $v['id']) ?>"><?= e($v['label']) ?></label>
              </div>
              <?php endforeach; ?>
            <?php break; case 'textarea': ?>
              <textarea name="<?= e($name) ?>" class="form-control" rows="3" <?= (int)$o['is_required'] ? 'required' : '' ?>
                        placeholder="<?= e($o['help_text'] ?: 'Type the exact text / details here') ?>"></textarea>
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
            <label class="form-label fw-semibold">Your artwork / reference <span class="text-muted small">(optional)</span></label>
            <input type="file" name="artwork" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf,.cdr,.ai,.psd,.zip">
          </div>
          <?php endif; ?>
          <button class="btn btn-primary btn-lg w-100"><i class="bi bi-cart-plus"></i> Add to Order</button>
          <p class="small text-muted mt-2 mb-0 text-center">Our team confirms your order and shares the design proof on WhatsApp.</p>
        </form>
      </div>
    </div>
  </div>
</div>
