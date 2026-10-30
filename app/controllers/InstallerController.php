<?php
/**
 * In-Browser Web Installer Controller
 * N.A Fresh Fruits & Coconuts
 */

class InstallerController {
    
    public static function handle(): void {
        // If application is already installed:
        if (is_installed()) {
            // Check for authorized admin repair mode
            if (!empty($_GET['admin_repair']) && AuthService::check()) {
                self::renderAdminRepairView();
                return;
            }

            self::renderLockedView();
            return;
        }

        $step = max(1, min(6, (int)($_GET['step'] ?? 1)));
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($step) {
            case 1:
                self::step1Requirements();
                break;
            case 2:
                self::step2Database($method);
                break;
            case 3:
                self::step3Initialize($method);
                break;
            case 4:
                self::step4Admin($method);
                break;
            case 5:
                self::step5Business($method);
                break;
            case 6:
                self::step6Complete();
                break;
            default:
                header('Location: /install?step=1');
                exit;
        }
    }

    private static function renderLockedView(): void {
        $isAdmin = AuthService::check();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Installer Locked - N.A Fresh Fruits & Coconuts</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-[#f8faf8] text-gray-800 min-h-screen flex items-center justify-center p-4">
            <div class="max-w-md w-full bg-white p-8 rounded-3xl border border-gray-100 shadow-sm text-center space-y-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Installer is Locked</h1>
                <p class="text-sm text-gray-500 leading-relaxed">
                    N.A Fresh Fruits & Coconuts has already been installed and initialized. For security reasons, the installation wizard cannot be re-run.
                </p>
                <div class="pt-3 flex flex-col gap-2">
                    <a href="/" class="bg-[#235d3a] hover:bg-[#1b4a2e] text-white font-bold text-sm py-3 px-6 rounded-xl transition">
                        Go to Storefront
                    </a>
                    <?php if ($isAdmin): ?>
                    <a href="/install?admin_repair=1" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-semibold text-xs py-2.5 px-4 rounded-xl border border-emerald-200 transition">
                        Administrator Database Health &amp; Diagnostics
                    </a>
                    <a href="/admin" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs py-2.5 px-4 rounded-xl transition">
                        Store Admin Dashboard
                    </a>
                    <?php else: ?>
                    <a href="/admin" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs py-2.5 px-4 rounded-xl transition">
                        Store Admin Login
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    private static function renderAdminRepairView(): void {
        AuthService::requireAdmin();
        $pdo = Database::getConnection();
        $message = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST[CSRF_TOKEN_KEY] ?? '')) {
                $message = ['type' => 'error', 'text' => 'CSRF verification failed.'];
            } else {
                try {
                    // Non-destructive verification and column additions
                    $schemaFile = __DIR__ . '/../../database/schema.sql';
                    $sql = file_get_contents($schemaFile);
                    $statements = self::splitSqlStatements($sql);
                    foreach ($statements as $st) {
                        if (preg_match('/^\s*(CREATE\s+DATABASE|USE\s+)/i', $st)) continue;
                        $pdo->exec($st);
                    }

                    // Ensure customer auth columns
                    $cols = $pdo->query("SHOW COLUMNS FROM `customers`")->fetchAll(PDO::FETCH_COLUMN);
                    if (!in_array('email', $cols)) {
                        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `email` VARCHAR(100) NULL AFTER `phone`");
                        $pdo->exec("ALTER TABLE `customers` ADD INDEX `idx_email` (`email`)");
                    }
                    if (!in_array('password_hash', $cols)) {
                        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `password_hash` VARCHAR(255) NULL AFTER `email`");
                    }
                    if (!in_array('status', $cols)) {
                        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'active' AFTER `pincode`");
                    }
                    if (!in_array('last_login_at', $cols)) {
                        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `last_login_at` DATETIME NULL AFTER `status`");
                    }

                    $message = ['type' => 'success', 'text' => 'Database tables and columns verified and synchronized safely without modifying any existing customer or order data.'];
                } catch (Exception $e) {
                    $message = ['type' => 'error', 'text' => 'Diagnostics error: ' . Database::sanitizeErrorMessage($e->getMessage())];
                }
            }
        }

        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $custCount = (int)$pdo->query("SELECT COUNT(*) FROM `customers`")->fetchColumn();
        $orderCount = (int)$pdo->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
        $prodCount = (int)$pdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Diagnostics - N.A Fresh Fruits & Coconuts</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-[#f8faf8] text-gray-800 min-h-screen p-4 sm:p-8">
            <div class="max-w-2xl mx-auto bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div>
                        <h1 class="text-xl font-black text-gray-900">Database Diagnostics &amp; Repair</h1>
                        <p class="text-xs text-gray-500">Authorized maintenance for store administrator</p>
                    </div>
                    <a href="/admin" class="text-xs font-bold text-gray-600 hover:text-gray-900">&larr; Back to Admin</a>
                </div>

                <?php if ($message): ?>
                <div class="p-3.5 rounded-xl text-xs font-semibold <?= $message['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
                    <?= e($message['text']) ?>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-100">
                        <span class="text-xl font-black text-gray-900 block"><?= $custCount ?></span>
                        <span class="text-[10px] text-gray-400 font-bold uppercase">Customers</span>
                    </div>
                    <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-100">
                        <span class="text-xl font-black text-gray-900 block"><?= $orderCount ?></span>
                        <span class="text-[10px] text-gray-400 font-bold uppercase">Orders</span>
                    </div>
                    <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-100">
                        <span class="text-xl font-black text-gray-900 block"><?= $prodCount ?></span>
                        <span class="text-[10px] text-gray-400 font-bold uppercase">Products</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <span class="text-xs font-bold text-gray-700 block">Active MySQL Tables (<?= count($tables) ?> detected):</span>
                    <div class="p-3 bg-gray-50 rounded-2xl text-xs font-mono text-gray-600 grid grid-cols-2 gap-1.5 border border-gray-100">
                        <?php foreach ($tables as $t): ?>
                        <div class="flex items-center gap-1.5">
                            <span class="text-emerald-600 font-bold">&bull;</span>
                            <span><?= e($t) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <form method="POST" class="pt-2">
                    <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
                    <button type="submit" class="w-full bg-[#235d3a] hover:bg-[#1b4a2e] text-white font-bold text-xs py-3 px-6 rounded-xl transition shadow-xs flex items-center justify-center gap-2">
                        <span>Verify &amp; Synchronize Table Schema (Non-Destructive)</span>
                    </button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    private static function step1Requirements(): void {
        $checks = [
            'php_version' => [
                'name' => 'PHP Version (8.2+ required)',
                'current' => PHP_VERSION,
                'pass' => version_compare(PHP_VERSION, '8.2.0', '>=')
            ],
            'ext_pdo' => [
                'name' => 'PDO Extension',
                'current' => extension_loaded('pdo') ? 'Installed' : 'Missing',
                'pass' => extension_loaded('pdo')
            ],
            'ext_pdo_mysql' => [
                'name' => 'PDO MySQL Driver',
                'current' => extension_loaded('pdo_mysql') ? 'Installed' : 'Missing',
                'pass' => extension_loaded('pdo_mysql')
            ],
            'ext_mbstring' => [
                'name' => 'Mbstring Extension',
                'current' => extension_loaded('mbstring') ? 'Installed' : 'Missing',
                'pass' => extension_loaded('mbstring')
            ],
            'ext_curl' => [
                'name' => 'cURL Extension',
                'current' => extension_loaded('curl') ? 'Installed' : 'Missing',
                'pass' => extension_loaded('curl')
            ],
            'ext_gd' => [
                'name' => 'GD Image Extension',
                'current' => extension_loaded('gd') ? 'Installed' : 'Missing',
                'pass' => extension_loaded('gd')
            ],
            'ext_json' => [
                'name' => 'JSON Extension',
                'current' => extension_loaded('json') ? 'Installed' : 'Missing',
                'pass' => extension_loaded('json')
            ],
            'ext_fileinfo' => [
                'name' => 'Fileinfo Extension',
                'current' => extension_loaded('fileinfo') ? 'Installed' : 'Missing',
                'pass' => extension_loaded('fileinfo')
            ],
            'ext_session' => [
                'name' => 'Session Support',
                'current' => extension_loaded('session') ? 'Installed' : 'Missing',
                'pass' => extension_loaded('session')
            ],
            'dir_storage' => [
                'name' => 'Storage Directory Writable',
                'current' => is_writable(__DIR__ . '/../../storage') ? 'Writable' : 'Read-only',
                'pass' => is_writable(__DIR__ . '/../../storage')
            ],
            'dir_config' => [
                'name' => 'Config Directory Writable',
                'current' => is_writable(__DIR__ . '/../../app/config') ? 'Writable' : 'Read-only',
                'pass' => is_writable(__DIR__ . '/../../app/config')
            ],
            'dir_uploads' => [
                'name' => 'Uploads Directory Writable',
                'current' => is_writable(__DIR__ . '/../../public/assets/uploads') ? 'Writable' : 'Read-only',
                'pass' => is_writable(__DIR__ . '/../../public/assets/uploads')
            ],
        ];

        $allPassed = true;
        foreach ($checks as $c) {
            if (!$c['pass']) {
                $allPassed = false;
                break;
            }
        }

        require __DIR__ . '/../views/installer/step1.php';
    }

    private static function step2Database(string $method): void {
        $error = null;

        // Current or submitted parameters
        $host = $_POST['host'] ?? ($_SESSION['installer_db']['host'] ?? DB_HOST);
        $port = $_POST['port'] ?? ($_SESSION['installer_db']['port'] ?? DB_PORT);
        $database = $_POST['database'] ?? ($_SESSION['installer_db']['database'] ?? DB_NAME);
        $username = $_POST['username'] ?? ($_SESSION['installer_db']['username'] ?? DB_USER);
        $password = $_POST['password'] ?? ($_SESSION['installer_db']['password'] ?? DB_PASS);
        $createDb = isset($_POST['create_db']) || !empty($_POST['create_db_flag']);

        if ($method === 'POST') {
            if (!verify_csrf_token($_POST[CSRF_TOKEN_KEY] ?? '')) {
                $error = "Session security token expired. Please try again.";
            } else {
                $test = Database::testConnection($host, $port, $database, $username, $password, $createDb);
                if ($test['success']) {
                    // Save in session
                    $_SESSION['installer_db'] = [
                        'host' => trim($host),
                        'port' => trim($port),
                        'database' => trim($database),
                        'username' => trim($username),
                        'password' => $password,
                        'prefix' => ''
                    ];

                    // Save to db_config.php safely
                    $saveRes = Database::saveConfiguration(trim($host), trim($port), trim($database), trim($username), $password, '');
                    if ($saveRes['success']) {
                        Database::setCustomConfig($_SESSION['installer_db']);
                        header('Location: /install?step=3');
                        exit;
                    } else {
                        $error = $saveRes['error'];
                    }
                } else {
                    $error = $test['error'];
                }
            }
        }

        require __DIR__ . '/../views/installer/step2.php';
    }

    private static function step3Initialize(string $method): void {
        $error = null;
        $existingTables = [];
        $isInitialized = false;
        $connectionError = null;

        // Use active or session config
        if (!empty($_SESSION['installer_db'])) {
            Database::setCustomConfig($_SESSION['installer_db']);
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $existingTables = $tables;
            $requiredTables = ['products', 'orders', 'admins', 'settings', 'order_items', 'customers'];
            $matching = array_intersect($requiredTables, $tables);
            $isInitialized = (count($matching) === count($requiredTables));
        } catch (Exception $e) {
            $connectionError = Database::sanitizeErrorMessage($e->getMessage());
        }

        if ($method === 'POST') {
            if ($connectionError) {
                $error = "Cannot connect to configured database: " . $connectionError . ". Please return to Step 2 and verify credentials.";
            } elseif (!verify_csrf_token($_POST[CSRF_TOKEN_KEY] ?? '')) {
                $error = "Session token expired. Please try again.";
            } else {
                try {
                    $schemaFile = __DIR__ . '/../../database/schema.sql';
                    if (!file_exists($schemaFile)) {
                        throw new Exception("Schema file database/schema.sql not found.");
                    }
                    $sql = file_get_contents($schemaFile);
                    
                    // Split into discrete statements safely
                    $statements = self::splitSqlStatements($sql);

                    foreach ($statements as $stmtSql) {
                        $stmtSql = trim($stmtSql);
                        if (empty($stmtSql)) continue;

                        // Never execute CREATE DATABASE or USE statements
                        if (preg_match('/^\s*(CREATE\s+DATABASE|USE\s+)/i', $stmtSql)) {
                            continue;
                        }

                        $pdo->exec($stmtSql);
                    }

                    // Ensure customer auth columns exist in customers table without deleting any existing records
                    $cols = $pdo->query("SHOW COLUMNS FROM `customers`")->fetchAll(PDO::FETCH_COLUMN);
                    if (!in_array('email', $cols)) {
                        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `email` VARCHAR(100) NULL AFTER `phone`");
                        $pdo->exec("ALTER TABLE `customers` ADD INDEX `idx_email` (`email`)");
                    }
                    if (!in_array('password_hash', $cols)) {
                        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `password_hash` VARCHAR(255) NULL AFTER `email`");
                    }
                    if (!in_array('status', $cols)) {
                        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'active' AFTER `pincode`");
                    }
                    if (!in_array('last_login_at', $cols)) {
                        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `last_login_at` DATETIME NULL AFTER `status`");
                    }

                    // Ensure customer_login_attempts table exists
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS `customer_login_attempts` (
                            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                            `identifier` VARCHAR(100) NOT NULL,
                            `ip_address` VARCHAR(45) NOT NULL,
                            `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            `success` TINYINT(1) NOT NULL DEFAULT 0,
                            INDEX `idx_ident_time` (`identifier`, `attempted_at`),
                            INDEX `idx_ip_time` (`ip_address`, `attempted_at`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");

                    // Seed default product if not present (non-destructive)
                    $stmtCheckProd = $pdo->query("SELECT COUNT(*) FROM `products` WHERE `slug` = 'fresh-coconut-water'");
                    if ($stmtCheckProd->fetchColumn() == 0) {
                        $gallery = json_encode([
                            '/assets/images/product-main.png',
                            '/assets/images/thumb-1.png',
                            '/assets/images/thumb-2.png',
                            '/assets/images/thumb-3.png',
                            '/assets/images/thumb-4.png'
                        ]);
                        $stmtInsProd = $pdo->prepare("
                            INSERT INTO `products` (`name`, `slug`, `tagline`, `description`, `price`, `stock_quantity`, `is_available`, `image_url`, `gallery_json`)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmtInsProd->execute([
                            'Fresh Coconut Water',
                            'fresh-coconut-water',
                            'Pure • Natural • Healthy',
                            'Fresh tender coconut water, straight from nature. No added sugar, no preservatives, no chemicals. Just pure and natural hydration.',
                            40.00,
                            150,
                            1,
                            '/assets/images/product-main.png',
                            $gallery
                        ]);
                    }

                    // Seed default settings if missing
                    $defaultSettings = [
                        'store_name' => 'N.A Fresh Fruits & Coconuts',
                        'store_tagline' => 'Fresh tender coconut water straight from farm',
                        'store_phone' => '+91 98765 43210',
                        'store_email' => 'support@nafruitsandcoconuts.com',
                        'store_address' => '123, Green Park, Near Main Road, Bhopal, Madhya Pradesh - 462016',
                        'currency_symbol' => '₹',
                        'local_delivery_enabled' => '1',
                        'shop_pickup_enabled' => '1',
                        'delivery_charge' => '40.00',
                        'min_order_amount' => '40.00',
                        'free_delivery_above' => '500.00',
                        'service_areas' => 'Bhopal, MP Nagar, Arera Colony, Kolar Road, Green Park, Shahpura',
                        'store_open_time' => '07:00 AM',
                        'store_close_time' => '09:00 PM',
                        'stock_deduction_policy' => 'order_placement',
                    ];
                    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `settings` WHERE `key_name` = ?");
                    $stmtInsert = $pdo->prepare("INSERT INTO `settings` (`key_name`, `value`, `description`) VALUES (?, ?, ?)");
                    foreach ($defaultSettings as $k => $v) {
                        $stmtCheck->execute([$k]);
                        if ($stmtCheck->fetchColumn() == 0) {
                            $stmtInsert->execute([$k, $v, 'Default setting for ' . $k]);
                        }
                    }

                    // Verify required tables exist
                    $stmtVerify = $pdo->query("SHOW TABLES");
                    $postTables = $stmtVerify->fetchAll(PDO::FETCH_COLUMN);
                    $required = ['products', 'orders', 'admins', 'settings', 'customers', 'customer_login_attempts'];
                    $missingTables = array_diff($required, $postTables);
                    if (!empty($missingTables)) {
                        throw new Exception("Required tables could not be verified: " . implode(', ', $missingTables));
                    }

                    header('Location: /install?step=4');
                    exit;
                } catch (PDOException $e) {
                    $errCode = (int)$e->getCode();
                    $errMsg = $e->getMessage();
                    if ($errCode === 1044 || $errCode === 1142 || str_contains($errMsg, '1044') || str_contains($errMsg, '1142') || str_contains($errMsg, 'Access denied')) {
                        $cfg = Database::getActiveConfig();
                        $error = "Hosting Permission Error (1044/1142): MySQL user '" . e($cfg['username']) . "' does not have permission to CREATE or ALTER tables in database '" . e($cfg['database']) . "'.\n\n"
                               . "Action Required in cPanel:\n"
                               . "1. In cPanel, navigate to 'MySQL Databases'.\n"
                               . "2. Scroll down to the 'Add User to Database' section.\n"
                               . "3. Select User: '" . e($cfg['username']) . "' and Database: '" . e($cfg['database']) . "'.\n"
                               . "4. Click 'Add', tick the 'ALL PRIVILEGES' checkbox, and click 'Make Changes'.\n"
                               . "5. Return here and click 'Create Tables & Continue'.";
                    } else {
                        $error = "Database initialization error: " . Database::sanitizeErrorMessage($errMsg);
                    }
                } catch (Exception $e) {
                    $error = "Database initialization error: " . Database::sanitizeErrorMessage($e->getMessage());
                }
            }
        }

        require __DIR__ . '/../views/installer/step3.php';
    }

    private static function splitSqlStatements(string $sql): array {
        // Strip block comments
        $sql = preg_replace('!/\*.*?\*/!s', '', $sql);
        // Strip line comments
        $sql = preg_replace('/^--.*?$/m', '', $sql);

        $tokens = explode(';', $sql);
        $clean = [];
        foreach ($tokens as $t) {
            $t = trim($t);
            if (!empty($t)) {
                $clean[] = $t;
            }
        }
        return $clean;
    }

    private static function step4Admin(string $method): void {
        $error = null;
        $pdo = Database::getConnection();

        $stmtAdmin = $pdo->query("SELECT COUNT(*) FROM `admins`");
        $adminCount = (int)$stmtAdmin->fetchColumn();

        if ($method === 'POST') {
            if (!verify_csrf_token($_POST[CSRF_TOKEN_KEY] ?? '')) {
                $error = "Security token mismatch.";
            } elseif (!empty($_POST['keep_existing_admin']) && $adminCount > 0) {
                // User opted to keep existing administrator
                header('Location: /install?step=5');
                exit;
            } else {
                $name = trim($_POST['admin_name'] ?? '');
                $username = trim($_POST['admin_username'] ?? '');
                $email = trim($_POST['admin_email'] ?? '');
                $password = $_POST['admin_password'] ?? '';
                $confirmPassword = $_POST['admin_password_confirm'] ?? '';

                if (empty($name) || strlen($name) < 2) {
                    $error = "Please enter administrator's full name.";
                } elseif (empty($username) || strlen($username) < 3) {
                    $error = "Username must be at least 3 characters.";
                } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = "Please enter a valid administrator email.";
                } elseif (strlen($password) < 8) {
                    $error = "Password must be at least 8 characters long.";
                } elseif ($password !== $confirmPassword) {
                    $error = "Passwords do not match.";
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    if ($adminCount === 0) {
                        $stmt = $pdo->prepare("INSERT INTO `admins` (`username`, `email`, `password_hash`, `name`, `role`) VALUES (?, ?, ?, ?, 'superadmin')");
                        $stmt->execute([$username, $email, $hash, $name]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE `admins` SET `username` = ?, `email` = ?, `password_hash` = ?, `name` = ?, `updated_at` = NOW() ORDER BY `id` ASC LIMIT 1");
                        $stmt->execute([$username, $email, $hash, $name]);
                    }

                    header('Location: /install?step=5');
                    exit;
                }
            }
        }

        require __DIR__ . '/../views/installer/step4.php';
    }

    private static function step5Business(string $method): void {
        $error = null;
        $pdo = Database::getConnection();

        if ($method === 'POST') {
            if (!verify_csrf_token($_POST[CSRF_TOKEN_KEY] ?? '')) {
                $error = "Security token mismatch.";
            } else {
                $storeName = trim($_POST['store_name'] ?? 'N.A Fresh Fruits & Coconuts');
                $currency = trim($_POST['currency_symbol'] ?? '₹');
                $price = max(1.0, (float)($_POST['product_price'] ?? 40.0));
                $stock = max(0, (int)($_POST['product_stock'] ?? 150));
                $deliveryFee = max(0.0, (float)($_POST['delivery_charge'] ?? 40.0));
                $minOrder = max(0.0, (float)($_POST['min_order_amount'] ?? 40.0));
                $freeAbove = max(0.0, (float)($_POST['free_delivery_above'] ?? 500.0));
                $localDelivery = isset($_POST['local_delivery_enabled']) ? '1' : '0';
                $shopPickup = isset($_POST['shop_pickup_enabled']) ? '1' : '0';
                $timezone = trim($_POST['timezone'] ?? 'Asia/Kolkata');
                $phone = trim($_POST['store_phone'] ?? '+91 98765 43210');
                $address = trim($_POST['store_address'] ?? '123, Green Park, Near Main Road, Bhopal, Madhya Pradesh - 462016');

                $settings = [
                    'store_name' => $storeName,
                    'currency_symbol' => $currency,
                    'delivery_charge' => number_format($deliveryFee, 2, '.', ''),
                    'min_order_amount' => number_format($minOrder, 2, '.', ''),
                    'free_delivery_above' => number_format($freeAbove, 2, '.', ''),
                    'local_delivery_enabled' => $localDelivery,
                    'shop_pickup_enabled' => $shopPickup,
                    'store_timezone' => $timezone,
                    'store_phone' => $phone,
                    'store_address' => $address,
                    'store_open_time' => '07:00 AM',
                    'store_close_time' => '09:00 PM',
                ];

                Setting::setMany($settings);

                // Update product price & stock
                $stmtProd = $pdo->prepare("UPDATE `products` SET `price` = ?, `stock_quantity` = ?, `updated_at` = NOW() WHERE `slug` = 'fresh-coconut-water'");
                $stmtProd->execute([$price, $stock]);

                header('Location: /install?step=6');
                exit;
            }
        }

        require __DIR__ . '/../views/installer/step5.php';
    }

    private static function step6Complete(): void {
        $validation = [];
        $allPassed = true;

        // 1. Check DB Connection
        try {
            $pdo = Database::getConnection();
            $validation['database'] = ['name' => 'MySQL Database Connection', 'pass' => true];
        } catch (Exception $e) {
            $validation['database'] = ['name' => 'MySQL Database Connection', 'pass' => false, 'error' => Database::sanitizeErrorMessage($e->getMessage())];
            $allPassed = false;
        }

        // 2. Check Required Tables
        if ($allPassed) {
            $requiredTables = ['admins', 'products', 'orders', 'order_items', 'settings', 'customers', 'customer_login_attempts'];
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $missing = array_diff($requiredTables, $tables);
            $validation['tables'] = ['name' => 'Core Schema Tables Verified', 'pass' => empty($missing)];
            if (!empty($missing)) {
                $allPassed = false;
                $validation['tables']['error'] = 'Missing tables: ' . implode(', ', $missing);
            }
        }

        // 3. Check Admin User Exists
        if ($allPassed) {
            $stmtAdmin = $pdo->query("SELECT `username`, `email` FROM `admins` LIMIT 1");
            $admin = $stmtAdmin->fetch();
            $validation['admin'] = ['name' => 'Administrator Account Ready', 'pass' => (bool)$admin, 'user' => $admin['username'] ?? 'admin'];
            if (!$admin) {
                $allPassed = false;
                $validation['admin']['error'] = 'No administrator account found in database.';
            }
        }

        // 4. Check Product Ready
        if ($allPassed) {
            $product = Product::getPrimary();
            $validation['product'] = ['name' => 'Fresh Coconut Water Initialized', 'pass' => (bool)$product];
            if (!$product) {
                $allPassed = false;
                $validation['product']['error'] = 'Primary product record not found.';
            }
        }

        // 5. Check Configuration File Saved & Readable
        $configFile = __DIR__ . '/../config/db_config.php';
        $configValid = file_exists($configFile) && is_readable($configFile);
        $validation['config'] = ['name' => 'Database Configuration Saved', 'pass' => $configValid];
        if (!$configValid) {
            $allPassed = false;
            $validation['config']['error'] = 'app/config/db_config.php was not found or is not readable.';
        }

        // 6. Finalize Installation Lock & Database Status
        if ($allPassed) {
            $storageDir = dirname(INSTALL_LOCK_FILE);
            if (!is_dir($storageDir)) {
                @mkdir($storageDir, 0755, true);
            }

            $lockData = json_encode([
                'installed_at' => date('Y-m-d H:i:s'),
                'php_version' => PHP_VERSION,
                'version' => '1.0.0',
                'app_name' => Setting::get('store_name', 'N.A Fresh Fruits & Coconuts'),
                'verified' => true
            ], JSON_PRETTY_PRINT);

            if (@file_put_contents(INSTALL_LOCK_FILE, $lockData) !== false) {
                @chmod(INSTALL_LOCK_FILE, 0644);
                $validation['lock'] = ['name' => 'Installation Security Lock Created', 'pass' => true];
            } else {
                $allPassed = false;
                $validation['lock'] = ['name' => 'Installation Security Lock Created', 'pass' => false, 'error' => 'Could not write storage/installed.lock. Please check directory permissions.'];
            }

            // Set system_installed flag in database settings
            try {
                Setting::set('system_installed', '1');
                Setting::set('system_installed_at', date('Y-m-d H:i:s'));
            } catch (Exception $e) {
                // non-blocking
            }

            // Clear installer session cache
            unset($_SESSION['installer_db']);
        }

        require __DIR__ . '/../views/installer/step6.php';
    }
}
