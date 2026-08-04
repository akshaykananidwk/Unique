<?php
use App\Core\Csrf;
use App\Core\Scheduler;

$title = 'Cron Jobs';

$stateColors = ['ok' => 'success', 'failing' => 'danger', 'late' => 'warning', 'down' => 'danger', 'never' => 'secondary'];
$stateLabels = ['ok' => 'Healthy', 'failing' => 'Failing', 'late' => 'Late', 'down' => 'Not running', 'never' => 'Never run'];
$runColors = ['success' => 'success', 'failed' => 'danger', 'running' => 'info', 'skipped' => 'secondary', 'never' => 'light'];

/** "every 15 min" / "daily at 21:00" in one line. */
$scheduleText = static function (array $j): string {
    if (($j['schedule_type'] ?? '') === 'daily') {
        return 'Daily at ' . substr((string)$j['run_at_time'], 0, 5);
    }
    $m = (int)$j['interval_minutes'];
    if ($m % 1440 === 0) { return 'Every ' . ($m / 1440) . ' day(s)'; }
    if ($m % 60 === 0) { return 'Every ' . ($m / 60) . ' hour(s)'; }
    return 'Every ' . $m . ' min';
};

$countdown = static function (?string $when): string {
    if (!$when) { return '—'; }
    $diff = strtotime($when) - time();
    return $diff <= 0 ? 'due now' : 'in ' . Scheduler::ago($diff);
};

$state = $health['state'];
$groups = [];
foreach ($jobs as $j) { $groups[$j['group_name']][] = $j; }
$failedCount = count(array_filter($jobs, fn($j) => $j['last_status'] === 'failed'));
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h4 class="mb-0">Cron Jobs <small class="text-muted fs-6">background tasks</small></h4>
  <div class="d-flex gap-2">
    <form method="post" action="<?= e(admin_url('system/cron/run')) ?>">
      <?= Csrf::field() ?><input type="hidden" name="job_key" value="">
      <button class="btn btn-primary btn-sm"><i class="bi bi-play-fill"></i> Run all due now</button>
    </form>
    <?php if ($failedCount > 0): ?>
    <form method="post" action="<?= e(admin_url('system/cron/retry-failed')) ?>">
      <?= Csrf::field() ?>
      <button class="btn btn-warning btn-sm"><i class="bi bi-arrow-repeat"></i> Retry failed (<?= $failedCount ?>)</button>
    </form>
    <?php endif; ?>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(admin_url('system/cron/history')) ?>"><i class="bi bi-clock-history"></i> Full history</a>
  </div>
</div>

<!-- ------------------------------------------------------------------ health -->
<div class="card mb-3 border-<?= e($stateColors[$state] ?? 'secondary') ?>">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-center gap-3">
      <span class="badge bg-<?= e($stateColors[$state] ?? 'secondary') ?> fs-6" id="cronState">
        <i class="bi bi-<?= $state === 'ok' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
        <?= e($stateLabels[$state] ?? $state) ?>
      </span>
      <span id="cronNote"><?= e($health['note']) ?></span>
    </div>
    <div class="row g-3 mt-1 text-center">
      <div class="col-6 col-md-3 col-lg-2">
        <div class="border rounded py-2">
          <div class="small text-muted">Last tick</div>
          <div class="fw-semibold" id="cronLastTick"><?= e($health['last_tick_at'] ? fmt_date($health['last_tick_at'], true) : 'Never') ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <div class="border rounded py-2"><div class="small text-muted">Ticks so far</div>
          <div class="fw-semibold"><?= number_format($health['tick_count']) ?></div></div>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <div class="border rounded py-2"><div class="small text-muted">Jobs on</div>
          <div class="fw-semibold"><?= (int)$health['jobs_enabled'] ?> / <?= (int)$health['jobs_total'] ?></div></div>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <div class="border rounded py-2"><div class="small text-muted">Runs (24h)</div>
          <div class="fw-semibold"><?= (int)$stats['total'] ?></div></div>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <div class="border rounded py-2"><div class="small text-muted">Failed (24h)</div>
          <div class="fw-semibold <?= $stats['failed'] > 0 ? 'text-danger' : '' ?>"><?= (int)$stats['failed'] ?></div></div>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <div class="border rounded py-2"><div class="small text-muted">Avg duration</div>
          <div class="fw-semibold"><?= (int)$stats['avg_ms'] ?> ms</div></div>
      </div>
    </div>

    <?php if ($health['stuck']): ?>
      <div class="alert alert-warning mt-3 mb-0 py-2 small">
        <strong>Stuck:</strong>
        <?php foreach ($health['stuck'] as $s): ?>
          <?= e($s['name']) ?> (locked since <?= e(fmt_date($s['locked_at'], true)) ?>)
          — use <em>Clear lock</em> below.
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if ($state === 'never' || $state === 'down'): ?>
      <div class="alert alert-danger mt-3 mb-0 py-2 small">
        Add this one line to the server crontab and everything below runs by itself:
        <code class="d-block mt-1"><?= e($cronCommand) ?></code>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- -------------------------------------------------------------------- jobs -->
<?php foreach ($groups as $groupName => $groupJobs): ?>
<div class="card mb-3"><div class="card-body">
  <h6 class="text-uppercase text-muted small mb-2"><?= e($groupName) ?></h6>
  <div class="table-responsive"><table class="table table-sm table-mobile kp-cron align-middle mb-0">
    <thead><tr>
      <th>Job</th><th>Schedule</th><th>Last run</th><th>Result</th><th>Next run</th><th>On</th><th class="text-end">Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach ($groupJobs as $j): $key = (string)$j['job_key']; ?>
      <tr class="<?= $j['last_status'] === 'failed' ? 'table-danger' : '' ?>">
        <td data-label="Job">
          <div class="fw-semibold"><?= e($j['name']) ?></div>
          <div class="small text-muted"><?= e($j['description']) ?></div>
          <code class="small text-muted"><?= e($key) ?></code>
          <?php if (!$j['has_handler']): ?>
            <span class="badge bg-danger">no handler</span>
          <?php endif; ?>
        </td>
        <td data-label="Schedule" class="small"><?= e($scheduleText($j)) ?></td>
        <td data-label="Last run" class="small">
          <?= e($j['last_run_at'] ? fmt_date($j['last_run_at'], true) : '—') ?>
          <?php if ($j['last_duration_ms'] !== null): ?>
            <div class="text-muted"><?= (int)$j['last_duration_ms'] ?> ms<?= (int)$j['last_items'] > 0 ? ' · ' . (int)$j['last_items'] . ' item(s)' : '' ?></div>
          <?php endif; ?>
        </td>
        <td data-label="Result" style="max-width:280px">
          <span class="badge bg-<?= e($runColors[$j['last_status']] ?? 'secondary') ?>"><?= e($j['last_status']) ?></span>
          <?php if ((int)$j['consecutive_failures'] > 0): ?>
            <span class="badge bg-danger">×<?= (int)$j['consecutive_failures'] ?></span>
          <?php endif; ?>
          <?php if ($j['last_message']): ?>
            <div class="small text-muted"><?= e(mb_substr((string)$j['last_message'], 0, 140)) ?></div>
          <?php endif; ?>
        </td>
        <td data-label="Next run" class="small">
          <?= e($j['next_run_at'] ? fmt_date($j['next_run_at'], true) : '—') ?>
          <div class="text-muted"><?= (int)$j['is_enabled'] === 1 ? e($countdown($j['next_run_at'])) : 'paused' ?></div>
        </td>
        <td data-label="On">
          <form method="post" action="<?= e(admin_url('system/cron/toggle')) ?>" class="m-0">
            <?= Csrf::field() ?>
            <input type="hidden" name="job_key" value="<?= e($key) ?>">
            <input type="hidden" name="enabled" value="<?= (int)$j['is_enabled'] === 1 ? '0' : '1' ?>">
            <div class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" role="switch" data-auto-submit
                     <?= (int)$j['is_enabled'] === 1 ? 'checked' : '' ?>
                     aria-label="Enable <?= e($j['name']) ?>">
            </div>
          </form>
        </td>
        <td data-label="Actions" class="text-end text-nowrap">
          <form method="post" action="<?= e(admin_url('system/cron/run')) ?>" class="d-inline">
            <?= Csrf::field() ?><input type="hidden" name="job_key" value="<?= e($key) ?>">
            <button class="btn btn-sm btn-outline-primary" title="Run now"><i class="bi bi-play-fill"></i></button>
          </form>
          <button class="btn btn-sm btn-outline-secondary" title="Schedule"
                  data-bs-toggle="modal" data-bs-target="#sched-<?= e(str_replace('.', '-', $key)) ?>">
            <i class="bi bi-clock"></i>
          </button>
          <a class="btn btn-sm btn-outline-secondary" title="This job's log"
             href="<?= e(admin_url('system/cron?job=' . urlencode($key))) ?>"><i class="bi bi-list-ul"></i></a>
          <?php if ($j['locked_at'] !== null): ?>
          <form method="post" action="<?= e(admin_url('system/cron/release-lock')) ?>" class="d-inline"
                data-confirm="Clear the lock on <?= e($j['name']) ?>? Only do this if it is genuinely stuck.">
            <?= Csrf::field() ?><input type="hidden" name="job_key" value="<?= e($key) ?>">
            <button class="btn btn-sm btn-outline-warning" title="Clear stuck lock"><i class="bi bi-unlock"></i></button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div></div>
<?php endforeach; ?>

<!-- ---------------------------------------------------------------- schedule modals -->
<?php foreach ($jobs as $j): $key = (string)$j['job_key']; $id = 'sched-' . str_replace('.', '-', $key); ?>
<div class="modal fade" id="<?= e($id) ?>" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="<?= e(admin_url('system/cron/schedule')) ?>">
      <?= Csrf::field() ?><input type="hidden" name="job_key" value="<?= e($key) ?>">
      <div class="modal-header"><h6 class="modal-title"><?= e($j['name']) ?> — schedule</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><label class="form-label">How often</label>
          <select name="schedule_type" class="form-select form-select-sm">
            <option value="interval" <?= $j['schedule_type'] === 'interval' ? 'selected' : '' ?>>Every N minutes</option>
            <option value="daily" <?= $j['schedule_type'] === 'daily' ? 'selected' : '' ?>>Once a day</option>
          </select></div>
        <div class="row g-2">
          <div class="col-6"><label class="form-label small">Minutes</label>
            <input type="number" min="1" max="10080" name="interval_minutes" class="form-control form-control-sm"
                   value="<?= (int)$j['interval_minutes'] ?>"></div>
          <div class="col-6"><label class="form-label small">Time of day</label>
            <input type="time" name="run_at_time" class="form-control form-control-sm"
                   value="<?= e(substr((string)($j['run_at_time'] ?: '00:00:00'), 0, 5)) ?>"></div>
        </div>
        <div class="form-text mt-2">The master cron ticks every minute; a job only runs when it is due.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-sm btn-primary">Save</button>
      </div>
    </form>
  </div></div>
</div>
<?php endforeach; ?>

<!-- ------------------------------------------------------------------- logs -->
<div class="card mb-3"><div class="card-body">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
    <h6 class="mb-0">Execution log
      <?php if ($filter !== ''): ?><small class="text-muted">— <?= e($filter) ?></small><?php endif; ?>
    </h6>
    <div class="d-flex gap-2">
      <?php if ($filter !== ''): ?>
        <a class="btn btn-sm btn-outline-secondary" href="<?= e(admin_url('system/cron')) ?>">Show all</a>
      <?php endif; ?>
      <a class="btn btn-sm btn-outline-secondary" href="<?= e(admin_url('system/cron/history')) ?>">Full history</a>
    </div>
  </div>
  <?php if (!$runs): ?>
    <div class="empty-state"><i class="bi bi-clock-history"></i>Nothing has run yet.</div>
  <?php else: ?>
  <div class="table-responsive"><table class="table table-sm table-mobile kp-cron align-middle mb-0">
    <thead><tr><th>When</th><th>Job</th><th>Status</th><th>Took</th><th>Items</th><th>By</th><th>Message</th></tr></thead>
    <tbody>
    <?php foreach ($runs as $r): ?>
      <tr>
        <td data-label="When" class="small text-nowrap"><?= e(fmt_date($r['started_at'], true)) ?></td>
        <td data-label="Job" class="small"><code><?= e($r['job_key']) ?></code></td>
        <td data-label="Status"><span class="badge bg-<?= e($runColors[$r['status']] ?? 'secondary') ?>"><?= e($r['status']) ?></span></td>
        <td data-label="Took" class="small"><?= $r['duration_ms'] !== null ? (int)$r['duration_ms'] . ' ms' : '—' ?></td>
        <td data-label="Items" class="small"><?= (int)$r['items'] ?></td>
        <td data-label="By" class="small"><?= e($r['triggered_by']) ?></td>
        <td data-label="Message" class="small" style="max-width:420px"><?= e(mb_substr((string)$r['message'], 0, 200)) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div></div>

<!-- ------------------------------------------------------------- server setup -->
<div class="card"><div class="card-body">
  <h6>Server setup</h6>
  <p class="small text-muted mb-2">
    This system needs <strong>one</strong> cron entry, nothing more. Everything on this page is
    scheduled inside the application, so a new background task never needs a new crontab line.
  </p>
  <pre class="bg-body-secondary p-2 rounded small mb-3"><?= e($cronCommand) ?></pre>

  <h6 class="mt-3">No shell access?</h6>
  <p class="small text-muted mb-2">
    Some hosts only offer a “URL cron”. Generate a key and point the host at this address once a minute.
    Keep the address private — anyone holding it can trigger a tick.
  </p>
  <?php if ($secret !== ''): ?>
    <pre class="bg-body-secondary p-2 rounded small mb-2"><?= e(rtrim(base_url(), '/') . '/tick.php?key=' . $secret) ?></pre>
  <?php endif; ?>
  <div class="d-flex gap-2">
    <form method="post" action="<?= e(admin_url('system/cron/web-key')) ?>"
          <?= $secret !== '' ? 'data-confirm="Generate a new key? The current URL stops working."' : '' ?>>
      <?= Csrf::field() ?>
      <button class="btn btn-sm btn-outline-primary"><?= $secret !== '' ? 'Regenerate key' : 'Generate URL key' ?></button>
    </form>
    <?php if ($secret !== ''): ?>
    <form method="post" action="<?= e(admin_url('system/cron/web-key')) ?>" data-confirm="Switch the URL trigger off?">
      <?= Csrf::field() ?><input type="hidden" name="clear" value="1">
      <button class="btn btn-sm btn-outline-secondary">Switch off</button>
    </form>
    <?php endif; ?>
  </div>
</div></div>

<script>
// Keep the health strip honest without reloading the whole page.
(function () {
  var url = <?= json_encode(admin_url('system/cron/status')) ?>;
  var labels = <?= json_encode($stateLabels) ?>, colors = <?= json_encode($stateColors) ?>;
  setInterval(function () {
    fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        if (!d) return;
        var badge = document.getElementById('cronState');
        badge.className = 'badge fs-6 bg-' + (colors[d.state] || 'secondary');
        badge.textContent = labels[d.state] || d.state;
        document.getElementById('cronNote').textContent = d.note;
        if (d.last_tick_at) document.getElementById('cronLastTick').textContent = d.last_tick_at;
      })
      .catch(function () {});
  }, 30000);
})();
</script>
