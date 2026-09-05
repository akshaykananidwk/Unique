<?php
use App\Core\Acl;
use App\Core\Csrf;
use App\Models\Status;

$title = $order['job_no'];
$overdue = Status::isOverdue($order['due_date'], (string)$order['status']);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <h4 class="mb-0"><?= e($order['job_no']) ?>
      <span class="badge bg-<?= e(Status::color((string)$order['status'])) ?>"><?= e(Status::label((string)$order['status'])) ?></span>
      <?= priority_badge($order['priority'], false) ?>
      <?php if ((int)$order['needs_review']): ?><span class="badge bg-info">New — needs review</span><?php endif; ?>
      <?php if ($overdue): ?><span class="badge bg-danger">OVERDUE</span><?php endif; ?>
    </h4>
    <small class="text-muted"><?= e(fmt_date($order['order_date'], true)) ?> ·
      Due <span class="<?= $overdue ? 'text-overdue fw-bold' : '' ?>"><?= e(fmt_date($order['due_date'], true)) ?></span></small>
  </div>
  <div class="btn-group">
    <a class="btn btn-outline-secondary btn-sm" target="_blank" href="<?= e(admin_url('orders/' . $order['id'] . '/job-card')) ?>"><i class="bi bi-printer"></i> Job Card</a>
    <a class="btn btn-outline-secondary btn-sm" target="_blank" href="<?= e(admin_url('orders/' . $order['id'] . '/job-card?format=thermal')) ?>"><i class="bi bi-receipt"></i> Thermal</a>
    <a class="btn btn-outline-success btn-sm" target="_blank" href="<?= e(base_url('track/' . $order['tracking_token'])) ?>"><i class="bi bi-geo"></i> Tracking Page</a>
    <?php if (Acl::can('order.edit') && !in_array($order['status'], ['delivered', 'completed', 'cancelled'], true)): ?>
      <a class="btn btn-outline-primary btn-sm" href="<?= e(admin_url('orders/' . $order['id'] . '/edit')) ?>"><i class="bi bi-pencil"></i> Edit</a>
    <?php endif; ?>
  </div>
</div>

<?php if ((int)$order['needs_review'] && Acl::can('order.edit')): ?>
<div class="alert alert-info d-flex justify-content-between align-items-center">
  <span>This order came from the public website — confirm price and due date before production.</span>
  <form method="post" action="<?= e(admin_url('orders/' . $order['id'] . '/confirm')) ?>"><?= Csrf::field() ?>
    <button class="btn btn-info btn-sm">Confirm Order</button>
  </form>
</div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-8">
    <!-- Items -->
    <?php foreach ($items as $item):
        $itemOverdue = Status::isOverdue($item['due_date'], (string)$item['status']); ?>
    <div class="card mb-3 <?= $itemOverdue ? 'border-danger' : '' ?>"><div class="card-body">
      <div class="d-flex flex-wrap justify-content-between gap-2">
        <div>
          <strong><?= e($item['item_name_snapshot']) ?></strong>
          <span class="badge bg-<?= e(Status::color((string)$item['status'])) ?>"><?= e(Status::label((string)$item['status'])) ?></span>
          <?php if ((int)$item['revision_count'] >= 3): ?><span class="badge bg-warning text-dark">Rev #<?= (int)$item['revision_count'] ?></span><?php endif; ?>
          <?php if (!empty($item['counter_approved_at'])): ?><span class="badge bg-success" title="Approved face-to-face at the counter on <?= e(fmt_date($item['counter_approved_at'], true)) ?>">✔ Approved in person</span><?php endif; ?>
          <div class="small text-muted">
            <?= e($item['category_name_snapshot']) ?> · Qty <?= e(rtrim(rtrim((string)$item['qty'], '0'), '.')) ?> <?= e($item['unit']) ?>
            · Rate <?= e(fmt_money($item['rate'])) ?><?= (int)$item['rate_overridden'] ? ' <span class="badge bg-secondary">edited</span>' : '' ?>
            · Line <?= e(fmt_money($item['line_total'])) ?>
          </div>
          <?php if ($item['spec_text']): ?><div class="small mt-1"><?= e($item['spec_text']) ?></div><?php endif; ?>
          <?php if ($item['special_instructions']): ?><div class="small mt-1 text-warning-emphasis">📌 <?= e($item['special_instructions']) ?></div><?php endif; ?>
          <div class="small mt-1 <?= $itemOverdue ? 'text-overdue fw-bold' : 'text-muted' ?>">Due: <?= e(fmt_date($item['due_date'], true)) ?></div>
        </div>
        <div class="text-end">
          <?php if ((int)$item['requires_design']): ?>
            <div class="small mb-1">Designer: <strong><?= e($item['designer_name'] ?? '— unassigned —') ?></strong></div>
            <?php if (Acl::can('order.assign') && !in_array($item['status'], ['delivered', 'completed', 'cancelled'], true)): ?>
            <form method="post" action="<?= e(admin_url('order-items/' . $item['id'] . '/assign')) ?>" class="d-flex gap-1 mb-2">
              <?= Csrf::field() ?>
              <select name="designer_id" class="form-select form-select-sm" required>
                <option value="">Assign designer…</option>
                <?php foreach ($designers as $d): ?>
                  <option value="<?= (int)$d['id'] ?>" <?= (int)$item['assigned_designer_id'] === (int)$d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm btn-outline-primary">Set</button>
            </form>
            <?php endif; ?>
          <?php endif; ?>
          <?php if (Acl::can('order.change_status')):
              // Any stage can be set freely. This tick simply records that the customer
              // approved face-to-face, so the proof list matches what actually happened.
              $hasApproval = !empty($item['counter_approved_at']);
              foreach ($item['proofs'] as $pf) {
                  if ($pf['status'] === 'approved' && (int)$pf['approval_confirmed'] === 1) { $hasApproval = true; break; }
              }
              $canOverride = (int)$item['requires_design'] === 1 && !$hasApproval && Acl::can('order.override_approval'); ?>
          <form method="post" action="<?= e(admin_url('order-items/' . $item['id'] . '/status')) ?>">
            <?= Csrf::field() ?>
            <div class="d-flex gap-1">
              <select name="status" class="form-select form-select-sm" required>
                <option value="">Change status…</option>
                <?php foreach (array_merge(array_keys(Status::RANKS), Status::SPECIAL) as $s): if ($s === $item['status']) continue; ?>
                  <option value="<?= e($s) ?>"><?= e(Status::label($s)) ?></option>
                <?php endforeach; ?>
              </select>
              <input name="note" class="form-control form-control-sm" placeholder="Note / reason" style="max-width:130px">
              <button class="btn btn-sm btn-outline-primary">Go</button>
            </div>
            <?php if ($canOverride): ?>
            <div class="form-check form-check-sm text-start mt-1">
              <input class="form-check-input" type="checkbox" value="1" name="approval_override" id="ovr<?= (int)$item['id'] ?>">
              <label class="form-check-label small text-muted" for="ovr<?= (int)$item['id'] ?>">
                Also record that the customer approved this design in person
              </label>
            </div>
            <?php endif; ?>
          </form>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($item['proofs']): ?>
      <hr class="my-2">
      <div class="small fw-semibold mb-1">Design proofs</div>
      <div class="d-flex flex-wrap gap-2">
        <?php foreach ($item['proofs'] as $proof): ?>
        <div class="border rounded p-2 small" style="min-width:170px">
          v<?= (int)$proof['version'] ?> —
          <span class="badge bg-<?= e(['pending' => 'warning', 'approved' => 'success', 'change_requested' => 'danger', 'superseded' => 'secondary'][$proof['status']] ?? 'secondary') ?>">
            <?= e(ucwords(str_replace('_', ' ', (string)$proof['status']))) ?></span>
          <div class="text-muted"><?= e(fmt_date($proof['created_at'], true)) ?></div>
          <?php if ($proof['status'] === 'approved'): ?>
            <div class="text-success">✔ Confirmed <?= e(fmt_date($proof['responded_at'], true)) ?><br>IP <?= e($proof['response_ip']) ?></div>
          <?php endif; ?>
          <a target="_blank" href="<?= e(base_url('proof/' . $proof['proof_token'])) ?>">Customer link</a>
          · <a target="_blank" href="<?= e(upload_url($proof['file_path'])) ?>">Original</a>
          <?php if ((int)$proof['feedback_count']): ?><div class="text-warning-emphasis">💬 <?= (int)$proof['feedback_count'] ?> feedback</div><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div></div>
    <?php endforeach; ?>

    <!-- History -->
    <div class="card"><div class="card-body">
      <h6>Status history</h6>
      <div class="table-responsive"><table class="table table-sm small">
        <thead><tr><th>When</th><th>Change</th><th>By</th><th>Note</th></tr></thead>
        <tbody>
        <?php foreach ($history as $h): ?>
          <tr>
            <td><?= e(fmt_date($h['created_at'], true)) ?></td>
            <td><?= e($h['from_status'] ? Status::label((string)$h['from_status']) . ' → ' : '') ?><?= e(Status::label((string)$h['to_status'])) ?></td>
            <td><?= e((int)$h['changed_by_customer'] === 1 ? 'Customer' : ($h['by_user'] ?? 'System')) ?></td>
            <td><?= e($h['note']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div></div>
  </div>

  <div class="col-lg-4">
    <!-- Customer -->
    <div class="card mb-3"><div class="card-body">
      <h6>Customer</h6>
      <a href="<?= e(admin_url('customers/' . $customer['id'])) ?>" class="fw-semibold"><?= e($customer['name']) ?></a>
      <div class="small text-muted">
        <?= e($customer['phone']) ?>
        <?php if (!empty($contact)): ?>
          · <span title="Who gave this order"><i class="bi bi-person"></i> <?= e($contact['name']) ?>
            <?= $contact['phone'] !== $customer['phone'] ? '(' . e($contact['phone']) . ')' : '' ?></span>
        <?php endif; ?>
      </div>
      <?php if ($customer['gstin']): ?><div class="small">GSTIN: <?= e($customer['gstin']) ?></div><?php endif; ?>
      <div class="mt-2 d-flex gap-2">
        <a class="btn btn-sm btn-outline-success" target="_blank" href="https://wa.me/<?= e(normalize_phone($customer['phone']) ?? '') ?>"><i class="bi bi-whatsapp"></i> Chat</a>
        <form method="post" action="<?= e(admin_url('orders/' . $order['id'] . '/whatsapp')) ?>"><?= Csrf::field() ?>
          <button class="btn btn-sm btn-outline-success"><i class="bi bi-send"></i> Send tracking link</button>
        </form>
      </div>
    </div></div>

    <!-- Who is on this order -->
    <div class="card mb-3"><div class="card-body">
      <h6>Order team</h6>
      <div class="d-flex justify-content-between border-bottom py-1 small">
        <span class="text-muted">Order taken by</span>
        <strong><?= e($people['taken_by'] ?? '—') ?></strong>
      </div>
      <div class="d-flex justify-content-between border-bottom py-1 small">
        <span class="text-muted">Accepted by</span>
        <strong><?= e($people['accepted_by'] ?? '—') ?></strong>
      </div>
      <div class="d-flex justify-content-between border-bottom py-1 small">
        <span class="text-muted">Designing</span>
        <strong><?= $people['designers'] ? e(implode(', ', $people['designers'])) : '— not assigned —' ?></strong>
      </div>
      <?php // The three names the bill prints. Prepared By follows the designer unless it
      // is set by hand — someone else may have finished the job. ?>
      <div class="d-flex justify-content-between border-bottom py-1 small">
        <span class="text-muted">Prepared by <span class="text-muted">(on the bill)</span></span>
        <strong><?= e($credits['prepared_by'] ?: '—') ?></strong>
      </div>
      <div class="d-flex justify-content-between py-1 small">
        <span class="text-muted">Prepaid by</span>
        <strong><?= e($credits['prepaid_by'] ?: '—') ?><?= $credits['advance'] > 0 ? ' · ' . e(fmt_money($credits['advance'])) : '' ?></strong>
      </div>
      <?php if (Acl::can('order.edit')): ?>
      <form method="post" action="<?= e(admin_url('orders/' . $order['id'] . '/prepared-by')) ?>" class="mt-2">
        <?= Csrf::field() ?>
        <div class="input-group input-group-sm">
          <select name="prepared_by_user_id" class="form-select">
            <option value="">— follow the designer —</option>
            <?php foreach ($designers as $d): ?>
              <option value="<?= (int)$d['id'] ?>" <?= (int)($order['prepared_by_user_id'] ?? 0) === (int)$d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-outline-primary">Set</button>
        </div>
        <div class="form-text">Who made this job, for the bill.</div>
      </form>
      <?php endif; ?>
    </div></div>

    <!-- Money -->
    <div class="card mb-3"><div class="card-body">
      <h6>Amounts</h6>
      <table class="table table-sm mb-2">
        <tr><td>Subtotal</td><td class="text-end"><?= e(fmt_money($order['subtotal'])) ?></td></tr>
        <?php if ((float)$order['discount_amount'] > 0): ?><tr><td>Discount</td><td class="text-end">− <?= e(fmt_money($order['discount_amount'])) ?></td></tr><?php endif; ?>
        <?php if ((float)$order['tax_amount'] > 0): ?><tr><td>Tax</td><td class="text-end"><?= e(fmt_money($order['tax_amount'])) ?></td></tr><?php endif; ?>
        <?php if ((float)$order['delivery_charge'] > 0): ?><tr><td>Delivery</td><td class="text-end"><?= e(fmt_money($order['delivery_charge'])) ?></td></tr><?php endif; ?>
        <?php if ((float)$order['round_off'] != 0): ?><tr><td>Round off</td><td class="text-end"><?= e(fmt_money($order['round_off'])) ?></td></tr><?php endif; ?>
        <tr class="fw-bold"><td>Total</td><td class="text-end"><?= e(fmt_money($order['total'])) ?></td></tr>
        <tr class="text-success"><td>Paid</td><td class="text-end"><?= e(fmt_money($order['paid_amount'])) ?></td></tr>
        <tr class="fw-bold <?= (float)$order['balance_amount'] > 0 ? 'text-danger' : 'text-success' ?>">
          <td>Balance</td><td class="text-end"><?= e(fmt_money($order['balance_amount'])) ?></td></tr>
      </table>

      <?php if (Acl::can('payment.create') && !in_array($order['status'], ['cancelled'], true)): ?>
      <form method="post" action="<?= e(admin_url('payments')) ?>" class="row g-1">
        <?= Csrf::field() ?>
        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
        <div class="col-4"><input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-sm" placeholder="₹" required></div>
        <div class="col-4"><select name="type" class="form-select form-select-sm">
          <option value="part">Part</option><option value="advance">Advance</option><option value="final">Final</option>
          <?php if (Acl::can('payment.refund')): ?><option value="refund">Refund</option><?php endif; ?>
        </select></div>
        <div class="col-4"><select name="mode" class="form-select form-select-sm">
          <option value="cash">Cash</option><option value="upi">UPI</option><option value="card">Card</option>
          <option value="bank">Bank</option><option value="cheque">Cheque</option><option value="credit">Credit</option>
        </select></div>
        <div class="col-8"><input name="reference" class="form-control form-control-sm" placeholder="Reference"></div>
        <div class="col-4"><button class="btn btn-sm btn-success w-100">Add</button></div>
      </form>
      <?php endif; ?>

      <?php if ($payments): ?>
      <hr class="my-2">
      <div class="small fw-semibold">Payments</div>
      <?php foreach ($payments as $p): ?>
        <div class="d-flex justify-content-between small border-bottom py-1">
          <span><?= e($p['receipt_no']) ?><br><span class="text-muted"><?= e(fmt_date($p['paid_at'], true)) ?> · <?= e($p['mode']) ?> · <?= e($p['type']) ?></span></span>
          <span class="text-end">
            <strong><?= e(fmt_money($p['amount'])) ?></strong><br>
            <a target="_blank" href="<?= e(admin_url('payments/' . $p['id'] . '/receipt')) ?>">Receipt</a>
            <?php if ($user['role_slug'] === 'super_admin'): ?>
            <form method="post" class="d-inline" action="<?= e(admin_url('payments/' . $p['id'] . '/delete')) ?>" data-confirm="Delete this payment? Balances will be recalculated.">
              <?= Csrf::field() ?><button class="btn btn-link btn-sm text-danger p-0">Delete</button>
            </form>
            <?php endif; ?>
          </span>
        </div>
      <?php endforeach; endif; ?>
    </div></div>

    <!-- Attachments -->
    <?php if ($attachments): ?>
    <div class="card mb-3"><div class="card-body">
      <h6>Attachments</h6>
      <?php foreach ($attachments as $a): ?>
        <div class="small py-1 border-bottom">
          <a target="_blank" href="<?= e(upload_url($a['file_path'])) ?>"><?= e($a['original_name'] ?: basename((string)$a['file_path'])) ?></a>
          <span class="text-muted">(<?= e($a['kind']) ?>, <?= number_format((int)$a['size_bytes'] / 1024, 0) ?> KB)</span>
        </div>
      <?php endforeach; ?>
    </div></div>
    <?php endif; ?>

    <!-- Cancel -->
    <?php if (Acl::can('order.cancel') && !in_array($order['status'], ['delivered', 'completed', 'cancelled'], true)): ?>
    <div class="card border-danger"><div class="card-body">
      <h6 class="text-danger">Cancel order</h6>
      <form method="post" action="<?= e(admin_url('orders/' . $order['id'] . '/cancel')) ?>" data-confirm="Cancel this entire order? The customer will be notified on WhatsApp.">
        <?= Csrf::field() ?>
        <div class="input-group input-group-sm">
          <input name="reason" class="form-control" placeholder="Reason (required)" required>
          <button class="btn btn-danger">Cancel Order</button>
        </div>
      </form>
    </div></div>
    <?php endif; ?>

    <?php if ((int)$order['is_cancelled']): ?>
    <div class="alert alert-danger">Cancelled <?= e(fmt_date($order['cancelled_at'], true)) ?> — <?= e($order['cancelled_reason']) ?></div>
    <?php endif; ?>

    <?php if ($user['role_slug'] === 'super_admin'): ?>
    <div class="card border-danger mt-3"><div class="card-body">
      <h6 class="text-danger">Delete order</h6>
      <p class="small text-muted mb-2">Removes this order and its payments from all lists (soft delete, kept in the database for audit).</p>
      <form method="post" action="<?= e(admin_url('orders/' . $order['id'] . '/delete')) ?>" data-confirm="Delete order <?= e($order['job_no']) ?> permanently from all screens?">
        <?= Csrf::field() ?><button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete Order</button>
      </form>
    </div></div>
    <?php endif; ?>
  </div>
</div>

<?php if (($_GET['print'] ?? '') === '1'): ?>
<script>window.open(<?= json_encode(admin_url('orders/' . $order['id'] . '/job-card')) ?>, '_blank');</script>
<?php endif; ?>
