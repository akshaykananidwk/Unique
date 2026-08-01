<?php
$finished = (bool)ins_data('finished');
$baseUrl = ins_data('base_url_final') ?: ins_detect_base_url();
$root = BASE_PATH;
?>
<?php if (!$finished): ?>
  <h5>Step 8 — Finish</h5>
  <p>Ready to write the configuration and lock the installer. This will:</p>
  <ul class="small">
    <li>Generate a random <code>APP_KEY</code> (used to encrypt the WhatsApp API key and GitHub token)</li>
    <li>Write <code>config/config.php</code> (auto-detected base URL: <code><?= ins_e($baseUrl) ?></code>)</li>
    <li>Create your branches and Super Admin account</li>
    <li>Create <code>storage/installed.lock</code> so this installer can never run again</li>
  </ul>
  <form method="post">
    <input type="hidden" name="_csrf" value="<?= ins_csrf() ?>">
    <input type="hidden" name="step" value="8">
    <div class="d-flex justify-content-between">
      <a class="btn btn-outline-secondary" href="?step=7">← Back</a>
      <button class="btn btn-success btn-lg">🚀 Finish Installation</button>
    </div>
  </form>
<?php else: ?>
  <div class="text-center mb-3">
    <div class="display-4">🎉</div>
    <h4>Installation complete!</h4>
    <p class="text-muted">The installer is now locked.</p>
  </div>
  <div class="alert alert-warning">
    <strong>Final step — paste these cron jobs into aaPanel</strong> (Cron → Add Task → Shell Script):
    <pre class="small mb-0 mt-2">* * * * *    /usr/bin/php <?= ins_e($root) ?>/cron/whatsapp_worker.php  &gt;/dev/null 2&gt;&amp;1
*/15 * * * * /usr/bin/php <?= ins_e($root) ?>/cron/reminders.php        &gt;/dev/null 2&gt;&amp;1
0 3 * * *    /usr/bin/php <?= ins_e($root) ?>/cron/update_check.php     &gt;/dev/null 2&gt;&amp;1
30 2 * * *   /usr/bin/php <?= ins_e($root) ?>/cron/auto_backup.php      &gt;/dev/null 2&gt;&amp;1</pre>
  </div>
  <div class="d-grid">
    <a class="btn btn-primary btn-lg" href="<?= ins_e($baseUrl) ?>/admin/">Go to Admin Panel →</a>
  </div>
  <?php session_destroy(); ?>
<?php endif; ?>
