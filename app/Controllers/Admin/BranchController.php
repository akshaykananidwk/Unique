<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\DB;
use App\Core\Logger;

class BranchController extends Controller
{
    public function index(): void
    {
        Acl::require('branch.manage');
        $branches = DB::all('SELECT * FROM `' . tbl('branches') . '` ORDER BY sort_order, id');
        $this->render('users/branches', compact('branches'));
    }

    public function store(): void
    {
        Acl::require('branch.manage');
        $name = trim((string)($_POST['name'] ?? ''));
        $code = strtoupper(trim((string)($_POST['code'] ?? '')));
        if ($name === '' || $code === '' || !preg_match('/^[A-Z0-9]{1,10}$/', $code)) {
            flash('danger', 'Branch name and a short alphanumeric code are required.');
            redirect(admin_url('branches'));
        }
        if (DB::get('SELECT id FROM `' . tbl('branches') . '` WHERE code = ?', [$code])) {
            flash('danger', 'Branch code already in use.');
            redirect(admin_url('branches'));
        }
        $id = DB::insert('branches', [
            'code' => $code,
            'name' => $name,
            'type' => in_array($_POST['type'] ?? '', ['shop', 'godown', 'office'], true) ? $_POST['type'] : 'shop',
            'address' => trim((string)($_POST['address'] ?? '')) ?: null,
            'city' => trim((string)($_POST['city'] ?? '')) ?: null,
            'phone' => local_phone((string)($_POST['phone'] ?? '')),
            'whatsapp' => local_phone((string)($_POST['whatsapp'] ?? '')),
            'gstin' => trim((string)($_POST['gstin'] ?? '')) ?: null,
            'invoice_prefix' => strtoupper(trim((string)($_POST['invoice_prefix'] ?? ''))) ?: null,
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Logger::activity('branch', 'create', 'branch', $id, "Branch $name ($code) created");
        flash('success', 'Branch added.');
        redirect(admin_url('branches'));
    }

    public function update(string $id): void
    {
        Acl::require('branch.manage');
        $branch = DB::get('SELECT * FROM `' . tbl('branches') . '` WHERE id = ?', [(int)$id]);
        if (!$branch) {
            abort(404, 'Branch not found.');
        }
        DB::update('branches', [
            'name' => trim((string)($_POST['name'] ?? $branch['name'])),
            'type' => in_array($_POST['type'] ?? '', ['shop', 'godown', 'office'], true) ? $_POST['type'] : $branch['type'],
            'address' => trim((string)($_POST['address'] ?? '')) ?: null,
            'city' => trim((string)($_POST['city'] ?? '')) ?: null,
            'phone' => local_phone((string)($_POST['phone'] ?? '')),
            'whatsapp' => local_phone((string)($_POST['whatsapp'] ?? '')),
            'gstin' => trim((string)($_POST['gstin'] ?? '')) ?: null,
            'invoice_prefix' => strtoupper(trim((string)($_POST['invoice_prefix'] ?? ''))) ?: null,
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'updated_at' => now(),
        ], ['id' => (int)$id]);
        Logger::activity('branch', 'update', 'branch', (int)$id, 'Branch updated');
        flash('success', 'Branch updated.');
        redirect(admin_url('branches'));
    }
}
