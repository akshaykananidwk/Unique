<?php use App\Core\Csrf; $title = 'Verify'; ?>
<div class="container section" style="max-width:420px">
  <div class="track-box text-center">
    <i class="bi bi-whatsapp text-success" style="font-size:2.4rem"></i>
    <h4 class="mt-2">Check WhatsApp</h4>
    <p class="text-muted">We sent a 6-digit code to <strong><?= e($phone) ?></strong>.</p>
    <form method="post" action="<?= e(base_url('my-orders/verify')) ?>">
      <?= Csrf::field() ?>
      <input type="text" id="otpInput" name="otp" class="form-control form-control-lg text-center mb-3" style="letter-spacing:8px"
             maxlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="••••••" required>
      <button class="btn btn-primary btn-lg w-100">See My Orders</button>
    </form>
    <a href="<?= e(base_url('my-orders')) ?>" class="small d-block mt-3">Use a different number</a>
  </div>
</div>
