<?php
declare(strict_types=1);

final class AdminAuth
{
    public static function loginForm(): void
    {
        if (Auth::check()) {
            View::redirect('/admin/');
        }
        View::admin('auth/login', ['title' => 'Sign in']);
    }

    public static function login(): void
    {
        $email = Request::str('email');
        $pass = (string) ($_POST['password'] ?? '');
        $remember = Request::bool('remember');
        $res = Auth::attempt($email, $pass, $remember);
        if ($res === 'ok') {
            View::redirect('/admin/');
        }
        if ($res === '2fa') {
            View::redirect('/admin/login/2fa/');
        }
        if ($res === 'locked') {
            View::flash('error', 'Too many attempts. Try again in 15 minutes.');
        } else {
            View::flash('error', 'Invalid email or password.');
        }
        View::redirect('/admin/login/');
    }

    public static function twoFactorForm(): void
    {
        if (empty($_SESSION['_2fa_pending'])) {
            View::redirect('/admin/login/');
        }
        View::admin('auth/two-factor', ['title' => 'Verification code']);
    }

    public static function twoFactor(): void
    {
        $code = Request::str('code');
        if (Auth::verifyTwoFactor($code)) {
            View::redirect('/admin/');
        }
        View::flash('error', 'That code is invalid or expired.');
        View::redirect('/admin/login/2fa/');
    }

    public static function logout(): void
    {
        Auth::logout();
        View::redirect('/admin/login/');
    }

    public static function forgotForm(): void
    {
        View::admin('auth/forgot', ['title' => 'Reset password']);
    }

    public static function forgot(): void
    {
        Auth::createReset(Request::str('email'));
        View::flash('success', 'If that email is on file, a reset link is on its way.');
        View::redirect('/admin/login/');
    }

    public static function resetForm(string $token): void
    {
        View::admin('auth/reset', ['title' => 'Choose a new password', 'token' => $token]);
    }

    public static function reset(string $token): void
    {
        $p1 = (string) ($_POST['password'] ?? '');
        $p2 = (string) ($_POST['password_confirm'] ?? '');
        if (strlen($p1) < 10 || $p1 !== $p2) {
            View::flash('error', 'Passwords must match and be at least 10 characters.');
            View::redirect('/admin/reset/' . rawurlencode($token) . '/');
        }
        if (!Auth::consumeReset($token, $p1)) {
            View::flash('error', 'This reset link is invalid or expired.');
            View::redirect('/admin/login/');
        }
        View::flash('success', 'Password updated. Sign in with your new password.');
        View::redirect('/admin/login/');
    }
}
