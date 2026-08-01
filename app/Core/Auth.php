<?php
declare(strict_types=1);

namespace App\Core;

class Auth
{
    private static ?array $user = null;

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int)$u['id'] : null;
    }

    public static function user(): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }
        $id = $_SESSION['user_id'] ?? null;
        if (!$id) {
            return null;
        }
        // Idle timeout
        $timeout = Settings::getInt('idle_timeout_minutes', 120);
        $last = (int)($_SESSION['last_activity'] ?? 0);
        if ($timeout > 0 && $last > 0 && (time() - $last) > $timeout * 60) {
            self::logout();
            return null;
        }
        $_SESSION['last_activity'] = time();

        $user = DB::get(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM `' . tbl('users') . '` u
             JOIN `' . tbl('roles') . '` r ON r.id = u.role_id
             WHERE u.id = ? AND u.is_active = 1 AND u.deleted_at IS NULL',
            [(int)$id]
        );
        if (!$user) {
            self::logout();
            return null;
        }
        self::$user = $user;
        return $user;
    }

    /** @return array{ok:bool,error?:string,user?:array} */
    public static function attempt(string $identifier, string $password): array
    {
        $identifier = trim($identifier);
        $ip = request_ip();

        if (self::isLockedOut($identifier, $ip)) {
            return ['ok' => false, 'error' => 'Too many failed attempts. Please wait 15 minutes and try again.'];
        }

        $phone = local_phone($identifier);
        $user = DB::get(
            'SELECT * FROM `' . tbl('users') . '` WHERE (phone = ? OR email = ?) AND deleted_at IS NULL',
            [$phone ?: $identifier, $identifier]
        );

        $ok = $user && (int)$user['is_active'] === 1 && password_verify($password, (string)$user['password_hash']);

        DB::insert('login_attempts', [
            'identifier' => substr($identifier, 0, 150),
            'ip' => $ip,
            'success' => $ok ? 1 : 0,
            'created_at' => now(),
        ]);

        if (!$ok) {
            return ['ok' => false, 'error' => 'Invalid phone/email or password.'];
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['last_activity'] = time();
        unset($_SESSION['acl_permissions'], $_SESSION['acl_branches']);

        if (password_needs_rehash((string)$user['password_hash'], PASSWORD_DEFAULT)) {
            DB::update('users', ['password_hash' => password_hash($password, PASSWORD_DEFAULT)], ['id' => $user['id']]);
        }
        DB::update('users', ['last_login_at' => now(), 'last_login_ip' => $ip], ['id' => $user['id']]);
        Logger::activity('auth', 'login', 'user', (int)$user['id'], 'Logged in');
        self::$user = null;
        return ['ok' => true, 'user' => $user];
    }

    private static function isLockedOut(string $identifier, string $ip): bool
    {
        $since = date('Y-m-d H:i:s', time() - 15 * 60);
        $fails = (int)DB::val(
            'SELECT COUNT(*) FROM `' . tbl('login_attempts') . '`
             WHERE (identifier = ? OR ip = ?) AND success = 0 AND created_at > ?',
            [substr($identifier, 0, 150), $ip, $since]
        );
        return $fails >= 5;
    }

    public static function logout(): void
    {
        self::$user = null;
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            session_destroy();
        }
    }

    /** Redirect to login when not authenticated (panel middleware). */
    public static function requireLogin(): array
    {
        $user = self::user();
        if (!$user) {
            $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? '';
            redirect(admin_url('login'));
        }
        return $user;
    }
}
