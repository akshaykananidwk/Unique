<?php
declare(strict_types=1);

// Runs every minute: sends pending WhatsApp queue rows, respecting the rate limit.
if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

use App\Core\Logger;
use App\Core\Whatsapp;

if (!is_installed()) {
    exit(0);
}

// Lock file prevents overlapping runs
$lock = fopen(BASE_PATH . '/storage/whatsapp_worker.lock', 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    exit(0);
}

try {
    $sent = Whatsapp::processQueue();
    if ($sent > 0) {
        Logger::file('whatsapp', "Worker sent $sent message(s).");
    }
} catch (Throwable $e) {
    Logger::file('whatsapp', 'Worker error: ' . $e->getMessage());
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
