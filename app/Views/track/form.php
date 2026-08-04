<?php use App\Core\Csrf; $title = 'Track Order'; ?>
<div class="container py-5" style="max-width:460px">
  <div class="card"><div class="card-body p-4">
    <h4 class="text-center mb-3"><i class="bi bi-geo"></i> Track Your Order</h4>
    <form method="post" action="<?= e(base_url('track')) ?>">
      <?= Csrf::field() ?>
      <div class="mb-3">
        <label class="form-label">Job Number *</label>
        <input name="job_no" class="form-control form-control-lg" placeholder="e.g. JOB-S1-2608-0042" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Registered Mobile Number *</label>
        <input type="tel" name="phone" class="form-control form-control-lg" inputmode="tel" placeholder="10-digit mobile" required>
        <div class="form-text">The mobile number used when placing the order.</div>
      </div>
      <button class="btn btn-primary btn-lg w-100">Track Order</button>
    </form>
    <p class="small text-muted text-center mt-3 mb-0">Tip: the WhatsApp message we sent you has a direct tracking link — no typing needed.</p>
  </div></div>
</div>
