<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\DB;
use App\Models\OrderService;
use App\Models\ProofService;

class DesignerController extends Controller
{
    /** Kanban: To Do → In Progress → Proof Sent → Changes Requested → Approved. */
    public function myJobs(): void
    {
        Acl::require('design.view');
        $designerId = (int)$this->user['id'];
        // Managers see every designer's board; everyone else sees their own work.
        $seeAll = Acl::can('order.assign');

        $sql = 'SELECT oi.*, o.job_no, o.priority, o.customer_note, o.id AS order_id,
                       c.name AS customer_name, c.phone AS customer_phone
                FROM `' . tbl('order_items') . '` oi
                JOIN `' . tbl('orders') . '` o ON o.id = oi.order_id
                JOIN `' . tbl('customers') . "` c ON c.id = o.customer_id
                WHERE oi.requires_design = 1 AND o.deleted_at IS NULL AND o.is_cancelled = 0
                  AND oi.status IN ('design_pending','design_in_progress','proof_sent','change_requested','design_approved')";
        $params = [];
        if (!$seeAll) {
            $sql .= ' AND oi.assigned_designer_id = ?';
            $params[] = $designerId;
        }
        $sql .= ' ORDER BY oi.due_date ASC';
        $jobs = DB::all($sql, $params);

        // The shared board: design work nobody has accepted yet. Anyone who may accept sees
        // it, and whoever presses Accept owns it from that moment.
        $unclaimed = Acl::can('design.claim') ? DB::all(
            'SELECT oi.*, o.job_no, o.priority, o.id AS order_id,
                    c.name AS customer_name, c.phone AS customer_phone
             FROM `' . tbl('order_items') . '` oi
             JOIN `' . tbl('orders') . '` o ON o.id = oi.order_id
             JOIN `' . tbl('customers') . "` c ON c.id = o.customer_id
             WHERE oi.requires_design = 1 AND oi.assigned_designer_id IS NULL
               AND o.deleted_at IS NULL AND o.is_cancelled = 0
               AND oi.status IN ('design_pending','design_in_progress','change_requested')
             ORDER BY o.priority = 'urgent' DESC, o.priority = 'rush' DESC, oi.due_date ASC"
        ) : [];

        foreach ($jobs as &$job) {
            $job['proofs'] = DB::all(
                'SELECT * FROM `' . tbl('design_proofs') . '` WHERE order_item_id = ? ORDER BY version DESC',
                [(int)$job['id']]
            );
            foreach ($job['proofs'] as &$proof) {
                $proof['feedback'] = DB::all(
                    'SELECT * FROM `' . tbl('proof_feedback') . '` WHERE proof_id = ? ORDER BY created_at DESC',
                    [(int)$proof['id']]
                );
            }
            unset($proof);
            $job['attachments'] = DB::all(
                'SELECT * FROM `' . tbl('order_attachments') . '` WHERE order_item_id = ? OR (order_id = ? AND order_item_id IS NULL)',
                [(int)$job['id'], (int)$job['order_id']]
            );
        }
        unset($job);

        $columns = [
            'design_pending' => ['label' => 'To Do', 'jobs' => []],
            'design_in_progress' => ['label' => 'In Progress', 'jobs' => []],
            'proof_sent' => ['label' => 'Proof Sent', 'jobs' => []],
            'change_requested' => ['label' => 'Changes Requested', 'jobs' => []],
            'design_approved' => ['label' => 'Approved', 'jobs' => []],
        ];
        foreach ($jobs as $job) {
            $columns[$job['status']]['jobs'][] = $job;
        }
        $this->render('designer/my_jobs', compact('columns', 'seeAll', 'unclaimed'));
    }

    /** Accept a job from the shared board — it becomes mine. */
    public function claim(string $itemId): void
    {
        Acl::require('design.claim');
        $result = OrderService::claimDesign((int)$itemId, (int)$this->user['id']);
        flash($result['ok'] ? 'success' : 'danger',
            $result['ok'] ? 'Accepted — this job is yours now.' : $result['error']);
        redirect(admin_url('my-jobs'));
    }

    /** Put a job back on the board. */
    public function release(string $itemId): void
    {
        Acl::require('design.claim');
        $result = OrderService::releaseDesign(
            (int)$itemId,
            (int)$this->user['id'],
            Acl::can('order.assign')
        );
        flash($result['ok'] ? 'success' : 'danger',
            $result['ok'] ? 'Put back on the board for someone else.' : $result['error']);
        redirect(admin_url('my-jobs'));
    }

    public function start(string $itemId): void
    {
        Acl::require('design.view');
        $item = $this->findAssigned((int)$itemId);
        $result = OrderService::changeItemStatus((int)$itemId, 'design_in_progress', (int)$this->user['id'], 'Designer started work');
        flash($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Job started.' : $result['error']);
        redirect(admin_url('my-jobs'));
    }

    public function uploadProof(string $itemId): void
    {
        Acl::require('design.upload');
        $this->findAssigned((int)$itemId);
        if (empty($_FILES['proof']['name'])) {
            flash('danger', 'Choose a JPG, PNG or PDF proof file first.');
            redirect(admin_url('my-jobs'));
        }
        $result = ProofService::upload(
            (int)$itemId,
            $_FILES['proof'],
            trim((string)($_POST['designer_note'] ?? '')),
            (int)$this->user['id']
        );
        flash($result['ok'] ? 'success' : 'danger',
            $result['ok'] ? 'Proof v' . $result['version'] . ' uploaded — the customer has been sent the approval link.' : $result['error']);
        redirect(admin_url('my-jobs'));
    }

    private function findAssigned(int $itemId): array
    {
        $item = DB::get('SELECT * FROM `' . tbl('order_items') . '` WHERE id = ?', [$itemId]);
        if (!$item) {
            abort(404, 'Job not found.');
        }
        // You work on your own jobs. Whoever can reassign work (a manager) may touch any —
        // the rule follows the permission, not a job title, because anyone can design now.
        $isManager = Acl::can('order.assign');
        if (!$isManager && (int)$item['assigned_designer_id'] !== (int)$this->user['id']) {
            abort(403, 'This job is not assigned to you.');
        }
        return $item;
    }
}
