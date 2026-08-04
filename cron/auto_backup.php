<?php
declare(strict_types=1);

/**
 * Kept only so an existing crontab entry does not break. All scheduling now lives in the
 * app — see cron/run.php and Admin → Cron Jobs. You can safely remove this line from the
 * server crontab and leave just:  * * * * * php cron/run.php
 */
if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

if (!is_installed()) {
    exit(0);
}
foreach (['system.backup'] as $jobKey) {
    App\Core\Scheduler::runJob($jobKey, 'schedule', null);
}
