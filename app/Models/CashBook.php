<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Crypt;
use App\Core\DB;
use App\Core\Logger;

/**
 * Whose pocket the shop's cash is in, and how it moves between pockets.
 *
 * Every cash receipt stays with the person who took it. Cash refunds they paid out come
 * off the same pocket. Nothing else touches it except a handover, and a handover only
 * counts once both people have stood together and finished it:
 *
 *   1. The sender starts it — amount, who it is going to, an optional note.
 *   2. A code appears on the RECEIVER's screen. Only that user can see it.
 *   3. The receiver reads it out; the sender types it in.
 *   4. The money moves: off the sender's book, onto the receiver's.
 *
 * Neither person can complete it alone, which is the entire point. Unfinished handovers
 * expire on their own, and the money simply stays where it was.
 *
 * Card, UPI, bank and cheque never enter this ledger — that money never sat in a pocket.
 */
class CashBook
{
    /** How long the receiver's code stays good for. */
    public const OTP_MINUTES = 30;

    /** Wrong codes allowed before it has to be issued again. */
    public const MAX_ATTEMPTS = 5;

    /** A handover cannot be started for less than this. */
    public const MIN_AMOUNT = 1.0;

    // ------------------------------------------------------------------ what people hold

    /**
     * SQL for one user's cash in hand, given the users alias — so a report can select it
     * alongside everything else it already asks for, in one query.
     */
    public static function sqlInHand(string $u = 'u'): string
    {
        $p = tbl('payments');
        $h = tbl('cash_handovers');
        return "(
            COALESCE((SELECT SUM(p.amount) FROM `$p` p
                      WHERE p.received_by_user_id = $u.id AND p.mode = 'cash' AND p.deleted_at IS NULL), 0)
          + COALESCE((SELECT SUM(h.amount) FROM `$h` h
                      WHERE h.to_user_id = $u.id AND h.status = 'completed'), 0)
          - COALESCE((SELECT SUM(h.amount) FROM `$h` h
                      WHERE h.from_user_id = $u.id AND h.status = 'completed'), 0)
        )";
    }

    /** What this person is holding right now. */
    public static function inHand(int $userId): float
    {
        $cash = (float)DB::val(
            'SELECT COALESCE(SUM(amount),0) FROM `' . tbl('payments') . "`
             WHERE received_by_user_id = ? AND mode = 'cash' AND deleted_at IS NULL",
            [$userId]
        );
        $in = (float)DB::val(
            'SELECT COALESCE(SUM(amount),0) FROM `' . tbl('cash_handovers') . "`
             WHERE to_user_id = ? AND status = 'completed'",
            [$userId]
        );
        $out = (float)DB::val(
            'SELECT COALESCE(SUM(amount),0) FROM `' . tbl('cash_handovers') . "`
             WHERE from_user_id = ? AND status = 'completed'",
            [$userId]
        );
        return round($cash + $in - $out, 2);
    }

    /** Already promised to somebody in a handover that has not been finished yet. */
    public static function reserved(int $userId): float
    {
        return round((float)DB::val(
            'SELECT COALESCE(SUM(amount),0) FROM `' . tbl('cash_handovers') . "`
             WHERE from_user_id = ? AND status = 'pending'",
            [$userId]
        ), 2);
    }

    /** What is actually free to hand over: what is held, less what is already promised. */
    public static function available(int $userId): float
    {
        return round(self::inHand($userId) - self::reserved($userId), 2);
    }

    /**
     * Every member of staff and what they are holding.
     *
     * Everyone who can hold cash is listed even at zero — an empty pocket is an answer,
     * and a name that disappears from the list looks like a mistake.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function balances(): array
    {
        $u = tbl('users');
        $r = tbl('roles');
        $p = tbl('payments');
        $h = tbl('cash_handovers');
        $inHand = self::sqlInHand('u');

        return DB::all(
            "SELECT u.id, u.name, u.phone, r.name AS role_name, r.slug AS role_slug,
                    $inHand AS in_hand,
                    COALESCE((SELECT SUM(p.amount) FROM `$p` p
                              WHERE p.received_by_user_id = u.id AND p.mode <> 'cash' AND p.deleted_at IS NULL), 0) AS digital_collected,
                    COALESCE((SELECT SUM(h.amount) FROM `$h` h
                              WHERE h.from_user_id = u.id AND h.status = 'pending'), 0) AS reserved,
                    (SELECT COUNT(*) FROM `$h` h WHERE h.to_user_id = u.id AND h.status = 'pending') AS waiting_on_them,
                    (SELECT MAX(p.paid_at) FROM `$p` p
                      WHERE p.received_by_user_id = u.id AND p.mode = 'cash' AND p.deleted_at IS NULL) AS last_cash_at
             FROM `$u` u
             JOIN `$r` r ON r.id = u.role_id
             WHERE u.deleted_at IS NULL AND u.is_active = 1 AND r.slug <> 'customer'
             ORDER BY in_hand DESC, u.name"
        );
    }

    /** The people a handover can be made to. */
    public static function staff(?int $excludeUserId = null): array
    {
        $rows = DB::all(
            'SELECT u.id, u.name, r.name AS role_name
             FROM `' . tbl('users') . '` u
             JOIN `' . tbl('roles') . '` r ON r.id = u.role_id
             WHERE u.deleted_at IS NULL AND u.is_active = 1 AND r.slug <> ?
             ORDER BY u.name',
            ['customer']
        );
        if ($excludeUserId === null) {
            return $rows;
        }
        return array_values(array_filter($rows, fn($x) => (int)$x['id'] !== $excludeUserId));
    }

    // ------------------------------------------------------------------------- handovers

    /**
     * Start a handover. Nothing moves yet — the money is only reserved.
     *
     * @return array{id:int, ref_no:string}
     * @throws \RuntimeException when the handover does not make sense
     */
    public static function create(int $fromUserId, int $toUserId, float $amount, string $note = ''): array
    {
        $amount = round($amount, 2);
        if ($toUserId === $fromUserId) {
            throw new \RuntimeException('You cannot hand money over to yourself.');
        }
        $to = DB::get(
            'SELECT u.id, u.name FROM `' . tbl('users') . '` u
             WHERE u.id = ? AND u.deleted_at IS NULL AND u.is_active = 1',
            [$toUserId]
        );
        if (!$to) {
            throw new \RuntimeException('Pick somebody to hand the money to.');
        }
        if ($amount < self::MIN_AMOUNT) {
            throw new \RuntimeException('Enter how much cash you are handing over.');
        }
        $free = self::available($fromUserId);
        if ($amount > $free + 0.009) {
            throw new \RuntimeException(sprintf(
                'You are holding %s that is free to hand over — %s is more than that.',
                fmt_money($free),
                fmt_money($amount)
            ));
        }

        return DB::transaction(function () use ($fromUserId, $toUserId, $to, $amount, $note) {
            $id = DB::insert('cash_handovers', [
                'ref_no' => 'TMP-' . bin2hex(random_bytes(8)),
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'amount' => $amount,
                'note' => trim($note) ?: null,
                'status' => 'pending',
                'otp_code' => Crypt::encrypt(self::newCode()),
                'otp_expires_at' => date('Y-m-d H:i:s', time() + self::OTP_MINUTES * 60),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $ref = 'HO-' . date('ym') . '-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT);
            DB::update('cash_handovers', ['ref_no' => $ref], ['id' => $id]);

            Logger::activity('cash', 'handover_start', 'cash_handover', $id,
                $ref . ': ' . fmt_money($amount) . ' offered to ' . $to['name']);
            Logger::notify(
                $toUserId,
                'Cash handover — ' . fmt_money($amount),
                'Open Cash in Hand to see the code, and read it out to the person giving you the money.',
                admin_url('cash'),
                'cash-stack'
            );
            return ['id' => $id, 'ref_no' => $ref];
        });
    }

    /**
     * The sender types in the code the receiver read out. This is the moment the money moves.
     *
     * @throws \RuntimeException with a message meant to be shown as it is
     */
    public static function confirm(int $id, string $typedCode, int $actorId): array
    {
        // Everything that can go wrong is checked out here, NOT inside the transaction.
        // A wrong code has to leave a mark on the row, and a rollback would rub it out —
        // the code could then be guessed for ever.
        $row = DB::get('SELECT * FROM `' . tbl('cash_handovers') . '` WHERE id = ?', [$id]);
        if (!$row) {
            throw new \RuntimeException('That handover no longer exists.');
        }
        if ((int)$row['from_user_id'] !== $actorId) {
            throw new \RuntimeException('Only the person handing the money over can finish it.');
        }
        if ($row['status'] !== 'pending') {
            throw new \RuntimeException('This handover is already ' . $row['status'] . '.');
        }
        if (strtotime((string)$row['otp_expires_at']) < time()) {
            self::close($id, 'expired', null, 'The code was never used.');
            throw new \RuntimeException('The code has run out. Ask for a new code.');
        }
        if ((int)$row['otp_attempts'] >= self::MAX_ATTEMPTS) {
            throw new \RuntimeException('Too many wrong codes. Ask for a new code before trying again.');
        }

        $typed = preg_replace('/\D/', '', $typedCode) ?? '';
        $real = Crypt::decrypt((string)$row['otp_code']);
        if ($typed === '' || !hash_equals($real, $typed)) {
            DB::run(
                'UPDATE `' . tbl('cash_handovers') . '` SET otp_attempts = otp_attempts + 1, updated_at = ? WHERE id = ?',
                [now(), $id]
            );
            $left = self::MAX_ATTEMPTS - ((int)$row['otp_attempts'] + 1);
            throw new \RuntimeException($left > 0
                ? 'That code is wrong. ' . $left . ' more ' . ($left === 1 ? 'try' : 'tries') . ' before it locks.'
                : 'That code is wrong, and it has now locked. Ask for a new code.');
        }

        // The code is right. Now — and only now — the money moves, under a row lock so two
        // taps on the button cannot pay it twice.
        return DB::transaction(function () use ($id, $typed, $actorId) {
            $row = DB::get('SELECT * FROM `' . tbl('cash_handovers') . '` WHERE id = ? FOR UPDATE', [$id]);
            if (!$row || $row['status'] !== 'pending') {
                throw new \RuntimeException('This handover was already finished.');
            }
            // It could have been given a new code in the moment between the two reads.
            if (!hash_equals(Crypt::decrypt((string)$row['otp_code']), $typed)) {
                throw new \RuntimeException('That code has just been replaced. Ask for the new one.');
            }
            // The money must still be there — a refund paid out since the handover started
            // could have emptied the pocket.
            $held = self::inHand((int)$row['from_user_id']);
            if ((float)$row['amount'] > $held + 0.009) {
                throw new \RuntimeException(sprintf(
                    'You are only holding %s now, so %s cannot be handed over. Cancel this one and start again.',
                    fmt_money($held),
                    fmt_money((float)$row['amount'])
                ));
            }

            DB::update('cash_handovers', [
                'status' => 'completed',
                'closed_at' => now(),
                'closed_by_user_id' => $actorId,
                'updated_at' => now(),
            ], ['id' => $id]);

            $from = (string)DB::val('SELECT name FROM `' . tbl('users') . '` WHERE id = ?', [(int)$row['from_user_id']]);
            $to = (string)DB::val('SELECT name FROM `' . tbl('users') . '` WHERE id = ?', [(int)$row['to_user_id']]);
            Logger::activity('cash', 'handover_done', 'cash_handover', $id,
                $row['ref_no'] . ': ' . fmt_money((float)$row['amount']) . ' handed from ' . $from . ' to ' . $to);
            Logger::notify(
                (int)$row['to_user_id'],
                fmt_money((float)$row['amount']) . ' received from ' . $from,
                'It is on your cash in hand now.',
                admin_url('cash'),
                'cash-stack'
            );
            return $row;
        });
    }

    /** The receiver says no — nothing moves. */
    public static function decline(int $id, int $actorId, string $reason = ''): void
    {
        $row = self::open($id);
        if ((int)$row['to_user_id'] !== $actorId) {
            throw new \RuntimeException('Only the person the money is going to can turn it down.');
        }
        self::close($id, 'declined', $actorId, trim($reason) ?: 'Turned down by the receiver.');
        Logger::notify(
            (int)$row['from_user_id'],
            'Handover turned down — ' . fmt_money((float)$row['amount']),
            trim($reason) ?: 'The money stays with you.',
            admin_url('cash'),
            'cash-stack'
        );
    }

    /** The sender changes their mind before it is finished. */
    public static function cancel(int $id, int $actorId): void
    {
        $row = self::open($id);
        if ((int)$row['from_user_id'] !== $actorId) {
            throw new \RuntimeException('Only the person who started the handover can cancel it.');
        }
        self::close($id, 'cancelled', $actorId, 'Cancelled by the sender.');
    }

    /**
     * A fresh code for the same handover — for when it ran out, or was typed wrong too often.
     * Only the sender can ask, and only the receiver ever sees the new code.
     */
    public static function reissue(int $id, int $actorId): void
    {
        $row = self::open($id);
        if ((int)$row['from_user_id'] !== $actorId && (int)$row['to_user_id'] !== $actorId) {
            throw new \RuntimeException('This handover is not yours.');
        }
        DB::update('cash_handovers', [
            'otp_code' => Crypt::encrypt(self::newCode()),
            'otp_expires_at' => date('Y-m-d H:i:s', time() + self::OTP_MINUTES * 60),
            'otp_attempts' => 0,
            'otp_issued_count' => (int)$row['otp_issued_count'] + 1,
            'updated_at' => now(),
        ], ['id' => $id]);
        Logger::activity('cash', 'handover_code', 'cash_handover', $id, $row['ref_no'] . ': new code issued');
        Logger::notify(
            (int)$row['to_user_id'],
            'New code for ' . fmt_money((float)$row['amount']),
            'Open Cash in Hand and read out the new code.',
            admin_url('cash'),
            'cash-stack'
        );
    }

    // ------------------------------------------------------------------------- reading it

    /**
     * Handovers waiting on this user to read a code out. The code is included — this is
     * the only place it is ever produced, and only for the person it is meant for.
     */
    public static function waitingForMe(int $userId): array
    {
        $rows = DB::all(
            'SELECT h.*, uf.name AS from_name
             FROM `' . tbl('cash_handovers') . '` h
             JOIN `' . tbl('users') . "` uf ON uf.id = h.from_user_id
             WHERE h.to_user_id = ? AND h.status = 'pending'
             ORDER BY h.created_at",
            [$userId]
        );
        foreach ($rows as &$row) {
            $row['code'] = Crypt::decrypt((string)$row['otp_code']);
            $row['expired'] = strtotime((string)$row['otp_expires_at']) < time();
            unset($row['otp_code']);
        }
        return $rows;
    }

    /** Handovers this user started and has not finished. Never carries the code. */
    public static function startedByMe(int $userId): array
    {
        $rows = DB::all(
            'SELECT h.*, ut.name AS to_name
             FROM `' . tbl('cash_handovers') . '` h
             JOIN `' . tbl('users') . "` ut ON ut.id = h.to_user_id
             WHERE h.from_user_id = ? AND h.status = 'pending'
             ORDER BY h.created_at",
            [$userId]
        );
        foreach ($rows as &$row) {
            $row['expired'] = strtotime((string)$row['otp_expires_at']) < time();
            $row['locked'] = (int)$row['otp_attempts'] >= self::MAX_ATTEMPTS;
            unset($row['otp_code']);
        }
        return $rows;
    }

    /**
     * The trail. A manager sees every handover; everybody else sees their own.
     *
     * @param array{user?:int, from?:string, to?:string, status?:string, limit?:int} $filter
     */
    public static function history(array $filter = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filter['user'])) {
            $where[] = '(h.from_user_id = ? OR h.to_user_id = ?)';
            $params[] = (int)$filter['user'];
            $params[] = (int)$filter['user'];
        }
        if (!empty($filter['status'])) {
            $where[] = 'h.status = ?';
            $params[] = (string)$filter['status'];
        }
        if (!empty($filter['from'])) {
            $where[] = 'h.created_at >= ?';
            $params[] = $filter['from'] . ' 00:00:00';
        }
        if (!empty($filter['to'])) {
            $where[] = 'h.created_at <= ?';
            $params[] = $filter['to'] . ' 23:59:59';
        }
        $limit = (int)($filter['limit'] ?? 100);
        $limit = $limit > 0 && $limit <= 1000 ? $limit : 100;

        return DB::all(
            'SELECT h.id, h.ref_no, h.amount, h.note, h.status, h.created_at, h.closed_at, h.close_reason,
                    uf.name AS from_name, ut.name AS to_name
             FROM `' . tbl('cash_handovers') . '` h
             JOIN `' . tbl('users') . '` uf ON uf.id = h.from_user_id
             JOIN `' . tbl('users') . '` ut ON ut.id = h.to_user_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY h.created_at DESC
             LIMIT ' . $limit,
            $params
        );
    }

    /**
     * One person's cash movements, newest first: what they took in cash and what they
     * passed on. This is the statement behind the "in hand" figure.
     */
    public static function statement(int $userId, int $limit = 60): array
    {
        $rows = DB::all(
            "SELECT p.paid_at AS at, p.amount AS amount, 'payment' AS kind,
                    CONCAT('Receipt ', p.receipt_no, ' · ', c.name) AS detail
             FROM `" . tbl('payments') . '` p
             JOIN `' . tbl('customers') . "` c ON c.id = p.customer_id
             WHERE p.received_by_user_id = ? AND p.mode = 'cash' AND p.deleted_at IS NULL
             UNION ALL
             SELECT h.closed_at AS at, h.amount AS amount, 'handover_in' AS kind,
                    CONCAT('From ', uf.name, ' · ', h.ref_no) AS detail
             FROM `" . tbl('cash_handovers') . '` h
             JOIN `' . tbl('users') . "` uf ON uf.id = h.from_user_id
             WHERE h.to_user_id = ? AND h.status = 'completed'
             UNION ALL
             SELECT h.closed_at AS at, -h.amount AS amount, 'handover_out' AS kind,
                    CONCAT('To ', ut.name, ' · ', h.ref_no) AS detail
             FROM `" . tbl('cash_handovers') . '` h
             JOIN `' . tbl('users') . "` ut ON ut.id = h.to_user_id
             WHERE h.from_user_id = ? AND h.status = 'completed'
             ORDER BY at DESC
             LIMIT " . max(1, min(500, $limit)),
            [$userId, $userId, $userId]
        );
        return $rows;
    }

    /** Close out handovers whose code was never used. The money stays with the sender. */
    public static function expireStale(): int
    {
        $due = DB::all(
            'SELECT id, ref_no, from_user_id, amount FROM `' . tbl('cash_handovers') . "`
             WHERE status = 'pending' AND otp_expires_at < NOW()"
        );
        foreach ($due as $row) {
            self::close((int)$row['id'], 'expired', null, 'The code was never used.');
            Logger::notify(
                (int)$row['from_user_id'],
                'Handover expired — ' . fmt_money((float)$row['amount']),
                $row['ref_no'] . ' was never finished, so the money is still with you.',
                admin_url('cash'),
                'cash-stack'
            );
        }
        return count($due);
    }

    // ---------------------------------------------------------------------------- private

    private static function newCode(): string
    {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /** A handover that is still open, or an exception saying why it is not. */
    private static function open(int $id): array
    {
        $row = DB::get('SELECT * FROM `' . tbl('cash_handovers') . '` WHERE id = ?', [$id]);
        if (!$row) {
            throw new \RuntimeException('That handover no longer exists.');
        }
        if ($row['status'] !== 'pending') {
            throw new \RuntimeException('This handover is already ' . $row['status'] . '.');
        }
        return $row;
    }

    private static function close(int $id, string $status, ?int $actorId, string $reason): void
    {
        DB::update('cash_handovers', [
            'status' => $status,
            'closed_at' => now(),
            'closed_by_user_id' => $actorId,
            'close_reason' => $reason,
            'updated_at' => now(),
        ], ['id' => $id]);
        Logger::activity('cash', 'handover_' . $status, 'cash_handover', $id, $reason);
    }
}
