<?php
declare(strict_types=1);

namespace App\Core;

class Whatsapp
{
    public static function enabled(): bool
    {
        return Settings::getBool('wa_enabled', false);
    }

    /** Render a template body by substituting {placeholders}. */
    public static function render(string $body, array $data): string
    {
        return preg_replace_callback(
            '/\{([a-z0-9_]+)\}/i',
            fn($m) => (string)($data[$m[1]] ?? $m[0]),
            $body
        ) ?? $body;
    }

    /**
     * Queue a message from a template for its configured recipient(s).
     * $ctx supplies routing info: customer_phone, designer_phone, branch_id, media_url.
     */
    public static function queueTemplate(string $code, array $data, array $ctx = []): void
    {
        if (!self::enabled()) {
            return;
        }
        $tpl = DB::get('SELECT * FROM `' . tbl('whatsapp_templates') . '` WHERE code = ? AND is_active = 1', [$code]);
        if (!$tpl) {
            return;
        }
        $numbers = self::resolveRecipients($tpl, $ctx);
        if (!$numbers) {
            return;
        }
        $message = self::render((string)$tpl['body'], $data);
        $delay = max(0, (int)$tpl['delay_minutes']);
        $scheduledAt = self::applyQuietHours(date('Y-m-d H:i:s', time() + $delay * 60));
        foreach (array_unique($numbers) as $number) {
            self::queueRaw(
                $number,
                $message,
                $ctx['media_url'] ?? ($tpl['media_url'] ?: null),
                $code,
                (string)$tpl['event_key'],
                $ctx['ref_type'] ?? null,
                isset($ctx['ref_id']) ? (int)$ctx['ref_id'] : null,
                (int)($ctx['priority'] ?? 5),
                $scheduledAt
            );
        }
    }

    public static function queueRaw(
        string $toNumber,
        string $message,
        ?string $mediaUrl = null,
        ?string $templateCode = null,
        ?string $eventKey = null,
        ?string $refType = null,
        ?int $refId = null,
        int $priority = 5,
        ?string $scheduledAt = null
    ): ?int {
        $normalized = normalize_phone($toNumber);
        if (!$normalized) {
            Logger::file('whatsapp', "Skipped queueing — invalid number '$toNumber' for template " . ($templateCode ?? '-'));
            return null;
        }
        return DB::insert('whatsapp_queue', [
            'template_code' => $templateCode,
            'event_key' => $eventKey,
            'to_number' => $normalized,
            'message' => $message,
            'media_url' => $mediaUrl,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'priority' => $priority,
            'status' => 'pending',
            'max_attempts' => Settings::getInt('wa_max_attempts', 3),
            'scheduled_at' => $scheduledAt ?? now(),
            'created_at' => now(),
        ]);
    }

    /** Push a send time out of the configured quiet window. */
    public static function applyQuietHours(string $datetime): string
    {
        $start = (string)Settings::get('wa_quiet_start', '');
        $end = (string)Settings::get('wa_quiet_end', '');
        if (!preg_match('/^\d{2}:\d{2}$/', $start) || !preg_match('/^\d{2}:\d{2}$/', $end) || $start === $end) {
            return $datetime;
        }
        $ts = strtotime($datetime);
        $time = date('H:i', $ts);
        $inQuiet = $start < $end
            ? ($time >= $start && $time < $end)
            : ($time >= $start || $time < $end); // window crosses midnight
        if (!$inQuiet) {
            return $datetime;
        }
        $endToday = strtotime(date('Y-m-d ', $ts) . $end . ':00');
        if ($endToday <= $ts) {
            $endToday = strtotime('+1 day', $endToday);
        }
        return date('Y-m-d H:i:s', $endToday);
    }

    /** Direct API call. @return array{ok:bool,response:string} */
    public static function sendNow(string $number, string $message, ?string $mediaUrl = null): array
    {
        $apiUrl = (string)Settings::get('wa_api_url', 'https://bulk.akdwk.in/api.php');
        $apiKey = (string)Settings::get('wa_api_key', '');
        $sessionId = (string)Settings::get('wa_session_id', '');
        if ($apiUrl === '' || $apiKey === '') {
            return ['ok' => false, 'response' => 'WhatsApp API URL or key not configured'];
        }
        $payload = [
            'api_key' => $apiKey,
            'number' => $number,
            'message' => $message,
            'session_id' => $sessionId,
        ];
        if ($mediaUrl) {
            $payload['media_url'] = $mediaUrl;
        }
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            return ['ok' => false, 'response' => "cURL error: $error"];
        }
        $body = (string)$response;
        $ok = $httpCode >= 200 && $httpCode < 300;
        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            $flag = $decoded['status'] ?? $decoded['success'] ?? null;
            if ($flag !== null) {
                $ok = $ok && !in_array(strtolower((string)$flag), ['false', 'error', 'failed', '0'], true);
            }
        }
        return ['ok' => $ok, 'response' => "HTTP $httpCode: " . substr($body, 0, 4000)];
    }

    /** Process pending queue rows. Returns number sent. Used by the cron worker. */
    public static function processQueue(int $maxRows = 0): int
    {
        if (!self::enabled()) {
            return 0;
        }
        $ratePerMin = max(1, Settings::getInt('wa_rate_limit_per_min', 12));
        $maxRows = $maxRows > 0 ? $maxRows : $ratePerMin;
        $delayMicro = (int)(60 / $ratePerMin * 1_000_000);
        $backoffMinutes = [1, 5, 30];
        $sent = 0;

        $rows = DB::all(
            'SELECT * FROM `' . tbl('whatsapp_queue') . '`
             WHERE status = ? AND (scheduled_at IS NULL OR scheduled_at <= ?)
             ORDER BY priority ASC, scheduled_at ASC, id ASC LIMIT ' . (int)$maxRows,
            ['pending', now()]
        );
        foreach ($rows as $row) {
            // Claim the row; another worker may have taken it
            $claimed = DB::run(
                'UPDATE `' . tbl('whatsapp_queue') . '` SET status = ?, attempts = attempts + 1 WHERE id = ? AND status = ?',
                ['processing', $row['id'], 'pending']
            )->rowCount();
            if (!$claimed) {
                continue;
            }
            $result = self::sendNow((string)$row['to_number'], (string)$row['message'], $row['media_url'] ?: null);
            if ($result['ok']) {
                DB::update('whatsapp_queue', [
                    'status' => 'sent',
                    'sent_at' => now(),
                    'api_response' => $result['response'],
                    'last_error' => null,
                ], ['id' => $row['id']]);
                $sent++;
            } else {
                $attempts = (int)$row['attempts'] + 1;
                $maxAttempts = (int)$row['max_attempts'];
                if ($attempts >= $maxAttempts) {
                    DB::update('whatsapp_queue', [
                        'status' => 'failed',
                        'last_error' => $result['response'],
                        'api_response' => $result['response'],
                    ], ['id' => $row['id']]);
                } else {
                    $backoff = $backoffMinutes[min($attempts - 1, count($backoffMinutes) - 1)];
                    DB::update('whatsapp_queue', [
                        'status' => 'pending',
                        'last_error' => $result['response'],
                        'scheduled_at' => date('Y-m-d H:i:s', time() + $backoff * 60),
                    ], ['id' => $row['id']]);
                }
            }
            usleep($delayMicro);
        }
        return $sent;
    }

    private static function resolveRecipients(array $tpl, array $ctx): array
    {
        $numbers = [];
        switch ($tpl['recipient']) {
            case 'customer':
                if (!empty($ctx['customer_phone'])) {
                    $numbers[] = (string)$ctx['customer_phone'];
                }
                break;
            case 'designer':
                if (!empty($ctx['designer_phone'])) {
                    $numbers[] = (string)$ctx['designer_phone'];
                }
                break;
            case 'production':
                $numbers = self::roleNumbers('production', $ctx['branch_id'] ?? null);
                break;
            case 'branch_manager':
                $numbers = self::roleNumbers('branch_manager', $ctx['branch_id'] ?? null);
                break;
            case 'admin':
                $numbers = self::roleNumbers('super_admin', null);
                break;
            case 'custom':
                foreach (preg_split('/[,\s]+/', (string)$tpl['custom_numbers']) ?: [] as $n) {
                    if (trim($n) !== '') {
                        $numbers[] = trim($n);
                    }
                }
                break;
        }
        return $numbers;
    }

    private static function roleNumbers(string $roleSlug, ?int $branchId): array
    {
        $sql = 'SELECT u.phone FROM `' . tbl('users') . '` u
                JOIN `' . tbl('roles') . '` r ON r.id = u.role_id
                WHERE r.slug = ? AND u.is_active = 1 AND u.deleted_at IS NULL';
        $params = [$roleSlug];
        if ($branchId) {
            $sql .= ' AND (u.primary_branch_id = ? OR EXISTS (
                        SELECT 1 FROM `' . tbl('user_branches') . '` ub
                        WHERE ub.user_id = u.id AND ub.branch_id = ?))';
            $params[] = $branchId;
            $params[] = $branchId;
        }
        return array_column(DB::all($sql, $params), 'phone');
    }
}
