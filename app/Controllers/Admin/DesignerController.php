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
        $seeAll = $this->user['role_slug'] !== 'designer' && Acl::can('order.assign');

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
        $this->render('designer/my_jobs', compact('columns', 'seeAll'));
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
        // Designers may only touch their own jobs; managers/admins may touch any
        if ($this->user['role_slug'] === 'designer' && (int)$item['assigned_designer_id'] !== (int)$this->user['id']) {
            abort(403, 'This job is not assigned to you.');
        }
        return $item;
    }
}
