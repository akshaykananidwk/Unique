<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use App\Core\Logger;
use App\Core\Settings;
use App\Core\Uploader;
use App\Core\WaEvents;

class ProofService
{
    /** Upload a new proof version for an order item. */
    public static function upload(int $orderItemId, array $file, string $designerNote, int $userId): array
    {
        $item = DB::get('SELECT * FROM `' . tbl('order_items') . '` WHERE id = ?', [$orderItemId]);
        if (!$item) {
            return ['ok' => false, 'error' => 'Job item not found.'];
        }
        if (!(bool)$item['requires_design']) {
            return ['ok' => false, 'error' => 'This item does not require design.'];
        }
        if (in_array($item['status'], ['design_approved', 'ready_for_print', 'printing', 'post_press',
            'quality_check', 'ready_for_delivery', 'out_for_delivery', 'delivered', 'completed', 'cancelled'], true)) {
            return ['ok' => false, 'error' => 'This job is already approved or in production — no new proofs allowed.'];
        }

        $maxMb = Settings::getInt('proof_max_upload_mb', 15);
        $stored = Uploader::store($file, 'proofs/' . date('Y/m'), Uploader::PROOFS, $maxMb);
        if (!$stored['ok']) {
            return $stored;
        }

        $version = (int)(DB::val(
            'SELECT COALESCE(MAX(version),0) FROM `' . tbl('design_proofs') . '` WHERE order_item_id = ?',
            [$orderItemId]
        ) ?? 0) + 1;

        // Supersede previous pending/change_requested proofs
        DB::run(
            'UPDATE `' . tbl('design_proofs') . '` SET status = ? WHERE order_item_id = ? AND status IN (?,?)',
            ['superseded', $orderItemId, 'pending', 'change_requested']
        );
        // A new artwork version invalidates any earlier "approved in person" — the customer
        // vouched for the OLD design, so this one needs approving again.
        OrderService::clearCounterApproval($orderItemId);

        $ext = strtolower(pathinfo((string)$stored['path'], PATHINFO_EXTENSION));
        $watermarked = null;
        $thumb = null;
        if ($ext !== 'pdf') {
            $watermarked = Uploader::watermark((string)$stored['path'], (string)Settings::get('business_name', 'PROOF'));
            $thumb = Uploader::thumbnail((string)$stored['path']);
        }

        $proofId = DB::insert('design_proofs', [
            'order_item_id' => $orderItemId,
            'version' => $version,
            'file_path' => $stored['path'],
            'watermarked_path' => $watermarked,
            'thumb_path' => $thumb,
            'file_type' => $ext,
            'proof_token' => random_token(24),
            'uploaded_by_user_id' => $userId,
            'designer_note' => $designerNote !== '' ? $designerNote : null,
            'status' => 'pending',
            'sent_at' => now(),
            'created_at' => now(),
        ]);

        OrderService::changeItemStatus($orderItemId, 'proof_sent', $userId, 'Proof v' . $version . ' uploaded');
        Logger::activity('design', 'upload_proof', 'design_proof', $proofId,
            'Proof v' . $version . ' uploaded for item #' . $orderItemId);
        WaEvents::proofSent($proofId);

        return ['ok' => true, 'proof_id' => $proofId, 'version' => $version];
    }

    /** Customer taps YES then confirms the "no further changes" modal. */
    public static function approve(int $proofId): array
    {
        $proof = DB::get('SELECT * FROM `' . tbl('design_proofs') . '` WHERE id = ?', [$proofId]);
        if (!$proof) {
            return ['ok' => false, 'error' => 'Proof not found.'];
        }
        if ($proof['status'] === 'approved') {
            return ['ok' => false, 'error' => 'This design is already approved.'];
        }
        if ($proof['status'] === 'superseded') {
            return ['ok' => false, 'error' => 'A newer version of this design exists. Please open the latest link.'];
        }
        DB::update('design_proofs', [
            'status' => 'approved',
            'approval_confirmed' => 1,
            'responded_at' => now(),
            'response_ip' => request_ip(),
            'response_user_agent' => request_agent(),
        ], ['id' => $proofId]);

        $itemId = (int)$proof['order_item_id'];
        OrderService::changeItemStatus($itemId, 'design_approved', null, 'Customer approved proof v' . $proof['version'], true);
        // Auto-advance to production queue
        OrderService::changeItemStatus($itemId, 'ready_for_print', null, 'Auto-moved after final approval', true);

        Logger::activity('design', 'customer_approved', 'design_proof', $proofId,
            'Customer approved proof v' . $proof['version'] . ' (IP ' . request_ip() . ')');
        WaEvents::proofApproved($proofId);
        return ['ok' => true];
    }

    /** Customer requests changes with typed text and/or a voice note. */
    public static function requestChange(
        int $proofId,
        string $feedbackText,
        ?array $voiceFile,
        ?array $referenceFile
    ): array {
        $proof = DB::get('SELECT * FROM `' . tbl('design_proofs') . '` WHERE id = ?', [$proofId]);
        if (!$proof) {
            return ['ok' => false, 'error' => 'Proof not found.'];
        }
        if ($proof['status'] === 'approved') {
            return ['ok' => false, 'error' => 'This design is already approved — changes are locked.'];
        }
        if ($proof['status'] === 'superseded') {
            return ['ok' => false, 'error' => 'A newer version of this design exists. Please open the latest link.'];
        }

        $voicePath = null;
        if ($voiceFile && ($voiceFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $stored = Uploader::store($voiceFile, 'voice/' . date('Y/m'), Uploader::AUDIO, 20);
            if ($stored['ok']) {
                $voicePath = $stored['path'];
            }
        }
        $refPath = null;
        if ($referenceFile && ($referenceFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $stored = Uploader::store($referenceFile, 'artwork/' . date('Y/m'), Uploader::IMAGES, 10);
            if ($stored['ok']) {
                $refPath = $stored['path'];
            }
        }

        $feedbackText = trim($feedbackText);
        if ($feedbackText === '' && !$voicePath) {
            return ['ok' => false, 'error' => 'Please type what you would like to change, or record a voice note.'];
        }
        $method = $feedbackText !== '' && $voicePath ? 'both' : ($voicePath ? 'voice' : 'typed');

        DB::insert('proof_feedback', [
            'proof_id' => $proofId,
            'feedback_text' => $feedbackText !== '' ? $feedbackText : null,
            'input_method' => $method,
            'voice_file_path' => $voicePath,
            'reference_file_path' => $refPath,
            'created_at' => now(),
        ]);
        DB::update('design_proofs', [
            'status' => 'change_requested',
            'responded_at' => now(),
            'response_ip' => request_ip(),
            'response_user_agent' => request_agent(),
        ], ['id' => $proofId]);

        $itemId = (int)$proof['order_item_id'];
        DB::run('UPDATE `' . tbl('order_items') . '` SET revision_count = revision_count + 1, updated_at = ? WHERE id = ?',
            [now(), $itemId]);
        OrderService::changeItemStatus($itemId, 'change_requested', null, 'Customer requested changes', true);
        OrderService::changeItemStatus($itemId, 'design_in_progress', null, 'Back to designer for revision', true);

        $item = DB::get('SELECT * FROM `' . tbl('order_items') . '` WHERE id = ?', [$itemId]);
        $revision = (int)$item['revision_count'];
        WaEvents::changeRequested($proofId, $feedbackText, $revision);
        Logger::activity('design', 'change_requested', 'design_proof', $proofId,
            "Customer requested changes (revision #$revision, $method)");

        // Too many revisions → flag amber + tell the branch manager
        $threshold = Settings::getInt('revision_flag_threshold', 3);
        if ($revision >= $threshold) {
            $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [(int)$item['order_id']]);
            $managers = DB::all(
                'SELECT u.id FROM `' . tbl('users') . '` u JOIN `' . tbl('roles') . '` r ON r.id = u.role_id
                 WHERE r.slug = ? AND u.is_active = 1 AND (u.primary_branch_id = ? OR EXISTS
                   (SELECT 1 FROM `' . tbl('user_branches') . '` ub WHERE ub.user_id = u.id AND ub.branch_id = ?))',
                ['branch_manager', (int)$order['branch_id'], (int)$order['branch_id']]
            );
            foreach ($managers as $manager) {
                Logger::notify((int)$manager['id'],
                    'High revision count on ' . $order['job_no'],
                    "Item \"{$item['item_name_snapshot']}\" has reached revision #$revision.",
                    admin_url('orders/' . $order['id']),
                    'exclamation-triangle');
            }
        }
        return ['ok' => true];
    }
}
