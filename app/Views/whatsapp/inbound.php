<?php $title = 'Inbound WhatsApp'; ?>
<h4 class="mb-3">WhatsApp</h4>
<?= App\Core\View::partial('whatsapp/_nav', ['waActive' => 'inbound']) ?>
<h5 class="mb-3">Inbound Messages <span class="text-muted fs-6">(<?= (int)$total ?>)</span></h5>
<?php if (!$rows): ?>
  <div class="empty-state"><i class="bi bi-inbox"></i>No inbound messages yet.
    Configure the webhook URL from WhatsApp Settings in your provider panel.</div>
<?php endif; ?>
<div class="list-group">
<?php foreach ($rows as $r): ?>
  <div class="list-group-item">
    <div class="d-flex justify-content-between">
      <strong><?= e($r['customer_name'] ?? $r['from_number']) ?></strong>
      <small class="text-muted"><?= e(fmt_date($r['created_at'], true)) ?></small>
    </div>
    <div class="small text-muted"><?= e($r['from_number']) ?>
      <?php if ($r['job_no']): ?> · latest open order: <a href="<?= e(admin_url('orders/' . $r['matched_order_id'])) ?>"><?= e($r['job_no']) ?></a><?php endif; ?>
    </div>
    <div class="mt-1"><?= e($r['message']) ?></div>
  </div>
<?php endforeach; ?>
</div>
