<?php use App\Core\Csrf; $title = 'Checkout'; ?>
<div class="container py-4" style="max-width:640px">
  <h2 class="mb-4">Your Details</h2>
  <form method="post" action="<?= e(base_url('checkout')) ?>">
    <?= Csrf::field() ?>
    <!-- Honeypot: real users never see or fill this -->
    <div style="position:absolute;left:-9999px" aria-hidden="true">
      <input type="text" name="company_website" tabindex="-1" autocomplete="off">
    </div>
    <div class="card mb-3"><div class="card-body row g-2">
      <div class="col-md-6"><label class="form-label">Your Name *</label>
        <input name="name" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label">Mobile Number * <small class="text-muted">(WhatsApp updates go here)</small></label>
        <input type="tel" name="phone" class="form-control" required maxlength="10" pattern="[0-9]{10}" inputmode="numeric"></div>
      <div class="col-md-8"><label class="form-label">Address</label>
        <input name="address" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">City</label>
        <input name="city" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Pickup Branch *</label>
        <select name="branch_id" class="form-select" required>
          <?php foreach ($branches as $b): ?>
            <option value="<?= (int)$b['id'] ?>"><?= e($b['name']) ?><?= $b['city'] ? ' — ' . e($b['city']) : '' ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="col-md-6"><label class="form-label">Delivery Preference</label>
        <select name="delivery_type" class="form-select">
          <option value="pickup">I will pick up</option>
          <option value="delivery">Deliver to my address</option>
        </select></div>
      <div class="col-12"><label class="form-label">Note for us (optional)</label>
        <textarea name="note" class="form-control" rows="2"></textarea></div>
    </div></div>
    <?php if ($otpRequired): ?>
      <p class="small text-muted">We will send a 6-digit code on WhatsApp to verify your number before confirming the order.</p>
    <?php endif; ?>
    <button class="btn btn-primary btn-lg w-100"><?= $otpRequired ? 'Verify & Place Order' : 'Place Order' ?></button>
  </form>
</div>
