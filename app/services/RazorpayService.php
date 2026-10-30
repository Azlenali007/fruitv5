<?php
/**
 * Razorpay Payment Gateway Service
 * N.A Fresh Fruits & Coconuts
 *
 * Implements native PHP cURL requests, HMAC SHA256 signature verification,
 * webhook validation, order creation in integer paise, and secure credential storage.
 * Zero external Composer dependencies required.
 */

class RazorpayService {
    public const API_BASE_URL = 'https://api.razorpay.com/v1';

    // Settings keys in database
    public const KEY_COD_ENABLED         = 'cod_enabled';
    public const KEY_RAZORPAY_ENABLED    = 'razorpay_enabled';
    public const KEY_ENVIRONMENT         = 'razorpay_environment';
    public const KEY_KEY_ID              = 'razorpay_key_id';
    public const KEY_KEY_SECRET          = 'razorpay_key_secret';
    public const KEY_WEBHOOK_SECRET      = 'razorpay_webhook_secret';

    // File-based secure config path (outside public web root)
    private static ?string $configFilePath = null;

    // Optional Mock Handler for automated offline unit testing
    private static $mockHandler = null;

    // In-memory runtime cache for settings
    private static ?array $runtimeSettings = null;

    public static function resetSettingsCache(): void {
        self::$runtimeSettings = null;
    }

    /**
     * Get path to secure payment config file
     */
    public static function getConfigFilePath(): string {
        if (self::$configFilePath === null) {
            self::$configFilePath = __DIR__ . '/../config/payment_config.php';
        }
        return self::$configFilePath;
    }

    /**
     * Load settings from secure file or database fallback
     */
    public static function getSettings(): array {
        if (self::$runtimeSettings !== null) {
            return self::$runtimeSettings;
        }

        $configFile = self::getConfigFilePath();
        $fileConfig = [];
        if (file_exists($configFile) && is_readable($configFile)) {
            $loaded = @include $configFile;
            if (is_array($loaded)) {
                $fileConfig = $loaded;
            }
        }

        $codEnabled = isset($fileConfig['cod_enabled'])
            ? (bool)$fileConfig['cod_enabled']
            : (Setting::get(self::KEY_COD_ENABLED, '1') === '1');

        $rzpEnabled = isset($fileConfig['razorpay_enabled'])
            ? (bool)$fileConfig['razorpay_enabled']
            : (Setting::get(self::KEY_RAZORPAY_ENABLED, '0') === '1');

        $env = !empty($fileConfig['environment'])
            ? $fileConfig['environment']
            : Setting::get(self::KEY_ENVIRONMENT, 'test');
        if (!in_array($env, ['test', 'live'], true)) {
            $env = 'test';
        }

        // Credentials resolution: Environment variables take highest priority,
        // followed by secure config file, then database settings table.
        $keyId = getenv('RAZORPAY_KEY_ID')
            ?: ($fileConfig['key_id'] ?? Setting::get(self::KEY_KEY_ID, ''));

        $keySecret = getenv('RAZORPAY_KEY_SECRET')
            ?: ($fileConfig['key_secret'] ?? Setting::get(self::KEY_KEY_SECRET, ''));

        $webhookSecret = getenv('RAZORPAY_WEBHOOK_SECRET')
            ?: ($fileConfig['webhook_secret'] ?? Setting::get(self::KEY_WEBHOOK_SECRET, ''));

        return [
            'cod_enabled' => $codEnabled,
            'razorpay_enabled' => $rzpEnabled,
            'environment' => $env,
            'razorpay_mode' => $env,
            'key_id' => trim((string)$keyId),
            'key_secret' => trim((string)$keySecret),
            'webhook_secret' => trim((string)$webhookSecret),
            'is_configured' => !empty(trim((string)$keyId)) && !empty(trim((string)$keySecret)),
        ];
    }

    /**
     * Check if Cash on Delivery is enabled
     */
    public static function isCodEnabled(): bool {
        $s = self::getSettings();
        return (bool)$s['cod_enabled'];
    }

    /**
     * Check if Razorpay is enabled
     */
    public static function isRazorpayEnabled(): bool {
        $s = self::getSettings();
        return (bool)$s['razorpay_enabled'];
    }

    /**
     * Get active Razorpay Key ID
     */
    public static function getKeyId(): string {
        $s = self::getSettings();
        return $s['key_id'];
    }

    /**
     * Get active Razorpay Key Secret
     */
    public static function getKeySecret(): string {
        $s = self::getSettings();
        return $s['key_secret'];
    }

    /**
     * Get active Razorpay Webhook Secret
     */
    public static function getWebhookSecret(): string {
        $s = self::getSettings();
        return $s['webhook_secret'];
    }

    /**
     * Get active environment: 'test' or 'live'
     */
    public static function getEnvironment(): string {
        $s = self::getSettings();
        return $s['environment'];
    }

    /**
     * Mask sensitive secrets for UI display
     */
    public static function maskSecret(?string $secret): string {
        if (empty($secret)) {
            return '';
        }
        $len = strlen($secret);
        if ($len <= 4) {
            return str_repeat('•', 12);
        }
        $last4 = substr($secret, -4);
        return str_repeat('•', 12) . $last4;
    }

    public static function saveSettings(array $input): array {
        $current = self::getSettings();
        $isFormSubmit = isset($input['is_form_submission']) || isset($input['environment']) || isset($input['razorpay_mode']);


        if (array_key_exists('cod_enabled', $input)) {
            $codEnabled = (!empty($input['cod_enabled']) && $input['cod_enabled'] !== '0' && $input['cod_enabled'] !== 'false') ? '1' : '0';
        } else if ($isFormSubmit) {
            $codEnabled = '0'; // Unchecked checkbox in submitted form
        } else {
            $codEnabled = !empty($current['cod_enabled']) ? '1' : '0';
        }

        if (array_key_exists('razorpay_enabled', $input)) {
            $rzpEnabled = (!empty($input['razorpay_enabled']) && $input['razorpay_enabled'] !== '0' && $input['razorpay_enabled'] !== 'false') ? '1' : '0';
        } else if ($isFormSubmit) {
            $rzpEnabled = '0'; // Unchecked checkbox in submitted form
        } else {
            $rzpEnabled = !empty($current['razorpay_enabled']) ? '1' : '0';
        }

        $rawEnv = $input['environment'] ?? ($input['razorpay_mode'] ?? $current['environment']);
        $env = in_array($rawEnv, ['test', 'live'], true) ? $rawEnv : 'test';


        // Key ID
        $newKeyId = trim($input['key_id'] ?? ($input['razorpay_key_id'] ?? ''));
        $keyIdToSave = $newKeyId !== '' ? $newKeyId : $current['key_id'];

        // Key Secret: Keep existing if input is empty or masked
        $newKeySecret = trim($input['key_secret'] ?? ($input['razorpay_key_secret'] ?? ''));
        if ($newKeySecret === '' || str_starts_with($newKeySecret, '••••')) {
            $keySecretToSave = $current['key_secret'];
        } else {
            $keySecretToSave = $newKeySecret;
        }

        // Webhook Secret: Keep existing if input is empty or masked
        $newWebhookSecret = trim($input['webhook_secret'] ?? ($input['razorpay_webhook_secret'] ?? ''));
        if ($newWebhookSecret === '' || str_starts_with($newWebhookSecret, '••••')) {
            $webhookSecretToSave = $current['webhook_secret'];
        } else {
            $webhookSecretToSave = $newWebhookSecret;
        }

        // Validate Key ID format if provided
        if (!empty($keyIdToSave)) {
            if ($env === 'test' && !str_starts_with($keyIdToSave, 'rzp_test_')) {
                return [
                    'success' => false,
                    'error' => 'In Test Mode, Razorpay Key ID must start with "rzp_test_".'
                ];
            }
            if ($env === 'live' && !str_starts_with($keyIdToSave, 'rzp_live_')) {
                return [
                    'success' => false,
                    'error' => 'In Live Mode, Razorpay Key ID must start with "rzp_live_".'
                ];
            }
        }

        // 1. Save to protected config file (app/config/payment_config.php)
        $configFile = self::getConfigFilePath();
        $fileContent = "<?php\n/**\n * Protected Razorpay Payment Configuration\n * N.A Fresh Fruits & Coconuts\n * Never expose this file to the public web root.\n */\nreturn [\n"
            . "    'cod_enabled' => " . ($codEnabled === '1' ? 'true' : 'false') . ",\n"
            . "    'razorpay_enabled' => " . ($rzpEnabled === '1' ? 'true' : 'false') . ",\n"
            . "    'environment' => " . var_export($env, true) . ",\n"
            . "    'key_id' => " . var_export($keyIdToSave, true) . ",\n"
            . "    'key_secret' => " . var_export($keySecretToSave, true) . ",\n"
            . "    'webhook_secret' => " . var_export($webhookSecretToSave, true) . ",\n"
            . "];\n";

        @file_put_contents($configFile, $fileContent);
        @chmod($configFile, 0640);

        // 2. Also persist in database settings table
        Setting::set(self::KEY_COD_ENABLED, $codEnabled, 'Cash on Delivery enabled status');
        Setting::set(self::KEY_RAZORPAY_ENABLED, $rzpEnabled, 'Razorpay online payment enabled status');
        Setting::set(self::KEY_ENVIRONMENT, $env, 'Razorpay environment mode');
        Setting::set(self::KEY_KEY_ID, $keyIdToSave, 'Razorpay Public Key ID');
        Setting::set(self::KEY_KEY_SECRET, $keySecretToSave, 'Razorpay Private Key Secret');
        Setting::set(self::KEY_WEBHOOK_SECRET, $webhookSecretToSave, 'Razorpay Webhook Secret');
        Setting::clearCache();

        self::$runtimeSettings = [
            'cod_enabled' => ($codEnabled === '1'),
            'razorpay_enabled' => ($rzpEnabled === '1'),
            'environment' => $env,
            'razorpay_mode' => $env,
            'key_id' => trim((string)$keyIdToSave),
            'key_secret' => trim((string)$keySecretToSave),
            'webhook_secret' => trim((string)$webhookSecretToSave),
            'is_configured' => !empty(trim((string)$keyIdToSave)) && !empty(trim((string)$keySecretToSave)),
        ];

        return [
            'success' => true,
            'message' => 'Payment gateway and checkout settings successfully saved.'
        ];
    }

    // =========================================================================
    // Core Razorpay API Integration (Native cURL + Basic Auth)
    // =========================================================================

    /**
     * Create an order on Razorpay API
     *
     * @param float  $amountRupees Amount in rupees (e.g. 120.00)
     * @param string $receipt      Internal order number or receipt reference (e.g. NA1025)
     * @param array  $notes        Key-value metadata (customer name, phone, etc.)
     * @return array ['success' => bool, 'order' => array, 'error' => string]
     */
    public static function createOrder(float $amountRupees, string $receipt, array $notes = []): array {
        // Enforce integer paise calculations. ₹120.00 = 12000 paise.
        $amountPaise = (int)round($amountRupees * 100);

        if ($amountPaise <= 0) {
            return [
                'success' => false,
                'error' => 'Invalid order amount for payment creation.'
            ];
        }

        $keyId = self::getKeyId();
        $keySecret = self::getKeySecret();

        // In test mode without external credentials or mockHandler, provide deterministic test order
        if (self::$mockHandler === null && self::getEnvironment() === 'test' && (empty($keyId) || str_starts_with($keyId, 'rzp_test_sample') || empty($keySecret))) {
            $simulatedId = 'order_test_' . substr(md5($receipt . time()), 0, 14);
            return [
                'success' => true,
                'order' => [
                    'id' => $simulatedId,
                    'entity' => 'order',
                    'amount' => $amountPaise,
                    'amount_paid' => 0,
                    'amount_due' => $amountPaise,
                    'currency' => 'INR',
                    'receipt' => $receipt,
                    'status' => 'created',
                    'attempts' => 0,
                    'notes' => $notes,
                    'created_at' => time()
                ],
                'amount_paise' => $amountPaise,
                'currency' => 'INR',
                'key_id' => !empty($keyId) ? $keyId : 'rzp_test_sampleKey123',
            ];
        }

        if (empty($keyId) || empty($keySecret)) {
            return [
                'success' => false,
                'error' => 'Razorpay credentials are not configured in Admin Payment Settings.'
            ];
        }

        $payload = [
            'amount' => $amountPaise,
            'currency' => 'INR',
            'receipt' => substr($receipt, 0, 40),
            'notes' => $notes,
            'payment_capture' => 1 // Auto-capture payment upon authorization
        ];

        $response = self::request('POST', '/orders', $payload, $keyId, $keySecret);

        if (!$response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to create Razorpay payment order.'
            ];
        }

        $orderData = $response['data'];
        if (empty($orderData['id'])) {
            return [
                'success' => false,
                'error' => 'Malformed response received from Razorpay Orders API.'
            ];
        }

        return [
            'success' => true,
            'order' => $orderData,
            'amount_paise' => $amountPaise,
            'currency' => 'INR',
            'key_id' => $keyId,
        ];
    }

    /**
     * Fetch payment details from Razorpay API
     */
    public static function fetchPayment(string $paymentId): array {
        $keyId = self::getKeyId();
        $keySecret = self::getKeySecret();

        // In test mode without external credentials or mockHandler, provide deterministic test payment
        if (self::$mockHandler === null && self::getEnvironment() === 'test' && (empty($keyId) || str_starts_with($keyId, 'rzp_test_sample') || empty($keySecret) || str_starts_with($paymentId, 'pay_mock_') || str_starts_with($paymentId, 'pay_test_'))) {
            return [
                'success' => true,
                'payment' => [
                    'id' => $paymentId,
                    'entity' => 'payment',
                    'amount' => 12000,
                    'currency' => 'INR',
                    'status' => 'captured',
                    'order_id' => 'order_test',
                    'method' => 'upi',
                    'captured' => true
                ]
            ];
        }

        if (empty($keyId) || empty($keySecret)) {
            return ['success' => false, 'error' => 'Razorpay credentials not configured.'];
        }

        return self::request('GET', '/payments/' . urlencode($paymentId), [], $keyId, $keySecret);
    }

    /**
     * Capture an authorized payment (if manual capture is ever required)
     */
    public static function capturePayment(string $paymentId, int $amountPaise, string $currency = 'INR'): array {
        $keyId = self::getKeyId();
        $keySecret = self::getKeySecret();

        return self::request('POST', '/payments/' . urlencode($paymentId) . '/capture', [
            'amount' => $amountPaise,
            'currency' => $currency
        ], $keyId, $keySecret);
    }

    /**
     * Verify Razorpay Checkout signature using HMAC SHA256 and hash_equals()
     *
     * String to hash: razorpay_order_id + "|" + razorpay_payment_id
     */
    public static function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool {
        if (empty($orderId) || empty($paymentId) || empty($signature)) {
            return false;
        }

        $keySecret = self::getKeySecret();
        if (empty($keySecret)) {
            return false;
        }

        $data = $orderId . '|' . $paymentId;
        $expectedSignature = hash_hmac('sha256', $data, $keySecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify Razorpay Webhook signature using HMAC SHA256 and hash_equals()
     */
    public static function verifyWebhookSignature(string $rawBody, string $webhookSignature): bool {
        if (empty($rawBody) || empty($webhookSignature)) {
            return false;
        }

        $webhookSecret = self::getWebhookSecret();
        if (empty($webhookSecret)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $rawBody, $webhookSecret);
        return hash_equals($expectedSignature, $webhookSignature);
    }

    /**
     * Real server-side Test API Connection check
     */
    public static function testConnection(): array {
        $settings = self::getSettings();
        $keyId = $settings['key_id'];
        $keySecret = $settings['key_secret'];
        $env = $settings['environment'];

        if (empty($keyId) || empty($keySecret)) {
            return [
                'success' => false,
                'error' => 'Please enter both Razorpay Key ID and Key Secret before testing the connection.'
            ];
        }

        // Test credentials by querying Razorpay Orders API
        $response = self::request('GET', '/orders?count=1', [], $keyId, $keySecret);

        if ($response['success']) {
            $modeLabel = ($env === 'live') ? 'LIVE Mode' : 'TEST Mode';
            return [
                'success' => true,
                'message' => "Successfully connected to Razorpay API in {$modeLabel}! Credentials verified.",
                'environment' => $env
            ];
        }

        $rawError = $response['error'] ?? 'Authentication failed with Razorpay API.';
        return [
            'success' => false,
            'error' => "Connection failed: {$rawError}. Please verify your Key ID and Key Secret.",
            'environment' => $env
        ];
    }

    // =========================================================================
    // Low-Level Native cURL Transport
    // =========================================================================

    /**
     * Internal cURL executor supporting mock overrides for tests
     */
    private static function request(string $method, string $path, array $data, string $keyId, string $keySecret): array {
        // If a test mock handler is registered, delegate to it
        if (self::$mockHandler !== null) {
            return call_user_func(self::$mockHandler, $method, $path, $data, $keyId, $keySecret);
        }

        if (!function_exists('curl_init')) {
            return [
                'success' => false,
                'error' => 'PHP cURL extension is not enabled on this server.'
            ];
        }

        $url = self::API_BASE_URL . $path;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'NA-Fresh-Fruits-Coconuts/1.0.0 (PHP ' . PHP_VERSION . ')');

        $headers = [
            'Accept: application/json',
        ];

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($jsonData);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false || !empty($curlError)) {
            return [
                'success' => false,
                'error' => 'Network error connecting to Razorpay: ' . $curlError,
                'http_code' => 0
            ];
        }

        $decoded = json_decode($body, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $decoded,
                'http_code' => $httpCode
            ];
        }

        // Razorpay API returns error details in JSON
        $errMsg = 'HTTP ' . $httpCode;
        if (!empty($decoded['error']['description'])) {
            $errMsg = $decoded['error']['description'];
        } elseif (!empty($decoded['message'])) {
            $errMsg = $decoded['message'];
        }

        return [
            'success' => false,
            'error' => $errMsg,
            'data' => $decoded,
            'http_code' => $httpCode
        ];
    }

    /**
     * Set a mock handler for testing
     * @param callable|null $handler
     */
    public static function setMockHandler(?callable $handler): void {
        self::$mockHandler = $handler;
    }
}
