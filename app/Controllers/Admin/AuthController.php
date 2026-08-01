<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Logger;
use App\Core\View;

class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect(admin_url());
        }
        View::render('auth/login', [], 'layouts/auth');
    }

    public function login(): void
    {
        $identifier = trim((string)($_POST['identifier'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        if ($identifier === '' || $password === '') {
            flash('danger', 'Please enter your phone/email and password.');
            redirect(admin_url('login'));
        }
        $result = Auth::attempt($identifier, $password);
        if (!$result['ok']) {
            flash('danger', $result['error'] ?? 'Login failed.');
            redirect(admin_url('login'));
        }
        if (!empty($_POST['remember'])) {
            $p = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() + 60 * 60 * 24 * 30, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        $intended = $_SESSION['intended'] ?? null;
        unset($_SESSION['intended']);
        redirect($intended && str_contains($intended, '/admin') ? $intended : admin_url());
    }

    public function logout(): void
    {
        Logger::activity('auth', 'logout', 'user', Auth::id(), 'Logged out');
        Auth::logout();
        start_session();
        flash('success', 'You have been logged out.');
        redirect(admin_url('login'));
    }
}
