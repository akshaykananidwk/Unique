<?php
declare(strict_types=1);

namespace App\Core;

use ZipArchive;

class Backup
{
    /**
     * Create a full backup (files.zip + database.sql + manifest.json).
     * @return array{ok:bool,path?:string,error?:string}
     */
    public static function create(bool $includeUploads = false, string $reason = 'manual'): array
    {
        $stamp = date('Ymd_His');
        $dir = BASE_PATH . '/backups/' . $stamp;
        if (!@mkdir($dir, 0755, true)) {
            return ['ok' => false, 'error' => 'Cannot create backup directory.'];
        }
        try {
            self::zipProject($dir . '/files.zip', $includeUploads);
            self::dumpDatabase($dir . '/database.sql');
            file_put_contents($dir . '/manifest.json', json_encode([
                'version' => (string)Settings::get('current_version', trim((string)@file_get_contents(BASE_PATH . '/VERSION'))),
                'commit' => (string)Settings::get('current_commit', ''),
                'php_version' => PHP_VERSION,
                'includes_uploads' => $includeUploads,
                'reason' => $reason,
                'created_at' => now(),
            ], JSON_PRETTY_PRINT));
            return ['ok' => true, 'path' => 'backups/' . $stamp];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public static function zipProject(string $zipPath, bool $includeUploads): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create ZIP: ' . $zipPath);
        }
        $base = BASE_PATH;
        $skipPrefixes = ['backups/', 'storage/tmp/', 'storage/cache/'];
        if (!$includeUploads) {
            $skipPrefixes[] = 'uploads/';
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if (!$file->isFile()) {
                continue;
            }
            $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($base) + 1));
            foreach ($skipPrefixes as $skip) {
                if (str_starts_with($rel, $skip)) {
                    continue 2;
                }
            }
            $zip->addFile($file->getPathname(), $rel);
        }
        if (!$zip->close()) {
            throw new \RuntimeException('Failed to finalise ZIP.');
        }
    }

    /** mysqldump when available, pure-PHP dump otherwise. */
    public static function dumpDatabase(string $outPath): void
    {
        $cfg = config('db');
        if (function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(',', (string)ini_get('disable_functions'))), true)) {
            $cmd = sprintf(
                'mysqldump --host=%s --port=%d --user=%s --password=%s --single-transaction --routines %s > %s 2>/dev/null',
                escapeshellarg((string)$cfg['host']),
                (int)$cfg['port'],
                escapeshellarg((string)$cfg['user']),
                escapeshellarg((string)$cfg['pass']),
                escapeshellarg((string)$cfg['name']),
                escapeshellarg($outPath)
            );
            @shell_exec($cmd);
            if (is_file($outPath) && filesize($outPath) > 500) {
                return;
            }
        }
        self::phpDump($outPath);
    }

    private static function phpDump(string $outPath): void
    {
        $pdo = DB::pdo();
        $fh = fopen($outPath, 'wb');
        if (!$fh) {
            throw new \RuntimeException('Cannot write dump file.');
        }
        fwrite($fh, "-- Krishna Print PHP dump " . now() . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
            fwrite($fh, "DROP TABLE IF EXISTS `$table`;\n" . $create['Create Table'] . ";\n\n");
            $stmt = $pdo->query("SELECT * FROM `$table`");
            $batch = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $values = array_map(
                    fn($v) => $v === null ? 'NULL' : $pdo->quote((string)$v),
                    array_values($row)
                );
                $batch[] = '(' . implode(',', $values) . ')';
                if (count($batch) >= 200) {
                    fwrite($fh, "INSERT INTO `$table` VALUES\n" . implode(",\n", $batch) . ";\n");
                    $batch = [];
                }
            }
            if ($batch) {
                fwrite($fh, "INSERT INTO `$table` VALUES\n" . implode(",\n", $batch) . ";\n");
            }
            fwrite($fh, "\n");
        }
        fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);
    }

    /** Restore project files from a backup ZIP (protected paths still skipped). */
    public static function restoreFiles(string $backupDir): void
    {
        $zipPath = BASE_PATH . '/' . trim($backupDir, '/') . '/files.zip';
        if (!is_file($zipPath)) {
            throw new \RuntimeException('Backup files.zip not found: ' . $zipPath);
        }
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Cannot open backup ZIP.');
        }
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $rel = (string)$zip->getNameIndex($i);
            if ($rel === '' || str_ends_with($rel, '/')) {
                continue;
            }
            if (str_contains($rel, '..')) {
                continue;
            }
            $target = BASE_PATH . '/' . $rel;
            $dirName = dirname($target);
            if (!is_dir($dirName)) {
                @mkdir($dirName, 0755, true);
            }
            $data = $zip->getFromIndex($i);
            if ($data !== false) {
                file_put_contents($target, $data);
                @chmod($target, 0644);
            }
        }
        $zip->close();
    }

    public static function restoreDatabase(string $backupDir): void
    {
        $sqlPath = BASE_PATH . '/' . trim($backupDir, '/') . '/database.sql';
        if (!is_file($sqlPath)) {
            throw new \RuntimeException('Backup database.sql not found: ' . $sqlPath);
        }
        DB::runSqlFile($sqlPath);
    }

    /** List available backups (newest first). */
    public static function listAll(): array
    {
        $out = [];
        foreach (glob(BASE_PATH . '/backups/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $manifest = [];
            if (is_file($dir . '/manifest.json')) {
                $manifest = json_decode((string)file_get_contents($dir . '/manifest.json'), true) ?: [];
            }
            $size = 0;
            foreach (glob($dir . '/*') ?: [] as $f) {
                $size += (int)@filesize($f);
            }
            $out[] = [
                'dir' => 'backups/' . basename($dir),
                'name' => basename($dir),
                'size' => $size,
                'manifest' => $manifest,
            ];
        }
        usort($out, fn($a, $b) => strcmp($b['name'], $a['name']));
        return $out;
    }

    public static function prune(int $keep): void
    {
        $all = self::listAll();
        foreach (array_slice($all, max(1, $keep)) as $backup) {
            self::deleteDir(BASE_PATH . '/' . $backup['dir']);
        }
    }

    public static function deleteDir(string $dir): void
    {
        if (!is_dir($dir) || !str_starts_with(realpath($dir) ?: '', realpath(BASE_PATH . '/backups') ?: '@')) {
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
