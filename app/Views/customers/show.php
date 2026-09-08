<?php use App\Core\Acl; use App\Core\Csrf; use App\Models\Status; $title = $customer['name']; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0"><?= e($customer['name']) ?>
      <?php if ((int)$customer['is_blocked']): ?><span class="badge bg-danger">Blocked</span><?php endif; ?></h4>
    <small class="text-muted"><?= e($customer['phone']) ?><?= $customer['address'] ? ' · ' . e($customer['address']) : '' ?> · since <?= e(fmt_date($customer['created_at'])) ?></small>
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

<!-- Everyone who gives work under this account -->
<div class="card mb-3"><div class="card-body">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0">People (<?= count($contacts) ?>)</h6>
    <?php if (Acl::can('customer.edit')): ?>
      <a class="btn btn-sm btn-outline-primary" href="<?= e(admin_url('customers/' . $customer['id'] . '/edit')) ?>">
        <i class="bi bi-person-plus"></i> Add / edit people</a>
    <?php endif; ?>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <?php foreach ($contacts as $ct): ?>
      <div class="border rounded px-2 py-1 small">
        <span class="fw-semibold"><?= e($ct['name']) ?></span>
        <?php if ((int)$ct['is_primary'] === 1): ?><span class="badge bg-primary">Main</span><?php endif; ?>
        <?php if ($ct['designation']): ?><span class="text-muted">· <?= e($ct['designation']) ?></span><?php endif; ?>
        <div class="text-muted">
          <a href="tel:<?= e($ct['phone']) ?>"><?= e($ct['phone']) ?></a>
          · <a target="_blank" rel="noopener" href="https://wa.me/<?= e(normalize_phone($ct['whatsapp'] ?: $ct['phone']) ?? '') ?>">
            <i class="bi bi-whatsapp"></i></a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div></div>

<?php // Take money from the party against everything they owe — a customer hands over a
// lump sum, not a payment per bill. ?>
<?php if ($totals['outstanding'] > 0 && Acl::can('payment.create')): ?>
<div class="card mb-3 border-danger"><div class="card-body">
  <h6 class="mb-1"><i class="bi bi-cash-coin"></i> Collect from <?= e($customer['name']) ?></h6>
  <p class="small text-muted mb-2">
    <?= e(fmt_money($totals['outstanding'])) ?> outstanding across
    <?= count(array_filter($orders, fn($o) => (float)$o['balance_amount'] > 0)) ?> unpaid bill(s).
    What you enter is put against the oldest bills first.
  </p>
  <form method="post" action="<?= e(admin_url('customers/' . $customer['id'] . '/collect')) ?>" class="row g-2">
    <?= Csrf::field() ?>
    <div class="col-6 col-md-2"><label class="form-label small">Amount ₹</label>
      <input type="number" step="0.01" min="0" name="amount" class="form-control form-control-sm"
             placeholder="<?= e(number_format((float)$totals['outstanding'], 2, '.', '')) ?>"></div>
    <?php if (Acl::can('payment.discount')): ?>
    <div class="col-6 col-md-2"><label class="form-label small">Discount ₹</label>
      <input type="number" step="0.01" min="0" name="discount_amount" class="form-control form-control-sm" placeholder="0">
      <div class="form-text">If he pays less</div></div>
    <?php endif; ?>
    <div class="col-6 col-md-2"><label class="form-label small">Mode</label>
      <select name="mode" class="form-select form-select-sm">
        <?php foreach (['cash', 'upi', 'card', 'bank', 'cheque', 'credit'] as $m): ?>
          <option value="<?= $m ?>"><?= ucfirst($m) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-6 col-md-2"><label class="form-label small">Date</label>
      <input type="datetime-local" name="paid_at" class="form-control form-control-sm"
             value="<?= e(date('Y-m-d\TH:i')) ?>"></div>
    <div class="col-6 col-md-2"><label class="form-label small">Reference</label>
      <input name="reference" class="form-control form-control-sm" placeholder="UPI / cheque"></div>
    <div class="col-6 col-md-2 d-flex align-items-end">
      <button class="btn btn-sm btn-danger w-100"><i class="bi bi-check-lg"></i> Settle</button></div>
  </form>
</div></div>
<?php endif; ?>

<div class="card mb-3"><div class="card-body">
  <h6>Orders (<?= count($orders) ?>) <span class="text-muted fs-6">— everything this customer has given, from any of its numbers</span></h6>
  <div class="table-responsive"><table class="table table-sm table-mobile">
    <thead><tr><th>Job No</th><th>Date</th><th>Given by</th><th>Status</th><th>Total</th><th>Balance</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td data-label="Job No"><a href="<?= e(admin_url('orders/' . $o['id'])) ?>"><?= e($o['job_no']) ?></a>
          <?= isset($o['priority']) ? priority_badge($o['priority']) : '' ?></td>
        <td data-label="Date"><?= e(fmt_date($o['order_date'])) ?></td>
        <td data-label="Given by" class="small">
          <?= e($o['contact_name'] ?: '—') ?>
          <?php if (!empty($o['contact_phone'])): ?><div class="text-muted"><?= e($o['contact_phone']) ?></div><?php endif; ?>
        </td>
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
    <thead><tr><th>Receipt</th><th>Job</th><th>Date</th><th>Type / Mode</th><th>Amount</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($payments as $p): ?>
      <tr>
        <td data-label="Receipt"><?= e($p['receipt_no']) ?></td>
        <td data-label="Job"><?= e($p['job_no']) ?></td>
        <td data-label="Date"><?= e(fmt_date($p['paid_at'], true)) ?></td>
        <td data-label="Type"><?= e($p['type']) ?> / <?= e($p['mode']) ?></td>
        <td data-label="Amount"><?= e(fmt_money($p['amount'])) ?>
          <?php if ((float)($p['discount_amount'] ?? 0) > 0): ?>
            <div class="small text-warning-emphasis">+ <?= e(fmt_money($p['discount_amount'])) ?> disc.</div>
          <?php endif; ?>
        </td>
        <td data-label="">
          <?php if (Acl::can('payment.edit')): ?>
            <a class="btn btn-sm btn-outline-primary" href="<?= e(admin_url('payments/' . $p['id'] . '/edit')) ?>"><i class="bi bi-pencil"></i></a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div></div>
