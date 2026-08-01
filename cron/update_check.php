<?php
declare(strict_types=1);

// Daily: check GitHub for a newer version. NEVER auto-installs.
if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

use App\Core\Logger;
use App\Core\Settings;
use App\Core\Updater;

if (!is_installed() || !Settings::getBool('upd_auto_check', true)) {
    exit(0);
}
if ((string)Settings::get('upd_repo_owner', '') === '') {
    exit(0);
}

try {
    $result = (new Updater())->checkForUpdate();
    Logger::file('updater', 'Auto-check: ' . ($result['ok']
        ? (($result['available'] ?? false) ? 'update available (' . ($result['latest_version'] ?? '?') . ')' : 'up to date')
        : 'failed — ' . ($result['message'] ?? '')));
} catch (Throwable $e) {
    Logger::file('updater', 'Auto-check error: ' . $e->getMessage());
}
