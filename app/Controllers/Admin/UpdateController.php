<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\Backup;
use App\Core\DB;
use App\Core\Logger;
use App\Core\Settings;
use App\Core\Updater;

class UpdateController extends Controller
{
    public function index(): void
    {
        Acl::require('update.manage');
        $meta = json_decode((string)Settings::get('latest_update_meta', ''), true) ?: null;
        $settings = [
            'upd_repo_owner' => (string)Settings::get('upd_repo_owner', ''),
            'upd_repo_name' => (string)Settings::get('upd_repo_name', ''),
            'upd_branch' => (string)Settings::get('upd_branch', 'main'),
            'upd_token' => (string)Settings::get('upd_token', ''),
            'upd_auto_check' => Settings::getBool('upd_auto_check', true),
            'upd_include_uploads' => Settings::getBool('upd_include_uploads', false),
            'upd_keep_backups' => Settings::getInt('upd_keep_backups', 5),
            'current_version' => (string)Settings::get('current_version', trim((string)@file_get_contents(BASE_PATH . '/VERSION'))),
            'current_commit' => (string)Settings::get('current_commit', ''),
            'last_update_check' => (string)Settings::get('last_update_check', ''),
        ];
        $this->render('settings/updates', compact('meta', 'settings'));
    }

    public function saveSettings(): void
    {
        Acl::require('update.manage');
        Settings::set('upd_repo_owner', trim((string)($_POST['upd_repo_owner'] ?? '')), 'update');
        Settings::set('upd_repo_name', trim((string)($_POST['upd_repo_name'] ?? '')), 'update');
        Settings::set('upd_branch', trim((string)($_POST['upd_branch'] ?? 'main')) ?: 'main', 'update');
        $token = trim((string)($_POST['upd_token'] ?? ''));
        if ($token !== '' && !preg_match('/^\*+$/', $token)) {
            Settings::set('upd_token', $token, 'update', true);
        }
        Settings::set('upd_auto_check', !empty($_POST['upd_auto_check']) ? '1' : '0', 'update');
        Settings::set('upd_include_uploads', !empty($_POST['upd_include_uploads']) ? '1' : '0', 'update');
        Settings::set('upd_keep_backups', (string)max(1, min(30, (int)($_POST['upd_keep_backups'] ?? 5))), 'update');
        Logger::activity('update', 'settings', null, null, 'Update settings saved');
        flash('success', 'Update settings saved.');
        redirect(admin_url('system/updates'));
    }

    public function testConnection(): void
    {
        Acl::require('update.manage');
        $result = (new Updater())->testConnection();
        flash($result['ok'] ? 'success' : 'danger', $result['message']);
        redirect(admin_url('system/updates'));
    }

    public function check(): void
    {
        Acl::require('update.manage');
        $result = (new Updater())->checkForUpdate();
        if (!$result['ok']) {
            flash('danger', $result['message'] ?? 'Check failed.');
        } elseif ($result['available']) {
            flash('warning', 'Update available: ' . e($result['latest_version']) . ' — review the details below.');
        } else {
            flash('success', 'You are running the latest version — checked ' . fmt_date(now(), true) . '.');
        }
        redirect(admin_url('system/updates'));
    }

    public function run(): void
    {
        Acl::require('update.manage');
        set_time_limit(0);
        $result = (new Updater())->updateNow((int)$this->user['id']);
        $this->render('settings/update_result', ['result' => $result]);
    }

    public function history(): void
    {
        Acl::require('update.manage');
        $rows = DB::all('SELECT * FROM `' . tbl('update_logs') . '` ORDER BY id DESC LIMIT 50');
        $this->render('settings/update_history', compact('rows'));
    }

    public function backups(): void
    {
        Acl::require('backup.manage');
        $backups = Backup::listAll();
        $this->render('settings/backups', compact('backups'));
    }

    public function createBackup(): void
    {
        Acl::require('backup.manage');
        set_time_limit(0);
        $result = Backup::create(Settings::getBool('upd_include_uploads', false), 'manual');
        Logger::activity('backup', 'create', null, null, $result['ok'] ? 'Backup ' . $result['path'] : 'Backup failed');
        flash($result['ok'] ? 'success' : 'danger',
            $result['ok'] ? 'Backup created at ' . e($result['path']) : 'Backup failed: ' . e($result['error'] ?? ''));
        redirect(admin_url('system/backups'));
    }

    public function restoreBackup(): void
    {
        Acl::require('backup.manage');
        if ($this->user['role_slug'] !== 'super_admin') {
            abort(403, 'Only the Super Admin can restore backups.');
        }
        $dir = (string)($_POST['backup_dir'] ?? '');
        if (!preg_match('#^backups/[0-9_]+$#', $dir) || !is_dir(BASE_PATH . '/' . $dir)) {
            flash('danger', 'Backup not found.');
            redirect(admin_url('system/backups'));
        }
        set_time_limit(0);
        $flag = BASE_PATH . '/storage/maintenance.flag';
        try {
            file_put_contents($flag, now());
            Backup::restoreFiles($dir);
            Backup::restoreDatabase($dir);
            @unlink($flag);
            Logger::activity('backup', 'restore', null, null, 'Restored from ' . $dir);
            flash('success', 'Backup restored — files and database are back to ' . e($dir) . '.');
        } catch (\Throwable $e) {
            @unlink($flag);
            flash('danger', 'Restore failed: ' . e($e->getMessage()));
        }
        redirect(admin_url('system/backups'));
    }

    public function deleteBackup(): void
    {
        Acl::require('backup.manage');
        $dir = (string)($_POST['backup_dir'] ?? '');
        if (preg_match('#^backups/[0-9_]+$#', $dir)) {
            Backup::deleteDir(BASE_PATH . '/' . $dir);
            Logger::activity('backup', 'delete', null, null, 'Deleted ' . $dir);
            flash('success', 'Backup deleted.');
        }
        redirect(admin_url('system/backups'));
    }
}
