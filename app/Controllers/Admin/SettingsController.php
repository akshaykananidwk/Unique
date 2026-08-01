<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Acl;
use App\Core\Logger;
use App\Core\Settings;
use App\Core\Uploader;

class SettingsController extends Controller
{
    private const GENERAL_KEYS = [
        'business_name', 'business_tagline', 'business_address', 'business_city', 'business_state',
        'business_pincode', 'business_phone', 'business_whatsapp', 'business_email', 'business_gstin',
        'currency_symbol', 'timezone', 'date_format', 'brand_color',
    ];

    private const APP_KEYS = [
        'base_url', 'job_prefix', 'idle_timeout_minutes', 'auto_assign_designer', 'revision_flag_threshold',
        'proof_max_upload_mb', 'upload_max_mb', 'public_order_auto_confirm', 'public_otp_required',
        'otp_expiry_minutes', 'balance_reminder_days', 'proof_reminder_hours', 'feedback_stale_hours',
    ];

    public function index(): void
    {
        Acl::require('settings.manage');
        $values = [];
        foreach (array_merge(self::GENERAL_KEYS, self::APP_KEYS, ['business_logo', 'business_favicon', 'login_image']) as $key) {
            $values[$key] = (string)Settings::get($key, '');
        }
        $this->render('settings/index', compact('values'));
    }

    public function save(): void
    {
        Acl::require('settings.manage');
        foreach (self::GENERAL_KEYS as $key) {
            if (isset($_POST[$key])) {
                Settings::set($key, trim((string)$_POST[$key]), 'general');
            }
        }
        foreach (self::APP_KEYS as $key) {
            if (isset($_POST[$key])) {
                Settings::set($key, trim((string)$_POST[$key]), 'app');
            }
        }
        // Checkbox toggles come as missing keys when unticked
        foreach (['auto_assign_designer', 'public_order_auto_confirm', 'public_otp_required'] as $toggle) {
            Settings::set($toggle, !empty($_POST[$toggle]) ? '1' : '0', 'app');
        }
        if (!empty($_FILES['business_logo']['name'])) {
            $stored = Uploader::store($_FILES['business_logo'], 'logos', Uploader::IMAGES, 5);
            if ($stored['ok']) {
                Settings::set('business_logo', $stored['path'], 'general');
            }
        }
        if (!empty($_FILES['business_favicon']['name'])) {
            $stored = Uploader::store($_FILES['business_favicon'], 'logos', array_merge(Uploader::IMAGES, ['ico']), 2);
            if ($stored['ok']) {
                Settings::set('business_favicon', $stored['path'], 'general');
            }
        }
        if (!empty($_FILES['login_image']['name'])) {
            $stored = Uploader::store($_FILES['login_image'], 'logos', Uploader::IMAGES, 8);
            if ($stored['ok']) {
                Settings::set('login_image', $stored['path'], 'general');
            }
        }
        Logger::activity('settings', 'save', null, null, 'Business/app settings updated');
        flash('success', 'Settings saved.');
        redirect(admin_url('settings'));
    }
}
