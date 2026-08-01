<?php
declare(strict_types=1);

use App\Core\Settings;

/** HTML-escape for output. Use on every echo of user data. */
function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Prefixed table name. */
function tbl(string $name): string
{
    return App\Core\DB::prefix() . $name;
}

function config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $file = BASE_PATH . '/config/config.php';
        $config = is_file($file) ? require $file : [];
    }
    if ($key === null) {
        return $config;
    }
    $value = $config;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function base_url(string $path = ''): string
{
    $base = rtrim((string)(Settings::get('base_url') ?: config('base_url', '')), '/');
    return $base . '/' . ltrim($path, '/');
}

function admin_url(string $path = ''): string
{
    return base_url('admin' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
}

function asset_url(string $path): string
{
    $v = (string)Settings::get('asset_version', '1');
    return base_url('assets/' . ltrim($path, '/')) . '?v=' . rawurlencode($v);
}

function upload_url(string $relative): string
{
    return base_url('uploads/' . ltrim($relative, '/'));
}

function redirect(string $url, int $code = 302): never
{
    header('Location: ' . $url, true, $code);
    exit;
}

function request_ip(): string
{
    return (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '');
}

function request_agent(): string
{
    return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 250);
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function fmt_date(?string $dt, bool $withTime = false): string
{
    if (!$dt) {
        return '—';
    }
    $format = (string)Settings::get('date_format', 'd-m-Y');
    $ts = strtotime($dt);
    if ($ts === false) {
        return '—';
    }
    return date($format . ($withTime ? ' h:i A' : ''), $ts);
}

function fmt_money(mixed $amount): string
{
    $symbol = (string)Settings::get('currency_symbol', '₹');
    return $symbol . number_format((float)$amount, 2);
}

function random_token(int $bytes = 16): string
{
    return bin2hex(random_bytes($bytes));
}

function json_response(mixed $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function abort(int $code, string $message = ''): never
{
    http_response_code($code);
    $view = BASE_PATH . '/app/Views/errors/' . $code . '.php';
    if (is_file($view)) {
        $error_message = $message;
        include $view;
    } else {
        echo e($message !== '' ? $message : 'Error ' . $code);
    }
    exit;
}

/** Flash messages (toast on next page load). */
function flash(?string $type = null, ?string $message = null): mixed
{
    if ($type !== null && $message !== null) {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
        return null;
    }
    $all = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $all;
}

/** Old input helper for form redisplay. */
function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function keep_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

/** Normalise a phone number to international digits (default country code from Settings). */
function normalize_phone(?string $raw): ?string
{
    $digits = preg_replace('/[^0-9]/', '', (string)$raw);
    if ($digits === '' || $digits === null) {
        return null;
    }
    $digits = ltrim($digits, '0');
    $cc = (string)Settings::get('wa_country_code', '91');
    if (strlen($digits) === 10) {
        $digits = $cc . $digits;
    }
    if (strlen($digits) < 11 || strlen($digits) > 15) {
        return null;
    }
    return $digits;
}

/** 10-digit local phone for storage/display. */
function local_phone(?string $raw): ?string
{
    $digits = preg_replace('/[^0-9]/', '', (string)$raw);
    if (!$digits) {
        return null;
    }
    $cc = (string)Settings::get('wa_country_code', '91');
    if (str_starts_with($digits, $cc) && strlen($digits) === strlen($cc) + 10) {
        $digits = substr($digits, strlen($cc));
    }
    $digits = ltrim($digits, '0');
    return strlen($digits) === 10 ? $digits : null;
}

/** Bootstrap-icon name for a category — its own icon, else a keyword guess, else a default. */
function category_icon(array $category): string
{
    if (!empty($category['icon'])) {
        return preg_replace('/[^a-z0-9\-]/', '', strtolower((string)$category['icon'])) ?: 'box-seam';
    }
    $name = strtolower(trim((string)($category['name'] ?? '') . ' ' . (string)($category['slug'] ?? '')));
    $map = [
        'visiting' => 'person-vcard', 'card' => 'person-vcard', 'business card' => 'person-vcard',
        'flex' => 'flag', 'banner' => 'flag', 'hoarding' => 'badge-ad',
        'bill' => 'journal-text', 'book' => 'journal-text', 'letterhead' => 'file-earmark-text', 'stationery' => 'file-earmark-text',
        'sticker' => 'sticky', 'label' => 'tag', 'board' => 'easel', 'signage' => 'signpost', 'sunboard' => 'easel',
        'stamp' => 'stamp', 'brochure' => 'file-richtext', 'pamphlet' => 'file-richtext', 'flyer' => 'file-richtext',
        'invitation' => 'envelope-paper', 'wedding' => 'envelope-heart', 'poster' => 'image',
        'mug' => 'cup-hot', 'tshirt' => 'person', 't-shirt' => 'person', 'id' => 'person-badge',
        'calendar' => 'calendar3', 'certificate' => 'award', 'menu' => 'card-list',
    ];
    foreach ($map as $kw => $icon) {
        if (str_contains($name, $kw)) {
            return $icon;
        }
    }
    return 'box-seam';
}

function is_installed(): bool
{
    return is_file(BASE_PATH . '/storage/installed.lock') && is_file(BASE_PATH . '/config/config.php');
}

function maintenance_active(): bool
{
    return is_file(BASE_PATH . '/storage/maintenance.flag');
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('kp_session');
    session_start();
}
