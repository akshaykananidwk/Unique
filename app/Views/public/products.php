<?php $title = 'Products'; ?>
<div class="container section">
  <h1 class="section-title">Our Products</h1>
  <p class="section-sub">Choose a product to customise and place your order.</p>

  <?php if (!$categories): ?>
    <div class="empty-state"><i class="bi bi-box"></i>The catalogue is being set up — please check back soon.</div>
  <?php else: ?>

  <!-- Quick category jump -->
  <div class="d-flex flex-wrap gap-2 mb-4">
    <?php foreach ($categories as $c): if (!$c['items']) continue; ?>
      <a class="btn btn-outline-secondary btn-sm rounded-pill" href="#cat-<?= e($c['slug']) ?>">
        <i class="bi bi-<?= e(category_icon($c)) ?>"></i> <?= e($c['name']) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php foreach ($categories as $c): if (!$c['items']) continue; ?>
  <section class="mb-5" id="cat-<?= e($c['slug']) ?>">
    <div class="d-flex align-items-center gap-2 mb-1">
      <span class="cat-ico" style="width:44px;height:44px;font-size:1.3rem;margin:0"><i class="bi bi-<?= e(category_icon($c)) ?>"></i></span>
      <h4 class="mb-0"><?= e($c['name']) ?></h4>
    </div>
    <?php if ($c['description']): ?><p class="text-muted small mb-3"><?= e($c['description']) ?></p><?php endif; ?>
    <div class="prod-grid">
      <?php foreach ($c['items'] as $i): ?>
      <a class="prod-card text-decoration-none text-reset" href="<?= e(base_url('product/' . strtolower((string)$i['sku']))) ?>">
        <div class="prod-thumb">
          <?php if ($i['image']): ?><img src="<?= e(upload_url($i['image'])) ?>" alt=""><?php else: ?><i class="bi bi-<?= e(category_icon($c)) ?>"></i><?php endif; ?>
        </div>
        <div class="prod-body">
          <h6><?= e($i['name']) ?></h6>
          <div class="prod-desc"><?= e($i['short_description']) ?></div>
          <span class="btn btn-primary btn-sm mt-2"><i class="bi bi-bag-plus"></i> Order</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
