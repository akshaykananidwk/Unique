<?php
declare(strict_types=1);

// Internal JSON endpoints for the panel — session-authenticated.
// The admin panel's own AJAX endpoints live under /admin/api/*; this file
// exists as a stable versioned alias: /api/v1/?action=...
define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/app/bootstrap.php';

use App\Core\Auth;

if (!is_installed()) {
    json_response(['ok' => false, 'error' => 'Not installed'], 503);
}
start_session();
if (!Auth::check()) {
    json_response(['ok' => false, 'error' => 'Authentication required'], 401);
}

$action = preg_replace('/[^a-z0-9_\-]/', '', (string)($_GET['action'] ?? ''));
$map = [
    'customer-lookup' => ['App\\Controllers\\Admin\\ApiController', 'customerLookup'],
    'designers' => ['App\\Controllers\\Admin\\ApiController', 'designers'],
    'search' => ['App\\Controllers\\Admin\\ApiController', 'search'],
];
if (!isset($map[$action])) {
    json_response(['ok' => false, 'error' => 'Unknown action'], 404);
}
[$class, $method] = $map[$action];
(new $class())->$method();
