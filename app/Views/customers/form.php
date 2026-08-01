<?php use App\Core\Csrf; $title = $customer ? 'Edit Customer' : 'Add Customer'; ?>
<h4 class="mb-3"><?= e($title) ?></h4>
<form method="post" action="<?= e($customer ? admin_url('customers/' . $customer['id'] . '/update') : admin_url('customers')) ?>">
  <?= Csrf::field() ?>
  <div class="card mb-3"><div class="card-body row g-2">
    <div class="col-md-4"><label class="form-label">Name *</label>
      <input name="name" class="form-control" required value="<?= e($customer['name'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Phone *</label>
      <input name="phone" class="form-control" required maxlength="10" pattern="[0-9]{10}" inputmode="numeric" value="<?= e($customer['phone'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">WhatsApp</label>
      <input name="whatsapp" class="form-control" maxlength="10" value="<?= e($customer['whatsapp'] ?? '') ?>" placeholder="Same as phone"></div>
    <div class="col-md-4"><label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= e($customer['email'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Address</label>
      <input name="address" class="form-control" value="<?= e($customer['address'] ?? '') ?>"></div>
    <div class="col-md-2"><label class="form-label">City</label>
      <input name="city" class="form-control" value="<?= e($customer['city'] ?? '') ?>"></div>
    <div class="col-md-2"><label class="form-label">Pincode</label>
      <input name="pincode" class="form-control" value="<?= e($customer['pincode'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">GSTIN</label>
      <input name="gstin" class="form-control" value="<?= e($customer['gstin'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Customer Type</label>
      <select name="customer_type" class="form-select">
        <?php foreach (['retail', 'dealer', 'corporate'] as $t): ?>
          <option value="<?= $t ?>" <?= ($customer['customer_type'] ?? 'retail') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-4"><label class="form-label">Price Group (dealer pricing)</label>
      <select name="price_group_id" class="form-select">
        <option value="">— None —</option>
        <?php foreach ($priceGroups as $pg): ?>
          <option value="<?= (int)$pg['id'] ?>" <?= (int)($customer['price_group_id'] ?? 0) === (int)$pg['id'] ? 'selected' : '' ?>>
            <?= e($pg['name']) ?> (−<?= e($pg['discount_percent']) ?>%)</option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-8"><label class="form-label">Notes</label>
      <input name="notes" class="form-control" value="<?= e($customer['notes'] ?? '') ?>"></div>
    <div class="col-md-4 d-flex align-items-end">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_blocked" id="is_blocked" value="1" <?= !empty($customer['is_blocked']) ? 'checked' : '' ?>>
        <label class="form-check-label text-danger" for="is_blocked">Block this customer</label>
      </div>
    </div>
  </div></div>
  <div class="sticky-actions d-flex gap-2">
    <button class="btn btn-primary flex-grow-1">Save</button>
    <a href="<?= e(admin_url('customers')) ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>
