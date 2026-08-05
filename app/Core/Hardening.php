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
        if (self::fixUploadsHtaccess()) {
            $fixed[] = 'uploads/.htaccess rewritten (an unguarded php_flag was making Apache '
                . 'return 500 for every uploaded file)';
        }
        return $fixed;
    }

    /**
     * An unguarded `php_flag` in uploads/.htaccess is fatal on FPM hosting: Apache rejects
     * the directive and serves 500 for every image, proof and attachment in the folder.
     * Rewrite the file when it is missing, or when it carries a php_flag that is not inside
     * an <IfModule> guard.
     */
    public static function fixUploadsHtaccess(): bool
    {
        $path = BASE_PATH . '/uploads/.htaccess';
        if (!is_dir(BASE_PATH . '/uploads')) {
            return false;
        }
        if (!is_file($path)) {
            return (bool)@file_put_contents($path, self::UPLOADS_HTACCESS);
        }
        $current = (string)@file_get_contents($path);
        if ($current !== '' && !self::hasUnguardedPhpFlag($current)) {
            return false;   // already fine, leave any local customisation alone
        }
        return (bool)@file_put_contents($path, self::UPLOADS_HTACCESS);
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
