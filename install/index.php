<?php
declare(strict_types=1);

/**
 * Krishna Print — one-click installer.
 * Upload files → open /install → follow 8 steps → done. No manual file editing.
 * Self-locks via storage/installed.lock after a successful run.
 */
define('BASE_PATH', dirname(__DIR__));
session_name('kp_install');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
mb_internal_encoding('UTF-8');

// The session that just finished installing may still view the final screen (cron commands)
if (is_file(BASE_PATH . '/storage/installed.lock') && empty($_SESSION['install']['finished'])) {
    http_response_code(403);
    echo '<!DOCTYPE html><meta charset="utf-8"><body style="font-family:system-ui;padding:3rem;text-align:center">'
        . '<h2>🔒 Already installed</h2><p>The installer is locked. To re-run it, delete '
        . '<code>storage/installed.lock</code> on the server first.</p>'
        . '<p><a href="../admin/">Go to Admin Panel</a></p></body>';
    exit;
}

// ---------------------------------------------------------------- helpers

function ins_e(mixed $v): string
{
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function ins_data(string $key, mixed $default = null): mixed
{
    return $_SESSION['install'][$key] ?? $default;
}

function ins_set(string $key, mixed $value): void
{
    $_SESSION['install'][$key] = $value;
}

function ins_csrf(): string
{
    if (empty($_SESSION['install_csrf'])) {
        $_SESSION['install_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['install_csrf'];
}

function ins_pdo(?array $db = null): PDO
{
    $db = $db ?? ins_data('db');
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db['host'], (int)$db['port'], $db['name']);
    return new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function ins_detect_base_url(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = rtrim(str_replace('/install', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return ($https ? 'https' : 'http') . '://' . $host . $path;
}

/** Split + run a .sql file on a raw PDO (mirrors App\Core\DB::runSqlScript). */
function ins_run_sql(PDO $pdo, string $path, string $prefix = ''): int
{
    $sql = (string)file_get_contents($path);
    $statements = [];
    $buffer = '';
    $inString = false;
    $stringChar = '';
    $len = strlen($sql);
    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];
        if ($inString) {
            $buffer .= $ch;
            if ($ch === $stringChar && $sql[$i - 1] !== '\\') {
                $inString = false;
            }
            continue;
        }
        if ($ch === "'" || $ch === '"') {
            $inString = true;
            $stringChar = $ch;
            $buffer .= $ch;
            continue;
        }
        if ($ch === '-' && ($sql[$i + 1] ?? '') === '-' && in_array($sql[$i + 2] ?? "\n", [' ', "\n"], true)) {
            while ($i < $len && $sql[$i] !== "\n") {
                $i++;
            }
            continue;
        }
        if ($ch === ';') {
            $statements[] = trim($buffer);
            $buffer = '';
            continue;
        }
        $buffer .= $ch;
    }
    if (trim($buffer) !== '') {
        $statements[] = trim($buffer);
    }
    $n = 0;
    foreach ($statements as $stmt) {
        if ($stmt === '') {
            continue;
        }
        if ($prefix !== '') {
            $stmt = preg_replace('/`([a-z_]+)`/', '`' . $prefix . '$1`', $stmt) ?? $stmt;
        }
        $pdo->exec($stmt);
        $n++;
    }
    return $n;
}

// ---------------------------------------------------------------- step routing

$step = max(1, min(8, (int)($_GET['step'] ?? 1)));
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!hash_equals($_SESSION['install_csrf'] ?? '', (string)($_POST['_csrf'] ?? ''))) {
        $errors[] = 'Security token mismatch — please retry.';
    } else {
        $result = require __DIR__ . '/steps/process.php';
        if ($result['ok']) {
            header('Location: ?step=' . $result['next']);
            exit;
        }
        $errors = $result['errors'];
        $step = $result['stay'] ?? $step;
    }
}

$stepTitles = [
    1 => 'System Check', 2 => 'Database', 3 => 'Import', 4 => 'Business Setup',
    5 => 'Branches', 6 => 'Super Admin', 7 => 'WhatsApp', 8 => 'Finish',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Install — Krishna Print (Step <?= $step ?>)</title>
<link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">
<style>
body{background:#f4f6f9}
.step-dot{width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;background:#dee2e6;color:#555}
.step-dot.active{background:#0d6efd;color:#fff}
.step-dot.done{background:#198754;color:#fff}
.check-ok{color:#198754}.check-fail{color:#dc3545}
</style>
</head>
<body>
<div class="container py-4" style="max-width:860px">
  <div class="text-center mb-4">
    <h3>🖨️ Krishna Print — Installer</h3>
    <div class="d-flex gap-2 justify-content-center flex-wrap mt-3">
      <?php foreach ($stepTitles as $n => $t): ?>
        <div class="text-center">
          <span class="step-dot <?= $n === $step ? 'active' : ($n < $step ? 'done' : '') ?>"><?= $n < $step ? '✓' : $n ?></span>
          <div class="small text-muted d-none d-md-block"><?= ins_e($t) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= $err /* installer messages, may include code tags */ ?></div>
  <?php endforeach; ?>
  <div class="card shadow-sm"><div class="card-body p-4">
    <?php require __DIR__ . '/steps/step' . $step . '.php'; ?>
  </div></div>
</div>
</body>
</html>
