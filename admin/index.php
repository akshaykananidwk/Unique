<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

if (!is_installed()) {
    exit;
}

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Router;

start_session();

$uri = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
// Strip everything up to and including "/admin"
$pos = strpos($uri, '/admin');
$path = $pos !== false ? substr($uri, $pos + 6) : $uri;
$path = $path === '' || $path === false ? '/' : $path;

// During maintenance only the login + update screens stay reachable
if (maintenance_active() && !preg_match('#^/(login|logout|system/update|system/updates)#', $path) && $path !== '/') {
    http_response_code(503);
    include BASE_PATH . '/app/Views/errors/maintenance.php';
    exit;
}

Csrf::check();

// Send any queued WhatsApp messages right after this response — no cron needed
App\Core\Whatsapp::registerAutoFlush();

$router = new Router('App\\Controllers\\Admin\\');

// Auth
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Dashboard
$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// Orders
$router->get('/orders', 'OrderController@index');
$router->get('/orders/create', 'OrderController@create');
$router->post('/orders', 'OrderController@store');
$router->get('/orders/kanban', 'OrderController@kanban');
$router->get('/orders/{id}', 'OrderController@show');
$router->get('/orders/{id}/edit', 'OrderController@edit');
$router->post('/orders/{id}/update', 'OrderController@update');
$router->post('/orders/{id}/cancel', 'OrderController@cancel');
$router->post('/orders/{id}/delete', 'OrderController@delete');
$router->post('/orders/{id}/confirm', 'OrderController@confirmPublic');
$router->post('/order-items/{id}/status', 'OrderController@itemStatus');
$router->post('/order-items/{id}/assign', 'OrderController@assignDesigner');
$router->get('/orders/{id}/job-card', 'OrderController@jobCard');
$router->post('/orders/{id}/whatsapp', 'OrderController@sendWhatsapp');

// Customers
$router->get('/customers', 'CustomerController@index');
$router->get('/customers/create', 'CustomerController@create');
$router->post('/customers', 'CustomerController@store');
$router->get('/customers/{id}', 'CustomerController@show');
$router->get('/customers/{id}/edit', 'CustomerController@edit');
$router->post('/customers/{id}/update', 'CustomerController@update');
$router->post('/customers/{id}/delete', 'CustomerController@delete');

// Catalog
$router->get('/categories', 'CatalogController@categories');
$router->post('/categories', 'CatalogController@storeCategory');
$router->post('/categories/{id}/update', 'CatalogController@updateCategory');
$router->post('/categories/{id}/delete', 'CatalogController@deleteCategory');
$router->get('/items', 'CatalogController@items');
$router->get('/items/create', 'CatalogController@createItem');
$router->post('/items', 'CatalogController@storeItem');
$router->get('/items/{id}/edit', 'CatalogController@editItem');
$router->post('/items/{id}/update', 'CatalogController@updateItem');
$router->post('/items/{id}/delete', 'CatalogController@deleteItem');
$router->post('/items/{id}/options', 'CatalogController@saveOptions');
$router->post('/items/{id}/slabs', 'CatalogController@saveSlabs');

// Designer workspace
$router->get('/my-jobs', 'DesignerController@myJobs');
$router->post('/my-jobs/{itemId}/start', 'DesignerController@start');
$router->post('/my-jobs/{itemId}/proof', 'DesignerController@uploadProof');

// Payments
$router->post('/payments', 'PaymentController@store');
$router->post('/payments/{id}/delete', 'PaymentController@delete');
$router->get('/payments/{id}/receipt', 'PaymentController@receipt');
$router->get('/cashbook', 'PaymentController@cashbook');

// Reports
$router->get('/reports', 'ReportController@index');
$router->get('/reports/staff', 'ReportController@staff');
$router->get('/reports/sales', 'ReportController@sales');
$router->get('/reports/outstanding', 'ReportController@outstanding');
$router->post('/reports/outstanding/remind', 'ReportController@sendReminders');
$router->get('/reports/gst', 'ReportController@gst');
$router->get('/reports/revisions', 'ReportController@revisions');
$router->get('/reports/cancelled', 'ReportController@cancelled');
$router->get('/reports/activity', 'ReportController@activity');

// WhatsApp
$router->get('/whatsapp/settings', 'WhatsappController@settings');
$router->post('/whatsapp/settings', 'WhatsappController@saveSettings');
$router->post('/whatsapp/test', 'WhatsappController@testSend');
$router->post('/whatsapp/process-now', 'WhatsappController@processNow');
$router->get('/whatsapp/templates', 'WhatsappController@templates');
$router->post('/whatsapp/templates/{id}', 'WhatsappController@saveTemplate');
$router->get('/whatsapp/logs', 'WhatsappController@logs');
$router->post('/whatsapp/logs/{id}/resend', 'WhatsappController@resend');
$router->post('/whatsapp/logs/resend-failed', 'WhatsappController@resendFailed');
$router->get('/whatsapp/inbound', 'WhatsappController@inbound');

// Users / roles / branches
$router->get('/users', 'UserController@index');
$router->get('/users/create', 'UserController@create');
$router->post('/users', 'UserController@store');
$router->get('/users/{id}/edit', 'UserController@edit');
$router->post('/users/{id}/update', 'UserController@update');
$router->post('/users/{id}/delete', 'UserController@delete');
$router->get('/roles', 'RoleController@index');
$router->post('/roles', 'RoleController@store');
$router->post('/roles/{id}/permissions', 'RoleController@savePermissions');
$router->post('/roles/{id}/delete', 'RoleController@delete');
$router->get('/branches', 'BranchController@index');
$router->post('/branches', 'BranchController@store');
$router->post('/branches/{id}/update', 'BranchController@update');

// Settings + system
$router->get('/settings', 'SettingsController@index');
$router->post('/settings', 'SettingsController@save');
$router->get('/system/updates', 'UpdateController@index');
$router->post('/system/updates/test', 'UpdateController@testConnection');
$router->post('/system/updates/check', 'UpdateController@check');
$router->post('/system/updates/run', 'UpdateController@run');
$router->post('/system/updates/settings', 'UpdateController@saveSettings');
$router->get('/system/backups', 'UpdateController@backups');
$router->post('/system/backups/create', 'UpdateController@createBackup');
$router->post('/system/backups/restore', 'UpdateController@restoreBackup');
$router->post('/system/backups/delete', 'UpdateController@deleteBackup');
$router->get('/system/update-history', 'UpdateController@history');

// Notifications + AJAX API
$router->get('/notifications', 'NotificationController@index');
$router->post('/notifications/read', 'NotificationController@markRead');
$router->get('/api/customer-lookup', 'ApiController@customerLookup');
$router->get('/api/item-options/{id}', 'ApiController@itemOptions');
$router->post('/api/calc-price', 'ApiController@calcPrice');
$router->get('/api/search', 'ApiController@search');
$router->get('/api/designers', 'ApiController@designers');

$router->dispatch($path);
