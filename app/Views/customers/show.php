<?php use App\Core\Acl; use App\Core\Csrf; use App\Models\Status; $title = $customer['name']; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0"><?= e($customer['name']) ?>
      <?php if ((int)$customer['is_blocked']): ?><span class="badge bg-danger">Blocked</span><?php endif; ?></h4>
    <small class="text-muted"><?= e($customer['phone']) ?> · <?= e($customer['city'] ?? '') ?> · since <?= e(fmt_date($customer['created_at'])) ?></small>
  </div>
  <div class="btn-group btn-group-sm">
    <a class="btn btn-outline-success" target="_blank" href="https://wa.me/<?= e(normalize_phone($customer['phone']) ?? '') ?>"><i class="bi bi-whatsapp"></i> Chat</a>
    <?php if (Acl::can('customer.edit')): ?><a class="btn btn-outline-primary" href="<?= e(admin_url('customers/' . $customer['id'] . '/edit')) ?>"><i class="bi bi-pencil"></i> Edit</a><?php endif; ?>
    <?php if (Acl::can('customer.delete')): ?>
    <form method="post" action="<?= e(admin_url('customers/' . $customer['id'] . '/delete')) ?>" data-confirm="Delete this customer?">
      <?= Csrf::field() ?><button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
    </form>
    <?php endif; ?>
  </div>
</div>

<div class="row g-2 mb-3">
  <div class="col-4"><div class="stat-card"><div class="stat-value"><?= e(fmt_money($totals['billed'])) ?></div><div class="stat-label">Total Billed</div></div></div>
  <div class="col-4"><div class="stat-card"><div class="stat-value text-success"><?= e(fmt_money($totals['paid'])) ?></div><div class="stat-label">Total Paid</div></div></div>
  <div class="col-4"><div class="stat-card <?= $totals['outstanding'] > 0 ? 'stat-danger' : '' ?>"><div class="stat-value"><?= e(fmt_money($totals['outstanding'])) ?></div><div class="stat-label">Outstanding</div></div></div>
</div>

<div class="card mb-3"><div class="card-body">
  <h6>Orders (<?= count($orders) ?>)</h6>
  <div class="table-responsive"><table class="table table-sm table-mobile">
    <thead><tr><th>Job No</th><th>Date</th><th>Branch</th><th>Status</th><th>Total</th><th>Balance</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td data-label="Job No"><a href="<?= e(admin_url('orders/' . $o['id'])) ?>"><?= e($o['job_no']) ?></a>
          <?= isset($o['priority']) ? priority_badge($o['priority']) : '' ?></td>
        <td data-label="Date"><?= e(fmt_date($o['order_date'])) ?></td>
        <td data-label="Branch"><?= e($o['branch_name']) ?></td>
        <td data-label="Status"><span class="badge bg-<?= e(Status::color((string)$o['status'])) ?>"><?= e(Status::label((string)$o['status'])) ?></span></td>
        <td data-label="Total"><?= e(fmt_money($o['total'])) ?></td>
        <td data-label="Balance" class="<?= (float)$o['balance_amount'] > 0 ? 'text-danger' : 'text-success' ?>"><?= e(fmt_money($o['balance_amount'])) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div></div>

<div class="card"><div class="card-body">
  <h6>Payment ledger</h6>
  <div class="table-responsive"><table class="table table-sm table-mobile">
    <thead><tr><th>Receipt</th><th>Job</th><th>Date</th><th>Type / Mode</th><th>Amount</th></tr></thead>
    <tbody>
    <?php foreach ($payments as $p): ?>
      <tr>
        <td data-label="Receipt"><?= e($p['receipt_no']) ?></td>
        <td data-label="Job"><?= e($p['job_no']) ?></td>
        <td data-label="Date"><?= e(fmt_date($p['paid_at'], true)) ?></td>
        <td data-label="Type"><?= e($p['type']) ?> / <?= e($p['mode']) ?></td>
        <td data-label="Amount"><?= e(fmt_money($p['amount'])) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div></div>
