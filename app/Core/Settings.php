<?php
declare(strict_types=1);

namespace App\Core;

/** Cached key/value settings backed by the settings table. */
class Settings
{
    private static array $cache = [];
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }
        $rows = DB::all('SELECT setting_key, setting_value, is_encrypted FROM `' . tbl('settings') . '`');
        foreach ($rows as $row) {
            $value = (string)($row['setting_value'] ?? '');
            if ((int)$row['is_encrypted'] === 1 && $value !== '') {
                $value = Crypt::decrypt($value);
            }
            self::$cache[$row['setting_key']] = $value;
        }
        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded) {
            return $default;
        }
        $value = self::$cache[$key] ?? null;
        return ($value === null || $value === '') ? $default : $value;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $v = self::get($key);
        if ($v === null) {
            return $default;
        }
        return in_array((string)$v, ['1', 'true', 'on', 'yes'], true);
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $v = self::get($key);
        return $v === null ? $default : (int)$v;
    }

    public static function set(string $key, mixed $value, string $group = 'general', bool $encrypted = false): void
    {
        $stored = (string)$value;
        if ($encrypted && $stored !== '') {
            $stored = Crypt::encrypt($stored);
        }
        $existing = DB::get('SELECT id FROM `' . tbl('settings') . '` WHERE setting_key = ?', [$key]);
        if ($existing) {
            DB::update('settings', [
                'setting_value' => $stored,
                'is_encrypted' => $encrypted ? 1 : 0,
                'updated_at' => now(),
            ], ['id' => $existing['id']]);
        } else {
            DB::insert('settings', [
                'group_key' => $group,
                'setting_key' => $key,
                'setting_value' => $stored,
                'is_encrypted' => $encrypted ? 1 : 0,
                'updated_at' => now(),
            ]);
        }
        self::$cache[$key] = (string)$value;
    }

    /** All settings of a group as key => decrypted value. */
    public static function group(string $group): array
    {
        $rows = DB::all('SELECT setting_key, setting_value, is_encrypted FROM `' . tbl('settings') . '` WHERE group_key = ?', [$group]);
        $out = [];
        foreach ($rows as $row) {
            $value = (string)($row['setting_value'] ?? '');
            if ((int)$row['is_encrypted'] === 1 && $value !== '') {
                $value = Crypt::decrypt($value);
            }
            $out[$row['setting_key']] = $value;
        }
        return $out;
    }

    public static function allKeys(): array
    {
        return DB::all('SELECT group_key, setting_key, is_encrypted, updated_at FROM `' . tbl('settings') . '` ORDER BY group_key, setting_key');
    }
}
