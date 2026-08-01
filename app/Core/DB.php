<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

class DB
{
    private static ?PDO $pdo = null;
    private static string $prefix = '';

    public static function init(array $cfg): void
    {
        self::$prefix = (string)($cfg['prefix'] ?? '');
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            (int)($cfg['port'] ?? 3306),
            $cfg['name'],
            $cfg['charset'] ?? 'utf8mb4'
        );
        self::$pdo = new PDO($dsn, (string)$cfg['user'], (string)$cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);
    }

    public static function pdo(): PDO
    {
        return self::$pdo;
    }

    /**
     * Align the MySQL session time zone with PHP's, so SQL NOW()/CURDATE()/DATE_SUB(NOW())
     * agree with PHP-written datetimes. Without this, "overdue" and "today" logic is wrong
     * on servers where MySQL runs in UTC but the app timezone is Asia/Kolkata.
     */
    public static function syncTimezone(): void
    {
        try {
            $offset = (new \DateTime('now'))->format('P'); // e.g. +05:30
            self::$pdo->exec("SET time_zone = '$offset'");
        } catch (\Throwable) {
            // Some managed MySQL forbid SET time_zone — safe to ignore
        }
    }

    public static function prefix(): string
    {
        return self::$prefix;
    }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function get(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function val(string $sql, array $params = []): mixed
    {
        $v = self::run($sql, $params)->fetchColumn();
        return $v === false ? null : $v;
    }

    /** Insert an assoc row, return new id. */
    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql = 'INSERT INTO `' . tbl($table) . '` (`' . implode('`,`', $cols) . '`) VALUES ('
            . implode(',', array_fill(0, count($cols), '?')) . ')';
        self::run($sql, array_values($data));
        return (int)self::$pdo->lastInsertId();
    }

    /** Update rows matching $where (assoc, ANDed). Returns affected count. */
    public static function update(string $table, array $data, array $where): int
    {
        $set = implode(',', array_map(fn($c) => "`$c`=?", array_keys($data)));
        $cond = implode(' AND ', array_map(fn($c) => "`$c`=?", array_keys($where)));
        $stmt = self::run(
            'UPDATE `' . tbl($table) . "` SET $set WHERE $cond",
            array_merge(array_values($data), array_values($where))
        );
        return $stmt->rowCount();
    }

    public static function delete(string $table, array $where): int
    {
        $cond = implode(' AND ', array_map(fn($c) => "`$c`=?", array_keys($where)));
        return self::run('DELETE FROM `' . tbl($table) . "` WHERE $cond", array_values($where))->rowCount();
    }

    public static function transaction(callable $fn): mixed
    {
        // Re-entrant: nested calls join the outer transaction
        if (self::$pdo->inTransaction()) {
            return $fn();
        }
        self::$pdo->beginTransaction();
        try {
            $result = $fn();
            self::$pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            throw $e;
        }
    }

    /** Run a multi-statement SQL file (schema/seed/migrations). */
    public static function runSqlFile(string $path): int
    {
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new \RuntimeException("Cannot read SQL file: $path");
        }
        return self::runSqlScript($sql);
    }

    /** Split a SQL script on top-level semicolons and execute each statement. */
    public static function runSqlScript(string $sql): int
    {
        $statements = [];
        $buffer = '';
        $inString = false;
        $stringChar = '';
        $len = strlen($sql);
        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            if ($inString) {
                $buffer .= $ch;
                if ($ch === $stringChar && ($i === 0 || $sql[$i - 1] !== '\\')) {
                    $inString = false;
                }
                continue;
            }
            if ($ch === "'" || $ch === '"') {
                $inString = true;
                $stringChar = $ch;
                $buffer .= $ch;
                continue;
            }
            if ($ch === '-' && ($sql[$i + 1] ?? '') === '-' && (($sql[$i + 2] ?? "\n") === ' ' || ($sql[$i + 2] ?? "\n") === "\n")) {
                while ($i < $len && $sql[$i] !== "\n") {
                    $i++;
                }
                continue;
            }
            if ($ch === '#') {
                while ($i < $len && $sql[$i] !== "\n") {
                    $i++;
                }
                continue;
            }
            if ($ch === ';') {
                $statements[] = trim($buffer);
                $buffer = '';
                continue;
            }
            $buffer .= $ch;
        }
        if (trim($buffer) !== '') {
            $statements[] = trim($buffer);
        }
        $count = 0;
        foreach ($statements as $statement) {
            if ($statement === '') {
                continue;
            }
            if (self::$prefix !== '') {
                $statement = self::applyPrefix($statement);
            }
            self::$pdo->exec($statement);
            $count++;
        }
        return $count;
    }

    /** Best-effort table-prefixing for shipped SQL files (backticked identifiers only). */
    private static function applyPrefix(string $statement): string
    {
        $tables = [
            'settings','branches','roles','permissions','role_permissions','users','user_branches',
            'customers','customer_otps','categories','items','item_options','item_option_values',
            'price_slabs','price_groups','orders','order_sequences','receipt_sequences','order_items',
            'order_attachments','order_status_history','design_proofs','proof_feedback','payments',
            'whatsapp_templates','whatsapp_queue','whatsapp_inbound','activity_logs','notifications',
            'migrations','update_logs','login_attempts','public_order_throttle',
        ];
        foreach ($tables as $t) {
            $statement = str_replace('`' . $t . '`', '`' . self::$prefix . $t . '`', $statement);
            // Unbacktick appearances in FROM/INTO subselects of seed data
            $statement = preg_replace(
                '/\b(FROM|INTO|UPDATE|JOIN)\s+' . $t . '\b/i',
                '$1 ' . self::$prefix . $t,
                $statement
            ) ?? $statement;
        }
        return $statement;
    }
}
