<?php use App\Core\Csrf; use App\Models\Status; $title = 'My Jobs'; ?>
<h4 class="mb-3">My Jobs <?= $seeAll ? '<small class="text-muted fs-6">(all designers)</small>' : '' ?></h4>

<?php // The shared board. Anyone can take an order; the design side works the same way —
// these are the jobs nobody has accepted yet, and accepting one makes it yours.
if (!empty($unclaimed)): ?>
<div class="card mb-3 border-primary"><div class="card-body">
  <h6 class="mb-1"><i class="bi bi-inbox"></i> Waiting to be accepted
    <span class="badge bg-primary"><?= count($unclaimed) ?></span></h6>
  <p class="small text-muted mb-2">
    Press <strong>Accept</strong> to take a job. It moves to your own list and counts as yours
    in the monthly design report. First to accept gets it.
  </p>
  <div class="row g-2">
    <?php foreach ($unclaimed as $u):
      $overdue = Status::isOverdue($u['due_date'], (string)$u['status']); ?>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="border rounded p-2 h-100 <?= e(priority_class($u['priority'])) ?>">
          <?php // Customer name is the link — that is how a job is recognised. The job
          // number moves underneath rather than away. ?>
          <div class="d-flex justify-content-between">
            <a href="<?= e(admin_url('orders/' . $u['order_id'])) ?>" class="fw-semibold"><?= e($u['customer_name']) ?></a>
            <?= priority_badge($u['priority']) ?>
          </div>
          <div><?= e($u['item_name_snapshot']) ?> × <?= e(rtrim(rtrim((string)$u['qty'], '0'), '.')) ?></div>
          <div class="small text-muted"><?= e($u['job_no']) ?></div>
          <div class="small <?= $overdue ? 'text-overdue fw-bold' : 'text-muted' ?>">
            <i class="bi bi-clock"></i> <?= e(fmt_date($u['due_date'], true)) ?></div>
          <?php if ($u['spec_text']): ?><div class="small border-top mt-1 pt-1"><?= e($u['spec_text']) ?></div><?php endif; ?>
          <form method="post" action="<?= e(admin_url('my-jobs/' . $u['id'] . '/claim')) ?>" class="mt-2">
            <?= Csrf::field() ?>
            <button class="btn btn-sm btn-primary w-100"><i class="bi bi-hand-index"></i> Accept — I'll do this</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div></div>
<?php endif; ?>

<div class="kanban-wrap">
  <?php foreach ($columns as $statusKey => $col): ?>
  <div class="kanban-col">
    <h6 class="d-flex justify-content-between">
      <span><?= e($col['label']) ?></span>
      <span class="badge bg-secondary"><?= count($col['jobs']) ?></span>
    </h6>
    <?php foreach ($col['jobs'] as $job):
        $overdue = Status::isOverdue($job['due_date'], (string)$job['status']); ?>
    <div class="kanban-card <?= $overdue ? 'overdue' : '' ?> <?= e(priority_class($job['priority'])) ?>">
      <div class="d-flex justify-content-between">
        <a href="<?= e(admin_url('orders/' . $job['order_id'])) ?>" class="fw-semibold"><?= e($job['customer_name']) ?></a>
        <?= priority_badge($job['priority']) ?>
      </div>
      <div><?= e($job['item_name_snapshot']) ?> × <?= e(rtrim(rtrim((string)$job['qty'], '0'), '.')) ?></div>
      <div class="text-muted small"><?= e($job['job_no']) ?> · <?= e($job['customer_phone']) ?></div>
      <div class="<?= $overdue ? 'text-overdue fw-bold' : 'text-muted' ?>"><i class="bi bi-clock"></i> <?= e(fmt_date($job['due_date'], true)) ?></div>
      <?php if ((int)$job['revision_count'] > 0): ?>
        <span class="badge bg-warning text-dark badge-status">Revision #<?= (int)$job['revision_count'] ?></span>
      <?php endif; ?>
      <?php if ($job['spec_text']): ?><div class="small mt-1 border-top pt-1"><?= e($job['spec_text']) ?></div><?php endif; ?>
      <?php if (empty($job['proofs']) && (int)$job['assigned_designer_id'] === (int)$user['id']): ?>
        <form method="post" action="<?= e(admin_url('my-jobs/' . $job['id'] . '/release')) ?>" class="mt-1"
              data-confirm="Put this job back for someone else to accept?">
          <?= Csrf::field() ?>
          <button class="btn btn-sm btn-outline-secondary w-100"><i class="bi bi-arrow-return-left"></i> Give back</button>
        </form>
      <?php endif; ?>
      <?php if ($job['special_instructions']): ?><div class="small text-warning-emphasis">📌 <?= e($job['special_instructions']) ?></div><?php endif; ?>

      <?php if ($job['attachments']): ?>
        <div class="small mt-1"><strong>Customer files:</strong>
        <?php foreach ($job['attachments'] as $a): ?>
          <a target="_blank" href="<?= e(upload_url($a['file_path'])) ?>"><?= e($a['original_name'] ?: 'file') ?></a>
        <?php endforeach; ?></div>
      <?php endif; ?>

      <?php // Latest feedback (including voice notes)
      $latestFeedback = [];
      foreach ($job['proofs'] as $proofRow) { foreach ($proofRow['feedback'] as $fb) { $latestFeedback[] = $fb; } }
      if ($latestFeedback): ?>
        <div class="small mt-1 border-top pt-1"><strong>Customer feedback:</strong>
        <?php foreach (array_slice($latestFeedback, 0, 3) as $fb): ?>
          <div class="mt-1">
            <?php if ($fb['feedback_text']): ?>💬 <?= e($fb['feedback_text']) ?><?php endif; ?>
            <?php if ($fb['voice_file_path']): ?>
              <audio controls preload="none" style="width:100%;height:30px" src="<?= e(upload_url($fb['voice_file_path'])) ?>"></audio>
            <?php endif; ?>
            <?php if ($fb['reference_file_path']): ?>
              <a target="_blank" href="<?= e(upload_url($fb['reference_file_path'])) ?>">📎 reference image</a>
            <?php endif; ?>
            <div class="text-muted"><?= e(fmt_date($fb['created_at'], true)) ?> (<?= e($fb['input_method']) ?>)</div>
          </div>
        <?php endforeach; ?></div>
      <?php endif; ?>

      <?php if ($job['proofs']): ?>
        <div class="small mt-1"><strong>Versions:</strong>
        <?php foreach ($job['proofs'] as $proofRow): ?>
          <a target="_blank" href="<?= e(upload_url($proofRow['file_path'])) ?>">v<?= (int)$proofRow['version'] ?></a>
        <?php endforeach; ?></div>
      <?php endif; ?>

      <div class="mt-2 d-grid gap-1">
        <?php if ($job['status'] === 'design_pending'): ?>
        <form method="post" action="<?= e(admin_url('my-jobs/' . $job['id'] . '/start')) ?>">
          <?= Csrf::field() ?><button class="btn btn-sm btn-primary w-100"><i class="bi bi-play"></i> Start Job</button>
        </form>
        <?php endif; ?>
        <?php if (in_array($job['status'], ['design_pending', 'design_in_progress', 'change_requested', 'proof_sent'], true)): ?>
        <form method="post" action="<?= e(admin_url('my-jobs/' . $job['id'] . '/proof')) ?>" enctype="multipart/form-data" class="d-grid gap-1">
          <?= Csrf::field() ?>
          <input type="file" name="proof" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" required>
          <input name="designer_note" class="form-control form-control-sm" placeholder="Note to customer (optional)">
          <button class="btn btn-sm btn-success"><i class="bi bi-cloud-arrow-up"></i> Upload Proof</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$col['jobs']): ?><div class="text-muted small text-center py-2">—</div><?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
