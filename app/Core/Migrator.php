<?php
declare(strict_types=1);

namespace App\Core;

class Migrator
{
    /** Run pending migrations from database/migrations, in filename order. @return string[] executed */
    public static function run(): array
    {
        $dir = BASE_PATH . '/database/migrations';
        if (!is_dir($dir)) {
            return [];
        }
        $files = glob($dir . '/*.sql') ?: [];
        sort($files, SORT_STRING);

        $done = array_column(
            DB::all('SELECT filename FROM `' . tbl('migrations') . '`'),
            'filename'
        );
        $batch = (int)(DB::val('SELECT COALESCE(MAX(batch),0) FROM `' . tbl('migrations') . '`') ?? 0) + 1;

        $executed = [];
        foreach ($files as $file) {
            $name = basename($file);
            if (in_array($name, $done, true)) {
                continue;
            }
            DB::runSqlFile($file);
            DB::insert('migrations', [
                'filename' => $name,
                'batch' => $batch,
                'executed_at' => now(),
            ]);
            $executed[] = $name;
        }
        return $executed;
    }

    /** Migration filenames present on disk but not yet recorded. */
    public static function pending(): array
    {
        $dir = BASE_PATH . '/database/migrations';
        $files = array_map('basename', glob($dir . '/*.sql') ?: []);
        sort($files, SORT_STRING);
        $done = array_column(DB::all('SELECT filename FROM `' . tbl('migrations') . '`'), 'filename');
        return array_values(array_diff($files, $done));
    }

    /** Record files as executed without running them (used by installer after schema import). */
    public static function markAllExecuted(): void
    {
        $dir = BASE_PATH . '/database/migrations';
        foreach (glob($dir . '/*.sql') ?: [] as $file) {
            $name = basename($file);
            $exists = DB::val('SELECT id FROM `' . tbl('migrations') . '` WHERE filename = ?', [$name]);
            if (!$exists) {
                DB::insert('migrations', ['filename' => $name, 'batch' => 1, 'executed_at' => now()]);
            }
        }
    }
}
