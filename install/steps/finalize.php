<?php
declare(strict_types=1);

/** Step 8 POST: write config, seed collected data, lock the installer. */

if (!ins_data('imported') || !ins_data('admin') || !ins_data('branches')) {
    return ['ok' => false, 'errors' => ['Earlier steps are incomplete.'], 'stay' => 2];
}

try {
    $db = ins_data('db');
    $prefix = (string)$db['prefix'];
    $pdo = ins_pdo();

    // 1. APP_KEY + config.php
    $appKey = 'base64:' . base64_encode(random_bytes(32));
    $baseUrl = ins_detect_base_url();
    $config = "<?php\n// Written by the installer on " . date('Y-m-d H:i:s') . " — do not edit by hand.\nreturn " .
        var_export([
            'db' => [
                'host' => $db['host'], 'port' => (int)$db['port'], 'name' => $db['name'],
                'user' => $db['user'], 'pass' => $db['pass'], 'prefix' => $prefix, 'charset' => 'utf8mb4',
            ],
            'app_key' => $appKey,
            'base_url' => $baseUrl,
            'installed_at' => date('Y-m-d H:i:s'),
        ], true) . ";\n";
    if (file_put_contents(BASE_PATH . '/config/config.php', $config) === false) {
        throw new RuntimeException('Cannot write config/config.php — check permissions.');
    }
    @chmod(BASE_PATH . '/config/config.php', 0640);

    // Simple AES helper matching App\Core\Crypt (iv + hmac + cipher, "enc:" prefix)
    $rawKey = base64_decode(substr($appKey, 7));
    $encrypt = function (string $plain) use ($rawKey): string {
        if ($plain === '') {
            return '';
        }
        $iv = random_bytes(16);
        $cipher = openssl_encrypt($plain, 'aes-256-cbc', $rawKey, OPENSSL_RAW_DATA, $iv);
        $mac = hash_hmac('sha256', $iv . $cipher, $rawKey, true);
        return 'enc:' . base64_encode($iv . $mac . $cipher);
    };

    $setSetting = function (string $key, string $value, string $group = 'general', bool $enc = false) use ($pdo, $prefix, $encrypt): void {
        $stored = $enc ? $encrypt($value) : $value;
        $stmt = $pdo->prepare("INSERT INTO `{$prefix}settings` (group_key, setting_key, setting_value, is_encrypted, updated_at)
            VALUES (?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value),
            is_encrypted = VALUES(is_encrypted), updated_at = NOW()");
        $stmt->execute([$group, $key, $stored, $enc ? 1 : 0]);
    };

    // 2. Business settings
    foreach ((array)ins_data('business') as $key => $value) {
        $group = in_array($key, ['job_prefix'], true) ? 'app' : 'general';
        $setSetting((string)$key, (string)$value, $group);
    }
    $setSetting('base_url', $baseUrl, 'app');
    $setSetting('current_version', trim((string)@file_get_contents(BASE_PATH . '/VERSION')) ?: '1.0.0', 'update');

    // 3. Branches
    $stmt = $pdo->prepare("INSERT INTO `{$prefix}branches` (code, name, type, address, phone, whatsapp, is_active, sort_order, created_at, updated_at)
        VALUES (?,?,?,?,?,?,1,?,NOW(),NOW())");
    $firstBranchId = null;
    foreach ((array)ins_data('branches') as $i => $branch) {
        $stmt->execute([$branch['code'], $branch['name'], $branch['type'], $branch['address'], $branch['phone'], $branch['phone'], $i]);
        if ($firstBranchId === null) {
            $firstBranchId = (int)$pdo->lastInsertId();
        }
    }

    // 4. Super admin user
    $admin = ins_data('admin');
    $roleId = (int)$pdo->query("SELECT id FROM `{$prefix}roles` WHERE slug = 'super_admin'")->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO `{$prefix}users` (name, phone, email, password_hash, role_id, primary_branch_id, is_active, created_at)
        VALUES (?,?,?,?,?,?,1,NOW())");
    $stmt->execute([
        $admin['name'], $admin['phone'], $admin['email'] ?: null,
        password_hash($admin['password'], PASSWORD_DEFAULT), $roleId, $firstBranchId,
    ]);

    // 5. WhatsApp settings
    $wa = ins_data('whatsapp');
    if ($wa) {
        $setSetting('wa_api_url', $wa['wa_api_url'], 'whatsapp');
        $setSetting('wa_api_key', $wa['wa_api_key'], 'whatsapp', true);
        $setSetting('wa_session_id', $wa['wa_session_id'], 'whatsapp');
        $setSetting('wa_country_code', $wa['wa_country_code'], 'whatsapp');
        $setSetting('wa_enabled', '1', 'whatsapp');
    }
    $setSetting('wa_webhook_secret', bin2hex(random_bytes(16)), 'whatsapp');

    // 6. Root .htaccess (only if missing — never clobber a customised one)
    if (!is_file(BASE_PATH . '/.htaccess')) {
        @copy(BASE_PATH . '/.htaccess.dist', BASE_PATH . '/.htaccess');
    }
    // uploads/.htaccess hardening (ships with repo; recreate if missing)
    if (!is_file(BASE_PATH . '/uploads/.htaccess')) {
        @file_put_contents(BASE_PATH . '/uploads/.htaccess',
            "php_flag engine off\nOptions -Indexes\n<FilesMatch \"\\.(php|phtml|phar)$\">\nRequire all denied\n</FilesMatch>\n");
    }

    // 7. Lock the installer
    if (!is_dir(BASE_PATH . '/storage')) {
        @mkdir(BASE_PATH . '/storage', 0755, true);
    }
    file_put_contents(BASE_PATH . '/storage/installed.lock', date('Y-m-d H:i:s'));

    ins_set('finished', true);
    ins_set('base_url_final', $baseUrl);
    return ['ok' => true, 'next' => 8];
} catch (Throwable $e) {
    return ['ok' => false, 'errors' => ['Finalise failed: ' . ins_e($e->getMessage())], 'stay' => 8];
}
