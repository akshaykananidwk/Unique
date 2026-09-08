<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\DB;
use App\Models\CashBook;
use App\Models\Designers;

class DashboardController extends Controller
{
    public function index(): void
    {
        Acl::require('dashboard.view');
        [$bw, $bp, $selectedBranch] = $this->branchScope('o.branch_id');
        $today = date('Y-m-d');
        $ot = tbl('orders');
        $pt = tbl('payments');
        $oit = tbl('order_items');

        $cards = [
            'orders_today' => (int)DB::val(
                "SELECT COUNT(*) FROM `$ot` o WHERE $bw AND DATE(o.order_date)=? AND o.deleted_at IS NULL",
                [...$bp, $today]),
            'value_today' => (float)DB::val(
                "SELECT COALESCE(SUM(o.total),0) FROM `$ot` o WHERE $bw AND DATE(o.order_date)=? AND o.deleted_at IS NULL AND o.is_cancelled=0",
                [...$bp, $today]),
            'collection_today' => (float)DB::val(
                'SELECT COALESCE(SUM(p.amount),0) FROM `' . $pt . '` p JOIN `' . $ot . '` o ON o.id=p.order_id
                 WHERE ' . str_replace('o.branch_id', 'p.branch_id', $bw) . ' AND DATE(p.paid_at)=? AND p.deleted_at IS NULL',
                [...$bp, $today]),
            'pending_balance' => (float)DB::val(
                "SELECT COALESCE(SUM(o.balance_amount),0) FROM `$ot` o
                 WHERE $bw AND o.is_cancelled=0 AND o.deleted_at IS NULL AND o.balance_amount > 0",
                $bp),
            'in_progress' => (int)DB::val(
                "SELECT COUNT(*) FROM `$ot` o WHERE $bw AND o.deleted_at IS NULL
                 AND o.status NOT IN ('delivered','completed','cancelled')",
                $bp),
            'overdue' => (int)DB::val(
                "SELECT COUNT(*) FROM `$ot` o WHERE $bw AND o.deleted_at IS NULL
                 AND o.due_date < NOW() AND o.status NOT IN ('delivered','completed','cancelled')",
                $bp),
            'awaiting_approval' => (int)DB::val(
                "SELECT COUNT(DISTINCT o.id) FROM `$ot` o JOIN `$oit` oi ON oi.order_id=o.id
                 WHERE $bw AND o.deleted_at IS NULL AND oi.status='proof_sent'",
                $bp),
            'ready_for_delivery' => (int)DB::val(
                "SELECT COUNT(*) FROM `$ot` o WHERE $bw AND o.deleted_at IS NULL AND o.status='ready_for_delivery'",
                $bp),
        ];

        $byStage = DB::all(
            "SELECT o.status, COUNT(*) AS c FROM `$ot` o
             WHERE $bw AND o.deleted_at IS NULL AND o.status NOT IN ('completed','cancelled')
             GROUP BY o.status", $bp);

        $trend = DB::all(
            "SELECT DATE(o.order_date) AS d, COALESCE(SUM(o.total),0) AS v FROM `$ot` o
             WHERE $bw AND o.deleted_at IS NULL AND o.is_cancelled=0 AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
             GROUP BY DATE(o.order_date) ORDER BY d", $bp);

        $topItems = DB::all(
            "SELECT oi.item_name_snapshot AS name, SUM(oi.line_total) AS v, COUNT(*) AS c
             FROM `$oit` oi JOIN `$ot` o ON o.id=oi.order_id
             WHERE $bw AND o.deleted_at IS NULL AND o.is_cancelled=0
               AND o.order_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
             GROUP BY oi.item_name_snapshot ORDER BY v DESC LIMIT 10", $bp);

        $branchComparison = [];
        if ($this->user['role_slug'] === 'super_admin') {
            $branchComparison = DB::all(
                "SELECT b.name, COUNT(o.id) AS orders, COALESCE(SUM(o.total),0) AS value
                 FROM `" . tbl('branches') . "` b
                 LEFT JOIN `$ot` o ON o.branch_id=b.id AND o.deleted_at IS NULL AND o.is_cancelled=0
                   AND o.order_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                 WHERE b.is_active=1 GROUP BY b.id, b.name ORDER BY b.sort_order");
        }

        $attention = [
            'overdue' => DB::all(
                "SELECT o.id, o.job_no, o.due_date, o.status, c.name AS customer
                 FROM `$ot` o JOIN `" . tbl('customers') . "` c ON c.id=o.customer_id
                 WHERE $bw AND o.deleted_at IS NULL AND o.due_date < NOW()
                   AND o.status NOT IN ('delivered','completed','cancelled')
                 ORDER BY o.due_date LIMIT 10", $bp),
            'unviewed_proofs' => DB::all(
                "SELECT o.id, o.job_no, dp.sent_at, c.name AS customer
                 FROM `" . tbl('design_proofs') . "` dp
                 JOIN `$oit` oi ON oi.id=dp.order_item_id
                 JOIN `$ot` o ON o.id=oi.order_id
                 JOIN `" . tbl('customers') . "` c ON c.id=o.customer_id
                 WHERE $bw AND dp.status='pending' AND dp.viewed_at IS NULL
                   AND dp.sent_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                 ORDER BY dp.sent_at LIMIT 10", $bp),
            'stale_changes' => DB::all(
                "SELECT o.id, o.job_no, oi.updated_at, c.name AS customer
                 FROM `$oit` oi
                 JOIN `$ot` o ON o.id=oi.order_id
                 JOIN `" . tbl('customers') . "` c ON c.id=o.customer_id
                 WHERE $bw AND oi.status='design_in_progress' AND oi.revision_count > 0
                   AND oi.updated_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)
                 ORDER BY oi.updated_at LIMIT 10", $bp),
            'needs_review' => DB::all(
                "SELECT o.id, o.job_no, o.created_at, c.name AS customer
                 FROM `$ot` o JOIN `" . tbl('customers') . "` c ON c.id=o.customer_id
                 WHERE $bw AND o.needs_review=1 AND o.deleted_at IS NULL AND o.is_cancelled=0
                 ORDER BY o.created_at LIMIT 10", $bp),
        ];

        $designerLoad = DB::all(
            "SELECT u.name, COUNT(oi.id) AS open_jobs
             FROM `" . tbl('users') . "` u
             LEFT JOIN `$oit` oi ON oi.assigned_designer_id=u.id
               AND oi.status IN ('design_pending','design_in_progress','proof_sent','change_requested')
             WHERE u.is_active=1 AND u.deleted_at IS NULL AND " . Designers::sqlCanDesign('u') . "
             GROUP BY u.id, u.name ORDER BY open_jobs DESC");

        $branches = DB::all('SELECT id, name FROM `' . tbl('branches') . '` WHERE is_active=1 ORDER BY sort_order');

        // ---- Performance panels: this month, user-wise and designer-wise ----
        $monthStart = date('Y-m-01 00:00:00');
        $pt = tbl('payments');

        $userWise = DB::all(
            "SELECT u.name,
                (SELECT COUNT(*) FROM `$ot` o WHERE o.taken_by_user_id=u.id AND o.deleted_at IS NULL
                   AND o.is_cancelled=0 AND o.order_date >= ?) AS orders,
                (SELECT COALESCE(SUM(o.total),0) FROM `$ot` o WHERE o.taken_by_user_id=u.id AND o.deleted_at IS NULL
                   AND o.is_cancelled=0 AND o.order_date >= ?) AS order_value,
                (SELECT COALESCE(SUM(p.amount),0) FROM `$pt` p WHERE p.received_by_user_id=u.id
                   AND p.deleted_at IS NULL AND p.type='advance' AND p.paid_at >= ?) AS advance,
                (SELECT COALESCE(SUM(p.amount),0) FROM `$pt` p WHERE p.received_by_user_id=u.id
                   AND p.deleted_at IS NULL AND p.paid_at >= ?) AS collection,
                (SELECT COALESCE(SUM(o.balance_amount),0) FROM `$ot` o WHERE o.taken_by_user_id=u.id
                   AND o.deleted_at IS NULL AND o.is_cancelled=0) AS pending
             FROM `" . tbl('users') . "` u
             WHERE u.deleted_at IS NULL AND u.is_active=1
             HAVING orders > 0 OR collection > 0 OR pending > 0
             ORDER BY order_value DESC LIMIT 10",
            [$monthStart, $monthStart, $monthStart, $monthStart]
        );

        $designerWise = DB::all(
            "SELECT u.name,
                (SELECT COUNT(*) FROM `" . tbl('design_proofs') . "` dp WHERE dp.uploaded_by_user_id=u.id
                   AND dp.created_at >= ?) AS designs,
                (SELECT COALESCE(SUM(oi.line_total),0) FROM `$oit` oi JOIN `$ot` o ON o.id=oi.order_id
                   WHERE oi.assigned_designer_id=u.id AND o.deleted_at IS NULL AND o.is_cancelled=0
                   AND oi.status <> 'cancelled' AND o.order_date >= ?) AS value_handled
             FROM `" . tbl('users') . "` u
             WHERE u.deleted_at IS NULL AND u.is_active=1 AND " . Designers::sqlCanDesign('u') . "
             ORDER BY value_handled DESC LIMIT 10",
            [$monthStart, $monthStart]
        );

        $overall = [
            'pending_payment' => (float)DB::val(
                "SELECT COALESCE(SUM(o.balance_amount),0) FROM `$ot` o
                 WHERE $bw AND o.deleted_at IS NULL AND o.is_cancelled=0", $bp),
            'received_payment' => (float)DB::val(
                "SELECT COALESCE(SUM(p.amount),0) FROM `$pt` p JOIN `$ot` o ON o.id=p.order_id
                 WHERE $bw AND p.deleted_at IS NULL AND p.paid_at >= ?", [...$bp, $monthStart]),
            'running_orders' => (int)DB::val(
                "SELECT COUNT(*) FROM `$ot` o WHERE $bw AND o.deleted_at IS NULL AND o.is_cancelled=0
                 AND o.status NOT IN ('delivered','completed')", $bp),
            'completed_orders' => (int)DB::val(
                "SELECT COUNT(*) FROM `$ot` o WHERE $bw AND o.deleted_at IS NULL AND o.is_cancelled=0
                 AND o.status IN ('delivered','completed')", $bp),
        ];

        // Cash the person looking at this screen is holding, and anything waiting on them
        // to read a code out. Money sitting in a pocket is easy to forget about.
        $myCash = Acl::can('cash.view') ? CashBook::inHand((int)$this->user['id']) : null;
        $cashWaiting = Acl::can('cash.view') ? CashBook::waitingForMe((int)$this->user['id']) : [];

        $this->render('dashboard/index', compact(
            'cards', 'byStage', 'trend', 'topItems', 'branchComparison',
            'attention', 'designerLoad', 'branches', 'selectedBranch',
            'userWise', 'designerWise', 'overall', 'myCash', 'cashWaiting'
        ));
    }
}
