<?php
declare(strict_types=1);

namespace App\Core;

class Whatsapp
{
    private static bool $autoFlushRegistered = false;

    public static function enabled(): bool
    {
        return Settings::getBool('wa_enabled', false);
    }

    /** Auto-send messages the moment they are queued, without waiting for the cron. */
    public static function autoSendEnabled(): bool
    {
        return self::enabled() && Settings::getBool('wa_auto_send', true);
    }

    /**
     * Register a once-per-request shutdown hook that flushes pending messages AFTER the
     * response is sent (via fastcgi_finish_request when available, so the user never waits).
     * This makes messages go out immediately even when no cron job is configured.
     * Call it early (before dispatch) in the front controllers.
     */
    public static function registerAutoFlush(): void
    {
        if (self::$autoFlushRegistered) {
            return;
        }
        self::$autoFlushRegistered = true;
        register_shutdown_function(static function (): void {
            if (!self::autoSendEnabled()) {
                return;
            }
            // Anything actually due to send right now?
            try {
                $due = (int)DB::val(
                    'SELECT COUNT(*) FROM `' . tbl('whatsapp_queue') . "` WHERE status = 'pending'
                     AND (scheduled_at IS NULL OR scheduled_at <= ?)",
                    [now()]
                );
            } catch (\Throwable) {
                return;
            }
            if ($due < 1) {
                return;
            }
            // Flush the HTTP response first so the user isn't kept waiting
            if (function_exists('fastcgi_finish_request')) {
                @fastcgi_finish_request();
            }
            // Guard against overlapping flushes (other requests / the cron worker)
            $lock = @fopen(BASE_PATH . '/storage/wa_autoflush.lock', 'c');
            if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
                if ($lock) {
                    fclose($lock);
                }
                return;
            }
            try {
                self::processQueue(30);
            } catch (\Throwable $e) {
                Logger::file('whatsapp', 'auto-flush error: ' . $e->getMessage());
            } finally {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        });
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
        $scheduledAt = date('Y-m-d H:i:s', time() + $delay * 60);
        // Quiet hours apply only to non-urgent bulk messages. Transactional messages
        // (proof link, order confirmation, OTP, payment receipt, status updates) go immediately.
        if (self::isQuietHoursEligible((string)$tpl['event_key'])) {
            $scheduledAt = self::applyQuietHours($scheduledAt);
        }
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

    /** Only non-urgent bulk reminders respect quiet hours; transactional messages send immediately. */
    private static function isQuietHoursEligible(string $eventKey): bool
    {
        $bulk = ['payment.balance_reminder', 'order.overdue', 'system.daily_summary', 'proof.reminder'];
        return in_array($eventKey, $bulk, true);
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
        // Record every attempt to run — lets the UI detect whether the worker/cron is alive
        Settings::set('wa_last_worker_run', now(), 'whatsapp');
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
        if ($sent > 0) {
            Settings::set('wa_last_worker_sent_at', now(), 'whatsapp');
        }
        return $sent;
    }

    /**
     * Health check for the WhatsApp pipeline — powers the Settings diagnostics panel
     * and answers "are my settings correct / why aren't messages going?".
     * @return array{ok:bool,checks:array<int,array{label:string,ok:bool,level:string,detail:string}>,counts:array}
     */
    public static function diagnostics(): array
    {
        $checks = [];
        $add = function (string $label, bool $ok, string $detail = '', string $level = 'danger') use (&$checks): void {
            $checks[] = ['label' => $label, 'ok' => $ok, 'level' => $ok ? 'success' : $level, 'detail' => $detail];
        };

        $enabled = self::enabled();
        $add('Master switch is ON', $enabled,
            $enabled ? 'WhatsApp sending is enabled.' : 'Turn on "Enable WhatsApp sending" above — nothing is queued or sent while it is off.');

        $apiUrl = (string)Settings::get('wa_api_url', '');
        $add('API URL is set', $apiUrl !== '', $apiUrl !== '' ? $apiUrl : 'Enter the API URL (default https://bulk.akdwk.in/api.php).');

        $key = (string)Settings::get('wa_api_key', '');
        $add('API key is saved', $key !== '', $key !== '' ? 'Saved (encrypted).' : 'Enter and save the API key.');

        $session = (string)Settings::get('wa_session_id', '');
        $add('Session ID / device is set', $session !== '',
            $session !== '' ? '' : 'Some accounts need the session/device id — set it if your provider requires it.', 'warning');

        $autoSend = self::autoSendEnabled();
        $add('Auto-send is ON (no cron needed)', $autoSend,
            $autoSend
                ? 'Messages are sent immediately, right after each action — you never need to press "Send pending now".'
                : 'Auto-send is off, so messages wait for the cron worker. Turn it on above for instant sending, or set up the per-minute cron.',
            'warning');

        // Queue health
        $counts = [];
        foreach (['pending', 'processing', 'sent', 'failed'] as $st) {
            $counts[$st] = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('whatsapp_queue') . '` WHERE status = ?', [$st]);
        }
        $counts['total'] = array_sum($counts);

        // Worker / cron alive? (only relevant as a backstop — auto-send covers the common case)
        $lastRun = (string)Settings::get('wa_last_worker_run', '');
        $lastRunTs = $lastRun ? strtotime($lastRun) : 0;
        $recent = $lastRunTs && (time() - $lastRunTs) < 600; // within 10 minutes
        if (!$autoSend) {
            $add('Sending worker (cron) is running', $recent,
                $lastRun
                    ? ('Last run: ' . fmt_date($lastRun, true) . ($recent ? '' : ' — more than 10 minutes ago. Either turn on Auto-send above, or check Admin → Cron Jobs.'))
                    : 'The worker has never run. Turn on Auto-send above (recommended), or add the master cron — see Admin → Cron Jobs.',
                'warning');
            if ($counts['pending'] > 0 && !$recent) {
                $add($counts['pending'] . ' message(s) waiting in the queue', false,
                    'Turn on Auto-send, or press "Send pending now" to send them immediately.', 'warning');
            }
        }

        $lastError = DB::get('SELECT to_number, last_error, created_at FROM `' . tbl('whatsapp_queue') . "` WHERE status = 'failed' AND last_error IS NOT NULL ORDER BY id DESC LIMIT 1");
        if ($lastError) {
            $add('Last failure from the API', false,
                'To ' . $lastError['to_number'] . ' — ' . mb_substr((string)$lastError['last_error'], 0, 300), 'warning');
        }

        $ok = $enabled && $apiUrl !== '' && $key !== '';
        return ['ok' => $ok, 'checks' => $checks, 'counts' => $counts];
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
