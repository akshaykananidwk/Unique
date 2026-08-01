<?php
declare(strict_types=1);

/** POST handler for all installer steps. Returns ['ok'=>bool,'next'=>int]|['ok'=>false,'errors'=>[],'stay'=>int] */

$post = (int)($_POST['step'] ?? 0);

switch ($post) {
    // -------------------------------------------------- 1 → 2 system check gate
    case 1:
        return ['ok' => true, 'next' => 2];

    // -------------------------------------------------- 2: database credentials
    case 2:
        $db = [
            'host' => trim((string)($_POST['db_host'] ?? '127.0.0.1')),
            'port' => (int)($_POST['db_port'] ?? 3306),
            'name' => trim((string)($_POST['db_name'] ?? '')),
            'user' => trim((string)($_POST['db_user'] ?? '')),
            'pass' => (string)($_POST['db_pass'] ?? ''),
            'prefix' => trim((string)($_POST['db_prefix'] ?? '')),
        ];
        if ($db['name'] === '' || $db['user'] === '') {
            return ['ok' => false, 'errors' => ['Database name and username are required.'], 'stay' => 2];
        }
        if ($db['prefix'] !== '' && !preg_match('/^[a-z0-9_]{1,12}$/', $db['prefix'])) {
            return ['ok' => false, 'errors' => ['Prefix may only contain a–z, 0–9 and underscore (max 12 chars).'], 'stay' => 2];
        }
        try {
            $pdo = ins_pdo($db);
            $existing = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            ins_set('db', $db);
            ins_set('db_existing_tables', count($existing));
            if ($existing && empty($_POST['wipe_confirm'])) {
                return ['ok' => false, 'errors' => [
                    '⚠️ The database already contains <strong>' . count($existing) . ' tables</strong>. '
                    . 'Tick "wipe existing tables" below to drop them, or choose an empty database.',
                ], 'stay' => 2];
            }
            if ($existing && !empty($_POST['wipe_confirm'])) {
                $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
                foreach ($existing as $tableName) {
                    $pdo->exec('DROP TABLE IF EXISTS `' . str_replace('`', '', (string)$tableName) . '`');
                }
                $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            }
            return ['ok' => true, 'next' => 3];
        } catch (Throwable $e) {
            return ['ok' => false, 'errors' => ['Connection failed: ' . ins_e($e->getMessage())], 'stay' => 2];
        }

    // -------------------------------------------------- 3: import schema + seed + migrations
    case 3:
        if (!ins_data('db')) {
            return ['ok' => false, 'errors' => ['Database not configured yet.'], 'stay' => 2];
        }
        try {
            set_time_limit(300);
            $db = ins_data('db');
            $pdo = ins_pdo();
            $prefix = (string)$db['prefix'];
            $log = [];
            $n = ins_run_sql($pdo, BASE_PATH . '/database/schema.sql', $prefix);
            $log[] = "schema.sql — $n statements";
            $n = ins_run_sql($pdo, BASE_PATH . '/database/seed.sql', $prefix);
            $log[] = "seed.sql — $n statements";
            $files = glob(BASE_PATH . '/database/migrations/*.sql') ?: [];
            sort($files, SORT_STRING);
            foreach ($files as $file) {
                $name = basename($file);
                ins_run_sql($pdo, $file, $prefix);
                $stmt = $pdo->prepare("INSERT INTO `{$prefix}migrations` (filename, batch, executed_at) VALUES (?, 1, NOW())");
                $stmt->execute([$name]);
                $log[] = "migration $name — done";
            }
            ins_set('import_log', $log);
            ins_set('imported', true);
            return ['ok' => true, 'next' => 4];
        } catch (Throwable $e) {
            return ['ok' => false, 'errors' => ['Import failed: ' . ins_e($e->getMessage())], 'stay' => 3];
        }

    // -------------------------------------------------- 4: business setup
    case 4:
        $biz = [
            'business_name' => trim((string)($_POST['business_name'] ?? '')),
            'business_tagline' => trim((string)($_POST['business_tagline'] ?? '')),
            'business_address' => trim((string)($_POST['business_address'] ?? '')),
            'business_city' => trim((string)($_POST['business_city'] ?? '')),
            'business_state' => trim((string)($_POST['business_state'] ?? '')),
            'business_pincode' => trim((string)($_POST['business_pincode'] ?? '')),
            'business_phone' => preg_replace('/\D/', '', (string)($_POST['business_phone'] ?? '')),
            'business_whatsapp' => preg_replace('/\D/', '', (string)($_POST['business_whatsapp'] ?? '')),
            'business_email' => trim((string)($_POST['business_email'] ?? '')),
            'business_gstin' => trim((string)($_POST['business_gstin'] ?? '')),
            'currency_symbol' => trim((string)($_POST['currency_symbol'] ?? '₹')) ?: '₹',
            'timezone' => trim((string)($_POST['timezone'] ?? 'Asia/Kolkata')) ?: 'Asia/Kolkata',
            'date_format' => trim((string)($_POST['date_format'] ?? 'd-m-Y')) ?: 'd-m-Y',
            'job_prefix' => strtoupper(trim((string)($_POST['job_prefix'] ?? 'JOB'))) ?: 'JOB',
            'brand_color' => preg_match('/^#[0-9a-f]{6}$/i', (string)($_POST['brand_color'] ?? '')) ? $_POST['brand_color'] : '#0d6efd',
        ];
        if ($biz['business_name'] === '') {
            return ['ok' => false, 'errors' => ['Business name is required.'], 'stay' => 4];
        }
        // Logo upload (safe subset — GD not required)
        foreach (['business_logo', 'business_favicon'] as $fileKey) {
            if (!empty($_FILES[$fileKey]['name']) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo((string)$_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'ico'], true)) {
                    $dir = BASE_PATH . '/uploads/logos';
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0755, true);
                    }
                    $name = 'logos/' . $fileKey . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], BASE_PATH . '/uploads/' . $name)) {
                        $biz[$fileKey] = $name;
                    }
                }
            }
        }
        ins_set('business', $biz);
        return ['ok' => true, 'next' => 5];

    // -------------------------------------------------- 5: branches
    case 5:
        $branches = [];
        $names = (array)($_POST['branch_name'] ?? []);
        $codes = (array)($_POST['branch_code'] ?? []);
        $types = (array)($_POST['branch_type'] ?? []);
        $addresses = (array)($_POST['branch_address'] ?? []);
        $phones = (array)($_POST['branch_phone'] ?? []);
        foreach ($names as $i => $name) {
            $name = trim((string)$name);
            $code = strtoupper(trim((string)($codes[$i] ?? '')));
            if ($name === '' || $code === '') {
                continue;
            }
            if (!preg_match('/^[A-Z0-9]{1,10}$/', $code)) {
                return ['ok' => false, 'errors' => ['Branch code "' . ins_e($code) . '" must be alphanumeric (max 10 chars).'], 'stay' => 5];
            }
            $branches[] = [
                'name' => $name,
                'code' => $code,
                'type' => in_array($types[$i] ?? '', ['shop', 'godown', 'office'], true) ? $types[$i] : 'shop',
                'address' => trim((string)($addresses[$i] ?? '')),
                'phone' => preg_replace('/\D/', '', (string)($phones[$i] ?? '')),
            ];
        }
        if (!$branches) {
            return ['ok' => false, 'errors' => ['Add at least one branch.'], 'stay' => 5];
        }
        $allCodes = array_column($branches, 'code');
        if (count($allCodes) !== count(array_unique($allCodes))) {
            return ['ok' => false, 'errors' => ['Branch codes must be unique.'], 'stay' => 5];
        }
        ins_set('branches', $branches);
        return ['ok' => true, 'next' => 6];

    // -------------------------------------------------- 6: super admin
    case 6:
        $name = trim((string)($_POST['admin_name'] ?? ''));
        $phone = preg_replace('/\D/', '', (string)($_POST['admin_phone'] ?? ''));
        $phone = ltrim($phone, '0');
        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            $phone = substr($phone, 2);
        }
        $email = trim((string)($_POST['admin_email'] ?? ''));
        $password = (string)($_POST['admin_password'] ?? '');
        if ($name === '' || strlen($phone) !== 10) {
            return ['ok' => false, 'errors' => ['Name and a valid 10-digit phone are required.'], 'stay' => 6];
        }
        if (strlen($password) < 8) {
            return ['ok' => false, 'errors' => ['Password must be at least 8 characters.'], 'stay' => 6];
        }
        if ($password !== (string)($_POST['admin_password2'] ?? '')) {
            return ['ok' => false, 'errors' => ['Passwords do not match.'], 'stay' => 6];
        }
        ins_set('admin', ['name' => $name, 'phone' => $phone, 'email' => $email, 'password' => $password]);
        return ['ok' => true, 'next' => 7];

    // -------------------------------------------------- 7: whatsapp (skippable)
    case 7:
        if (!empty($_POST['skip'])) {
            ins_set('whatsapp', null);
            return ['ok' => true, 'next' => 8];
        }
        $wa = [
            'wa_api_url' => trim((string)($_POST['wa_api_url'] ?? 'https://bulk.akdwk.in/api.php')),
            'wa_api_key' => trim((string)($_POST['wa_api_key'] ?? '')),
            'wa_session_id' => trim((string)($_POST['wa_session_id'] ?? '')),
            'wa_country_code' => preg_replace('/\D/', '', (string)($_POST['wa_country_code'] ?? '91')) ?: '91',
        ];
        if (!empty($_POST['test_send'])) {
            $testNumber = preg_replace('/\D/', '', (string)($_POST['test_number'] ?? ''));
            if (strlen($testNumber) === 10) {
                $testNumber = $wa['wa_country_code'] . $testNumber;
            }
            $ch = curl_init($wa['wa_api_url']);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    'api_key' => $wa['wa_api_key'], 'number' => $testNumber,
                    'message' => 'Krishna Print installer test — ' . date('H:i:s'), 'session_id' => $wa['wa_session_id'],
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            ins_set('whatsapp', $wa);
            return ['ok' => false, 'errors' => [
                'Test result — HTTP ' . (int)$code . ': <code>' . ins_e(substr((string)$resp, 0, 400)) . '</code>'
                . '<br>If that looks good, press <strong>Save & Continue</strong>.',
            ], 'stay' => 7];
        }
        ins_set('whatsapp', $wa['wa_api_key'] !== '' ? $wa : null);
        return ['ok' => true, 'next' => 8];

    // -------------------------------------------------- 8: finalize
    case 8:
        return require __DIR__ . '/finalize.php';
}

return ['ok' => false, 'errors' => ['Unknown step.'], 'stay' => 1];
