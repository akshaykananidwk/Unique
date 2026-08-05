<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Repairs to protective files that the auto-updater is not allowed to touch.
 *
 * uploads/, storage/ and backups/ are on the updater's protected list — customer files live
 * there and an update must never overwrite them. The side effect is that a broken .htaccess
 * inside one of those folders can never be fixed by shipping a new one; it has to be repaired
 * in place. That is what this class is for.
 */
class Hardening
{
    /**
     * The uploads guard, written so it is safe under mod_php AND under PHP-FPM/FastCGI.
     * Keep in step with uploads/.htaccess in the repository.
     */
    public const UPLOADS_HTACCESS = <<<'HT'
# Uploaded files only: never execute anything here, never list the directory.
#
# NOTE: php_flag / php_value come from mod_php. On PHP-FPM or FastCGI hosting mod_php is
# not loaded, and an UNGUARDED php_flag makes Apache return 500 for every file in this
# folder — images included. Always keep it inside an <IfModule> guard.
<IfModule mod_php.c>
    php_flag engine off
</IfModule>
<IfModule mod_php7.c>
    php_flag engine off
</IfModule>
<IfModule mod_php5.c>
    php_flag engine off
</IfModule>

Options -Indexes

# Works however PHP is wired up: strip the handlers, then deny the files outright.
<IfModule mod_mime.c>
    RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .php8 .phar
    RemoveType .php .phtml .php3 .php4 .php5 .php7 .php8 .phar
</IfModule>

<FilesMatch "\.(php|phtml|php\d|phar|pl|py|cgi|sh)$">
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order allow,deny
        Deny from all
    </IfModule>
</FilesMatch>

HT;

    /**
     * Run every repair. Safe to call as often as you like — it only writes when something
     * is actually wrong.
     *
     * @return string[] a line per repair made, empty when nothing needed doing
     */
    public static function run(): array
    {
        $fixed = [];
        $result = self::checkUploadsHtaccess(true);
        if ($result['changed']) {
            $fixed[] = 'uploads/.htaccess rewritten (an unguarded php_flag was making Apache '
                . 'return 500 for every uploaded file)';
        } elseif ($result['state'] === 'broken') {
            // Never fail silently here — a silent no-op is exactly why this went unnoticed.
            Logger::file('hardening', 'COULD NOT REPAIR uploads/.htaccess: ' . $result['detail']);
        }
        return $fixed;
    }

    /**
     * Inspect everything without changing a thing, for the admin panel.
     *
     * @return array<int,array{key:string,label:string,state:string,detail:string}>
     */
    public static function report(): array
    {
        $uploads = self::checkUploadsHtaccess(false);
        return [[
            'key' => 'uploads_htaccess',
            'label' => 'Uploaded files are being served',
            'state' => $uploads['state'],
            'detail' => $uploads['detail'],
        ]];
    }

    /** Old name, kept so nothing that calls it breaks. */
    public static function fixUploadsHtaccess(): bool
    {
        return self::checkUploadsHtaccess(true)['changed'];
    }

    /**
     * Inspect — and optionally repair — uploads/.htaccess.
     *
     * An unguarded `php_flag` there is fatal on FPM hosting: Apache rejects the directive and
     * serves 500 for every image, proof and attachment in the folder.
     *
     * Repair order matters. Overwriting an existing file needs write permission on the FILE,
     * which PHP may not have if the file was laid down by root or by git. Deleting and
     * recreating only needs write permission on the DIRECTORY — which PHP certainly has,
     * because that is where it saves every upload. So: write, else chmod+write, else
     * unlink+create. Only when all three fail is it genuinely stuck.
     *
     * @return array{state:string,changed:bool,detail:string,writable:bool}
     *         state: ok | broken | missing | unwritable
     */
    public static function checkUploadsHtaccess(bool $repair = false): array
    {
        $dir = BASE_PATH . '/uploads';
        $path = $dir . '/.htaccess';

        if (!is_dir($dir)) {
            return ['state' => 'ok', 'changed' => false, 'detail' => 'No uploads folder.', 'writable' => false];
        }

        if (!is_file($path)) {
            if (!$repair) {
                return ['state' => 'missing', 'changed' => false,
                    'detail' => 'uploads/.htaccess is missing — uploaded files are not protected.',
                    'writable' => is_writable($dir)];
            }
            $ok = (bool)@file_put_contents($path, self::UPLOADS_HTACCESS);
            return ['state' => $ok ? 'ok' : 'unwritable', 'changed' => $ok,
                'detail' => $ok ? 'Created uploads/.htaccess.' : self::whyNotWritable($dir, $path),
                'writable' => is_writable($dir)];
        }

        $current = (string)@file_get_contents($path);
        if ($current !== '' && !self::hasUnguardedPhpFlag($current)) {
            return ['state' => 'ok', 'changed' => false,
                'detail' => 'uploads/.htaccess looks correct.', 'writable' => is_writable($path)];
        }

        $detail = 'uploads/.htaccess has a php_flag outside any <IfModule> guard. On PHP-FPM '
            . 'hosting Apache rejects that line and answers 500 for every file in uploads/.';
        if (!$repair) {
            return ['state' => 'broken', 'changed' => false, 'detail' => $detail,
                'writable' => is_writable($path)];
        }

        // 1. straight overwrite
        if (@file_put_contents($path, self::UPLOADS_HTACCESS) !== false) {
            return ['state' => 'ok', 'changed' => true, 'detail' => 'Rewritten.', 'writable' => true];
        }
        // 2. make it writable first
        if (@chmod($path, 0644) && @file_put_contents($path, self::UPLOADS_HTACCESS) !== false) {
            return ['state' => 'ok', 'changed' => true, 'detail' => 'Rewritten after chmod.', 'writable' => true];
        }
        // 3. delete and recreate — needs the directory, not the file
        if (@unlink($path) && @file_put_contents($path, self::UPLOADS_HTACCESS) !== false) {
            return ['state' => 'ok', 'changed' => true, 'detail' => 'Replaced.', 'writable' => true];
        }
        return ['state' => 'broken', 'changed' => false,
            'detail' => $detail . ' It could not be repaired automatically: ' . self::whyNotWritable($dir, $path),
            'writable' => false];
    }

    /** A plain-English reason a write failed, so the admin knows what to fix. */
    private static function whyNotWritable(string $dir, string $path): string
    {
        $who = function_exists('posix_getpwuid') && function_exists('posix_geteuid')
            ? (string)((posix_getpwuid(posix_geteuid())['name'] ?? '') ?: 'the web user')
            : 'the web user';
        if (is_file($path) && !is_writable($path)) {
            return "the file is not writable by $who (owner "
                . self::ownerName($path) . ', mode ' . self::modeOf($path) . ')';
        }
        if (!is_writable($dir)) {
            return "the uploads folder is not writable by $who (owner "
                . self::ownerName($dir) . ', mode ' . self::modeOf($dir) . ')';
        }
        return 'the write was refused by the filesystem';
    }

    private static function ownerName(string $path): string
    {
        $uid = @fileowner($path);
        if ($uid === false) {
            return 'unknown';
        }
        if (function_exists('posix_getpwuid')) {
            $info = @posix_getpwuid($uid);
            if (!empty($info['name'])) {
                return (string)$info['name'];
            }
        }
        return 'uid ' . $uid;
    }

    private static function modeOf(string $path): string
    {
        $perms = @fileperms($path);
        return $perms === false ? '?' : substr(sprintf('%o', $perms), -4);
    }

    /** True if a php_flag/php_value sits outside every <IfModule> block. */
    public static function hasUnguardedPhpFlag(string $config): bool
    {
        $depth = 0;
        foreach (preg_split('/\R/', $config) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (preg_match('/^<IfModule\b/i', $line)) {
                $depth++;
                continue;
            }
            if (preg_match('#^</IfModule>#i', $line)) {
                $depth = max(0, $depth - 1);
                continue;
            }
            if ($depth === 0 && preg_match('/^php_(flag|value|admin_flag|admin_value)\b/i', $line)) {
                return true;
            }
        }
        return false;
    }
}
