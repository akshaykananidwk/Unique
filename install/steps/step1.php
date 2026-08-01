<?php
$phpOk = version_compare(PHP_VERSION, '8.1.0', '>=');
$extensions = ['pdo_mysql', 'curl', 'mbstring', 'zip', 'openssl', 'fileinfo', 'json'];
$gdOk = extension_loaded('gd') || extension_loaded('imagick');
$writable = ['config', 'uploads', 'storage', 'backups', '.'];
$allOk = $phpOk && $gdOk;
$rows = [];
$rows[] = ['PHP ≥ 8.1 (found ' . PHP_VERSION . ')', $phpOk, 'Change the site PHP version in aaPanel → Website → PHP Version'];
foreach ($extensions as $ext) {
    $ok = extension_loaded($ext);
    $allOk = $allOk && $ok;
    $rows[] = ["PHP extension: $ext", $ok, "aaPanel → App Store → PHP → Settings → Install extension → $ext"];
}
$rows[] = ['PHP extension: gd (or imagick)', $gdOk, 'aaPanel → App Store → PHP → Settings → Install extension → gd'];
foreach ($writable as $dir) {
    $path = BASE_PATH . '/' . ($dir === '.' ? '' : $dir);
    $ok = is_writable($path ?: BASE_PATH);
    $allOk = $allOk && $ok;
    $rows[] = ['Writable: ' . ($dir === '.' ? 'web root (for .htaccess)' : $dir . '/'), $ok, "chown -R www:www " . BASE_PATH . " && chmod -R 755 " . BASE_PATH];
}
$curlOk = function_exists('curl_init');
$fopenOk = ini_get('allow_url_fopen');
$rows[] = ['Outbound HTTP (cURL' . ($fopenOk ? ' + allow_url_fopen' : '') . ')', $curlOk, 'Enable the curl extension'];
$allOk = $allOk && $curlOk;
$rows[] = ['max_execution_time ≥ 60 (found ' . (ini_get('max_execution_time') ?: '?') . ')', (int)ini_get('max_execution_time') === 0 || (int)ini_get('max_execution_time') >= 60,
    'aaPanel → PHP Settings → max_execution_time = 300'];
$rows[] = ['upload_max_filesize ≥ 16M (found ' . ini_get('upload_max_filesize') . ')', (int)ini_get('upload_max_filesize') >= 16,
    'aaPanel → PHP Settings → upload_max_filesize = 32M'];
$rows[] = ['post_max_size ≥ 16M (found ' . ini_get('post_max_size') . ')', (int)ini_get('post_max_size') >= 16,
    'aaPanel → PHP Settings → post_max_size = 32M'];
?>
<h5>Step 1 — System Check</h5>
<table class="table table-sm align-middle">
  <?php foreach ($rows as [$label, $ok, $fix]): ?>
  <tr>
    <td><?= ins_e($label) ?></td>
    <td class="text-center" style="width:40px"><?= $ok ? '<span class="check-ok fs-5">✔</span>' : '<span class="check-fail fs-5">✘</span>' ?></td>
    <td class="small text-muted"><?= $ok ? '' : 'Fix: <code>' . ins_e($fix) . '</code>' ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<form method="post">
  <input type="hidden" name="_csrf" value="<?= ins_csrf() ?>">
  <input type="hidden" name="step" value="1">
  <div class="d-flex justify-content-between">
    <a class="btn btn-outline-secondary" href="?step=1">↻ Re-check</a>
    <button class="btn btn-primary" <?= $allOk ? '' : 'disabled title="Fix the red items first"' ?>>Continue →</button>
  </div>
  <?php if (!$allOk): ?><p class="small text-danger mt-2 mb-0">Fix the items marked ✘, then press Re-check.</p><?php endif; ?>
</form>
