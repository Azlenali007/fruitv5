<?php
/**
 * Admin Authentication Service
 * N.A Fresh Fruits & Coconuts
 */

class AuthService {
    private const SESSION_ADMIN_ID = 'na_admin_id';
    private const SESSION_ADMIN_USER = 'na_admin_username';

    public static function login(string $username, string $password, string $ip): array {
        if (Admin::isIpThrottled($ip)) {
            return [
                'success' => false,
                'error' => 'Too many failed login attempts. Please wait 15 minutes before trying again.'
            ];
        }

        $admin = Admin::findByUsername($username);
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            Admin::recordLoginAttempt($ip, false);
            return [
                'success' => false,
                'error' => 'Invalid username or password.'
            ];
        }

        // Login success: regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        $_SESSION[self::SESSION_ADMIN_ID] = $admin['id'];
        $_SESSION[self::SESSION_ADMIN_USER] = $admin['username'];
        $_SESSION['na_admin_name'] = $admin['name'];

        Admin::recordLoginAttempt($ip, true);
        Admin::updateLastLogin($admin['id']);

        return [
            'success' => true,
            'admin' => $admin
        ];
    }

    public static function logout(): void {
        unset($_SESSION[self::SESSION_ADMIN_ID]);
        unset($_SESSION[self::SESSION_ADMIN_USER]);
        unset($_SESSION['na_admin_name']);
        session_regenerate_id(true);
    }

    public static function check(): bool {
        return !empty($_SESSION[self::SESSION_ADMIN_ID]);
    }

    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }
        return Admin::findById((int)$_SESSION[self::SESSION_ADMIN_ID]);
    }

    public static function requireAdmin(): void {
        if (!self::check()) {
            $_SESSION['admin_intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: /admin/login');
            exit;
        }
    }
}
