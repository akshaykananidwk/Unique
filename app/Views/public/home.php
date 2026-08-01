<?php use App\Core\Settings; $title = (string)Settings::get('business_name', 'Krishna Print'); ?>
<section class="hero text-center">
  <div class="container">
    <h1 class="display-5 fw-bold"><?= e($title) ?></h1>
    <p class="lead"><?= e((string)Settings::get('business_tagline', 'Quality printing, on time')) ?></p>
    <div class="d-flex gap-2 justify-content-center">
      <a class="btn btn-light btn-lg" href="<?= e(base_url('products')) ?>">Order Now</a>
      <a class="btn btn-outline-light btn-lg" href="<?= e(base_url('track')) ?>">Track Order</a>
    </div>
  </div>
</section>

<section class="container py-5">
  <h3 class="mb-3">What we print</h3>
  <div class="row g-3">
    <?php foreach ($categories as $c): ?>
    <div class="col-6 col-md-3">
      <a class="card card-category h-100 text-decoration-none" href="<?= e(base_url('products')) ?>#cat-<?= e($c['slug']) ?>">
        <?php if ($c['image']): ?><img src="<?= e(upload_url($c['image'])) ?>" class="card-img-top" alt=""><?php endif; ?>
        <div class="card-body"><h6 class="mb-1"><?= e($c['name']) ?></h6>
          <small class="text-muted"><?= e(mb_substr((string)$c['description'], 0, 60)) ?></small></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php if ($items): ?>
<section class="container pb-5">
  <h3 class="mb-3">Popular products</h3>
  <div class="row g-3">
    <?php foreach ($items as $i): ?>
    <div class="col-6 col-md-3">
      <a class="card h-100 text-decoration-none" href="<?= e(base_url('product/' . strtolower((string)$i['sku']))) ?>">
        <div class="card-body">
          <h6><?= e($i['name']) ?></h6>
          <small class="text-muted"><?= e($i['short_description']) ?></small>
          <div class="mt-2 fw-bold text-primary">From <?= e(fmt_money($i['base_price'])) ?>/<?= e($i['unit']) ?></div>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="container pb-5">
  <div class="card"><div class="card-body row g-3">
    <div class="col-md-6">
      <h5>Visit us</h5>
      <p class="mb-1"><?= e((string)Settings::get('business_address', '')) ?>, <?= e((string)Settings::get('business_city', '')) ?></p>
      <p class="mb-1">📞 <?= e((string)Settings::get('business_phone', '')) ?></p>
      <p class="mb-0">✉️ <?= e((string)Settings::get('business_email', '')) ?></p>
    </div>
    <div class="col-md-6">
      <h5>How it works</h5>
      <ol class="small mb-0">
        <li>Place your order online or at the counter</li>
        <li>Get your job number + tracking link on WhatsApp</li>
        <li>Approve your design proof from your phone</li>
        <li>We print &amp; deliver — you stay updated at every step</li>
      </ol>
    </div>
  </div></div>
</section>
