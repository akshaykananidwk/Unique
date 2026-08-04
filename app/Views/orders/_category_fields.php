<?php
/**
 * The question set for one category, rendered into the Add Item box.
 * Every control carries data-spec-key so the browser can collect the answers.
 * @var array $options category_options rows, each with a `values` list
 */
if (!$options): ?>
  <div class="text-muted small">No extra questions for this category.</div>
<?php return; endif; ?>

<div class="row g-2">
<?php foreach ($options as $option):
    $key = (string)$option['field_key'];
    $req = (int)$option['is_required'] === 1;
    $wide = in_array($option['field_type'], ['textarea'], true); ?>
  <div class="<?= $wide ? 'col-12' : 'col-md-6' ?> mb-1">
    <label class="form-label mb-1"><?= e($option['label']) ?><?= $req ? ' *' : '' ?></label>

    <?php if ($option['field_type'] === 'select'): ?>
      <select class="form-select form-select-sm" data-spec-key="<?= e($key) ?>" data-required="<?= $req ? 1 : 0 ?>">
        <option value="">— select —</option>
        <?php foreach ($option['values'] as $v): ?>
          <option value="<?= e($v['label']) ?>"><?= e($v['label']) ?></option>
        <?php endforeach; ?>
      </select>

    <?php elseif ($option['field_type'] === 'radio'): ?>
      <div>
        <?php foreach ($option['values'] as $i => $v): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="opt_<?= e($key) ?>" id="opt<?= (int)$option['id'] ?>_<?= $i ?>"
                   value="<?= e($v['label']) ?>" data-spec-key="<?= e($key) ?>" data-required="<?= $req ? 1 : 0 ?>">
            <label class="form-check-label small" for="opt<?= (int)$option['id'] ?>_<?= $i ?>"><?= e($v['label']) ?></label>
          </div>
        <?php endforeach; ?>
      </div>

    <?php elseif ($option['field_type'] === 'checkbox'): ?>
      <div>
        <?php foreach ($option['values'] as $i => $v): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="opc<?= (int)$option['id'] ?>_<?= $i ?>"
                   value="<?= e($v['label']) ?>" data-spec-key="<?= e($key) ?>" data-required="<?= $req ? 1 : 0 ?>">
            <label class="form-check-label small" for="opc<?= (int)$option['id'] ?>_<?= $i ?>"><?= e($v['label']) ?></label>
          </div>
        <?php endforeach; ?>
      </div>

    <?php elseif ($option['field_type'] === 'textarea'): ?>
      <textarea class="form-control form-control-sm" rows="2" data-spec-key="<?= e($key) ?>" data-required="<?= $req ? 1 : 0 ?>"></textarea>

    <?php else: ?>
      <input type="<?= $option['field_type'] === 'number' ? 'number' : ($option['field_type'] === 'date' ? 'date' : 'text') ?>"
             class="form-control form-control-sm" data-spec-key="<?= e($key) ?>" data-required="<?= $req ? 1 : 0 ?>"
             <?= $option['field_type'] === 'number' ? 'step="any"' : '' ?>>
    <?php endif; ?>

    <?php if (!empty($option['help_text'])): ?>
      <div class="form-text"><?= e($option['help_text']) ?></div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>
