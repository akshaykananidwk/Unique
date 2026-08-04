<?php use App\Core\Csrf; $title = 'Track My Orders'; ?>
<div class="container section" style="max-width:460px">
  <div class="track-box text-center">
    <h3 class="section-title"><i class="bi bi-geo-alt text-primary"></i> Track My Orders</h3>
    <p class="section-sub mb-3">Enter your mobile number to see all your orders and their status.</p>
    <form method="post" action="<?= e(base_url('my-orders')) ?>">
      <?= Csrf::field() ?>
      <input type="tel" name="phone" class="form-control form-control-lg text-center mb-3"
             inputmode="tel" placeholder="10-digit mobile" required autofocus>
      <button class="btn btn-primary btn-lg w-100">Track</button>
    </form>
    <p class="small text-muted mt-3 mb-0">Got a WhatsApp tracking link? Just tap it — no typing needed.</p>
  </div>
</div>
