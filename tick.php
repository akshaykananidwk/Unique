<?php
declare(strict_types=1);

/**
 * Web entry point for the master scheduler tick.
 *
 * Only needed on hosting with no shell cron, where the control panel can call a URL on a
 * schedule instead. Generate the key in Admin → Cron Jobs, then point the host at:
 *
 *   https://your-site/tick.php?key=THE_KEY      (once a minute)
 *
 * It lives at the document root on purpose: .htaccess blocks the whole cron/ directory, so
 * cron/run.php is reachable from the command line only.
 *
 * With no key configured this file does nothing at all, so it is safe to leave in place.
 */
define('BASE_PATH', __DIR__);
require BASE_PATH . '/app/bootstrap.php';

use App\Core\Logger;
use App\Core\Scheduler;
use App\Core\Settings;

if (!is_installed()) {
    http_response_code(503);
    exit('Not installed');
}

$secret = (string)Settings::get('cron_web_secret', '');
$given = (string)($_GET['key'] ?? '');
if ($secret === '' || !hash_equals($secret, $given)) {
    http_response_code(403);
    exit('Forbidden');
}

// One tick at a time — a slow tick must never be overlapped by the next minute's call.
$lock = fopen(BASE_PATH . '/storage/cron.lock', 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    header('Content-Type: application/json');
    exit(json_encode(['ok' => true, 'skipped' => 'a tick is already running']));
}

header('Content-Type: application/json');
try {
    $result = Scheduler::tick('schedule');
    if ($result['ran'] > 0 || $result['failed'] > 0) {
        Logger::file('cron', sprintf(
            'web tick: %d ran, %d failed, %d skipped — %s',
            $result['ran'], $result['failed'], $result['skipped'], implode(', ', $result['jobs'])
        ));
    }
    echo json_encode(['ok' => true] + $result);
} catch (Throwable $e) {
    Logger::file('cron', 'web tick error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
