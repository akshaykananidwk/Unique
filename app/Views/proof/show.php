<?php
use App\Core\Csrf;
use App\Core\Settings;

$title = 'Design Proof — ' . $order['job_no'];
$isApproved = $proof['status'] === 'approved';
$isSuperseded = $proof['status'] === 'superseded';
$isChangeRequested = $proof['status'] === 'change_requested';
$fileUrl = base_url('proof/' . $proof['proof_token'] . '/file/' . $proof['version']);
$isPdf = $proof['file_type'] === 'pdf';
?>
<div class="container py-4" style="max-width:640px">
  <div class="text-center mb-3">
    <?php if ($logo = (string)Settings::get('business_logo', '')): ?>
      <img src="<?= e(upload_url($logo)) ?>" alt="" style="max-height:48px">
    <?php endif; ?>
    <h4 class="mt-2 mb-0">Design Proof</h4>
    <div class="text-muted">Job <strong><?= e($order['job_no']) ?></strong> · <?= e($item['item_name_snapshot']) ?>
      · Version <?= (int)$proof['version'] ?></div>
    <div class="small">Hello <?= e($customer['name']) ?> 🙏</div>
  </div>

  <?php if ($isSuperseded): ?>
    <div class="alert alert-warning">A newer version of this design exists.
      <?php foreach ($versions as $v): if ($v['status'] !== 'superseded'): ?>
        <a class="alert-link" href="<?= e(base_url('proof/' . $v['proof_token'])) ?>">Open version <?= (int)$v['version'] ?> →</a>
      <?php break; endif; endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($proof['designer_note']): ?>
    <div class="alert alert-info"><i class="bi bi-chat-left-text"></i> <strong>Designer's note:</strong> <?= e($proof['designer_note']) ?></div>
  <?php endif; ?>

  <div class="proof-image-wrap mb-3">
    <?php if ($isPdf): ?>
      <iframe src="<?= e($fileUrl) ?>" style="width:100%;height:70vh;border:0;background:#fff" title="Design proof PDF"></iframe>
    <?php else: ?>
      <img src="<?= e($fileUrl) ?>" alt="Design proof — tap to zoom">
    <?php endif; ?>
  </div>
  <p class="text-center small text-muted">Tap the image for full screen · pinch to zoom on your phone</p>

  <?php if (count($versions) > 1): ?>
  <details class="mb-3"><summary class="small">View previous versions (<?= count($versions) - 1 ?>)</summary>
    <ul class="small mt-2">
      <?php foreach ($versions as $v): if ((int)$v['version'] === (int)$proof['version']) continue; ?>
      <li>Version <?= (int)$v['version'] ?> — <?= e(ucwords(str_replace('_', ' ', (string)$v['status']))) ?>
        · <a href="<?= e(base_url('proof/' . $proof['proof_token'] . '/file/' . $v['version'])) ?>" target="_blank">view</a></li>
      <?php endforeach; ?>
    </ul>
  </details>
  <?php endif; ?>

  <?php if ($isApproved): ?>
    <div class="alert alert-success text-center fs-5">
      ✅ <strong>Approved on <?= e(fmt_date($proof['responded_at'], true)) ?></strong><br>
      <span class="fs-6">Printing will start with this exact design. No further changes are possible.</span>
    </div>
  <?php elseif (!$isSuperseded): ?>

    <?php if ($isChangeRequested): ?>
      <div class="alert alert-warning">✏️ You requested changes on <?= e(fmt_date($proof['responded_at'], true)) ?>.
        The designer is working on a new version — you'll get a WhatsApp link when it's ready.
        You can still add more feedback below.</div>
    <?php endif; ?>

    <div class="proof-actions d-grid gap-2 mb-3">
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">✅ APPROVE — Looks Perfect</button>
      <button class="btn btn-outline-warning" type="button" id="btnRequestChange">✏️ REQUEST CHANGES</button>
    </div>

    <!-- Change request form -->
    <form id="changeForm" class="card d-none mb-3" method="post" enctype="multipart/form-data"
          action="<?= e(base_url('proof/' . $proof['proof_token'] . '/change')) ?>">
      <div class="card-body">
        <?= Csrf::field() ?>
        <label class="form-label fw-semibold">What would you like to change?</label>
        <div class="d-flex gap-2 align-items-start">
          <textarea id="feedbackText" name="feedback" class="form-control" rows="4"
                    placeholder="Type here… or tap the mic and speak"></textarea>
          <div class="text-center">
            <button type="button" id="micBtn" class="btn btn-outline-secondary mic-btn rounded-circle" style="width:52px;height:52px"
                    title="Speak instead of typing">🎤</button>
            <select id="speechLang" class="form-select form-select-sm mt-1" style="width:90px">
              <option value="gu-IN" selected>ગુજરાતી</option>
              <option value="hi-IN">हिन्दी</option>
              <option value="en-IN">English</option>
            </select>
          </div>
        </div>
        <div id="voiceStatus" class="small text-muted mt-1"></div>
        <input type="file" id="voiceFile" name="voice_note" class="d-none" accept="audio/*">
        <div class="mt-2">
          <label class="form-label small">Attach a reference image (optional)</label>
          <input type="file" name="reference" class="form-control form-control-sm" accept="image/*">
        </div>
        <button class="btn btn-warning w-100 mt-3">Send Change Request</button>
      </div>
    </form>
  <?php endif; ?>

  <p class="text-center small text-muted">
    <a href="<?= e(base_url('track/' . $order['tracking_token'])) ?>">← Back to order tracking</a>
  </p>
</div>

<!-- Double-confirm approval modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning-subtle">
        <h5 class="modal-title">⚠️ Are you sure?</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Once you approve this design, <strong>no further changes can be made</strong>.
           Printing will start with this exact design.</p>
        <p class="mb-0">Please check <strong>spelling, phone numbers and logo</strong> carefully.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <form method="post" action="<?= e(base_url('proof/' . $proof['proof_token'] . '/approve')) ?>">
          <?= Csrf::field() ?>
          <input type="hidden" name="confirmed" value="yes">
          <button class="btn btn-success">Yes, I Confirm — Start Printing</button>
        </form>
      </div>
    </div>
  </div>
</div>
