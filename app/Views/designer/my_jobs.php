<?php use App\Core\Csrf; use App\Models\Status; $title = 'My Jobs'; ?>
<h4 class="mb-3">My Jobs <?= $seeAll ? '<small class="text-muted fs-6">(all designers)</small>' : '' ?></h4>
<div class="kanban-wrap">
  <?php foreach ($columns as $statusKey => $col): ?>
  <div class="kanban-col">
    <h6 class="d-flex justify-content-between">
      <span><?= e($col['label']) ?></span>
      <span class="badge bg-secondary"><?= count($col['jobs']) ?></span>
    </h6>
    <?php foreach ($col['jobs'] as $job):
        $overdue = Status::isOverdue($job['due_date'], (string)$job['status']); ?>
    <div class="kanban-card <?= $overdue ? 'overdue' : '' ?>">
      <div class="d-flex justify-content-between">
        <a href="<?= e(admin_url('orders/' . $job['order_id'])) ?>" class="fw-semibold"><?= e($job['job_no']) ?></a>
        <?php if ($job['priority'] !== 'normal'): ?><span class="badge bg-danger badge-status"><?= e(strtoupper((string)$job['priority'])) ?></span><?php endif; ?>
      </div>
      <div><?= e($job['item_name_snapshot']) ?> × <?= e(rtrim(rtrim((string)$job['qty'], '0'), '.')) ?></div>
      <div class="text-muted"><?= e($job['customer_name']) ?> (<?= e($job['customer_phone']) ?>)</div>
      <div class="<?= $overdue ? 'text-overdue fw-bold' : 'text-muted' ?>"><i class="bi bi-clock"></i> <?= e(fmt_date($job['due_date'], true)) ?></div>
      <?php if ((int)$job['revision_count'] > 0): ?>
        <span class="badge bg-warning text-dark badge-status">Revision #<?= (int)$job['revision_count'] ?></span>
      <?php endif; ?>
      <?php if ($job['spec_text']): ?><div class="small mt-1 border-top pt-1"><?= e($job['spec_text']) ?></div><?php endif; ?>
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
