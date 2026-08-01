<?php
declare(strict_types=1);

namespace App\Core;

/**
 * AES-256-CBC encryption with HMAC-SHA256 authentication.
 * Key comes from config APP_KEY ("base64:..." generated at install).
 */
class Crypt
{
    private static string $key = '';

    public static function init(string $appKey): void
    {
        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);
            self::$key = $decoded !== false ? $decoded : '';
        } else {
            self::$key = $appKey;
        }
        if (strlen(self::$key) < 32) {
            self::$key = hash('sha256', self::$key, true);
        }
    }

    public static function encrypt(string $plain): string
    {
        if ($plain === '') {
            return '';
        }
        $iv = random_bytes(16);
        $cipher = openssl_encrypt($plain, 'aes-256-cbc', self::$key, OPENSSL_RAW_DATA, $iv);
        if ($cipher === false) {
            throw new \RuntimeException('Encryption failed');
        }
        $mac = hash_hmac('sha256', $iv . $cipher, self::$key, true);
        return 'enc:' . base64_encode($iv . $mac . $cipher);
    }

    public static function decrypt(string $payload): string
    {
        if ($payload === '' ) {
            return '';
        }
        if (!str_starts_with($payload, 'enc:')) {
            return $payload; // legacy/plain value
        }
        $raw = base64_decode(substr($payload, 4), true);
        if ($raw === false || strlen($raw) < 49) {
            return '';
        }
        $iv = substr($raw, 0, 16);
        $mac = substr($raw, 16, 32);
        $cipher = substr($raw, 48);
        $expected = hash_hmac('sha256', $iv . $cipher, self::$key, true);
        if (!hash_equals($expected, $mac)) {
            return '';
        }
        $plain = openssl_decrypt($cipher, 'aes-256-cbc', self::$key, OPENSSL_RAW_DATA, $iv);
        return $plain === false ? '' : $plain;
    }

    public static function generateAppKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }

    /** Deterministic signature for public links (proof/track tokens are random already). */
    public static function sign(string $data): string
    {
        return hash_hmac('sha256', $data, self::$key);
    }
}
