<?php use App\Core\Acl; use App\Core\Csrf; use App\Models\Status;
$title = 'Orders';
$backUrl = admin_url('orders') . ($_GET ? '?' . http_build_query($_GET) : '');
$qs = $_GET;
unset($qs['page']);
$filterQs = http_build_query($qs);

/** A column heading that sorts. Clicking the one already in use turns it round. */
$sortLink = function (string $key, string $label) use ($qs, $sort, $dir): string {
    $next = ($sort === $key && $dir === 'asc') ? 'desc' : 'asc';
    $arrow = $sort === $key ? ($dir === 'asc' ? ' <i class="bi bi-caret-up-fill"></i>' : ' <i class="bi bi-caret-down-fill"></i>') : '';
    $url = '?' . http_build_query(array_merge($qs, ['sort' => $key, 'dir' => $next, 'page' => 1]));
    $cls = $sort === $key ? 'text-body fw-bold' : 'text-body-secondary';
    return '<a class="text-decoration-none ' . $cls . '" href="' . e($url) . '" title="Sort by ' . e($label) . '">'
        . e($label) . $arrow . '</a>';
};
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h4 class="mb-0">Orders <span class="text-muted fs-6">(<?= (int)$total ?>)</span></h4>
  <div class="d-flex flex-wrap gap-2">
    <?php // The tick-all lives here as well as in the heading — on a phone the heading row
    // is not drawn at all, and picking rows has to work there too. ?>
    <?php if ($orders): ?>
      <button type="button" class="btn btn-outline-secondary btn-sm" id="orderSelectAllBtn"
              title="Tick every order on this page"><i class="bi bi-check2-square"></i> Select all</button>
    <?php endif; ?>
    <?php // Print or export exactly what is filtered — the daily hand-out. ?>
    <a href="<?= e(admin_url('orders/print') . ($filterQs ? '?' . $filterQs : '')) ?>"
       target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm" id="printAll">
      <i class="bi bi-printer"></i> Print list
    </a>
    <a href="<?= e(admin_url('orders/export') . ($filterQs ? '?' . $filterQs : '')) ?>"
       class="btn btn-outline-success btn-sm" id="excelAll">
      <i class="bi bi-file-earmark-spreadsheet"></i> Excel
    </a>
    <a href="<?= e(admin_url('orders/create')) ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New Order</a>
  </div>
</div>

<form method="get" class="row g-2 mb-3">
  <?php // Sorting is part of the view, so it must survive a filter. ?>
  <?php if ($sort): ?>
    <input type="hidden" name="sort" value="<?= e($sort) ?>">
    <input type="hidden" name="dir" value="<?= e($dir) ?>">
  <?php endif; ?>
  <div class="col-6 col-md-3"><input name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Job no / customer / phone"></div>
  <div class="col-6 col-md-2">
    <select name="status" class="form-select form-select-sm">
      <option value="">All statuses</option>
      <option value="overdue" <?= $status === 'overdue' ? 'selected' : '' ?>>⚠ Overdue</option>
      <option value="needs_review" <?= $status === 'needs_review' ? 'selected' : '' ?>>🆕 Needs review</option>
      <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>⏳ Still pending (not delivered)</option>
      <?php foreach (array_merge(array_keys(Status::RANKS), Status::SPECIAL) as $s): ?>
        <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(Status::label($s)) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-6 col-md-2">
    <select name="person" class="form-select form-select-sm">
      <option value="">Anyone</option>
      <?php foreach ($staff as $sf): ?>
        <option value="<?= (int)$sf['id'] ?>" <?= $personId === (int)$sf['id'] ? 'selected' : '' ?>><?= e($sf['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-6 col-md-2">
    <select name="person_role" class="form-select form-select-sm">
      <option value="designer" <?= $personRole === 'designer' ? 'selected' : '' ?>>…is designing it</option>
      <option value="taken" <?= $personRole === 'taken' ? 'selected' : '' ?>>…took the order</option>
      <option value="accepted" <?= $personRole === 'accepted' ? 'selected' : '' ?>>…accepted it</option>
    </select>
  </div>
  <div class="col-6 col-md-2"><input type="date" name="from" value="<?= e($_GET['from'] ?? '') ?>" class="form-control form-control-sm"></div>
  <div class="col-6 col-md-2"><input type="date" name="to" value="<?= e($_GET['to'] ?? '') ?>" class="form-control form-control-sm"></div>
  <div class="col-6 col-md-1"><button class="btn btn-outline-primary btn-sm w-100">Filter</button></div>
  <?php if ($q !== '' || $status !== '' || $personId || !empty($_GET['from']) || !empty($_GET['to']) || $sort): ?>
    <div class="col-6 col-md-2"><a class="btn btn-outline-secondary btn-sm w-100" href="<?= e(admin_url('orders')) ?>">Clear</a></div>
  <?php endif; ?>
</form>

<?php if (!$orders): ?>
  <div class="empty-state"><i class="bi bi-receipt"></i>No orders match. <a href="<?= e(admin_url('orders/create')) ?>">Create the first one</a>.</div>
<?php else: ?>

<?php // Ticked rows travel to the printer and to Excel as ?ids=… — see kpOrderSelect in app.js ?>
<div id="orderSelectBar" class="alert alert-primary d-none d-flex flex-wrap align-items-center gap-2 py-2">
  <strong><span id="orderSelectCount">0</span> selected</strong>
  <div class="ms-auto d-flex flex-wrap gap-2">
    <a href="#" id="printSelected" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
      <i class="bi bi-printer"></i> Print selected</a>
    <a href="#" id="excelSelected" class="btn btn-sm btn-success">
      <i class="bi bi-file-earmark-spreadsheet"></i> Selected to Excel</a>
    <button type="button" id="clearSelected" class="btn btn-sm btn-outline-secondary">Clear</button>
  </div>
</div>

<div class="table-responsive">
<table class="table table-sm table-hover align-middle table-mobile" id="ordersTable"
       data-print-url="<?= e(admin_url('orders/print')) ?>"
       data-excel-url="<?= e(admin_url('orders/export')) ?>">
  <thead><tr>
    <th style="width:34px" class="kp-pick">
      <input type="checkbox" class="form-check-input" id="orderSelectAll" title="Select everything on this page">
    </th>
    <th><?= $sortLink('customer', 'Customer') ?></th>
    <th><?= $sortLink('job', 'Job No') ?></th>
    <th><?= $sortLink('date', 'Date') ?> / <?= $sortLink('due', 'Due') ?></th>
    <th><?= $sortLink('status', 'Status') ?></th>
    <th class="text-end"><?= $sortLink('total', 'Total') ?></th>
    <th class="text-end"><?= $sortLink('balance', 'Balance') ?></th>
    <th>Actions</th>
  </tr></thead>
  <tbody>
  <?php foreach ($orders as $o):
      $overdue = Status::isOverdue($o['due_date'], (string)$o['status']); ?>
    <tr class="<?= $overdue ? 'row-overdue' : e(priority_class($o['priority'])) ?>">
      <td data-label="Select" class="kp-pick">
        <input type="checkbox" class="form-check-input kp-order-pick" value="<?= (int)$o['id'] ?>"
               aria-label="Select <?= e($o['job_no']) ?>">
      </td>
      <?php // The customer is what the shop actually looks for, so that is the link, with the
      // number to ring underneath. The job number is a column of its own — it is read out,
      // written on the challan and searched for, so it should not have to be hunted for. ?>
      <td data-label="Customer">
        <a href="<?= e(admin_url('orders/' . $o['id'])) ?>" class="fw-semibold"><?= e($o['customer_name']) ?></a>
        <?php if ((int)$o['needs_review']): ?><span class="badge bg-info badge-status">New — needs review</span><?php endif; ?>
        <?= priority_badge($o['priority']) ?>
        <div class="small text-muted"><?= e($o['customer_phone']) ?></div>
      </td>
      <td data-label="Job No" class="text-nowrap">
        <a href="<?= e(admin_url('orders/' . $o['id'])) ?>" class="text-body text-decoration-none"><code class="small"><?= e($o['job_no']) ?></code></a>
      </td>
      <td data-label="Date / Due"><span class="small"><?= e(fmt_date($o['order_date'])) ?></span>
        <div class="small <?= $overdue ? 'text-overdue fw-bold' : 'text-muted' ?>">Due <?= e(fmt_date($o['due_date'], true)) ?></div></td>
      <td data-label="Status">
        <?php if (Acl::can('order.change_status')): ?>
          <form method="post" action="<?= e(admin_url('orders/' . $o['id'] . '/status')) ?>" class="d-inline">
            <?= Csrf::field() ?>
            <input type="hidden" name="back" value="<?= e($backUrl) ?>">
            <select name="status" data-auto-submit
                    class="form-select form-select-sm kp-status-pick text-bg-<?= e(Status::color((string)$o['status'])) ?>"
                    title="Change status — applies to every job in this order">
              <?php foreach (array_keys(Status::RANKS) as $s): ?>
                <option value="<?= e($s) ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= e(Status::label($s)) ?></option>
              <?php endforeach; ?>
              <?php if (!isset(Status::RANKS[$o['status']])): ?>
                <option value="<?= e($o['status']) ?>" selected><?= e(Status::label((string)$o['status'])) ?></option>
              <?php endif; ?>
            </select>
          </form>
        <?php else: ?>
          <span class="badge bg-<?= e(Status::color((string)$o['status'])) ?>"><?= e(Status::label((string)$o['status'])) ?></span>
        <?php endif; ?>
      </td>
      <td data-label="Total" class="text-end"><?= e(fmt_money($o['total'])) ?></td>
      <td data-label="Balance" class="text-end <?= (float)$o['balance_amount'] > 0 ? 'text-danger fw-semibold' : 'text-success' ?>"><?= e(fmt_money($o['balance_amount'])) ?></td>
      <td data-label="Actions">
        <div class="btn-group btn-group-sm">
          <a class="btn btn-outline-secondary" title="Open" href="<?= e(admin_url('orders/' . $o['id'])) ?>"><i class="bi bi-eye"></i></a>
          <a class="btn btn-outline-secondary" title="Job card" target="_blank" href="<?= e(admin_url('orders/' . $o['id'] . '/job-card')) ?>"><i class="bi bi-printer"></i></a>
          <a class="btn btn-outline-success" title="WhatsApp customer" target="_blank" href="https://wa.me/<?= e(normalize_phone($o['customer_phone']) ?? '') ?>"><i class="bi bi-whatsapp"></i></a>
          <?php if ($user['role_slug'] === 'super_admin'): ?>
          <form method="post" class="d-inline" action="<?= e(admin_url('orders/' . $o['id'] . '/delete')) ?>" data-confirm="Delete order <?= e($o['job_no']) ?>? It will be removed from all lists.">
            <?= Csrf::field() ?><button class="btn btn-outline-danger" title="Delete order"><i class="bi bi-trash"></i></button>
          </form>
          <?php endif; ?>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot><tr class="fw-semibold">
    <td colspan="5" class="text-end">All <?= (int)$total ?> matching orders</td>
    <td class="text-end"><?= e(fmt_money($sums['total'])) ?></td>
    <td class="text-end text-danger"><?= e(fmt_money($sums['balance'])) ?></td>
    <td></td>
  </tr></tfoot>
</table>
</div>
<?php $pages = (int)ceil($total / $perPage); if ($pages > 1): ?>
<nav><ul class="pagination pagination-sm">
  <?php for ($p = 1; $p <= $pages; $p++): $pq = $_GET; $pq['page'] = $p; ?>
    <li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query($pq)) ?>"><?= $p ?></a></li>
  <?php endfor; ?>
</ul></nav>
<?php endif; endif; ?>
