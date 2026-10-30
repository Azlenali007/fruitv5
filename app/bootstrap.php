<?php
/**
 * Application Bootstrap
 * N.A Fresh Fruits & Coconuts
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Ensure storage directories exist
@mkdir(__DIR__ . '/../storage/logs', 0755, true);
@mkdir(__DIR__ . '/../storage/uploads', 0755, true);
@mkdir(__DIR__ . '/../public/assets/uploads', 0755, true);

// Configure Secure Session Parameters
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Global XSS Sanitization Helper
function e(?string $string): string {
    return htmlspecialchars((string)$string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Global CSRF Token Helper
function csrf_token(): string {
    if (empty($_SESSION[CSRF_TOKEN_KEY])) {
        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_KEY];
}

function verify_csrf_token(?string $token): bool {
    if (empty($_SESSION[CSRF_TOKEN_KEY]) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_KEY], $token);
}

// Flash messages helpers
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'info'
        'message' => $message
    ];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Response json helper
function json_response(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// Load Models and Services
require_once __DIR__ . '/models/Setting.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/models/Admin.php';
require_once __DIR__ . '/models/Customer.php';
require_once __DIR__ . '/models/Coupon.php';
require_once __DIR__ . '/models/PaymentTransaction.php';
require_once __DIR__ . '/services/CartService.php';
require_once __DIR__ . '/services/OrderService.php';
require_once __DIR__ . '/services/AuthService.php';
require_once __DIR__ . '/services/CustomerAuthService.php';
require_once __DIR__ . '/services/RazorpayService.php';

