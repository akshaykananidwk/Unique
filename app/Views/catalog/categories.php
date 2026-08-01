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
          <form method="post" action="<?= e(admin_url('categories/' . $c['id'] . '/delete')) ?>" data-confirm="Delete this category?">
            <?= Csrf::field() ?><button class="btn btn-sm btn-outline-danger">Delete</button>
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
      <div class="mb-2"><label class="form-label">Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
      <div class="mb-2"><label class="form-label">Sort order</label><input type="number" name="sort_order" class="form-control" value="0"></div>
      <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="na">
        <label class="form-check-label" for="na">Active</label></div>
      <div class="form-check"><input class="form-check-input" type="checkbox" name="show_on_public" value="1" checked id="np">
        <label class="form-check-label" for="np">Show on public website</label></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Add Category</button></div>
  </form>
</div></div></div>
