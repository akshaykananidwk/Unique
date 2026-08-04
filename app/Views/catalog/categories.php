<?php use App\Core\Csrf; $title = 'Categories'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Categories</h4>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#catModal"><i class="bi bi-plus-lg"></i> Add Category</button>
</div>
<?php if (!$categories): ?><div class="empty-state"><i class="bi bi-tags"></i>No categories yet — add the first one.</div><?php endif; ?>
<div class="row g-3">
<?php foreach ($categories as $c): ?>
  <div class="col-md-6 col-lg-4">
    <div class="card h-100"><div class="card-body">
      <form method="post" action="<?= e(admin_url('categories/' . $c['id'] . '/update')) ?>" enctype="multipart/form-data">
        <?= Csrf::field() ?>
        <div class="d-flex justify-content-between">
          <input name="name" class="form-control form-control-sm fw-semibold mb-2" value="<?= e($c['name']) ?>">
          <span class="badge bg-secondary ms-2"><?= (int)$c['item_count'] ?> items</span>
        </div>
        <input name="description" class="form-control form-control-sm mb-2" placeholder="Description" value="<?= e($c['description']) ?>">
        <div class="input-group input-group-sm mb-2">
          <span class="input-group-text"><i class="bi bi-<?= e(category_icon($c)) ?>"></i></span>
          <input name="icon" class="form-control" placeholder="Icon (e.g. person-vcard)" value="<?= e($c['icon'] ?? '') ?>">
        </div>
        <div class="d-flex gap-2 align-items-center mb-2">
          <input type="number" name="sort_order" class="form-control form-control-sm" style="width:80px" value="<?= (int)$c['sort_order'] ?>" title="Sort order">
          <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?= (int)$c['is_active'] ? 'checked' : '' ?> id="ca<?= (int)$c['id'] ?>">
            <label class="form-check-label small" for="ca<?= (int)$c['id'] ?>">Active</label></div>
          <div class="form-check"><input class="form-check-input" type="checkbox" name="show_on_public" value="1" <?= (int)$c['show_on_public'] ? 'checked' : '' ?> id="cp<?= (int)$c['id'] ?>">
            <label class="form-check-label small" for="cp<?= (int)$c['id'] ?>">Public</label></div>
        </div>
        <input type="file" name="image" class="form-control form-control-sm mb-2" accept="image/*">
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary">Save</button>
      </form>
          <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                  data-bs-target="#comp<?= (int)$c['id'] ?>">
            <i class="bi bi-diagram-3"></i> Components (<?= count($c['components']) ?>)
          </button>
          <form method="post" action="<?= e(admin_url('categories/' . $c['id'] . '/delete')) ?>"
                data-confirm="Delete “<?= e($c['name']) ?>”<?= (int)$c['item_count'] > 0
                    ? ' and all ' . (int)$c['item_count'] . ' item' . ((int)$c['item_count'] === 1 ? '' : 's') . ' inside it'
                    : '' ?>? Past orders keep their record.">
            <?= Csrf::field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
          </form>
        </div>

        <!-- Component presets: pure data, so new ones can be added without touching code -->
        <div class="collapse mt-2" id="comp<?= (int)$c['id'] ?>">
          <form method="post" action="<?= e(admin_url('categories/' . $c['id'] . '/components')) ?>" class="border-top pt-2">
            <?= Csrf::field() ?>
            <div class="small text-muted mb-1">Ready components offered when adding an item in this category.
              Mark a component <strong>ft × ft</strong> and it will ask width and height.</div>
            <div class="kp-comp-rows">
              <?php foreach (array_merge($c['components'], [null]) as $comp): ?>
                <div class="row g-1 mb-1 kp-comp-row">
                  <div class="col-6"><input name="comp_name[]" class="form-control form-control-sm"
                         placeholder="Component name" value="<?= e($comp['name'] ?? '') ?>"></div>
                  <div class="col-3"><select name="comp_mode[]" class="form-select form-select-sm">
                      <option value="simple" <?= ($comp['calc_mode'] ?? '') === 'simple' ? 'selected' : '' ?>>Qty × Rate</option>
                      <option value="sqft" <?= ($comp['calc_mode'] ?? '') === 'sqft' ? 'selected' : '' ?>>ft × ft</option>
                    </select></div>
                  <div class="col-3"><input name="comp_unit[]" class="form-control form-control-sm"
                         placeholder="unit" value="<?= e($comp['unit'] ?? '') ?>"></div>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-secondary kp-comp-add"><i class="bi bi-plus-lg"></i> Add row</button>
              <button class="btn btn-sm btn-primary">Save Components</button>
            </div>
            <div class="form-text">Clear a name and save to remove that component.</div>
          </form>
        </div>
    </div></div>
  </div>
<?php endforeach; ?>
</div>

<div class="modal fade" id="catModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="post" action="<?= e(admin_url('categories')) ?>" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <div class="modal-header"><h5 class="modal-title">Add Category</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-2"><label class="form-label">Name *</label><input name="name" class="form-control" required></div>
      <div class="mb-2"><label class="form-label">Description</label><input name="description" class="form-control"></div>
      <div class="mb-2"><label class="form-label">Icon <small class="text-muted">(optional Bootstrap Icon name, e.g. <code>person-vcard</code>, <code>flag</code>)</small></label>
        <input name="icon" class="form-control" placeholder="auto-picked from the name if left blank"></div>
      <div class="mb-2"><label class="form-label">Image <small class="text-muted">(shown instead of the icon when set)</small></label><input type="file" name="image" class="form-control" accept="image/*"></div>
      <div class="mb-2"><label class="form-label">Sort order</label><input type="number" name="sort_order" class="form-control" value="0"></div>
      <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="na">
        <label class="form-check-label" for="na">Active</label></div>
      <div class="form-check"><input class="form-check-input" type="checkbox" name="show_on_public" value="1" checked id="np">
        <label class="form-check-label" for="np">Show on public website</label></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Add Category</button></div>
  </form>
</div></div></div>
