<?php
/**
 * Customer Authentication & Session Management Service
 * N.A Fresh Fruits & Coconuts
 */

class CustomerAuthService {
    public const SESSION_CUSTOMER_ID = 'na_customer_id';
    public const SESSION_CUSTOMER_NAME = 'na_customer_name';
    public const SESSION_CUSTOMER_PHONE = 'na_customer_phone';
    public const SESSION_CUSTOMER_EMAIL = 'na_customer_email';

    /**
     * Authenticate customer with mobile number or email and password
     */
    public static function login(string $identifier, string $password, string $ip): array {
        $identifier = trim($identifier);

        if (empty($identifier) || empty($password)) {
            return [
                'success' => false,
                'error' => 'Please provide both your mobile number/email and password.'
            ];
        }

        // Check login rate limiting (max 5 failed attempts in 15 minutes)
        if (Customer::isLoginThrottled($identifier, $ip)) {
            return [
                'success' => false,
                'error' => 'Too many failed login attempts. For security, please wait 15 minutes before trying again.'
            ];
        }

        $customer = Customer::findByIdentifier($identifier);

        if (!$customer) {
            Customer::recordLoginAttempt($identifier, $ip, false);
            return [
                'success' => false,
                'error' => 'Invalid mobile number, email, or password.'
            ];
        }

        // If customer was created via older guest order and has no password
        if (empty($customer['password_hash'])) {
            Customer::recordLoginAttempt($identifier, $ip, false);
            return [
                'success' => false,
                'error' => 'An account with this mobile number was created during a previous order. Please register to set your password and activate your account.'
            ];
        }

        // Check account status
        if ($customer['status'] !== 'active') {
            Customer::recordLoginAttempt($identifier, $ip, false);
            return [
                'success' => false,
                'error' => 'Your account is currently inactive. Please contact store support.'
            ];
        }

        // Verify bcrypt password
        if (!password_verify($password, $customer['password_hash'])) {
            Customer::recordLoginAttempt($identifier, $ip, false);
            return [
                'success' => false,
                'error' => 'Invalid mobile number, email, or password.'
            ];
        }

        // Authentication success: regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION[self::SESSION_CUSTOMER_ID] = (int)$customer['id'];
        $_SESSION[self::SESSION_CUSTOMER_NAME] = $customer['name'];
        $_SESSION[self::SESSION_CUSTOMER_PHONE] = $customer['phone'];
        $_SESSION[self::SESSION_CUSTOMER_EMAIL] = $customer['email'];

        Customer::recordLoginAttempt($identifier, $ip, true);
        Customer::clearLoginAttempts($identifier, $ip);
        Customer::updateLastLogin($customer['id']);

        return [
            'success' => true,
            'customer' => $customer
        ];
    }

    /**
     * Register a new customer or activate an existing guest record
     */
    public static function register(array $data, string $ip): array {
        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? $data['password_confirm'] ?? '';
        $address = trim($data['address'] ?? '');

        // 1. Validation
        if (empty($name) || mb_strlen($name) < 2) {
            return ['success' => false, 'error' => 'Please enter your full name (at least 2 characters).'];
        }

        $cleanPhone = Customer::normalizePhone($phone);
        if (strlen($cleanPhone) !== 10) {
            return ['success' => false, 'error' => 'Please enter a valid 10-digit Indian mobile number.'];
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Please enter a valid email address, or leave it blank.'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters long.'];
        }

        if ($password !== $confirmPassword) {
            return ['success' => false, 'error' => 'Passwords do not match. Please verify and retype.'];
        }

        // 2. Check for duplicate accounts
        $existingCustomer = Customer::findByPhone($cleanPhone);

        if ($existingCustomer) {
            // If already registered with a password
            if (!empty($existingCustomer['password_hash'])) {
                return [
                    'success' => false,
                    'error' => 'An account with this mobile number already exists. Please log in with your password.'
                ];
            }

            // Existing guest record without password: activate it safely
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $activated = Customer::activateAccount($existingCustomer['id'], [
                'name' => $name,
                'email' => $email,
                'password_hash' => $hash
            ]);

            if (!$activated) {
                return ['success' => false, 'error' => 'Unable to complete registration. Please try again.'];
            }

            $customerId = (int)$existingCustomer['id'];
        } else {
            // Check duplicate email if provided
            if (!empty($email) && Customer::emailExists($email)) {
                return [
                    'success' => false,
                    'error' => 'An account with this email address already exists. Please log in or use another email.'
                ];
            }

            $hash = password_hash($password, PASSWORD_BCRYPT);
            $customerId = Customer::create([
                'name' => $name,
                'phone' => $cleanPhone,
                'email' => $email,
                'password_hash' => $hash,
                'address' => $address
            ]);
        }

        // 3. Log in newly registered user immediately
        session_regenerate_id(true);
        $_SESSION[self::SESSION_CUSTOMER_ID] = $customerId;
        $_SESSION[self::SESSION_CUSTOMER_NAME] = $name;
        $_SESSION[self::SESSION_CUSTOMER_PHONE] = '+91 ' . $cleanPhone;
        $_SESSION[self::SESSION_CUSTOMER_EMAIL] = $email;

        Customer::updateLastLogin($customerId);

        return [
            'success' => true,
            'customer_id' => $customerId
        ];
    }

    /**
     * Log out customer (does not touch admin session)
     */
    public static function logout(): void {
        unset($_SESSION[self::SESSION_CUSTOMER_ID]);
        unset($_SESSION[self::SESSION_CUSTOMER_NAME]);
        unset($_SESSION[self::SESSION_CUSTOMER_PHONE]);
        unset($_SESSION[self::SESSION_CUSTOMER_EMAIL]);
        session_regenerate_id(true);
    }

    /**
     * Check if customer is currently authenticated
     */
    public static function check(): bool {
        return !empty($_SESSION[self::SESSION_CUSTOMER_ID]);
    }

    /**
     * Get authenticated customer ID
     */
    public static function id(): ?int {
        return isset($_SESSION[self::SESSION_CUSTOMER_ID]) ? (int)$_SESSION[self::SESSION_CUSTOMER_ID] : null;
    }

    /**
     * Get full customer record from database
     */
    public static function user(): ?array {
        $id = self::id();
        if (!$id) {
            return null;
        }
        return Customer::findById($id);
    }

    /**
     * Enforce customer authentication on checkout or account routes
     */
    public static function requireLogin(string $intendedUrl = '/checkout'): void {
        if (!self::check()) {
            $_SESSION['customer_intended_url'] = $intendedUrl;
            set_flash('info', 'Please log in or create an account to place your order.');
            header('Location: /login');
            exit;
        }
    }
}
