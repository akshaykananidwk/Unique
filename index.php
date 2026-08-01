<?php
declare(strict_types=1);

define('BASE_PATH', __DIR__);
require __DIR__ . '/app/bootstrap.php';

if (!is_installed()) {
    exit; // bootstrap already redirected to /install
}

use App\Core\Router;

if (maintenance_active()) {
    http_response_code(503);
    include BASE_PATH . '/app/Views/errors/maintenance.php';
    exit;
}

start_session();

$router = new Router('App\\Controllers\\Site\\');

$router->get('/', 'HomeController@index');
$router->get('/products', 'ProductController@index');
$router->get('/product/{slug}', 'ProductController@show');

$router->post('/cart/add', 'CartController@add');
$router->get('/cart', 'CartController@index');
$router->post('/cart/remove', 'CartController@remove');

$router->get('/checkout', 'CheckoutController@form');
$router->post('/checkout', 'CheckoutController@submit');
$router->post('/checkout/verify-otp', 'CheckoutController@verifyOtp');
$router->post('/checkout/resend-otp', 'CheckoutController@resendOtp');
$router->get('/order-placed/{token}', 'CheckoutController@placed');

$router->get('/track', 'TrackController@form');
$router->post('/track', 'TrackController@lookup');

$router->get('/my-orders', 'TrackController@myOrdersForm');
$router->post('/my-orders', 'TrackController@myOrders');
$router->post('/my-orders/verify', 'TrackController@verifyMyOrders');
$router->get('/my-orders/exit', 'TrackController@exitMyOrders');

$router->get('/track/{token}', 'TrackController@byToken');

$router->get('/proof/{token}', 'ProofController@show');
$router->post('/proof/{token}/approve', 'ProofController@approve');
$router->post('/proof/{token}/change', 'ProofController@change');
$router->get('/proof/{token}/file/{version}', 'ProofController@file');

$router->get('/receipt/{token}/{paymentId}', 'TrackController@receipt');

$uri = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
$scriptDir = rtrim(str_replace('\\', '/', dirname((string)$_SERVER['SCRIPT_NAME'])), '/');
if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
    $uri = substr($uri, strlen($scriptDir)) ?: '/';
}
$router->dispatch($uri);
