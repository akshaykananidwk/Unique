<?php use App\Core\Csrf; $title = 'WhatsApp Templates';
$placeholders = ['{customer_name}','{customer_phone}','{job_no}','{order_date}','{due_date}','{priority}',
  '{items_list}','{item_name}','{qty}','{total}','{advance}','{balance}','{paid}',
  '{tracking_link}','{proof_link}','{receipt_link}','{branch_name}','{branch_phone}','{branch_address}',
  '{staff_name}','{designer_name}','{feedback_text}','{revision_no}','{business_name}','{business_phone}','{today}'];
?>
<h4 class="mb-3">WhatsApp Templates</h4>
<p class="small text-muted">Tap a placeholder chip to copy it, then paste into the message body. Messages can be typed in English or Gujarati.</p>
<div class="mb-3 d-flex flex-wrap gap-1">
  <?php foreach ($placeholders as $ph): ?>
    <button type="button" class="btn btn-sm btn-outline-secondary py-0 ph-chip" data-ph="<?= e($ph) ?>"><?= e($ph) ?></button>
  <?php endforeach; ?>
</div>
<div class="accordion" id="tplAccordion">
<?php foreach ($templates as $i => $t): ?>
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#tpl<?= (int)$t['id'] ?>">
        <span class="me-2"><?= (int)$t['is_active'] ? '🟢' : '⚪' ?></span>
        <code class="me-2"><?= e($t['code']) ?></code> <?= e($t['title']) ?>
        <span class="badge bg-secondary ms-2"><?= e($t['recipient']) ?></span>
        <?php if ((int)$t['delay_minutes'] > 0): ?><span class="badge bg-info ms-1">+<?= (int)$t['delay_minutes'] ?>m</span><?php endif; ?>
      </button>
    </h2>
    <div id="tpl<?= (int)$t['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#tplAccordion">
      <div class="accordion-body">
        <form method="post" action="<?= e(admin_url('whatsapp/templates/' . $t['id'])) ?>">
          <?= Csrf::field() ?>
          <div class="row g-2">
            <div class="col-md-6"><label class="form-label">Title</label>
              <input name="title" class="form-control form-control-sm" value="<?= e($t['title']) ?>"></div>
            <div class="col-md-3"><label class="form-label">Delay (minutes)</label>
              <input type="number" min="0" name="delay_minutes" class="form-control form-control-sm" value="<?= (int)$t['delay_minutes'] ?>"></div>
            <div class="col-md-3 d-flex align-items-end">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="ta<?= (int)$t['id'] ?>" <?= (int)$t['is_active'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="ta<?= (int)$t['id'] ?>">Active</label>
              </div>
            </div>
            <div class="col-12"><label class="form-label">Message body</label>
              <textarea name="body" class="form-control tpl-body" rows="7"><?= e($t['body']) ?></textarea></div>
            <div class="col-md-6"><label class="form-label">Media URL (optional image/PDF)</label>
              <input name="media_url" class="form-control form-control-sm" value="<?= e($t['media_url']) ?>"></div>
            <?php if ($t['recipient'] === 'custom'): ?>
            <div class="col-md-6"><label class="form-label">Custom numbers (comma separated)</label>
              <input name="custom_numbers" class="form-control form-control-sm" value="<?= e($t['custom_numbers']) ?>"></div>
            <?php endif; ?>
          </div>
          <button class="btn btn-primary btn-sm mt-2">Save Template</button>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<script>
let lastBody = null;
document.addEventListener('focusin', e => { if (e.target.classList.contains('tpl-body')) lastBody = e.target; });
document.querySelectorAll('.ph-chip').forEach(chip => chip.addEventListener('click', () => {
  const ph = chip.dataset.ph;
  if (lastBody) {
    const s = lastBody.selectionStart;
    lastBody.value = lastBody.value.slice(0, s) + ph + lastBody.value.slice(lastBody.selectionEnd);
    lastBody.focus();
    lastBody.selectionStart = lastBody.selectionEnd = s + ph.length;
  } else if (navigator.clipboard) {
    navigator.clipboard.writeText(ph);
    chip.textContent = '✓ copied';
    setTimeout(() => chip.textContent = ph, 900);
  }
}));
</script>
