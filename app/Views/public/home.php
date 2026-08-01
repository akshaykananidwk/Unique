<?php use App\Core\Csrf; use App\Core\Settings; $title = (string)Settings::get('business_name', 'Krishna Print'); ?>
<section class="hero text-center">
  <?= kp_cubes_svg('hero-cubes') ?>
  <div class="container position-relative" style="z-index:1">
    <h1 class="display-4"><?= e($title) ?></h1>
    <p class="lead mb-4"><?= e((string)Settings::get('business_tagline', 'Quality printing, delivered on time')) ?></p>
    <div class="d-flex gap-2 justify-content-center flex-wrap mb-4">
      <a class="btn btn-light" href="<?= e(base_url('products')) ?>"><i class="bi bi-bag-plus"></i> Order Now</a>
      <a class="btn btn-outline-light" href="#track"><i class="bi bi-geo-alt"></i> Track Order</a>
    </div>
    <div class="hero-badges d-flex gap-2 justify-content-center flex-wrap">
      <span><i class="bi bi-whatsapp"></i> WhatsApp updates</span>
      <span><i class="bi bi-check2-circle"></i> Approve design on phone</span>
      <span><i class="bi bi-truck"></i> Fast delivery</span>
    </div>
  </div>
</section>

<!-- Categories -->
<section class="section">
  <div class="container">
    <h2 class="section-title">What we print</h2>
    <p class="section-sub">Pick a category and place your order in a minute.</p>
    <?php if (!$categories): ?>
      <div class="empty-state"><i class="bi bi-box"></i>The catalogue is being set up — please check back soon.</div>
    <?php else: ?>
    <div class="cat-grid">
      <?php foreach ($categories as $c): ?>
      <a class="cat-tile" href="<?= e(base_url('products')) ?>#cat-<?= e($c['slug']) ?>">
        <?php if ($c['image']): ?>
          <img class="cat-photo" src="<?= e(upload_url($c['image'])) ?>" alt="<?= e($c['name']) ?>">
        <?php else: ?>
          <span class="cat-ico"><i class="bi bi-<?= e(category_icon($c)) ?>"></i></span>
        <?php endif; ?>
        <span class="cat-name"><?= e($c['name']) ?></span>
        <?php if (!empty($c['item_count'])): ?><span class="cat-count"><?= (int)$c['item_count'] ?> options</span><?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- Popular products -->
<?php if ($items): ?>
<section class="section pt-0">
  <div class="container">
    <h2 class="section-title">Popular products</h2>
    <p class="section-sub">Tap any product to customise and order.</p>
    <div class="prod-grid">
      <?php foreach ($items as $i): ?>
      <a class="prod-card text-decoration-none text-reset" href="<?= e(base_url('product/' . strtolower((string)$i['sku']))) ?>">
        <div class="prod-thumb">
          <?php if ($i['image']): ?><img src="<?= e(upload_url($i['image'])) ?>" alt=""><?php else: ?><i class="bi bi-<?= e(category_icon(['name' => $i['name']])) ?>"></i><?php endif; ?>
        </div>
        <div class="prod-body">
          <h6><?= e($i['name']) ?></h6>
          <div class="prod-desc"><?= e($i['short_description']) ?></div>
          <span class="btn btn-primary btn-sm mt-2"><i class="bi bi-bag-plus"></i> Order</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Track by mobile -->
<section class="section" id="track" style="background:linear-gradient(180deg,transparent,color-mix(in srgb,var(--kp-brand) 6%,transparent))">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="track-box text-center">
          <h2 class="section-title"><i class="bi bi-geo-alt text-primary"></i> Track your order</h2>
          <p class="section-sub mb-3">Enter your mobile number to see all your orders and their live status.</p>
          <form method="post" action="<?= e(base_url('my-orders')) ?>" class="row g-2 justify-content-center">
            <?= Csrf::field() ?>
            <div class="col-sm-7">
              <input type="tel" name="phone" class="form-control form-control-lg text-center" maxlength="10"
                     pattern="[0-9]{10}" inputmode="numeric" placeholder="Your 10-digit mobile" required>
            </div>
            <div class="col-sm-4 d-grid">
              <button class="btn btn-primary btn-lg">Track</button>
            </div>
          </form>
          <p class="small text-muted mt-2 mb-0">Have a tracking link from WhatsApp? Just tap it — no typing needed.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- How it works -->
<section class="section pt-0">
  <div class="container">
    <h2 class="section-title text-center">How it works</h2>
    <p class="section-sub text-center">Four simple steps.</p>
    <div class="row g-3">
      <?php foreach ([
        ['bag-check', 'Place your order', 'Choose a product, add your details and submit — online or at our counter.'],
        ['whatsapp', 'Get your job number', 'We send your job number and a tracking link on WhatsApp instantly.'],
        ['palette', 'Approve your design', 'Check the design proof on your phone and tap Approve, or ask for changes.'],
        ['truck', 'Printed & delivered', 'We print, finish and deliver — you stay updated at every step.'],
      ] as $n => [$icon, $t, $d]): ?>
      <div class="col-6 col-md-3">
        <div class="feature">
          <span class="fi"><i class="bi bi-<?= e($icon) ?>"></i></span>
          <div class="fw-bold"><span class="step-num"><?= $n + 1 ?></span><?= e($t) ?></div>
          <p class="small text-muted mt-2 mb-0"><?= e($d) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Contact -->
<section class="section pt-0">
  <div class="container">
    <div class="track-box">
      <div class="row g-3 align-items-center">
        <div class="col-md-8">
          <h4 class="mb-1"><?= e($title) ?></h4>
          <div class="text-muted"><?= e((string)Settings::get('business_address', '')) ?> <?= e((string)Settings::get('business_city', '')) ?></div>
          <div class="mt-1"><i class="bi bi-telephone"></i> <?= e((string)Settings::get('business_phone', '')) ?>
            <?php if ($email = (string)Settings::get('business_email', '')): ?> &nbsp;·&nbsp; <i class="bi bi-envelope"></i> <?= e($email) ?><?php endif; ?></div>
        </div>
        <div class="col-md-4 text-md-end">
          <a class="btn btn-primary" href="<?= e(base_url('products')) ?>"><i class="bi bi-bag-plus"></i> Start an order</a>
        </div>
      </div>
    </div>
  </div>
</section>
