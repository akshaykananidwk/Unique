<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\DB;
use App\Core\WaEvents;

class ReportController extends Controller
{
    public function index(): void
    {
        Acl::require('dashboard.view');
        $this->render('reports/index');
    }

    /** Staff performance — the core report. */
    /**
     * User performance — what each person brought in and what they collected.
     * Orders taken (count + value) · advance taken · still pending on their orders · recovered.
     */
    public function users(): void
    {
        Acl::require('report.staff');
        [$from, $to] = $this->dateRange();
        $ot = tbl('orders');
        $pt = tbl('payments');
        $ut = tbl('users');

        $rows = DB::all(
            "SELECT u.id, u.name, r.name AS role_name,

                -- Orders this user TOOK in the period
                (SELECT COUNT(*) FROM `$ot` o WHERE o.taken_by_user_id = u.id
                   AND o.deleted_at IS NULL AND o.is_cancelled = 0
                   AND o.order_date BETWEEN ? AND ?) AS orders_taken,
                (SELECT COALESCE(SUM(o.total),0) FROM `$ot` o WHERE o.taken_by_user_id = u.id
                   AND o.deleted_at IS NULL AND o.is_cancelled = 0
                   AND o.order_date BETWEEN ? AND ?) AS order_value,

                -- Orders this user ACCEPTED in the period
                (SELECT COUNT(*) FROM `$ot` o WHERE o.accepted_by_user_id = u.id
                   AND o.deleted_at IS NULL AND o.is_cancelled = 0
                   AND o.order_date BETWEEN ? AND ?) AS orders_accepted,

                -- Money this user took at the counter
                (SELECT COALESCE(SUM(p.amount),0) FROM `$pt` p WHERE p.received_by_user_id = u.id
                   AND p.deleted_at IS NULL AND p.type = 'advance'
                   AND p.paid_at BETWEEN ? AND ?) AS advance_taken,
                (SELECT COALESCE(SUM(p.amount),0) FROM `$pt` p WHERE p.received_by_user_id = u.id
                   AND p.deleted_at IS NULL AND p.type <> 'advance'
                   AND p.paid_at BETWEEN ? AND ?) AS recovered,
                (SELECT COALESCE(SUM(p.amount),0) FROM `$pt` p WHERE p.received_by_user_id = u.id
                   AND p.deleted_at IS NULL
                   AND p.paid_at BETWEEN ? AND ?) AS collected_total,

                -- Still owed on the orders this user took (live figure, not period-bound)
                (SELECT COALESCE(SUM(o.balance_amount),0) FROM `$ot` o WHERE o.taken_by_user_id = u.id
                   AND o.deleted_at IS NULL AND o.is_cancelled = 0) AS pending_amount

             FROM `$ut` u JOIN `" . tbl('roles') . "` r ON r.id = u.role_id
             WHERE u.deleted_at IS NULL
             ORDER BY order_value DESC, u.name",
            [$from, $to, $from, $to, $from, $to, $from, $to, $from, $to, $from, $to]
        );
        $rows = array_values(array_filter($rows, fn($r) =>
            (int)$r['orders_taken'] || (int)$r['orders_accepted'] || (float)$r['collected_total'] || (float)$r['pending_amount']));

        $totals = [
            'orders_taken' => array_sum(array_column($rows, 'orders_taken')),
            'order_value' => array_sum(array_map('floatval', array_column($rows, 'order_value'))),
            'advance_taken' => array_sum(array_map('floatval', array_column($rows, 'advance_taken'))),
            'recovered' => array_sum(array_map('floatval', array_column($rows, 'recovered'))),
            'collected_total' => array_sum(array_map('floatval', array_column($rows, 'collected_total'))),
            'pending_amount' => array_sum(array_map('floatval', array_column($rows, 'pending_amount'))),
        ];

        if (($_GET['export'] ?? '') === 'csv') {
            $this->csv('user-performance',
                ['User', 'Role', 'Orders Taken', 'Order Value', 'Orders Accepted', 'Advance Taken', 'Recovered', 'Total Collected', 'Pending'],
                array_map(fn($r) => [$r['name'], $r['role_name'], $r['orders_taken'], $r['order_value'],
                    $r['orders_accepted'], $r['advance_taken'], $r['recovered'], $r['collected_total'], $r['pending_amount']], $rows));
        }
        $this->render('reports/users', compact('rows', 'totals', 'from', 'to'));
    }

    /**
     * Designer performance — how much design work each person actually did.
     * Designs made (proof versions) · jobs worked on · orders touched · value handled.
     */
    public function designers(): void
    {
        Acl::require('report.staff');
        [$from, $to] = $this->dateRange();
        $ot = tbl('orders');
        $oit = tbl('order_items');
        $dpt = tbl('design_proofs');

        $rows = DB::all(
            "SELECT u.id, u.name,

                -- Proof versions this designer uploaded in the period
                (SELECT COUNT(*) FROM `$dpt` dp WHERE dp.uploaded_by_user_id = u.id
                   AND dp.created_at BETWEEN ? AND ?) AS designs_made,

                -- Of those, how many were approved by the customer
                (SELECT COUNT(*) FROM `$dpt` dp WHERE dp.uploaded_by_user_id = u.id
                   AND dp.status = 'approved' AND dp.created_at BETWEEN ? AND ?) AS designs_approved,

                -- Jobs and orders assigned to this designer in the period
                (SELECT COUNT(*) FROM `$oit` oi JOIN `$ot` o ON o.id = oi.order_id
                   WHERE oi.assigned_designer_id = u.id AND o.deleted_at IS NULL AND o.is_cancelled = 0
                   AND o.order_date BETWEEN ? AND ?) AS jobs_handled,
                (SELECT COUNT(DISTINCT oi.order_id) FROM `$oit` oi JOIN `$ot` o ON o.id = oi.order_id
                   WHERE oi.assigned_designer_id = u.id AND o.deleted_at IS NULL AND o.is_cancelled = 0
                   AND o.order_date BETWEEN ? AND ?) AS orders_handled,

                -- Value of the lines this designer handled
                (SELECT COALESCE(SUM(oi.line_total),0) FROM `$oit` oi JOIN `$ot` o ON o.id = oi.order_id
                   WHERE oi.assigned_designer_id = u.id AND o.deleted_at IS NULL AND o.is_cancelled = 0
                   AND oi.status <> 'cancelled' AND o.order_date BETWEEN ? AND ?) AS value_handled,

                -- Still on their desk right now
                (SELECT COUNT(*) FROM `$oit` oi JOIN `$ot` o ON o.id = oi.order_id
                   WHERE oi.assigned_designer_id = u.id AND o.deleted_at IS NULL
                   AND oi.status IN ('design_pending','design_in_progress','proof_sent','change_requested')) AS open_now

             FROM `" . tbl('users') . "` u JOIN `" . tbl('roles') . "` r ON r.id = u.role_id
             WHERE u.deleted_at IS NULL AND r.slug = 'designer'
             ORDER BY value_handled DESC, u.name",
            [$from, $to, $from, $to, $from, $to, $from, $to, $from, $to]
        );

        $totals = [
            'designs_made' => array_sum(array_column($rows, 'designs_made')),
            'jobs_handled' => array_sum(array_column($rows, 'jobs_handled')),
            'orders_handled' => array_sum(array_column($rows, 'orders_handled')),
            'value_handled' => array_sum(array_map('floatval', array_column($rows, 'value_handled'))),
        ];

        if (($_GET['export'] ?? '') === 'csv') {
            $this->csv('designer-performance',
                ['Designer', 'Designs Made', 'Approved', 'Jobs Handled', 'Orders Handled', 'Value Handled', 'Open Now'],
                array_map(fn($r) => [$r['name'], $r['designs_made'], $r['designs_approved'], $r['jobs_handled'],
                    $r['orders_handled'], $r['value_handled'], $r['open_now']], $rows));
        }
        $this->render('reports/designers', compact('rows', 'totals', 'from', 'to'));
    }

    public function staff(): void
    {
        Acl::require('report.staff');
        [$from, $to] = $this->dateRange();
        [$bw, $bp, $selectedBranch] = $this->branchScope('o.branch_id');
        $ot = tbl('orders');
        $pt = tbl('payments');
        $oit = tbl('order_items');
        $dpt = tbl('design_proofs');

        $users = DB::all(
            'SELECT u.id, u.name, r.name AS role_name, r.slug AS role_slug
             FROM `' . tbl('users') . '` u JOIN `' . tbl('roles') . '` r ON r.id = u.role_id
             WHERE u.deleted_at IS NULL AND r.slug != ? ORDER BY u.name',
            ['customer']
        );

        $rows = [];
        foreach ($users as $staff) {
            $uid = (int)$staff['id'];
            $orderStats = DB::get(
                "SELECT COUNT(*) AS cnt, COALESCE(SUM(o.total),0) AS value
                 FROM `$ot` o WHERE $bw AND o.taken_by_user_id = ? AND o.deleted_at IS NULL AND o.is_cancelled = 0
                 AND o.order_date BETWEEN ? AND ?",
                [...$bp, $uid, $from, $to]
            );
            $advance = (float)DB::val(
                "SELECT COALESCE(SUM(p.amount),0) FROM `$pt` p JOIN `$ot` o ON o.id = p.order_id
                 WHERE $bw AND p.received_by_user_id = ? AND p.type = 'advance' AND p.deleted_at IS NULL
                 AND p.paid_at BETWEEN ? AND ?",
                [...$bp, $uid, $from, $to]
            );
            $collections = (float)DB::val(
                "SELECT COALESCE(SUM(p.amount),0) FROM `$pt` p JOIN `$ot` o ON o.id = p.order_id
                 WHERE $bw AND p.received_by_user_id = ? AND p.deleted_at IS NULL
                 AND p.paid_at BETWEEN ? AND ?",
                [...$bp, $uid, $from, $to]
            );
            // Designs completed = items this designer got approved in range
            $designStats = DB::get(
                "SELECT COUNT(DISTINCT dp.order_item_id) AS completed,
                        AVG(TIMESTAMPDIFF(HOUR, oi.designer_assigned_at, dp.responded_at)) AS avg_hours
                 FROM `$dpt` dp
                 JOIN `$oit` oi ON oi.id = dp.order_item_id
                 JOIN `$ot` o ON o.id = oi.order_id
                 WHERE $bw AND oi.assigned_designer_id = ? AND dp.status = 'approved' AND dp.approval_confirmed = 1
                 AND dp.responded_at BETWEEN ? AND ?",
                [...$bp, $uid, $from, $to]
            );
            $revisionStats = DB::get(
                "SELECT AVG(oi.revision_count) AS avg_rev,
                        SUM(CASE WHEN oi.revision_count = 0 THEN 1 ELSE 0 END) AS first_time,
                        COUNT(*) AS total
                 FROM `$oit` oi JOIN `$ot` o ON o.id = oi.order_id
                 WHERE $bw AND oi.assigned_designer_id = ?
                 AND oi.status IN ('design_approved','ready_for_print','printing','post_press','quality_check','ready_for_delivery','out_for_delivery','delivered','completed')
                 AND oi.updated_at BETWEEN ? AND ?",
                [...$bp, $uid, $from, $to]
            );
            $onTime = DB::get(
                "SELECT SUM(CASE WHEN o.delivered_at <= o.due_date THEN 1 ELSE 0 END) AS on_time, COUNT(*) AS total
                 FROM `$ot` o WHERE $bw AND o.taken_by_user_id = ? AND o.delivered_at IS NOT NULL
                 AND o.delivered_at BETWEEN ? AND ?",
                [...$bp, $uid, $from, $to]
            );
            $pendingNow = (int)DB::val(
                "SELECT COUNT(*) FROM `$oit` oi JOIN `$ot` o ON o.id = oi.order_id
                 WHERE $bw AND (oi.assigned_designer_id = ? OR o.taken_by_user_id = ?)
                 AND oi.status NOT IN ('delivered','completed','cancelled') AND o.deleted_at IS NULL",
                [...$bp, $uid, $uid]
            );
            $overdueNow = (int)DB::val(
                "SELECT COUNT(*) FROM `$oit` oi JOIN `$ot` o ON o.id = oi.order_id
                 WHERE $bw AND (oi.assigned_designer_id = ? OR o.taken_by_user_id = ?)
                 AND oi.due_date < NOW() AND oi.status NOT IN ('delivered','completed','cancelled') AND o.deleted_at IS NULL",
                [...$bp, $uid, $uid]
            );

            $row = [
                'name' => $staff['name'],
                'role' => $staff['role_name'],
                'orders_taken' => (int)$orderStats['cnt'],
                'order_value' => (float)$orderStats['value'],
                'advance_collected' => $advance,
                'total_collections' => $collections,
                'designs_completed' => (int)($designStats['completed'] ?? 0),
                'avg_turnaround_hours' => $designStats['avg_hours'] !== null ? round((float)$designStats['avg_hours'], 1) : null,
                'avg_revisions' => $revisionStats['avg_rev'] !== null ? round((float)$revisionStats['avg_rev'], 2) : null,
                'first_time_approval' => (int)($revisionStats['total'] ?? 0) > 0
                    ? round((int)$revisionStats['first_time'] * 100 / (int)$revisionStats['total'], 1) : null,
                'on_time_percent' => (int)($onTime['total'] ?? 0) > 0
                    ? round((int)$onTime['on_time'] * 100 / (int)$onTime['total'], 1) : null,
                'pending_now' => $pendingNow,
                'overdue_now' => $overdueNow,
            ];
            if ($row['orders_taken'] || $row['designs_completed'] || $row['total_collections'] || $pendingNow) {
                $rows[] = $row;
            }
        }

        if (($_GET['export'] ?? '') === 'csv') {
            $this->csv('staff-performance', [
                'Staff', 'Role', 'Orders Taken', 'Order Value', 'Advance Collected', 'Total Collections',
                'Designs Completed', 'Avg Turnaround (h)', 'Avg Revisions', 'First-time Approval %',
                'On-time %', 'Pending Now', 'Overdue Now',
            ], array_map(fn($r) => array_values($r), $rows));
        }

        // Month-by-month per staff (orders + collections) for the comparison chart
        $monthly = DB::all(
            "SELECT DATE_FORMAT(o.order_date,'%Y-%m') AS ym, u.name, COUNT(*) AS orders, COALESCE(SUM(o.total),0) AS value
             FROM `$ot` o JOIN `" . tbl('users') . "` u ON u.id = o.taken_by_user_id
             WHERE $bw AND o.deleted_at IS NULL AND o.is_cancelled = 0
             AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
             GROUP BY ym, u.id, u.name ORDER BY ym",
            $bp
        );
        $branches = DB::all('SELECT id, name FROM `' . tbl('branches') . '` WHERE is_active = 1 ORDER BY sort_order');
        $this->render('reports/staff', compact('rows', 'monthly', 'from', 'to', 'branches', 'selectedBranch'));
    }

    public function sales(): void
    {
        Acl::require('report.sales');
        [$from, $to] = $this->dateRange();
        [$bw, $bp, $selectedBranch] = $this->branchScope('o.branch_id');
        $group = in_array($_GET['group'] ?? '', ['order', 'item', 'category'], true) ? $_GET['group'] : 'order';
        $ot = tbl('orders');
        $oit = tbl('order_items');

        if ($group === 'order') {
            $rows = DB::all(
                'SELECT o.job_no, o.order_date, c.name AS customer, b.name AS branch, o.subtotal, o.discount_amount,
                        o.tax_amount, o.total, o.paid_amount, o.balance_amount, o.status
                 FROM `' . $ot . '` o
                 JOIN `' . tbl('customers') . '` c ON c.id = o.customer_id
                 JOIN `' . tbl('branches') . "` b ON b.id = o.branch_id
                 WHERE $bw AND o.deleted_at IS NULL AND o.is_cancelled = 0 AND o.order_date BETWEEN ? AND ?
                 ORDER BY o.order_date",
                [...$bp, $from, $to]
            );
            $headers = ['Job No', 'Date', 'Customer', 'Branch', 'Subtotal', 'Discount', 'Tax', 'Total', 'Paid', 'Balance', 'Status'];
        } elseif ($group === 'item') {
            $rows = DB::all(
                "SELECT oi.item_name_snapshot AS item, COUNT(*) AS lines, SUM(oi.qty) AS qty,
                        SUM(oi.amount) AS amount, SUM(oi.tax_amount) AS tax, SUM(oi.line_total) AS total
                 FROM `$oit` oi JOIN `$ot` o ON o.id = oi.order_id
                 WHERE $bw AND o.deleted_at IS NULL AND o.is_cancelled = 0 AND o.order_date BETWEEN ? AND ?
                 GROUP BY oi.item_name_snapshot ORDER BY total DESC",
                [...$bp, $from, $to]
            );
            $headers = ['Item', 'Lines', 'Qty', 'Amount', 'Tax', 'Total'];
        } else {
            $rows = DB::all(
                "SELECT oi.category_name_snapshot AS category, COUNT(*) AS lines, SUM(oi.qty) AS qty,
                        SUM(oi.amount) AS amount, SUM(oi.tax_amount) AS tax, SUM(oi.line_total) AS total
                 FROM `$oit` oi JOIN `$ot` o ON o.id = oi.order_id
                 WHERE $bw AND o.deleted_at IS NULL AND o.is_cancelled = 0 AND o.order_date BETWEEN ? AND ?
                 GROUP BY oi.category_name_snapshot ORDER BY total DESC",
                [...$bp, $from, $to]
            );
            $headers = ['Category', 'Lines', 'Qty', 'Amount', 'Tax', 'Total'];
        }

        if (($_GET['export'] ?? '') === 'csv') {
            $this->csv('sales-' . $group, $headers, array_map('array_values', $rows));
        }
        $branches = DB::all('SELECT id, name FROM `' . tbl('branches') . '` WHERE is_active = 1 ORDER BY sort_order');
        $this->render('reports/sales', compact('rows', 'headers', 'group', 'from', 'to', 'branches', 'selectedBranch'));
    }

    public function outstanding(): void
    {
        Acl::require('report.outstanding');
        [$bw, $bp, $selectedBranch] = $this->branchScope('o.branch_id');
        $bucket = (string)($_GET['bucket'] ?? '');
        $bucketSql = match ($bucket) {
            '0-7' => 'AND DATEDIFF(NOW(), o.order_date) BETWEEN 0 AND 7',
            '8-15' => 'AND DATEDIFF(NOW(), o.order_date) BETWEEN 8 AND 15',
            '16-30' => 'AND DATEDIFF(NOW(), o.order_date) BETWEEN 16 AND 30',
            '30+' => 'AND DATEDIFF(NOW(), o.order_date) > 30',
            default => '',
        };
        $rows = DB::all(
            'SELECT o.id, o.job_no, o.order_date, o.total, o.paid_amount, o.balance_amount, o.status,
                    DATEDIFF(NOW(), o.order_date) AS age_days,
                    c.id AS customer_id, c.name AS customer, c.phone, b.name AS branch
             FROM `' . tbl('orders') . '` o
             JOIN `' . tbl('customers') . '` c ON c.id = o.customer_id
             JOIN `' . tbl('branches') . "` b ON b.id = o.branch_id
             WHERE $bw AND o.deleted_at IS NULL AND o.is_cancelled = 0 AND o.balance_amount > 0 $bucketSql
             ORDER BY o.balance_amount DESC",
            $bp
        );
        if (($_GET['export'] ?? '') === 'csv') {
            $this->csv('outstanding', ['Job No', 'Date', 'Customer', 'Phone', 'Branch', 'Total', 'Paid', 'Balance', 'Age (days)'],
                array_map(fn($r) => [$r['job_no'], $r['order_date'], $r['customer'], $r['phone'], $r['branch'],
                    $r['total'], $r['paid_amount'], $r['balance_amount'], $r['age_days']], $rows));
        }
        $branches = DB::all('SELECT id, name FROM `' . tbl('branches') . '` WHERE is_active = 1 ORDER BY sort_order');
        $this->render('reports/outstanding', compact('rows', 'bucket', 'branches', 'selectedBranch'));
    }

    /** Bulk WhatsApp balance reminders for selected orders. */
    public function sendReminders(): void
    {
        Acl::require('report.outstanding');
        $ids = array_map('intval', (array)($_POST['order_ids'] ?? []));
        $count = 0;
        foreach ($ids as $orderId) {
            $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ? AND deleted_at IS NULL', [$orderId]);
            if ($order && (float)$order['balance_amount'] > 0 && \App\Core\Acl::canAccessBranch((int)$order['branch_id'])) {
                WaEvents::balanceReminder($orderId);
                $count++;
            }
        }
        flash('success', "Queued balance reminders for $count order(s).");
        redirect(admin_url('reports/outstanding'));
    }

    public function gst(): void
    {
        Acl::require('report.sales');
        [$from, $to] = $this->dateRange();
        [$bw, $bp, $selectedBranch] = $this->branchScope('o.branch_id');
        $rows = DB::all(
            'SELECT oi.tax_percent, COUNT(*) AS lines, SUM(oi.amount) AS taxable, SUM(oi.tax_amount) AS tax,
                    SUM(oi.line_total) AS total
             FROM `' . tbl('order_items') . '` oi JOIN `' . tbl('orders') . "` o ON o.id = oi.order_id
             WHERE $bw AND o.deleted_at IS NULL AND o.is_cancelled = 0 AND o.order_date BETWEEN ? AND ?
             GROUP BY oi.tax_percent ORDER BY oi.tax_percent",
            [...$bp, $from, $to]
        );
        if (($_GET['export'] ?? '') === 'csv') {
            $this->csv('gst-summary', ['Tax %', 'Lines', 'Taxable Value', 'Tax Amount', 'Total'], array_map('array_values', $rows));
        }
        $branches = DB::all('SELECT id, name FROM `' . tbl('branches') . '` WHERE is_active = 1 ORDER BY sort_order');
        $this->render('reports/gst', compact('rows', 'from', 'to', 'branches', 'selectedBranch'));
    }

    /** Which items cause the most design rework. */
    public function revisions(): void
    {
        Acl::require('report.sales');
        [$from, $to] = $this->dateRange();
        [$bw, $bp, $selectedBranch] = $this->branchScope('o.branch_id');
        $rows = DB::all(
            'SELECT oi.item_name_snapshot AS item, COUNT(*) AS jobs, SUM(oi.revision_count) AS revisions,
                    AVG(oi.revision_count) AS avg_revisions, MAX(oi.revision_count) AS max_revisions
             FROM `' . tbl('order_items') . '` oi JOIN `' . tbl('orders') . "` o ON o.id = oi.order_id
             WHERE $bw AND oi.requires_design = 1 AND o.deleted_at IS NULL AND o.order_date BETWEEN ? AND ?
             GROUP BY oi.item_name_snapshot HAVING revisions > 0 ORDER BY avg_revisions DESC",
            [...$bp, $from, $to]
        );
        if (($_GET['export'] ?? '') === 'csv') {
            $this->csv('revision-analysis', ['Item', 'Jobs', 'Total Revisions', 'Avg Revisions', 'Max Revisions'],
                array_map('array_values', $rows));
        }
        $branches = DB::all('SELECT id, name FROM `' . tbl('branches') . '` WHERE is_active = 1 ORDER BY sort_order');
        $this->render('reports/revisions', compact('rows', 'from', 'to', 'branches', 'selectedBranch'));
    }

    public function cancelled(): void
    {
        Acl::require('report.sales');
        [$from, $to] = $this->dateRange();
        [$bw, $bp, $selectedBranch] = $this->branchScope('o.branch_id');
        $rows = DB::all(
            'SELECT o.job_no, o.order_date, o.cancelled_at, o.cancelled_reason, o.total,
                    c.name AS customer, u.name AS cancelled_by_name, b.name AS branch
             FROM `' . tbl('orders') . '` o
             JOIN `' . tbl('customers') . '` c ON c.id = o.customer_id
             JOIN `' . tbl('branches') . '` b ON b.id = o.branch_id
             LEFT JOIN `' . tbl('users') . "` u ON u.id = o.cancelled_by
             WHERE $bw AND o.is_cancelled = 1 AND o.cancelled_at BETWEEN ? AND ?
             ORDER BY o.cancelled_at DESC",
            [...$bp, $from, $to]
        );
        if (($_GET['export'] ?? '') === 'csv') {
            $this->csv('cancelled-orders', ['Job No', 'Order Date', 'Cancelled At', 'Reason', 'Value', 'Customer', 'By', 'Branch'],
                array_map('array_values', $rows));
        }
        $branches = DB::all('SELECT id, name FROM `' . tbl('branches') . '` WHERE is_active = 1 ORDER BY sort_order');
        $this->render('reports/cancelled', compact('rows', 'from', 'to', 'branches', 'selectedBranch'));
    }

    public function activity(): void
    {
        Acl::require('log.view');
        [$from, $to] = $this->dateRange();
        $module = trim((string)($_GET['module'] ?? ''));
        $where = ['a.created_at BETWEEN ? AND ?'];
        $params = [$from, $to];
        if ($module !== '') {
            $where[] = 'a.module = ?';
            $params[] = $module;
        }
        if (!empty($_GET['user_id'])) {
            $where[] = 'a.user_id = ?';
            $params[] = (int)$_GET['user_id'];
        }
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $whereSql = implode(' AND ', $where);
        $total = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('activity_logs') . "` a WHERE $whereSql", $params);
        $rows = DB::all(
            'SELECT a.*, u.name AS user_name FROM `' . tbl('activity_logs') . '` a
             LEFT JOIN `' . tbl('users') . "` u ON u.id = a.user_id
             WHERE $whereSql ORDER BY a.created_at DESC, a.id DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage),
            $params
        );
        $users = DB::all('SELECT id, name FROM `' . tbl('users') . '` WHERE deleted_at IS NULL ORDER BY name');
        $this->render('reports/activity', compact('rows', 'from', 'to', 'module', 'users', 'total', 'page', 'perPage'));
    }

    // ---------------------------------------------------------------- helpers

    /** [from,to] datetimes from ?range= presets or ?from/?to. */
    private function dateRange(): array
    {
        $range = (string)($_GET['range'] ?? 'this_month');
        $today = date('Y-m-d');
        [$from, $to] = match ($range) {
            'today' => [$today, $today],
            'yesterday' => [date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day'))],
            'this_week' => [date('Y-m-d', strtotime('monday this week')), $today],
            'last_month' => [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))],
            'this_year' => [date('Y-01-01'), $today],
            'custom' => [
                preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['from'] ?? '')) ? $_GET['from'] : date('Y-m-01'),
                preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['to'] ?? '')) ? $_GET['to'] : $today,
            ],
            default => [date('Y-m-01'), $today],
        };
        return [$from . ' 00:00:00', $to . ' 23:59:59'];
    }

    private function csv(string $name, array $headers, array $rows): never
    {
        Acl::require('report.export');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $name . '-' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, array_map(fn($v) => $v ?? '', $row));
        }
        fclose($out);
        exit;
    }
}
