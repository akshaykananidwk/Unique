<?php $title = 'Items'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Items</h4>
  <a class="btn btn-primary btn-sm" href="<?= e(admin_url('items/create')) ?>"><i class="bi bi-plus-lg"></i> Add Item</a>
</div>
<?php if (!$items): ?><div class="empty-state"><i class="bi bi-box-seam"></i>No items yet. Adding an item here immediately adds it to the order forms.</div><?php endif; ?>
<div class="table-responsive"><table class="table table-sm table-hover table-mobile align-middle">
  <thead><tr><th>Item</th><th>Category</th><th>Pricing</th><th>Base ₹</th><th>Tax %</th><th>Design?</th><th>Public?</th><th>Options</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($items as $i): ?>
    <tr class="<?= (int)$i['is_active'] ? '' : 'opacity-50' ?>">
      <td data-label="Item"><a class="fw-semibold" href="<?= e(admin_url('items/' . $i['id'] . '/edit')) ?>"><?= e($i['name']) ?></a>
        <div class="small text-muted"><?= e($i['sku']) ?> · <?= e($i['unit']) ?></div></td>
      <td data-label="Category"><?= e($i['category_name']) ?></td>
      <td data-label="Pricing"><span class="badge bg-secondary badge-status"><?= e($i['pricing_type']) ?></span></td>
      <td data-label="Base ₹"><?= e(number_format((float)$i['base_price'], 2)) ?></td>
      <td data-label="Tax %"><?= e($i['tax_percent']) ?></td>
      <td data-label="Design?"><?= (int)$i['requires_design'] ? '✔' : '—' ?></td>
      <td data-label="Public?"><?= (int)$i['show_on_public'] ? '✔' : '—' ?></td>
      <td data-label="Options"><?= (int)$i['option_count'] ?></td>
      <td data-label=""><a class="btn btn-sm btn-outline-primary" href="<?= e(admin_url('items/' . $i['id'] . '/edit')) ?>"><i class="bi bi-pencil"></i></a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
