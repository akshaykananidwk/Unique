<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Models\CashBook;

/**
 * Cash in hand: who is holding the shop's money, and passing it between them.
 *
 * The screen answers one question at a glance — how much is with each person — and gives
 * the two halves of a handover: the sender's form, and the receiver's code.
 */
class CashController extends Controller
{
    public function index(): void
    {
        Acl::require('cash.view');
        $me = (int)$this->user['id'];

        // A code that ran out should not still be shown as if it were live.
        CashBook::expireStale();

        $seeAll = Acl::can('cash.view_all');
        $balances = $seeAll ? CashBook::balances() : [];
        $mine = [
            'in_hand' => CashBook::inHand($me),
            'reserved' => CashBook::reserved($me),
            'available' => CashBook::available($me),
        ];
        $waiting = CashBook::waitingForMe($me);     // codes only this user may see
        $started = CashBook::startedByMe($me);
        $staff = CashBook::staff($me);

        $filter = [
            'user' => $seeAll ? (int)($_GET['user'] ?? 0) : $me,
            'status' => in_array($_GET['status'] ?? '', ['pending', 'completed', 'declined', 'cancelled', 'expired'], true)
                ? $_GET['status'] : '',
            'from' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['from'] ?? '')) ? $_GET['from'] : '',
            'to' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['to'] ?? '')) ? $_GET['to'] : '',
        ];
        $history = CashBook::history($filter);
        $statement = CashBook::statement($seeAll && $filter['user'] ? (int)$filter['user'] : $me);
        $statementFor = $seeAll && $filter['user'] ? (int)$filter['user'] : $me;

        $this->render('cash/index', compact(
            'balances', 'mine', 'waiting', 'started', 'staff', 'history', 'filter', 'seeAll',
            'statement', 'statementFor'
        ));
    }

    /** The sender starts a handover. Nothing moves until the code is typed in. */
    public function handover(): void
    {
        Acl::require('cash.view');
        try {
            $res = CashBook::create(
                (int)$this->user['id'],
                (int)($_POST['to_user_id'] ?? 0),
                (float)($_POST['amount'] ?? 0),
                (string)($_POST['note'] ?? '')
            );
            flash('success', 'Handover ' . e($res['ref_no']) . ' started. Ask them to read out the code on their screen, then type it in below.');
        } catch (\Throwable $e) {
            flash('danger', e($e->getMessage()));
        }
        redirect(admin_url('cash'));
    }

    /** The sender types in the code the receiver read out — the money moves here. */
    public function confirm(string $id): void
    {
        Acl::require('cash.view');
        try {
            $row = CashBook::confirm((int)$id, (string)($_POST['code'] ?? ''), (int)$this->user['id']);
            flash('success', 'Handed over. ' . e(fmt_money((float)$row['amount'])) . ' is off your cash in hand.');
        } catch (\Throwable $e) {
            flash('danger', e($e->getMessage()));
        }
        redirect(admin_url('cash'));
    }

    public function decline(string $id): void
    {
        Acl::require('cash.view');
        try {
            CashBook::decline((int)$id, (int)$this->user['id'], (string)($_POST['reason'] ?? ''));
            flash('success', 'Turned down. The money stays with the person who offered it.');
        } catch (\Throwable $e) {
            flash('danger', e($e->getMessage()));
        }
        redirect(admin_url('cash'));
    }

    public function cancel(string $id): void
    {
        Acl::require('cash.view');
        try {
            CashBook::cancel((int)$id, (int)$this->user['id']);
            flash('success', 'Handover cancelled.');
        } catch (\Throwable $e) {
            flash('danger', e($e->getMessage()));
        }
        redirect(admin_url('cash'));
    }

    public function reissue(string $id): void
    {
        Acl::require('cash.view');
        try {
            CashBook::reissue((int)$id, (int)$this->user['id']);
            flash('success', 'A new code is on their screen.');
        } catch (\Throwable $e) {
            flash('danger', e($e->getMessage()));
        }
        redirect(admin_url('cash'));
    }
}
