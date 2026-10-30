<?php
/**
 * Global Application Configuration
 * N.A Fresh Fruits & Coconuts
 */

// Timezone (Indian Standard Time matching the business context)
date_default_timezone_set('Asia/Kolkata');

// Error reporting
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../storage/logs/php_error.log');

// Environment settings
if (!defined('APP_NAME')) define('APP_NAME', 'N.A Fresh Fruits & Coconuts');
if (!defined('APP_URL')) define('APP_URL', '');
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', '₹');

// Installation Lock File Path
if (!defined('INSTALL_LOCK_FILE')) {
    define('INSTALL_LOCK_FILE', __DIR__ . '/../../storage/installed.lock');
}

function is_installed(): bool {
    return file_exists(INSTALL_LOCK_FILE);
}

// Database configuration loading
$dbConfigFile = __DIR__ . '/db_config.php';
$loadedDbConfig = (file_exists($dbConfigFile) && is_readable($dbConfigFile)) ? (require $dbConfigFile) : [];
if (!is_array($loadedDbConfig)) {
    $loadedDbConfig = [];
}

if (!defined('DB_HOST')) define('DB_HOST', !empty($loadedDbConfig['host']) ? (string)$loadedDbConfig['host'] : (getenv('DB_HOST') ?: '127.0.0.1'));
if (!defined('DB_PORT')) define('DB_PORT', !empty($loadedDbConfig['port']) ? (string)$loadedDbConfig['port'] : (getenv('DB_PORT') ?: '3306'));
if (!defined('DB_NAME')) define('DB_NAME', !empty($loadedDbConfig['database']) ? (string)$loadedDbConfig['database'] : (getenv('DB_NAME') ?: 'na_coconuts'));
if (!defined('DB_USER')) define('DB_USER', !empty($loadedDbConfig['username']) ? (string)$loadedDbConfig['username'] : (getenv('DB_USER') ?: 'na_user'));
if (!defined('DB_PASS')) define('DB_PASS', isset($loadedDbConfig['password']) ? (string)$loadedDbConfig['password'] : (getenv('DB_PASS') ?: 'na_pass123'));
if (!defined('DB_PREFIX')) define('DB_PREFIX', (string)($loadedDbConfig['prefix'] ?? ''));

// Security settings
define('SESSION_LIFETIME', 86400 * 7); // 7 days
define('CSRF_TOKEN_KEY', '_csrf_token');
define('UPLOAD_DIR', __DIR__ . '/../../public/assets/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
