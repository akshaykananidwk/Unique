<?php use App\Core\Acl; use App\Core\Csrf; $title = 'Edit Receipt ' . $payment['receipt_no']; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <h4 class="mb-0">Receipt <?= e($payment['receipt_no']) ?></h4>
    <small class="text-muted">
      <?= e($customer['name']) ?> ·
      <a href="<?= e(admin_url('orders/' . $order['id'])) ?>"><?= e($order['job_no']) ?></a>
    </small>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener"
       href="<?= e(admin_url('payments/' . $payment['id'] . '/receipt')) ?>"><i class="bi bi-printer"></i> Print</a>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(admin_url('orders/' . $order['id'])) ?>">Back to order</a>
  </div>
</div>

<div class="row g-2 mb-3">
  <div class="col-4"><div class="stat-card"><div class="text-muted small">Bill total</div>
    <div class="fs-5 fw-bold"><?= e(fmt_money($order['total'])) ?></div></div></div>
  <div class="col-4"><div class="stat-card"><div class="text-muted small">Paid so far</div>
    <div class="fs-5 fw-bold text-success"><?= e(fmt_money($order['paid_amount'])) ?>
      <?php if ((float)$order['settled_discount'] > 0): ?>
        <div class="small text-warning-emphasis">+ <?= e(fmt_money($order['settled_discount'])) ?> discount</div>
      <?php endif; ?>
    </div></div></div>
  <div class="col-4"><div class="stat-card <?= (float)$order['balance_amount'] > 0 ? 'stat-danger' : '' ?>">
    <div class="text-muted small">Balance</div>
    <div class="fs-5 fw-bold"><?= e(fmt_money($order['balance_amount'])) ?></div></div></div>
</div>

<form method="post" action="<?= e(admin_url('payments/' . $payment['id'] . '/update')) ?>">
  <?= Csrf::field() ?>
  <div class="card mb-3"><div class="card-body row g-2">
    <div class="col-md-3"><label class="form-label">Amount received ₹</label>
      <input type="number" step="0.01" min="0" name="amount" class="form-control"
             value="<?= e(abs((float)$payment['amount'])) ?>" required>
      <?php if ($payment['type'] === 'refund'): ?>
        <div class="form-text text-warning-emphasis">This is a refund — it goes out, not in.</div>
      <?php endif; ?>
    </div>
    <div class="col-md-3"><label class="form-label">Discount allowed ₹</label>
      <input type="number" step="0.01" min="0" name="discount_amount" class="form-control"
             value="<?= e((float)$payment['discount_amount']) ?>"
             <?= Acl::can('payment.discount') ? '' : 'readonly' ?>>
      <div class="form-text">Written off, not money received.</div>
    </div>
    <div class="col-md-3"><label class="form-label">Mode</label>
      <select name="mode" class="form-select">
        <?php foreach (['cash', 'upi', 'card', 'bank', 'cheque', 'credit'] as $m): ?>
          <option value="<?= $m ?>" <?= $payment['mode'] === $m ? 'selected' : '' ?>><?= ucfirst($m) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-3"><label class="form-label">Date &amp; time</label>
      <input type="datetime-local" name="paid_at" class="form-control"
             value="<?= e(date('Y-m-d\TH:i', strtotime((string)$payment['paid_at']))) ?>"></div>
    <div class="col-md-4"><label class="form-label">Reference</label>
      <input name="reference" class="form-control" value="<?= e($payment['reference']) ?>"
             placeholder="UPI ref / cheque no"></div>
    <div class="col-md-8"><label class="form-label">Note</label>
      <input name="note" class="form-control" value="<?= e($payment['note']) ?>"></div>
  </div></div>

  <div class="sticky-actions d-flex gap-2">
    <button class="btn btn-primary flex-grow-1"><i class="bi bi-check-lg"></i> Save receipt</button>
    <a href="<?= e(admin_url('payments')) ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>

<?php // A receipt that can be changed has to say what it used to be. ?>
<div class="card mt-3"><div class="card-body">
  <h6>Change history</h6>
  <?php if (!$edits): ?>
    <p class="small text-muted mb-0">This receipt has not been changed since it was written.</p>
  <?php else: ?>
    <div class="table-responsive"><table class="table table-sm table-mobile mb-0">
      <thead><tr><th>When</th><th>Field</th><th>Was</th><th>Became</th><th>By</th></tr></thead>
      <tbody>
      <?php foreach ($edits as $ed): ?>
        <tr>
          <td data-label="When" class="small text-nowrap"><?= e(fmt_date($ed['created_at'], true)) ?></td>
          <td data-label="Field" class="small"><?= e(str_replace('_', ' ', $ed['field'])) ?></td>
          <td data-label="Was" class="small text-muted"><?= e($ed['old_value'] ?: '—') ?></td>
          <td data-label="Became" class="small fw-semibold"><?= e($ed['new_value'] ?: '—') ?></td>
          <td data-label="By" class="small"><?= e($ed['by_user'] ?: '—') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
</div></div>
