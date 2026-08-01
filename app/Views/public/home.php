<?php use App\Core\Csrf; use App\Core\Settings; $title = (string)Settings::get('business_name', 'Krishna Print'); ?>

<!-- HERO -->
<section class="hero">
  <?= kp_cubes_svg('hero-cubes') ?>
  <div class="container position-relative" style="z-index:2">
    <div class="row align-items-center g-4">
      <div class="col-lg-6 text-center text-lg-start">
        <span class="hero-badges d-inline-block mb-3"><span><i class="bi bi-stars"></i> Screen printing · Digital · Large format</span></span>
        <h1 class="display-3 mb-2"><?= e($title) ?></h1>
        <p class="lead mb-4"><?= e((string)Settings::get('business_tagline', 'We print what you think')) ?></p>
        <div class="d-flex gap-2 justify-content-center justify-content-lg-start flex-wrap mb-4">
          <a class="btn btn-light btn-lg" href="<?= e(base_url('products')) ?>"><i class="bi bi-bag-plus"></i> Order Now</a>
          <a class="btn btn-outline-light btn-lg" href="#track"><i class="bi bi-geo-alt"></i> Track Order</a>
        </div>
        <div class="hero-badges d-flex gap-2 justify-content-center justify-content-lg-start flex-wrap">
          <span><i class="bi bi-whatsapp"></i> WhatsApp updates</span>
          <span><i class="bi bi-check2-circle"></i> Approve on phone</span>
          <span><i class="bi bi-truck"></i> Fast delivery</span>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-3d">
          <div class="card3d c-a"><span class="chip"></span><div class="c3-label">YOUR BRAND</div><div class="c3-sub">Visiting Cards · Premium finish</div></div>
          <div class="card3d c-b"><div class="c3-label">GRAND OPENING</div><div class="c3-sub">Flex Banners</div></div>
          <div class="card3d c-c"><div class="c3-label">50% OFF</div><div class="c3-sub">Stickers &amp; Labels</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TRUST STRIP -->
<section class="trust-strip">
  <div class="container">
    <div class="row">
      <div class="col-6 col-md-3 trust-item"><i class="bi bi-lightning-charge-fill ti"></i><div><b>Same-day</b><small>proofs on WhatsApp</small></div></div>
      <div class="col-6 col-md-3 trust-item"><i class="bi bi-palette-fill ti"></i><div><b>Free design</b><small>help &amp; proof</small></div></div>
      <div class="col-6 col-md-3 trust-item"><i class="bi bi-award-fill ti"></i><div><b>Premium</b><small>quality prints</small></div></div>
      <div class="col-6 col-md-3 trust-item"><i class="bi bi-emoji-smile-fill ti"></i><div><b>Trusted by</b><small>businesses locally</small></div></div>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<section class="section">
  <div class="container reveal">
    <h2 class="section-title text-center">What we print</h2>
    <p class="section-sub text-center">Pick a category and place your order in a minute.</p>
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

<!-- FEATURED PRODUCTS -->
<?php if ($items): ?>
<section class="section pt-0">
  <div class="container reveal">
    <h2 class="section-title text-center">Popular products</h2>
    <p class="section-sub text-center">Tap any product to customise and order.</p>
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

<!-- WHY CHOOSE US -->
<section class="section pt-0">
  <div class="container reveal">
    <h2 class="section-title text-center">Why choose us</h2>
    <p class="section-sub text-center">Everything a growing business needs from its print partner.</p>
    <div class="row g-3">
      <?php foreach ([
        ['whatsapp', 'Order &amp; track on WhatsApp', 'Place an order, get your job number and a live tracking link — all on your phone.'],
        ['palette2', 'Approve your design online', 'See the design proof, zoom in, and tap Approve or ask for changes by voice or text.'],
        ['printer', 'Every kind of printing', 'Visiting cards, flex banners, bill books, stickers, boards and more — one place.'],
        ['shield-check', 'On-time, every time', 'Clear due dates and status updates at every step, so you are never left guessing.'],
      ] as [$icon, $t, $d]): ?>
      <div class="col-6 col-lg-3">
        <div class="why-card">
          <span class="wi"><i class="bi bi-<?= e($icon) ?>"></i></span>
          <h6 class="fw-bold"><?= $t ?></h6>
          <p class="small text-muted mb-0"><?= e($d) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TRACK -->
<section class="section pt-0" id="track">
  <div class="container">
    <div class="cta-band reveal">
      <div class="row align-items-center g-3">
        <div class="col-lg-7 text-center text-lg-start">
          <h2 class="mb-1"><i class="bi bi-geo-alt"></i> Track your order</h2>
          <p class="mb-0 opacity-75">Enter your mobile number to see all your orders and their live status.</p>
        </div>
        <div class="col-lg-5">
          <form method="post" action="<?= e(base_url('my-orders')) ?>" class="d-flex gap-2">
            <?= Csrf::field() ?>
            <input type="tel" name="phone" class="form-control form-control-lg" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" placeholder="10-digit mobile" required>
            <button class="btn btn-light btn-lg px-4">Track</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section pt-0">
  <div class="container reveal">
    <h2 class="section-title text-center">How it works</h2>
    <p class="section-sub text-center">Four simple steps.</p>
    <div class="row g-3">
      <?php foreach ([
        ['bag-check', 'Place your order', 'Choose a product, add your details and submit — online or at our counter.'],
        ['whatsapp', 'Get your job number', 'We send your job number and a tracking link on WhatsApp instantly.'],
        ['palette', 'Approve your design', 'Check the design proof on your phone and tap Approve, or ask for changes.'],
        ['truck', 'Printed &amp; delivered', 'We print, finish and deliver — you stay updated at every step.'],
      ] as $n => [$icon, $t, $d]): ?>
      <div class="col-6 col-md-3">
        <div class="feature">
          <span class="fi"><i class="bi bi-<?= e($icon) ?>"></i></span>
          <div class="fw-bold"><span class="step-num"><?= $n + 1 ?></span><?= $t ?></div>
          <p class="small text-muted mt-2 mb-0"><?= e($d) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CONTACT -->
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
          <a class="btn btn-primary btn-lg" href="<?= e(base_url('products')) ?>"><i class="bi bi-bag-plus"></i> Start an order</a>
        </div>
      </div>
    </div>
  </div>
</section>
