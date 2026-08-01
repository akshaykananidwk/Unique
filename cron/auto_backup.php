<?php
declare(strict_types=1);

// Nightly backup + prune.
if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

use App\Core\Backup;
use App\Core\Logger;
use App\Core\Settings;

if (!is_installed()) {
    exit(0);
}

$lock = fopen(BASE_PATH . '/storage/backup.lock', 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    exit(0);
}

try {
    $result = Backup::create(Settings::getBool('upd_include_uploads', false), 'auto');
    Logger::file('backup', $result['ok'] ? 'Auto-backup created: ' . $result['path'] : 'Auto-backup FAILED: ' . ($result['error'] ?? ''));
    Backup::prune(Settings::getInt('upd_keep_backups', 5));
} catch (Throwable $e) {
    Logger::file('backup', 'Auto-backup error: ' . $e->getMessage());
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
