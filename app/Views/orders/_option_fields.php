<?php /** Dynamic option fields — used by the staff order modal (AJAX). */ ?>
<?php foreach ($options as $option): $key = 'spec_' . $option['field_key']; ?>
<div class="mb-3">
  <label class="form-label"><?= e($option['label']) ?><?= (int)$option['is_required'] ? ' *' : '' ?></label>
  <?php if ($option['help_text']): ?><div class="form-text mt-0 mb-1"><?= e($option['help_text']) ?></div><?php endif; ?>
  <?php switch ($option['field_type']):
      case 'select': ?>
    <select class="form-select" data-spec-key="<?= e($option['field_key']) ?>" data-required="<?= (int)$option['is_required'] ?>">
      <option value="">— Select —</option>
      <?php foreach ($option['values'] as $v): ?>
        <option value="<?= (int)$v['id'] ?>" <?= (int)$v['is_default'] ? 'selected' : '' ?>>
          <?= e($v['label']) ?><?= (float)$v['price_delta'] != 0 && $v['price_mode'] === 'add' ? ' (+₹' . e($v['price_delta']) . ')' : '' ?>
        </option>
      <?php endforeach; ?>
    </select>
  <?php break; case 'radio': ?>
    <?php foreach ($option['values'] as $v): ?>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="<?= e($key) ?>" id="<?= e($key . $v['id']) ?>"
               value="<?= (int)$v['id'] ?>" data-spec-key="<?= e($option['field_key']) ?>"
               data-required="<?= (int)$option['is_required'] ?>" <?= (int)$v['is_default'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="<?= e($key . $v['id']) ?>">
          <?= e($v['label']) ?><?= (float)$v['price_delta'] != 0 && $v['price_mode'] === 'add' ? ' (+₹' . e($v['price_delta']) . ')' : '' ?>
        </label>
      </div>
    <?php endforeach; ?>
  <?php break; case 'checkbox': ?>
    <?php foreach ($option['values'] as $v): ?>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" id="<?= e($key . $v['id']) ?>" value="<?= (int)$v['id'] ?>"
               data-spec-key="<?= e($option['field_key']) ?>" <?= (int)$v['is_default'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="<?= e($key . $v['id']) ?>"><?= e($v['label']) ?></label>
      </div>
    <?php endforeach; ?>
  <?php break; case 'textarea': ?>
    <textarea class="form-control" rows="3" data-spec-key="<?= e($option['field_key']) ?>"
              data-required="<?= (int)$option['is_required'] ?>"></textarea>
  <?php break; case 'number': ?>
    <input type="number" step="any" class="form-control" data-spec-key="<?= e($option['field_key']) ?>"
           data-required="<?= (int)$option['is_required'] ?>">
  <?php break; case 'date': ?>
    <input type="date" class="form-control" data-spec-key="<?= e($option['field_key']) ?>"
           data-required="<?= (int)$option['is_required'] ?>">
  <?php break; case 'color': ?>
    <input type="color" class="form-control form-control-color" data-spec-key="<?= e($option['field_key']) ?>">
  <?php break; case 'file': ?>
    <div class="form-text">Attach this file in the “Customer artwork” box below.</div>
  <?php break; default: ?>
    <input type="text" class="form-control" data-spec-key="<?= e($option['field_key']) ?>"
           data-required="<?= (int)$option['is_required'] ?>">
  <?php endswitch; ?>
</div>
<?php endforeach; ?>
<?php if (!$options): ?><p class="text-muted small">This item has no extra options.</p><?php endif; ?>
