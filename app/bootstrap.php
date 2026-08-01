<?php
declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php_errors.log');

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $path = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($path)) {
            require $path;
        }
    }
});

require_once BASE_PATH . '/app/helpers.php';

use App\Core\Crypt;
use App\Core\DB;
use App\Core\Settings;

if (!is_installed()) {
    // Not installed yet — send everything to the installer.
    if (!str_contains((string)($_SERVER['REQUEST_URI'] ?? ''), '/install') && PHP_SAPI !== 'cli') {
        $script = rtrim(str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'))), '/');
        $base = preg_replace('#/(admin|api(/v1)?)$#', '', $script) ?? '';
        header('Location: ' . $base . '/install/');
        exit;
    }
    return;
}

$config = config();
DB::init($config['db']);
Crypt::init((string)$config['app_key']);
Settings::load();
date_default_timezone_set((string)Settings::get('timezone', 'Asia/Kolkata'));
DB::syncTimezone();
