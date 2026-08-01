<?php use App\Core\Acl; $title = 'Customers'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Customers <span class="text-muted fs-6">(<?= (int)$total ?>)</span></h4>
  <?php if (Acl::can('customer.create')): ?>
    <a class="btn btn-primary btn-sm" href="<?= e(admin_url('customers/create')) ?>"><i class="bi bi-plus-lg"></i> Add Customer</a>
  <?php endif; ?>
</div>
<form method="get" class="mb-3 d-flex gap-2">
  <input name="q" value="<?= e($q) ?>" class="form-control form-control-sm" style="max-width:280px" placeholder="Name / phone / city">
  <button class="btn btn-outline-primary btn-sm">Search</button>
</form>
<?php if (!$customers): ?>
  <div class="empty-state"><i class="bi bi-people"></i>No customers found.</div>
<?php else: ?>
<div class="table-responsive">
<table class="table table-sm table-hover align-middle table-mobile">
  <thead><tr><th>Name</th><th>Phone</th><th>City</th><th>Type</th><th>Orders</th><th>Outstanding</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($customers as $c): ?>
    <tr>
      <td data-label="Name"><a href="<?= e(admin_url('customers/' . $c['id'])) ?>" class="fw-semibold"><?= e($c['name']) ?></a>
        <?php if ((int)$c['is_blocked']): ?><span class="badge bg-danger badge-status">Blocked</span><?php endif; ?></td>
      <td data-label="Phone"><?= e($c['phone']) ?></td>
      <td data-label="City"><?= e($c['city'] ?? '—') ?></td>
      <td data-label="Type"><span class="badge bg-secondary badge-status"><?= e($c['customer_type']) ?></span></td>
      <td data-label="Orders"><?= (int)$c['order_count'] ?></td>
      <td data-label="Outstanding" class="<?= (float)$c['outstanding'] > 0 ? 'text-danger fw-semibold' : '' ?>"><?= e(fmt_money($c['outstanding'])) ?></td>
      <td data-label="">
        <a class="btn btn-sm btn-outline-success" target="_blank" href="https://wa.me/<?= e(normalize_phone($c['phone']) ?? '') ?>"><i class="bi bi-whatsapp"></i></a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php $pages = (int)ceil($total / $perPage); if ($pages > 1): ?>
<nav><ul class="pagination pagination-sm">
  <?php for ($p = 1; $p <= $pages; $p++): $qs = $_GET; $qs['page'] = $p; ?>
    <li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query($qs)) ?>"><?= $p ?></a></li>
  <?php endfor; ?>
</ul></nav>
<?php endif; endif; ?>
