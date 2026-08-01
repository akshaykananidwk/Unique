<?php use App\Core\Csrf; $title = 'Branches'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Branches</h4>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#branchModal"><i class="bi bi-plus-lg"></i> Add Branch</button>
</div>
<div class="row g-3">
<?php foreach ($branches as $b): ?>
  <div class="col-md-6 col-lg-4">
    <div class="card h-100 <?= (int)$b['is_active'] ? '' : 'opacity-50' ?>"><div class="card-body">
      <form method="post" action="<?= e(admin_url('branches/' . $b['id'] . '/update')) ?>" class="row g-2">
        <?= Csrf::field() ?>
        <div class="col-8"><input name="name" class="form-control form-control-sm fw-semibold" value="<?= e($b['name']) ?>"></div>
        <div class="col-4"><input class="form-control form-control-sm" value="<?= e($b['code']) ?>" disabled title="Branch code (fixed — used in job numbers)"></div>
        <div class="col-6"><select name="type" class="form-select form-select-sm">
          <?php foreach (['shop', 'godown', 'office'] as $t): ?>
            <option value="<?= $t ?>" <?= $b['type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
          <?php endforeach; ?></select></div>
        <div class="col-6"><input name="invoice_prefix" class="form-control form-control-sm" placeholder="Receipt prefix" value="<?= e($b['invoice_prefix']) ?>"></div>
        <div class="col-12"><input name="address" class="form-control form-control-sm" placeholder="Address" value="<?= e($b['address']) ?>"></div>
        <div class="col-6"><input name="city" class="form-control form-control-sm" placeholder="City" value="<?= e($b['city']) ?>"></div>
        <div class="col-6"><input name="phone" class="form-control form-control-sm" placeholder="Phone" value="<?= e($b['phone']) ?>"></div>
        <div class="col-6"><input name="whatsapp" class="form-control form-control-sm" placeholder="WhatsApp" value="<?= e($b['whatsapp']) ?>"></div>
        <div class="col-6"><input name="gstin" class="form-control form-control-sm" placeholder="GSTIN" value="<?= e($b['gstin']) ?>"></div>
        <div class="col-4"><input type="number" name="sort_order" class="form-control form-control-sm" value="<?= (int)$b['sort_order'] ?>" title="Sort order"></div>
        <div class="col-4 form-check ms-2 pt-1">
          <input class="form-check-input" type="checkbox" name="is_active" value="1" id="ba<?= (int)$b['id'] ?>" <?= (int)$b['is_active'] ? 'checked' : '' ?>>
          <label class="form-check-label small" for="ba<?= (int)$b['id'] ?>">Active</label></div>
        <div class="col-3"><button class="btn btn-sm btn-outline-primary w-100">Save</button></div>
      </form>
    </div></div>
  </div>
<?php endforeach; ?>
</div>

<div class="modal fade" id="branchModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="post" action="<?= e(admin_url('branches')) ?>">
    <?= Csrf::field() ?>
    <div class="modal-header"><h5 class="modal-title">Add Branch</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body row g-2">
      <div class="col-8"><label class="form-label">Name *</label><input name="name" class="form-control" required></div>
      <div class="col-4"><label class="form-label">Code *</label><input name="code" class="form-control" required maxlength="10" placeholder="S1"></div>
      <div class="col-6"><label class="form-label">Type</label><select name="type" class="form-select">
        <option value="shop">Shop</option><option value="godown">Godown</option><option value="office">Office</option></select></div>
      <div class="col-6"><label class="form-label">Receipt prefix</label><input name="invoice_prefix" class="form-control"></div>
      <div class="col-12"><label class="form-label">Address</label><input name="address" class="form-control"></div>
      <div class="col-6"><label class="form-label">City</label><input name="city" class="form-control"></div>
      <div class="col-6"><label class="form-label">Phone</label><input name="phone" class="form-control"></div>
      <input type="hidden" name="is_active" value="1">
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Add Branch</button></div>
  </form>
</div></div></div>
