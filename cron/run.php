<?php
declare(strict_types=1);

/**
 * THE ONLY SERVER CRON THIS APPLICATION NEEDS.
 *
 *   * * * * * /usr/bin/php /path/to/cron/run.php >> /dev/null 2>&1
 *
 * Everything else — WhatsApp queue, reminders, payment chasers, daily summary, backups,
 * update checks, housekeeping and anything added later — is scheduled inside the app and
 * managed from Admin → Cron Jobs.
 */
// .htaccess denies the whole cron/ directory, so this is command line only.
// Hosts with no shell cron use /tick.php?key=… instead — same tick, same lock.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only — use tick.php for a URL-triggered run.');
}
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

use App\Core\Logger;
use App\Core\Scheduler;

if (!is_installed()) {
    exit(0);
}

// One tick at a time. If the previous minute is somehow still going, leave it alone —
// each job has its own lock too, so this is belt and braces.
$lockFile = BASE_PATH . '/storage/cron.lock';
$lock = fopen($lockFile, 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    exit(0);
}

try {
    $result = Scheduler::tick('schedule');
    if ($result['ran'] > 0 || $result['failed'] > 0) {
        Logger::file('cron', sprintf(
            'tick: %d ran, %d failed, %d skipped — %s',
            $result['ran'], $result['failed'], $result['skipped'], implode(', ', $result['jobs'])
        ));
    }
} catch (Throwable $e) {
    Logger::file('cron', 'tick error: ' . $e->getMessage());
    fwrite(STDERR, 'Cron tick failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);   // the lock goes with the process
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
