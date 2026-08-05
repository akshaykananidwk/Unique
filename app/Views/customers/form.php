<?php use App\Core\Csrf; $title = $customer ? 'Edit Customer' : 'Add Customer'; ?>
<h4 class="mb-3"><?= e($title) ?></h4>
<form method="post" action="<?= e($customer ? admin_url('customers/' . $customer['id'] . '/update') : admin_url('customers')) ?>">
  <?= Csrf::field() ?>
  <div class="card mb-3"><div class="card-body row g-2">
    <div class="col-md-4"><label class="form-label">Customer / Company Name *</label>
      <input name="name" class="form-control" required value="<?= e($customer['name'] ?? '') ?>"
             placeholder="e.g. Tata Motors, or the person's name">
      <div class="form-text">The account work is billed to. People are listed below.</div></div>
    <div class="col-md-4"><label class="form-label">Main Number *</label>
      <input name="phone" class="form-control" required inputmode="tel" placeholder="10-digit mobile" value="<?= e($customer['phone'] ?? '') ?>">
      <div class="form-text">Used for WhatsApp and order tracking.</div></div>
    <div class="col-md-4"><label class="form-label">WhatsApp</label>
      <input name="whatsapp" class="form-control" inputmode="tel" value="<?= e($customer['whatsapp'] ?? '') ?>" placeholder="Same as phone"></div>
    <div class="col-md-4"><label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= e($customer['email'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Address</label>
      <input name="address" class="form-control" value="<?= e($customer['address'] ?? '') ?>"></div>
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

<?php if ($customer): ?>
<!-- People. A company gives work through several of them, each with their own number,
     and every order records which one it came from. -->
<div class="card mt-3"><div class="card-body">
  <h6>People at <?= e($customer['name']) ?> <span class="text-muted fs-6">(<?= count($contacts) ?>)</span></h6>
  <p class="small text-muted">
    Any of these numbers finds this customer on the New Order screen, and every order they
    give is listed together under this one account.
  </p>

  <?php // The row forms live outside the table on purpose: a <form> inside <tr> is invalid
  // HTML and the browser hoists it out, so the inputs bind by form="" id instead.
  foreach ($contacts as $ct): ?>
    <form method="post" id="ct<?= (int)$ct['id'] ?>"
          action="<?= e(admin_url('customers/' . $customer['id'] . '/contacts/' . $ct['id'] . '/update')) ?>">
      <?= Csrf::field() ?>
    </form>
  <?php endforeach; ?>

  <div class="table-responsive"><table class="table table-sm align-middle table-mobile">
    <thead><tr><th>Name</th><th>Mobile</th><th>WhatsApp</th><th>Role</th><th>Main</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($contacts as $ct): ?>
      <tr>
        <td data-label="Name"><input form="ct<?= (int)$ct['id'] ?>" name="name" class="form-control form-control-sm" value="<?= e($ct['name']) ?>"></td>
        <td data-label="Mobile"><input form="ct<?= (int)$ct['id'] ?>" name="phone" class="form-control form-control-sm" inputmode="tel" value="<?= e($ct['phone']) ?>"></td>
        <td data-label="WhatsApp"><input form="ct<?= (int)$ct['id'] ?>" name="whatsapp" class="form-control form-control-sm" inputmode="tel" value="<?= e($ct['whatsapp']) ?>"></td>
        <td data-label="Role"><input form="ct<?= (int)$ct['id'] ?>" name="designation" class="form-control form-control-sm" placeholder="e.g. Purchase" value="<?= e($ct['designation'] ?? '') ?>"></td>
        <td data-label="Main">
          <?php if ((int)$ct['is_primary'] === 1): ?>
            <span class="badge bg-primary">Main</span>
          <?php else: ?>
            <div class="form-check"><input form="ct<?= (int)$ct['id'] ?>" class="form-check-input" type="checkbox" name="make_primary" value="1"
                   id="mp<?= (int)$ct['id'] ?>"><label class="form-check-label small" for="mp<?= (int)$ct['id'] ?>">Make main</label></div>
          <?php endif; ?>
        </td>
        <td data-label="" class="text-nowrap">
          <button form="ct<?= (int)$ct['id'] ?>" class="btn btn-sm btn-outline-primary" title="Save"><i class="bi bi-check-lg"></i></button>
          <?php if (count($contacts) > 1): ?>
          <form method="post" class="d-inline" action="<?= e(admin_url('customers/' . $customer['id'] . '/contacts/' . $ct['id'] . '/delete')) ?>"
                data-confirm="Remove <?= e($ct['name']) ?>? Their past orders stay on this customer.">
            <?= Csrf::field() ?><button class="btn btn-sm btn-outline-danger" title="Remove"><i class="bi bi-trash"></i></button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>

  <form method="post" action="<?= e(admin_url('customers/' . $customer['id'] . '/contacts')) ?>" class="row g-2 mt-1">
    <?= Csrf::field() ?>
    <div class="col-md-3"><input name="contact_name" class="form-control form-control-sm" placeholder="Person's name" required></div>
    <div class="col-md-3"><input name="contact_phone" class="form-control form-control-sm" inputmode="tel" placeholder="Their mobile" required></div>
    <div class="col-md-2"><input name="contact_whatsapp" class="form-control form-control-sm" inputmode="tel" placeholder="WhatsApp"></div>
    <div class="col-md-2"><input name="contact_designation" class="form-control form-control-sm" placeholder="Role"></div>
    <div class="col-md-2"><button class="btn btn-sm btn-success w-100"><i class="bi bi-plus-lg"></i> Add person</button></div>
  </form>
</div></div>
<?php endif; ?>
