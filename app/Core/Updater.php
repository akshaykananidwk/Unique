<?php
declare(strict_types=1);

namespace App\Core;

use ZipArchive;

/**
 * GitHub auto-update engine.
 * Check → backup → download → extract → copy (protected paths skipped) →
 * migrations → cache clear → log. Any failure rolls files + DB back.
 */
class Updater
{
    private array $log = [];

    private function say(string $line): void
    {
        $this->log[] = '[' . date('H:i:s') . '] ' . $line;
        Logger::file('updater', $line);
    }

    public function logText(): string
    {
        return implode("\n", $this->log);
    }

    // ---------------------------------------------------------------- GitHub API

    private function gh(string $path): array
    {
        $token = (string)Settings::get('upd_token', '');
        $ch = curl_init('https://api.github.com' . $path);
        $headers = [
            'Accept: application/vnd.github+json',
            'User-Agent: KrishnaPrint-Updater',
        ];
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($body === false) {
            return ['_http' => 0, '_error' => $err];
        }
        $data = json_decode((string)$body, true);
        if (!is_array($data)) {
            $data = ['_raw' => substr((string)$body, 0, 500)];
        }
        $data['_http'] = $code;
        return $data;
    }

    private function repoPath(): string
    {
        $owner = trim((string)Settings::get('upd_repo_owner', ''));
        $repo = trim((string)Settings::get('upd_repo_name', ''));
        return "/repos/{$owner}/{$repo}";
    }

    /**
     * URL-encode a git ref for a path segment while PRESERVING slashes.
     * Branch names like "claude/feature-x" must keep the slash — GitHub's
     * /commits/{ref} and /zipball/{ref} endpoints 404 on a %2F-encoded slash.
     */
    private function refPath(string $ref): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $ref)));
    }

    public function testConnection(): array
    {
        $owner = (string)Settings::get('upd_repo_owner', '');
        $repo = (string)Settings::get('upd_repo_name', '');
        if ($owner === '' || $repo === '') {
            return ['ok' => false, 'message' => 'Repository owner and name are not configured.'];
        }
        $res = $this->gh($this->repoPath());
        if (($res['_http'] ?? 0) === 200) {
            return ['ok' => true, 'message' => 'Connected to ' . ($res['full_name'] ?? "$owner/$repo") . ' (default branch: ' . ($res['default_branch'] ?? '?') . ')'];
        }
        return ['ok' => false, 'message' => 'GitHub returned HTTP ' . ($res['_http'] ?? '0') . ': ' . ($res['message'] ?? $res['_error'] ?? 'unknown error')];
    }

    /** Query GitHub and store availability flags. */
    public function checkForUpdate(): array
    {
        $branch = (string)Settings::get('upd_branch', 'main');
        $commit = $this->gh($this->repoPath() . '/commits/' . $this->refPath($branch));
        if (($commit['_http'] ?? 0) !== 200) {
            return ['ok' => false, 'message' => 'Cannot reach GitHub: HTTP ' . ($commit['_http'] ?? 0) . ' ' . ($commit['message'] ?? '')];
        }
        $newSha = (string)($commit['sha'] ?? '');
        $currentSha = (string)Settings::get('current_commit', '');

        // Remote version.json for semantic version + changelog
        $remoteVersion = null;
        $vfile = $this->gh($this->repoPath() . '/contents/version.json?ref=' . rawurlencode($branch));
        // (query-param ref accepts an encoded slash fine; path-segment refs do not — see refPath)
        if (($vfile['_http'] ?? 0) === 200 && !empty($vfile['content'])) {
            $decoded = json_decode((string)base64_decode((string)$vfile['content'], true), true);
            if (is_array($decoded)) {
                $remoteVersion = $decoded;
            }
        }

        $filesChanged = null;
        $migrationsIncluded = false;
        if ($currentSha !== '' && $newSha !== '' && $currentSha !== $newSha) {
            $cmp = $this->gh($this->repoPath() . '/compare/' . $currentSha . '...' . $newSha);
            if (($cmp['_http'] ?? 0) === 200) {
                $files = $cmp['files'] ?? [];
                $filesChanged = [
                    'total' => count($files),
                    'added' => count(array_filter($files, fn($f) => ($f['status'] ?? '') === 'added')),
                    'removed' => count(array_filter($files, fn($f) => ($f['status'] ?? '') === 'removed')),
                    'modified' => count(array_filter($files, fn($f) => ($f['status'] ?? '') === 'modified')),
                ];
                foreach ($files as $f) {
                    if (str_starts_with((string)($f['filename'] ?? ''), 'database/migrations/')) {
                        $migrationsIncluded = true;
                    }
                }
            }
        }

        $currentVersion = (string)Settings::get('current_version', trim((string)@file_get_contents(BASE_PATH . '/VERSION')));
        $latestVersion = (string)($remoteVersion['version'] ?? '');
        $available = ($currentSha === '' && $newSha !== '')
            ? ($latestVersion !== '' && version_compare($latestVersion, $currentVersion, '>'))
            : ($newSha !== '' && $newSha !== $currentSha);

        $meta = [
            'available' => $available,
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion ?: $currentVersion,
            'current_commit' => $currentSha,
            'latest_commit' => $newSha,
            'commit_message' => (string)($commit['commit']['message'] ?? ''),
            'commit_author' => (string)($commit['commit']['author']['name'] ?? ''),
            'commit_date' => !empty($commit['commit']['author']['date'])
                ? date('d-m-Y h:i A', strtotime((string)$commit['commit']['author']['date']))
                : '',
            'files_changed' => $filesChanged,
            'changelog' => $remoteVersion['changelog'] ?? [],
            'migrations_included' => $migrationsIncluded,
            'checked_at' => now(),
        ];
        Settings::set('update_available', $available ? '1' : '0', 'update');
        Settings::set('latest_version', $meta['latest_version'], 'update');
        Settings::set('latest_commit', $newSha, 'update');
        Settings::set('last_update_check', now(), 'update');
        Settings::set('latest_update_meta', json_encode($meta, JSON_UNESCAPED_UNICODE), 'update');
        return ['ok' => true] + $meta;
    }

    // ---------------------------------------------------------------- Update Now

    public function updateNow(?int $userId = null): array
    {
        $lock = BASE_PATH . '/storage/update.lock';
        $lockHandle = fopen($lock, 'c');
        if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            return ['ok' => false, 'message' => 'Another update is already running.'];
        }

        $startedAt = now();
        $fromVersion = (string)Settings::get('current_version', '');
        $fromCommit = (string)Settings::get('current_commit', '');
        $backupPath = null;
        $maintenanceFlag = BASE_PATH . '/storage/maintenance.flag';

        try {
            $this->say('Update started.');

            // 2. Pre-flight
            $this->preflight();
            $this->say('Pre-flight checks passed.');

            $check = $this->checkForUpdate();
            if (!$check['ok']) {
                throw new \RuntimeException('Update check failed: ' . ($check['message'] ?? ''));
            }
            $toCommit = (string)$check['latest_commit'];
            $toVersion = (string)$check['latest_version'];

            // 3. Maintenance mode
            file_put_contents($maintenanceFlag, now());
            $this->say('Maintenance mode enabled.');

            // 4. Backup
            $backup = Backup::create(Settings::getBool('upd_include_uploads', false), 'pre-update');
            if (!$backup['ok']) {
                throw new \RuntimeException('Backup failed: ' . ($backup['error'] ?? ''));
            }
            $backupPath = $backup['path'];
            $this->say('Backup created at ' . $backupPath);

            try {
                // 5. Download
                $zipPath = $this->downloadZipball();
                $this->say('Downloaded update package (' . number_format((float)filesize($zipPath) / 1024, 1) . ' KB).');

                // 6. Extract
                $extractDir = $this->extract($zipPath);
                $this->say('Extracted update package.');

                // 7+8. Copy over protected list
                $written = $this->copyOver($extractDir);
                $this->say('Copied ' . count($written) . ' files into place.');

                // 8b. Repair protected files the copy step is not allowed to overwrite
                foreach (Hardening::run() as $repair) {
                    $this->say('Repaired: ' . $repair);
                }

                // 9. Migrations
                $ran = Migrator::run();
                $this->say($ran ? 'Ran migrations: ' . implode(', ', $ran) : 'No new migrations.');

                // 10. Cache clear
                $this->clearCaches();
                $this->say('Caches cleared.');

                // 11. Settings
                Settings::set('current_commit', $toCommit, 'update');
                if ($toVersion !== '') {
                    Settings::set('current_version', $toVersion, 'update');
                }
                Settings::set('update_available', '0', 'update');
                Settings::set('last_update_at', now(), 'update');
            } catch (\Throwable $inner) {
                // AUTO-ROLLBACK
                $this->say('FAILURE: ' . $inner->getMessage());
                $this->say('Trace: ' . $inner->getTraceAsString());
                $this->say('Rolling back files from backup...');
                Backup::restoreFiles($backupPath);
                $this->say('Rolling back database from backup...');
                Backup::restoreDatabase($backupPath);
                @unlink($maintenanceFlag);
                $this->say('Rollback complete. Maintenance mode disabled.');
                DB::insert('update_logs', [
                    'from_version' => $fromVersion,
                    'to_version' => $toVersion,
                    'from_commit' => $fromCommit,
                    'to_commit' => $toCommit,
                    'status' => 'rolled_back',
                    'log_text' => $this->logText(),
                    'backup_path' => $backupPath,
                    'started_at' => $startedAt,
                    'finished_at' => now(),
                    'triggered_by' => $userId,
                ]);
                $this->alertAdmins('⚠️ {business} update FAILED and was rolled back automatically. Error: ' . substr($inner->getMessage(), 0, 300));
                return [
                    'ok' => false,
                    'rolled_back' => true,
                    'message' => 'Update failed: ' . $inner->getMessage() . ' — files and database were restored from backup.',
                    'backup_path' => $backupPath,
                    'log' => $this->logText(),
                ];
            }

            // 12. Log success
            DB::insert('update_logs', [
                'from_version' => $fromVersion,
                'to_version' => $toVersion,
                'from_commit' => $fromCommit,
                'to_commit' => $toCommit,
                'status' => 'success',
                'log_text' => $this->logText(),
                'backup_path' => $backupPath,
                'started_at' => $startedAt,
                'finished_at' => now(),
                'triggered_by' => $userId,
            ]);

            // 13. Maintenance off
            @unlink($maintenanceFlag);
            $this->say('Maintenance mode disabled.');

            // 14. Prune backups
            Backup::prune(Settings::getInt('upd_keep_backups', 5));
            $this->say('Old backups pruned.');

            return [
                'ok' => true,
                'message' => "Updated from {$fromVersion} to {$toVersion}.",
                'changelog' => $check['changelog'] ?? [],
                'log' => $this->logText(),
            ];
        } catch (\Throwable $e) {
            @unlink($maintenanceFlag);
            $this->say('ABORTED: ' . $e->getMessage());
            try {
                DB::insert('update_logs', [
                    'from_version' => $fromVersion,
                    'to_version' => null,
                    'from_commit' => $fromCommit,
                    'to_commit' => null,
                    'status' => 'failed',
                    'log_text' => $this->logText(),
                    'backup_path' => $backupPath,
                    'started_at' => $startedAt,
                    'finished_at' => now(),
                    'triggered_by' => $userId,
                ]);
            } catch (\Throwable) {
            }
            return ['ok' => false, 'message' => $e->getMessage(), 'log' => $this->logText()];
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
            @unlink($lock);
        }
    }

    private function preflight(): void
    {
        $projectSize = $this->dirSize(BASE_PATH, ['backups', 'storage/tmp']);
        $free = (float)@disk_free_space(BASE_PATH);
        if ($free > 0 && $free < $projectSize * 3) {
            throw new \RuntimeException('Not enough disk space (need ~3× project size, ' . number_format($projectSize * 3 / 1048576, 1) . ' MB).');
        }
        foreach (['config', 'uploads', 'storage', 'backups'] as $dir) {
            if (!is_writable(BASE_PATH . '/' . $dir)) {
                throw new \RuntimeException("Directory not writable: $dir/");
            }
        }
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('PHP zip extension is missing.');
        }
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('PHP curl extension is missing.');
        }
        DB::val('SELECT 1');
    }

    private function downloadZipball(): string
    {
        $branch = (string)Settings::get('upd_branch', 'main');
        $token = (string)Settings::get('upd_token', '');
        $tmpDir = BASE_PATH . '/storage/tmp';
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0755, true);
        }
        $zipPath = $tmpDir . '/update.zip';
        @unlink($zipPath);

        $fh = fopen($zipPath, 'wb');
        $ch = curl_init('https://api.github.com' . $this->repoPath() . '/zipball/' . $this->refPath($branch));
        $headers = ['Accept: application/vnd.github+json', 'User-Agent: KrishnaPrint-Updater'];
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fh,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 600,
        ]);
        curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        fclose($fh);

        if ($code !== 200) {
            throw new \RuntimeException("Zipball download failed (HTTP $code $err).");
        }
        if (!is_file($zipPath) || filesize($zipPath) < 10240) {
            throw new \RuntimeException('Downloaded package is missing or suspiciously small.');
        }
        $test = new ZipArchive();
        if ($test->open($zipPath, ZipArchive::CHECKCONS) !== true) {
            throw new \RuntimeException('Downloaded package is not a valid ZIP.');
        }
        $test->close();
        return $zipPath;
    }

    private function extract(string $zipPath): string
    {
        $extractDir = BASE_PATH . '/storage/tmp/extracted';
        $this->rrmdir($extractDir);
        @mkdir($extractDir, 0755, true);
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Cannot open update ZIP.');
        }
        $zip->extractTo($extractDir);
        $zip->close();

        // GitHub wraps in a single root folder — strip it
        $entries = array_values(array_diff(scandir($extractDir) ?: [], ['.', '..']));
        if (count($entries) === 1 && is_dir($extractDir . '/' . $entries[0])) {
            return $extractDir . '/' . $entries[0];
        }
        return $extractDir;
    }

    /** @return string[] relative paths written */
    private function copyOver(string $sourceDir): array
    {
        $protected = $this->protectedPatterns();
        $written = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($sourceDir) + 1));
            if ($rel === '' || $this->isProtected($rel, $protected)) {
                continue;
            }
            $target = BASE_PATH . '/' . $rel;
            if ($file->isDir()) {
                if (!is_dir($target)) {
                    @mkdir($target, 0755, true);
                }
                continue;
            }
            $dir = dirname($target);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if (!copy($file->getPathname(), $target)) {
                throw new \RuntimeException("Failed to write file: $rel");
            }
            @chmod($target, 0644);
            $written[] = $rel;
        }

        // Delete-manifest support: repo may ship database/delete-manifest.json
        $manifest = $sourceDir . '/database/delete-manifest.json';
        if (is_file($manifest)) {
            $toDelete = json_decode((string)file_get_contents($manifest), true);
            if (is_array($toDelete)) {
                foreach ($toDelete as $rel) {
                    $rel = str_replace(['..', '\\'], ['', '/'], (string)$rel);
                    if ($rel !== '' && !$this->isProtected($rel, $protected) && is_file(BASE_PATH . '/' . $rel)) {
                        @unlink(BASE_PATH . '/' . $rel);
                        $this->say('Deleted (manifest): ' . $rel);
                    }
                }
            }
        }
        return $written;
    }

    private function protectedPatterns(): array
    {
        $patterns = [
            'config/config.php',
            '.env',
            'uploads/**',
            'storage/**',
            'backups/**',
            '.htaccess',
        ];
        $ignoreFile = BASE_PATH . '/.updateignore';
        if (is_file($ignoreFile)) {
            foreach (file($ignoreFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);
                if ($line !== '' && !str_starts_with($line, '#')) {
                    $patterns[] = $line;
                }
            }
        }
        return array_unique($patterns);
    }

    private function isProtected(string $rel, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (str_ends_with($pattern, '/**')) {
                if (str_starts_with($rel, substr($pattern, 0, -2))) {
                    return true;
                }
            } elseif ($rel === $pattern) {
                return true;
            }
        }
        return false;
    }

    private function clearCaches(): void
    {
        foreach (glob(BASE_PATH . '/storage/cache/*') ?: [] as $f) {
            is_file($f) ? @unlink($f) : $this->rrmdir($f);
        }
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
        Settings::set('asset_version', (string)(Settings::getInt('asset_version', 1) + 1), 'app');
    }

    private function alertAdmins(string $message): void
    {
        $message = str_replace('{business}', (string)Settings::get('business_name', 'System'), $message);
        $admins = DB::all(
            'SELECT u.phone FROM `' . tbl('users') . '` u JOIN `' . tbl('roles') . '` r ON r.id = u.role_id
             WHERE r.slug = ? AND u.is_active = 1 AND u.deleted_at IS NULL',
            ['super_admin']
        );
        foreach ($admins as $admin) {
            Whatsapp::queueRaw((string)$admin['phone'], $message, null, null, 'system.update_failed', 'update', null, 1);
        }
    }

    private function dirSize(string $dir, array $skipTopDirs = []): float
    {
        $size = 0.0;
        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..' || in_array($entry, $skipTopDirs, true)) {
                continue;
            }
            $path = $dir . '/' . $entry;
            if (is_file($path)) {
                $size += (float)@filesize($path);
            } elseif (is_dir($path)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
                );
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $size += (float)$file->getSize();
                    }
                }
            }
        }
        return $size;
    }

    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($dir);
    }
}
