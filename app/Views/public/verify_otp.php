<?php use App\Core\Csrf; $title = 'Verify your number'; ?>
<div class="container py-5" style="max-width:420px">
  <div class="card"><div class="card-body text-center">
    <i class="bi bi-whatsapp fs-1 text-success"></i>
    <h4 class="mt-2">Check WhatsApp</h4>
    <p class="text-muted">We sent a 6-digit code to <strong><?= e($phone) ?></strong>.</p>
    <form method="post" action="<?= e(base_url('checkout/verify-otp')) ?>">
      <?= Csrf::field() ?>
      <input type="text" id="otpInput" name="otp" class="form-control form-control-lg text-center mb-3" style="letter-spacing:8px"
             maxlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="••••••" required>
      <button class="btn btn-primary btn-lg w-100">Confirm Order</button>
    </form>
    <form method="post" action="<?= e(base_url('checkout/resend-otp')) ?>" class="mt-3">
      <?= Csrf::field() ?>
      <button class="btn btn-link btn-sm">Didn't get it? Resend code</button>
    </form>
  </div></div>
</div>
