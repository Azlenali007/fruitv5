<?php
/**
 * Database Connection Helper (PDO Singleton)
 * N.A Fresh Fruits & Coconuts
 */

class Database {
    private static ?PDO $instance = null;

    private static ?array $customConfig = null;

    public static function setCustomConfig(?array $config): void {
        self::$customConfig = $config;
        self::resetConnection();
    }

    public static function getActiveConfig(): array {
        if (self::$customConfig !== null) {
            return self::$customConfig;
        }
        return [
            'host' => DB_HOST,
            'port' => DB_PORT,
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASS,
            'prefix' => DB_PREFIX
        ];
    }

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $cfg = self::getActiveConfig();
            $dsn = "mysql:host=" . $cfg['host'] . ";port=" . $cfg['port'] . ";dbname=" . $cfg['database'] . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            try {
                self::$instance = new PDO($dsn, $cfg['username'], $cfg['password'], $options);
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                $cleanMsg = self::sanitizeErrorMessage($e->getMessage());
                throw new Exception("Unable to connect to MySQL database '" . $cfg['database'] . "': " . $cleanMsg);
            }
        }
        return self::$instance;
    }

    public static function resetConnection(): void {
        self::$instance = null;
    }

    public static function testConnection(string $host, string $port, string $dbname, string $user, string $pass, bool $createDb = false): array {
        // 1. Input validation
        $host = trim($host);
        $port = trim($port);
        $dbname = trim($dbname);
        $user = trim($user);

        if (empty($host)) {
            return ['success' => false, 'error' => 'Database host cannot be empty (use 127.0.0.1 or localhost).', 'db_exists' => false];
        }
        $portInt = (int)$port;
        if ($portInt <= 0 || $portInt > 65535) {
            return ['success' => false, 'error' => 'Invalid MySQL port number. Default is 3306.', 'db_exists' => false];
        }
        if (empty($dbname)) {
            return ['success' => false, 'error' => 'Database name cannot be empty.', 'db_exists' => false];
        }
        if (empty($user)) {
            return ['success' => false, 'error' => 'Database username cannot be empty.', 'db_exists' => false];
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5
        ];

        // 2. Direct connection test to the target database first
        try {
            $dsnWithDb = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            $pdo = new PDO($dsnWithDb, $user, $pass, $options);
            
            // Verify query permissions on this database
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return [
                'success' => true,
                'error' => null,
                'db_exists' => true,
                'db_created' => false,
                'tables_count' => count($tables)
            ];
        } catch (PDOException $e) {
            $errCode = (int)$e->getCode();
            $errMsg = $e->getMessage();

            // 1049: Unknown database
            if ($errCode === 1049 || str_contains($errMsg, '1049') || str_contains($errMsg, 'Unknown database')) {
                if ($createDb) {
                    try {
                        $dsnNoDb = "mysql:host={$host};port={$port};charset=utf8mb4";
                        $pdoRoot = new PDO($dsnNoDb, $user, $pass, $options);
                        $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        
                        // Re-test connection to newly created database
                        new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, $options);
                        return [
                            'success' => true,
                            'error' => null,
                            'db_exists' => true,
                            'db_created' => true,
                            'tables_count' => 0
                        ];
                    } catch (PDOException $createErr) {
                        return [
                            'success' => false,
                            'error' => "Database '{$dbname}' does not exist, and user '{$user}' does not have CREATE DATABASE privileges. In your hosting cPanel, please open 'MySQL Databases', create the database '{$dbname}', add user '{$user}', and grant 'ALL PRIVILEGES'.",
                            'db_exists' => false
                        ];
                    }
                }

                return [
                    'success' => false,
                    'error' => "Database '{$dbname}' does not exist on this MySQL server. Please create the database '{$dbname}' in your hosting control panel or check 'Attempt to create database automatically'.",
                    'db_exists' => false
                ];
            }

            // 1044: Access denied for user to database
            if ($errCode === 1044 || str_contains($errMsg, '1044') || (str_contains($errMsg, 'Access denied for user') && str_contains($errMsg, 'to database'))) {
                return [
                    'success' => false,
                    'error' => "Access denied for user '{$user}' to database '{$dbname}'. In your hosting control panel (cPanel), please navigate to 'MySQL Databases', scroll to 'Add User to Database', select user '{$user}' and database '{$dbname}', and grant 'ALL PRIVILEGES'.",
                    'db_exists' => true
                ];
            }

            // 1045: Authentication failed (bad password or username)
            if ($errCode === 1045 || str_contains($errMsg, '1045') || str_contains($errMsg, 'using password')) {
                return [
                    'success' => false,
                    'error' => "Authentication failed for user '{$user}'. Please verify your MySQL username and password.",
                    'db_exists' => false
                ];
            }

            // 2002: Connection refused / host unreachable
            if ($errCode === 2002 || str_contains($errMsg, '2002') || str_contains($errMsg, 'Connection refused')) {
                return [
                    'success' => false,
                    'error' => "Could not connect to MySQL server at '{$host}:{$port}'. Verify host name and port (usually '127.0.0.1' or 'localhost' on shared hosting).",
                    'db_exists' => false
                ];
            }

            return [
                'success' => false,
                'error' => "Connection error: " . self::sanitizeErrorMessage($errMsg),
                'db_exists' => false
            ];
        }
    }

    /**
     * Save database configuration atomically and safely to app/config/db_config.php
     */
    public static function saveConfiguration(string $host, string $port, string $database, string $username, string $password, string $prefix = ''): array {
        $configDir = __DIR__;
        $configFile = $configDir . '/db_config.php';
        $tmpFile = $configDir . '/db_config.php.tmp.' . bin2hex(random_bytes(6));

        if (!is_writable($configDir) && (!file_exists($configFile) || !is_writable($configFile))) {
            return [
                'success' => false,
                'error' => "Configuration directory {$configDir} is not writable. Please grant write permissions (e.g. chmod 775 app/config) to save your database credentials."
            ];
        }

        $content = "<?php\n/**\n * Database Configuration - Saved by Web Installer\n * N.A Fresh Fruits & Coconuts\n */\nreturn [\n"
            . "    'host' => " . var_export($host, true) . ",\n"
            . "    'port' => " . var_export((string)$port, true) . ",\n"
            . "    'database' => " . var_export($database, true) . ",\n"
            . "    'username' => " . var_export($username, true) . ",\n"
            . "    'password' => " . var_export($password, true) . ",\n"
            . "    'prefix' => " . var_export($prefix, true) . ",\n"
            . "];\n";

        if (@file_put_contents($tmpFile, $content) === false) {
            return [
                'success' => false,
                'error' => "Failed to write temporary configuration file. Check disk permissions."
            ];
        }

        if (!@rename($tmpFile, $configFile)) {
            if (@file_put_contents($configFile, $content) === false) {
                @unlink($tmpFile);
                return [
                    'success' => false,
                    'error' => "Failed to update {$configFile}. Please check file permissions."
                ];
            }
        }
        @chmod($configFile, 0640);

        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($configFile, true);
        }

        // Verify the saved file by reading it back
        try {
            $verified = require $configFile;
            if (!is_array($verified) || ($verified['database'] ?? '') !== $database) {
                return ['success' => false, 'error' => "Saved configuration file could not be verified."];
            }
        } catch (Throwable $e) {
            return ['success' => false, 'error' => "Configuration verification error: " . $e->getMessage()];
        }

        self::resetConnection();
        return ['success' => true, 'error' => null];
    }

    /**
     * Checks if database has core products table
     */
    public static function isReady(): bool {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
            return (bool)$stmt->fetchColumn();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Checks if database is fully populated with all core tables and data
     */
    public static function isConfiguredAndPopulated(): bool {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $required = ['products', 'orders', 'admins', 'settings', 'order_items', 'customers'];
            if (count(array_intersect($required, $tables)) < count($required)) {
                return false;
            }

            // Check if admin exists
            $stmtAdmin = $pdo->query("SELECT COUNT(*) FROM `admins`");
            if ((int)$stmtAdmin->fetchColumn() === 0) {
                return false;
            }

            // Check if primary product exists
            $stmtProd = $pdo->query("SELECT COUNT(*) FROM `products` WHERE `slug` = 'fresh-coconut-water'");
            if ((int)$stmtProd->fetchColumn() === 0) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function sanitizeErrorMessage(string $msg): string {
        // Strip out passwords and sensitive paths
        $msg = preg_replace('/(using password: )(YES|NO)/i', '$1[PROTECTED]', $msg);
        $msg = preg_replace('/password=\S+/i', 'password=[PROTECTED]', $msg);
        return $msg;
    }
}
