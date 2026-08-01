<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\View;
use App\Models\ProofService;

class ProofController
{
    public function show(string $token): void
    {
        $proof = $this->findByToken($token);
        if (!$proof['viewed_at']) {
            DB::update('design_proofs', ['viewed_at' => now()], ['id' => (int)$proof['id']]);
        }
        $item = DB::get('SELECT * FROM `' . tbl('order_items') . '` WHERE id = ?', [(int)$proof['order_item_id']]);
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [(int)$item['order_id']]);
        $customer = DB::get('SELECT name FROM `' . tbl('customers') . '` WHERE id = ?', [(int)$order['customer_id']]);
        $versions = DB::all(
            'SELECT id, version, status, created_at, proof_token FROM `' . tbl('design_proofs') . '`
             WHERE order_item_id = ? ORDER BY version DESC',
            [(int)$item['id']]
        );
        View::render('proof/show', compact('proof', 'item', 'order', 'customer', 'versions'), 'layouts/public');
    }

    public function approve(string $token): void
    {
        Csrf::check();
        $proof = $this->findByToken($token);
        // The double-confirm flag must arrive from the modal's second button
        if (empty($_POST['confirmed']) || $_POST['confirmed'] !== 'yes') {
            flash('danger', 'Please confirm the approval in the popup.');
            redirect(base_url('proof/' . $token));
        }
        $result = ProofService::approve((int)$proof['id']);
        flash($result['ok'] ? 'success' : 'danger',
            $result['ok'] ? 'Design approved — printing will start with this exact design. Thank you!' : $result['error']);
        redirect(base_url('proof/' . $token));
    }

    public function change(string $token): void
    {
        Csrf::check();
        $proof = $this->findByToken($token);
        $result = ProofService::requestChange(
            (int)$proof['id'],
            trim((string)($_POST['feedback'] ?? '')),
            $_FILES['voice_note'] ?? null,
            $_FILES['reference'] ?? null
        );
        flash($result['ok'] ? 'success' : 'danger',
            $result['ok'] ? 'Your change request has been sent to the designer. You will get a new proof on WhatsApp.' : $result['error']);
        redirect(base_url('proof/' . $token));
    }

    /** Serve the watermarked preview (or original PDF) for a proof version. */
    public function file(string $token, string $version): void
    {
        $proof = $this->findByToken($token);
        $target = DB::get(
            'SELECT * FROM `' . tbl('design_proofs') . '` WHERE order_item_id = ? AND version = ?',
            [(int)$proof['order_item_id'], (int)$version]
        );
        if (!$target) {
            abort(404, 'Version not found.');
        }
        // Customers always get the watermarked preview when one exists
        $relative = $target['watermarked_path'] ?: $target['file_path'];
        $path = BASE_PATH . '/uploads/' . $relative;
        if (!is_file($path)) {
            abort(404, 'File missing.');
        }
        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            default => 'image/jpeg',
        };
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string)filesize($path));
        header('Cache-Control: private, max-age=300');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    private function findByToken(string $token): array
    {
        if (!preg_match('/^[a-f0-9]{32,64}$/', $token)) {
            abort(404, 'Invalid link.');
        }
        $proof = DB::get('SELECT * FROM `' . tbl('design_proofs') . '` WHERE proof_token = ?', [$token]);
        if (!$proof) {
            abort(404, 'Proof not found. The link may be old — please use the newest WhatsApp link.');
        }
        return $proof;
    }
}
