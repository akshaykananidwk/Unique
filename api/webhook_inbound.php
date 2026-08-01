<?php
declare(strict_types=1);

// Inbound WhatsApp webhook — receives messages forwarded by the provider.
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

use App\Core\DB;
use App\Core\Logger;
use App\Core\Settings;

if (!is_installed()) {
    http_response_code(503);
    exit('Not installed');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit('POST only');
}

$raw = (string)file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    $payload = $_POST;
}

// Shared-secret check (query param or header). Empty secret = webhook disabled.
$secret = (string)Settings::get('wa_webhook_secret', '');
$given = (string)($_GET['secret'] ?? $_SERVER['HTTP_X_WEBHOOK_SECRET'] ?? ($payload['secret'] ?? ''));
if ($secret === '' || !hash_equals($secret, $given)) {
    Logger::file('whatsapp', 'Inbound webhook rejected — bad secret from ' . request_ip());
    http_response_code(403);
    exit('Forbidden');
}

$from = normalize_phone((string)($payload['number'] ?? $payload['from'] ?? $payload['sender'] ?? ''));
$message = (string)($payload['message'] ?? $payload['text'] ?? $payload['body'] ?? '');
if (!$from) {
    http_response_code(422);
    exit('Missing sender number');
}

$customer = DB::get(
    'SELECT * FROM `' . tbl('customers') . '` WHERE phone = ? AND deleted_at IS NULL',
    [local_phone($from) ?? $from]
);
$order = null;
if ($customer) {
    $order = DB::get(
        'SELECT * FROM `' . tbl('orders') . "` WHERE customer_id = ? AND deleted_at IS NULL
         AND status NOT IN ('completed','cancelled') ORDER BY created_at DESC LIMIT 1",
        [(int)$customer['id']]
    );
}

$inboundId = DB::insert('whatsapp_inbound', [
    'from_number' => $from,
    'message' => $message,
    'raw_json' => substr($raw !== '' ? $raw : json_encode($payload), 0, 60000),
    'matched_customer_id' => $customer['id'] ?? null,
    'matched_order_id' => $order['id'] ?? null,
    'created_at' => now(),
]);

// Notify branch staff (managers of the order's branch, else all super admins)
$title = 'WhatsApp from ' . ($customer['name'] ?? $from);
$body = mb_substr($message, 0, 200);
$url = admin_url('whatsapp/inbound');
if ($order) {
    $staff = DB::all(
        'SELECT u.id FROM `' . tbl('users') . '` u JOIN `' . tbl('roles') . '` r ON r.id = u.role_id
         WHERE r.slug IN (?,?) AND u.is_active = 1 AND u.deleted_at IS NULL
         AND (r.slug = ? OR u.primary_branch_id = ?)',
        ['super_admin', 'branch_manager', 'super_admin', (int)$order['branch_id']]
    );
} else {
    $staff = DB::all(
        'SELECT u.id FROM `' . tbl('users') . '` u JOIN `' . tbl('roles') . '` r ON r.id = u.role_id
         WHERE r.slug = ? AND u.is_active = 1 AND u.deleted_at IS NULL',
        ['super_admin']
    );
}
foreach ($staff as $member) {
    Logger::notify((int)$member['id'], $title, $body, $url, 'whatsapp');
}

json_response(['ok' => true, 'id' => $inboundId]);
