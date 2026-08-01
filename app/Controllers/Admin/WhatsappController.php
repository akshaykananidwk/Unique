<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\DB;
use App\Core\Logger;
use App\Core\Settings;
use App\Core\Whatsapp;

class WhatsappController extends Controller
{
    public function settings(): void
    {
        Acl::require('whatsapp.settings');
        $settings = [
            'wa_enabled' => Settings::getBool('wa_enabled') ? '1' : '0',
            'wa_api_url' => (string)Settings::get('wa_api_url', 'https://bulk.akdwk.in/api.php'),
            'wa_api_key' => (string)Settings::get('wa_api_key', ''),
            'wa_session_id' => (string)Settings::get('wa_session_id', ''),
            'wa_country_code' => (string)Settings::get('wa_country_code', '91'),
            'wa_rate_limit_per_min' => (string)Settings::getInt('wa_rate_limit_per_min', 12),
            'wa_max_attempts' => (string)Settings::getInt('wa_max_attempts', 3),
            'wa_quiet_start' => (string)Settings::get('wa_quiet_start', ''),
            'wa_quiet_end' => (string)Settings::get('wa_quiet_end', ''),
            'wa_webhook_secret' => (string)Settings::get('wa_webhook_secret', ''),
        ];
        $this->render('whatsapp/settings', compact('settings'));
    }

    public function saveSettings(): void
    {
        Acl::require('whatsapp.settings');
        Settings::set('wa_enabled', !empty($_POST['wa_enabled']) ? '1' : '0', 'whatsapp');
        Settings::set('wa_api_url', trim((string)($_POST['wa_api_url'] ?? '')), 'whatsapp');
        // Masked key field: only overwrite when the admin typed a new value
        $key = trim((string)($_POST['wa_api_key'] ?? ''));
        if ($key !== '' && !preg_match('/^\*+$/', $key)) {
            Settings::set('wa_api_key', $key, 'whatsapp', true);
        }
        Settings::set('wa_session_id', trim((string)($_POST['wa_session_id'] ?? '')), 'whatsapp');
        Settings::set('wa_country_code', preg_replace('/\D/', '', (string)($_POST['wa_country_code'] ?? '91')) ?: '91', 'whatsapp');
        Settings::set('wa_rate_limit_per_min', (string)max(1, min(60, (int)($_POST['wa_rate_limit_per_min'] ?? 12))), 'whatsapp');
        Settings::set('wa_max_attempts', (string)max(1, min(10, (int)($_POST['wa_max_attempts'] ?? 3))), 'whatsapp');
        foreach (['wa_quiet_start', 'wa_quiet_end'] as $field) {
            $v = trim((string)($_POST[$field] ?? ''));
            Settings::set($field, preg_match('/^\d{2}:\d{2}$/', $v) ? $v : '', 'whatsapp');
        }
        if (!empty($_POST['regenerate_secret'])) {
            Settings::set('wa_webhook_secret', random_token(16), 'whatsapp');
        }
        Logger::activity('whatsapp', 'settings', null, null, 'WhatsApp settings updated');
        flash('success', 'WhatsApp settings saved.');
        redirect(admin_url('whatsapp/settings'));
    }

    public function testSend(): void
    {
        Acl::require('whatsapp.settings');
        $number = normalize_phone((string)($_POST['test_number'] ?? ''));
        if (!$number) {
            flash('danger', 'Enter a valid mobile number for the test.');
            redirect(admin_url('whatsapp/settings'));
        }
        $result = Whatsapp::sendNow($number, 'Test message from ' . Settings::get('business_name', 'Krishna Print') . ' — ' . now());
        Logger::activity('whatsapp', 'test_send', null, null, 'Test send to ' . $number . ' — ' . ($result['ok'] ? 'OK' : 'FAILED'));
        flash($result['ok'] ? 'success' : 'danger',
            ($result['ok'] ? 'Test message sent. ' : 'Test failed. ') . 'API response: <code>' . e($result['response']) . '</code>');
        redirect(admin_url('whatsapp/settings'));
    }

    public function templates(): void
    {
        Acl::require('whatsapp.templates');
        $templates = DB::all('SELECT * FROM `' . tbl('whatsapp_templates') . '` ORDER BY code');
        $this->render('whatsapp/templates', compact('templates'));
    }

    public function saveTemplate(string $id): void
    {
        Acl::require('whatsapp.templates');
        $template = DB::get('SELECT * FROM `' . tbl('whatsapp_templates') . '` WHERE id = ?', [(int)$id]);
        if (!$template) {
            abort(404, 'Template not found.');
        }
        DB::update('whatsapp_templates', [
            'title' => trim((string)($_POST['title'] ?? $template['title'])),
            'body' => (string)($_POST['body'] ?? $template['body']),
            'media_url' => trim((string)($_POST['media_url'] ?? '')) ?: null,
            'custom_numbers' => trim((string)($_POST['custom_numbers'] ?? '')) ?: null,
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'delay_minutes' => max(0, (int)($_POST['delay_minutes'] ?? 0)),
            'updated_at' => now(),
        ], ['id' => (int)$id]);
        Logger::activity('whatsapp', 'template', 'whatsapp_template', (int)$id, 'Template ' . $template['code'] . ' updated');
        flash('success', 'Template saved.');
        redirect(admin_url('whatsapp/templates'));
    }

    public function logs(): void
    {
        Acl::require('whatsapp.logs');
        $where = ['1=1'];
        $params = [];
        if (!empty($_GET['status'])) {
            $where[] = 'q.status = ?';
            $params[] = $_GET['status'];
        }
        $q = trim((string)($_GET['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(q.to_number LIKE ? OR q.template_code LIKE ? OR q.message LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like);
        }
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 30;
        $whereSql = implode(' AND ', $where);
        $total = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('whatsapp_queue') . "` q WHERE $whereSql", $params);
        $rows = DB::all(
            'SELECT q.* FROM `' . tbl('whatsapp_queue') . "` q WHERE $whereSql
             ORDER BY q.id DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage),
            $params
        );
        $this->render('whatsapp/logs', compact('rows', 'total', 'page', 'perPage', 'q'));
    }

    public function resend(string $id): void
    {
        Acl::require('whatsapp.resend');
        $row = DB::get('SELECT * FROM `' . tbl('whatsapp_queue') . '` WHERE id = ?', [(int)$id]);
        if (!$row) {
            abort(404, 'Queue row not found.');
        }
        DB::update('whatsapp_queue', [
            'status' => 'pending',
            'attempts' => 0,
            'last_error' => null,
            'scheduled_at' => now(),
        ], ['id' => (int)$id]);
        Logger::activity('whatsapp', 'resend', 'whatsapp_queue', (int)$id, 'Message re-queued to ' . $row['to_number']);
        flash('success', 'Message re-queued — the worker sends it within a minute.');
        redirect(admin_url('whatsapp/logs'));
    }

    public function resendFailed(): void
    {
        Acl::require('whatsapp.resend');
        $count = DB::run(
            'UPDATE `' . tbl('whatsapp_queue') . '` SET status = ?, attempts = 0, last_error = NULL, scheduled_at = ? WHERE status = ?',
            ['pending', now(), 'failed']
        )->rowCount();
        Logger::activity('whatsapp', 'resend_bulk', null, null, "Re-queued $count failed messages");
        flash('success', "Re-queued $count failed message(s).");
        redirect(admin_url('whatsapp/logs'));
    }

    public function inbound(): void
    {
        Acl::require('whatsapp.logs');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 30;
        $total = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('whatsapp_inbound') . '`');
        $rows = DB::all(
            'SELECT wi.*, c.name AS customer_name, o.job_no
             FROM `' . tbl('whatsapp_inbound') . '` wi
             LEFT JOIN `' . tbl('customers') . '` c ON c.id = wi.matched_customer_id
             LEFT JOIN `' . tbl('orders') . "` o ON o.id = wi.matched_order_id
             ORDER BY wi.id DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
        );
        DB::run('UPDATE `' . tbl('whatsapp_inbound') . '` SET is_read = 1 WHERE is_read = 0');
        $this->render('whatsapp/inbound', compact('rows', 'total', 'page', 'perPage'));
    }
}
