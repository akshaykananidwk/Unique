<?php use App\Core\Csrf;
$title = 'Cash in Hand';
$totalHeld = array_sum(array_map(fn($b) => (float)$b['in_hand'], $balances));
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h4 class="mb-0">Cash in Hand</h4>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(admin_url('cashbook')) ?>"><i class="bi bi-cash-stack"></i> Cash Book</a>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(admin_url('payments')) ?>"><i class="bi bi-cash-coin"></i> Payments</a>
  </div>
</div>

<?php // What the person looking at this screen is holding right now. ?>
<div class="row g-2 mb-3">
  <div class="col-6 col-lg-3"><div class="stat-card">
    <div class="text-muted small">With you</div>
    <div class="fs-3 fw-bold text-success"><?= e(fmt_money($mine['in_hand'])) ?></div>
    <div class="small text-muted">Cash you took, less what you have passed on</div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-card">
    <div class="text-muted small">Promised out</div>
    <div class="fs-3 fw-bold text-warning-emphasis"><?= e(fmt_money($mine['reserved'])) ?></div>
    <div class="small text-muted">Handovers you started but have not finished</div></div></div>
  <div class="col-6 col-lg-3"><div class="stat-card">
    <div class="text-muted small">Free to hand over</div>
    <div class="fs-3 fw-bold"><?= e(fmt_money($mine['available'])) ?></div></div></div>
  <?php if ($seeAll): ?>
  <div class="col-6 col-lg-3"><div class="stat-card">
    <div class="text-muted small">With all staff</div>
    <div class="fs-3 fw-bold"><?= e(fmt_money($totalHeld)) ?></div>
    <div class="small text-muted">Cash not yet banked</div></div></div>
  <?php endif; ?>
</div>

<?php // ---------------------------------------------------------------- the receiver's half
// The code lives here and nowhere else. Only the person the money is coming to sees it. ?>
<?php foreach ($waiting as $w): ?>
  <div class="card border-primary mb-3"><div class="card-body">
    <div class="row align-items-center g-3">
      <div class="col-md-7">
        <h5 class="mb-1"><?= e($w['from_name']) ?> is handing you <?= e(fmt_money($w['amount'])) ?></h5>
        <div class="small text-muted mb-2">
          <?= e($w['ref_no']) ?> · started <?= e(fmt_date($w['created_at'], true)) ?>
          <?php if ($w['note']): ?> · <?= e($w['note']) ?><?php endif; ?>
        </div>
        <?php if ($w['expired']): ?>
          <div class="alert alert-warning py-2 mb-0 small">This code has run out. Ask them to send a new one.</div>
        <?php else: ?>
          <p class="mb-0 small">Count the money, then read this code out. It is only good until
            <strong><?= e(fmt_date($w['otp_expires_at'], true)) ?></strong>.</p>
        <?php endif; ?>
      </div>
      <div class="col-md-3 text-center">
        <div class="text-muted small text-uppercase">Code</div>
        <div class="fw-bold" style="font-size:2.2rem;letter-spacing:.35rem;font-family:monospace">
          <?= e($w['code']) ?></div>
      </div>
      <div class="col-md-2 d-grid gap-2">
        <form method="post" action="<?= e(admin_url('cash/handover/' . $w['id'] . '/decline')) ?>"
              data-confirm="Turn down <?= e(fmt_money($w['amount'])) ?> from <?= e($w['from_name']) ?>?">
          <?= Csrf::field() ?>
          <button class="btn btn-outline-danger btn-sm w-100">Not received</button>
        </form>
      </div>
    </div>
  </div></div>
<?php endforeach; ?>

<?php // ------------------------------------------------------------------ the sender's half ?>
<?php foreach ($started as $s): ?>
  <div class="card mb-3"><div class="card-body">
    <div class="row align-items-end g-2">
      <div class="col-md-5">
        <h6 class="mb-1">You are handing <?= e(fmt_money($s['amount'])) ?> to <?= e($s['to_name']) ?></h6>
        <div class="small text-muted"><?= e($s['ref_no']) ?> · started <?= e(fmt_date($s['created_at'], true)) ?>
          <?php if ($s['note']): ?> · <?= e($s['note']) ?><?php endif; ?></div>
        <?php if ($s['expired']): ?>
          <div class="small text-danger mt-1">The code ran out — ask for a new one.</div>
        <?php elseif ($s['locked']): ?>
          <div class="small text-danger mt-1">Too many wrong codes. Ask for a new one.</div>
        <?php else: ?>
          <div class="small text-muted mt-1">Ask <?= e($s['to_name']) ?> to read out the code on their screen.</div>
        <?php endif; ?>
      </div>
      <div class="col-md-4">
        <form method="post" action="<?= e(admin_url('cash/handover/' . $s['id'] . '/confirm')) ?>" class="d-flex gap-2">
          <?= Csrf::field() ?>
          <input name="code" class="form-control text-center" inputmode="numeric" autocomplete="off"
                 maxlength="6" placeholder="6-digit code"
                 style="letter-spacing:.3rem;font-family:monospace">
          <button class="btn btn-primary text-nowrap"><i class="bi bi-check-lg"></i> Done</button>
        </form>
      </div>
      <div class="col-md-3 d-flex gap-2 justify-content-md-end">
        <form method="post" action="<?= e(admin_url('cash/handover/' . $s['id'] . '/reissue')) ?>">
          <?= Csrf::field() ?><button class="btn btn-outline-secondary btn-sm">New code</button>
        </form>
        <form method="post" action="<?= e(admin_url('cash/handover/' . $s['id'] . '/cancel')) ?>"
              data-confirm="Cancel this handover? The money stays with you.">
          <?= Csrf::field() ?><button class="btn btn-outline-danger btn-sm">Cancel</button>
        </form>
      </div>
    </div>
  </div></div>
<?php endforeach; ?>

<?php // ------------------------------------------------------------------------ start one ?>
<div class="card mb-3"><div class="card-body">
  <h6 class="text-uppercase text-muted small">Hand cash over</h6>
  <?php if (!$staff): ?>
    <p class="small text-muted mb-0">There is nobody else on the staff list to hand money to.</p>
  <?php else: ?>
  <form method="post" action="<?= e(admin_url('cash/handover')) ?>" class="row g-2 align-items-end">
    <?= Csrf::field() ?>
    <div class="col-12 col-md-4"><label class="form-label">To</label>
      <select name="to_user_id" class="form-select" required>
        <option value="">— pick a person —</option>
        <?php foreach ($staff as $sf): ?>
          <option value="<?= (int)$sf['id'] ?>"><?= e($sf['name']) ?> — <?= e($sf['role_name']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-6 col-md-3"><label class="form-label">Amount ₹</label>
      <input type="number" step="0.01" min="1" max="<?= e((string)$mine['available']) ?>"
             name="amount" class="form-control" required>
      <div class="form-text"><?= e(fmt_money($mine['available'])) ?> free</div></div>
    <div class="col-12 col-md-3"><label class="form-label">Note (optional)</label>
      <input name="note" class="form-control" placeholder="Day's collection, bank deposit…"></div>
    <div class="col-6 col-md-2 d-grid">
      <button class="btn btn-primary"><i class="bi bi-arrow-left-right"></i> Hand over</button></div>
  </form>
  <div class="form-text mt-2">
    A code appears on their screen. They read it out, you type it in — only then does the money move.
  </div>
  <?php endif; ?>
</div></div>

<?php // -------------------------------------------------------------- everybody's pockets ?>
<?php if ($seeAll): ?>
<div class="card mb-3"><div class="card-body">
  <h6 class="text-uppercase text-muted small">Who is holding what</h6>
  <div class="table-responsive"><table class="table table-sm table-hover align-middle table-mobile mb-0">
    <thead><tr>
      <th>Staff</th><th>Role</th>
      <th class="text-end">Cash in hand</th>
      <th class="text-end">Promised out</th>
      <th class="text-end">Digital collected</th>
      <th>Last cash taken</th><th></th>
    </tr></thead>
    <tbody>
    <?php foreach ($balances as $b): ?>
      <tr>
        <td data-label="Staff"><span class="fw-semibold"><?= e($b['name']) ?></span>
          <?php if ((int)$b['waiting_on_them']): ?>
            <span class="badge bg-info badge-status"><?= (int)$b['waiting_on_them'] ?> waiting on them</span>
          <?php endif; ?>
        </td>
        <td data-label="Role" class="small text-muted"><?= e($b['role_name']) ?></td>
        <td data-label="Cash in hand" class="text-end fw-semibold <?= (float)$b['in_hand'] > 0 ? 'text-success' : 'text-muted' ?>">
          <?= e(fmt_money($b['in_hand'])) ?></td>
        <td data-label="Promised out" class="text-end"><?= (float)$b['reserved'] > 0 ? e(fmt_money($b['reserved'])) : '—' ?></td>
        <td data-label="Digital collected" class="text-end text-muted"><?= e(fmt_money($b['digital_collected'])) ?></td>
        <td data-label="Last cash taken" class="small text-muted">
          <?= $b['last_cash_at'] ? e(fmt_date($b['last_cash_at'], true)) : '—' ?></td>
        <td data-label="" class="text-end">
          <a class="btn btn-sm btn-outline-secondary" href="?user=<?= (int)$b['id'] ?>">Statement</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr class="fw-bold">
      <td colspan="2">Total not yet banked</td>
      <td class="text-end"><?= e(fmt_money($totalHeld)) ?></td>
      <td colspan="4"></td>
    </tr></tfoot>
  </table></div>
  <div class="form-text">Cash only. UPI, card, bank and cheque never sat in anybody's pocket, so they are shown apart.</div>
</div></div>
<?php endif; ?>

<?php // ------------------------------------------------------------------- the trail ?>
<div class="row g-3">
  <div class="col-lg-6">
    <div class="card h-100"><div class="card-body">
      <h6 class="text-uppercase text-muted small">
        Statement<?php if ($seeAll && $statementFor !== (int)$user['id']): ?>
          — <?= e((string)(array_column($balances, 'name', 'id')[$statementFor] ?? '')) ?>
        <?php endif; ?>
      </h6>
      <?php if (!$statement): ?>
        <p class="small text-muted mb-0">No cash has passed through this pocket yet.</p>
      <?php else: ?>
        <div class="table-responsive" style="max-height:420px;overflow-y:auto">
          <table class="table table-sm mb-0">
            <tbody>
            <?php foreach ($statement as $m): ?>
              <tr>
                <td class="small text-nowrap text-muted"><?= e(fmt_date($m['at'], true)) ?></td>
                <td class="small"><?= e($m['detail']) ?></td>
                <td class="text-end fw-semibold <?= (float)$m['amount'] < 0 ? 'text-danger' : 'text-success' ?>">
                  <?= ((float)$m['amount'] < 0 ? '−' : '+') . e(fmt_money(abs((float)$m['amount']))) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div></div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100"><div class="card-body">
      <h6 class="text-uppercase text-muted small">Handovers</h6>
      <form method="get" class="row g-2 mb-2">
        <?php if ($seeAll): ?>
        <div class="col-6"><select name="user" class="form-select form-select-sm">
          <option value="">Everyone</option>
          <?php foreach ($balances as $b): ?>
            <option value="<?= (int)$b['id'] ?>" <?= (int)$filter['user'] === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
          <?php endforeach; ?>
        </select></div>
        <?php endif; ?>
        <div class="col-6"><select name="status" class="form-select form-select-sm">
          <option value="">Any status</option>
          <?php foreach (['pending', 'completed', 'declined', 'cancelled', 'expired'] as $st): ?>
            <option value="<?= $st ?>" <?= $filter['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
          <?php endforeach; ?>
        </select></div>
        <div class="col-6 col-md-4"><input type="date" name="from" value="<?= e($filter['from']) ?>" class="form-control form-control-sm"></div>
        <div class="col-6 col-md-4"><input type="date" name="to" value="<?= e($filter['to']) ?>" class="form-control form-control-sm"></div>
        <div class="col-12 col-md-4 d-grid"><button class="btn btn-outline-primary btn-sm">Filter</button></div>
      </form>
      <?php if (!$history): ?>
        <p class="small text-muted mb-0">No handovers yet.</p>
      <?php else: ?>
        <div class="table-responsive" style="max-height:420px;overflow-y:auto">
          <table class="table table-sm mb-0">
            <thead><tr><th>Ref</th><th>From → To</th><th class="text-end">Amount</th><th>Status</th></tr></thead>
            <tbody>
            <?php
            $badge = ['completed' => 'success', 'pending' => 'warning text-dark', 'declined' => 'danger',
                      'cancelled' => 'secondary', 'expired' => 'secondary'];
            foreach ($history as $h): ?>
              <tr>
                <td class="small"><code><?= e($h['ref_no']) ?></code>
                  <div class="text-muted"><?= e(fmt_date($h['created_at'], true)) ?></div></td>
                <td class="small"><?= e($h['from_name']) ?> → <?= e($h['to_name']) ?>
                  <?php if ($h['note']): ?><div class="text-muted"><?= e($h['note']) ?></div><?php endif; ?></td>
                <td class="text-end fw-semibold"><?= e(fmt_money($h['amount'])) ?></td>
                <td><span class="badge bg-<?= e($badge[$h['status']] ?? 'secondary') ?>"><?= e($h['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div></div>
  </div>
</div>
