<?php
declare(strict_types=1);

namespace App\Core;

class Logger
{
    public static function activity(
        string $module,
        string $action,
        ?string $refType = null,
        ?int $refId = null,
        string $description = ''
    ): void {
        try {
            DB::insert('activity_logs', [
                'user_id' => Auth::id(),
                'module' => $module,
                'action' => $action,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'description' => substr($description, 0, 500),
                'ip' => request_ip(),
                'user_agent' => request_agent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Logging must never break the request
        }
    }

    public static function file(string $channel, string $message): void
    {
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents(
            $dir . '/' . preg_replace('/[^a-z0-9_\-]/i', '', $channel) . '.log',
            '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    public static function notify(int $userId, string $title, string $body = '', string $url = '', string $icon = 'bell'): void
    {
        try {
            DB::insert('notifications', [
                'user_id' => $userId,
                'title' => substr($title, 0, 150),
                'body' => substr($body, 0, 500),
                'url' => substr($url, 0, 255),
                'icon' => $icon,
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
        }
    }
}
