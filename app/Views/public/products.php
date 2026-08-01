<?php $title = 'Products'; ?>
<div class="container py-4">
  <h2 class="mb-4">Our Products</h2>
  <?php if (!$categories): ?><div class="empty-state"><i class="bi bi-box"></i>The catalogue is being set up — please check back soon.</div><?php endif; ?>
  <?php foreach ($categories as $c): if (!$c['items']) continue; ?>
  <section class="mb-5" id="cat-<?= e($c['slug']) ?>">
    <h4><?= e($c['name']) ?></h4>
    <?php if ($c['description']): ?><p class="text-muted small"><?= e($c['description']) ?></p><?php endif; ?>
    <div class="row g-3">
      <?php foreach ($c['items'] as $i): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100">
          <?php if ($i['image']): ?><img src="<?= e(upload_url($i['image'])) ?>" class="card-img-top" style="height:130px;object-fit:cover" alt=""><?php endif; ?>
          <div class="card-body d-flex flex-column">
            <h6><?= e($i['name']) ?></h6>
            <small class="text-muted flex-grow-1"><?= e($i['short_description']) ?></small>
            <div class="fw-bold text-primary my-2">From <?= e(fmt_money($i['base_price'])) ?>/<?= e($i['unit']) ?></div>
            <a class="btn btn-primary btn-sm" href="<?= e(base_url('product/' . strtolower((string)$i['sku']))) ?>">Order</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>
</div>
