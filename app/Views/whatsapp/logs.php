<?php use App\Core\Acl; use App\Core\Csrf; $title = 'WhatsApp Logs';
$statusColors = ['pending' => 'secondary', 'processing' => 'info', 'sent' => 'success', 'failed' => 'danger', 'cancelled' => 'dark'];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h4 class="mb-0">WhatsApp Logs <span class="text-muted fs-6">(<?= (int)$total ?>)</span></h4>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(admin_url('whatsapp/inbound')) ?>"><i class="bi bi-inbox"></i> Inbound</a>
    <?php if (Acl::can('whatsapp.resend')): ?>
    <form method="post" action="<?= e(admin_url('whatsapp/logs/resend-failed')) ?>" data-confirm="Re-queue ALL failed messages?">
      <?= Csrf::field() ?><button class="btn btn-warning btn-sm"><i class="bi bi-arrow-repeat"></i> Resend all failed</button>
    </form>
    <?php endif; ?>
  </div>
</div>
<form method="get" class="d-flex gap-2 mb-3">
  <input name="q" value="<?= e($q) ?>" class="form-control form-control-sm" style="max-width:240px" placeholder="Number / template / text">
  <select name="status" class="form-select form-select-sm" style="max-width:150px">
    <option value="">All statuses</option>
    <?php foreach (array_keys($statusColors) as $s): ?>
      <option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-outline-primary btn-sm">Filter</button>
</form>
<?php if (!$rows): ?><div class="empty-state"><i class="bi bi-whatsapp"></i>No messages yet.</div><?php endif; ?>
<div class="table-responsive"><table class="table table-sm table-mobile align-middle">
  <thead><tr><th>#</th><th>To</th><th>Template</th><th>Message</th><th>Status</th><th>Scheduled / Sent</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td data-label="#"><?= (int)$r['id'] ?></td>
      <td data-label="To"><?= e($r['to_number']) ?></td>
      <td data-label="Template"><code class="small"><?= e($r['template_code'] ?? '—') ?></code></td>
      <td data-label="Message" style="max-width:340px">
        <details><summary class="small"><?= e(mb_substr((string)$r['message'], 0, 60)) ?>…</summary>
          <pre class="small mb-1" style="white-space:pre-wrap"><?= e($r['message']) ?></pre>
          <?php if ($r['last_error']): ?><div class="text-danger small">Error: <?= e($r['last_error']) ?></div><?php endif; ?>
          <?php if ($r['api_response']): ?><div class="text-muted small">API: <?= e(mb_substr((string)$r['api_response'], 0, 300)) ?></div><?php endif; ?>
        </details></td>
      <td data-label="Status"><span class="badge bg-<?= e($statusColors[$r['status']] ?? 'secondary') ?>"><?= e($r['status']) ?></span>
        <?php if ((int)$r['attempts'] > 0): ?><small class="text-muted">×<?= (int)$r['attempts'] ?></small><?php endif; ?></td>
      <td data-label="When" class="small"><?= e(fmt_date($r['sent_at'] ?: $r['scheduled_at'], true)) ?></td>
      <td data-label="">
        <?php if (Acl::can('whatsapp.resend') && in_array($r['status'], ['failed', 'sent', 'cancelled'], true)): ?>
        <form method="post" action="<?= e(admin_url('whatsapp/logs/' . $r['id'] . '/resend')) ?>">
          <?= Csrf::field() ?><button class="btn btn-sm btn-outline-warning" title="Resend"><i class="bi bi-arrow-repeat"></i></button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php $pages = (int)ceil($total / $perPage); if ($pages > 1): ?>
<nav><ul class="pagination pagination-sm">
  <?php for ($p = max(1, $page - 5); $p <= min($pages, $page + 5); $p++): $qs = $_GET; $qs['page'] = $p; ?>
    <li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query($qs)) ?>"><?= $p ?></a></li>
  <?php endfor; ?>
</ul></nav>
<?php endif; ?>
