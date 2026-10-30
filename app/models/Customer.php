<?php
/**
 * Customer Model & Database Operations
 * N.A Fresh Fruits & Coconuts
 */

class Customer {
    /**
     * Clean and normalize Indian phone numbers to 10-digit string
     */
    public static function normalizePhone(string $phone): string {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) > 10 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }
        return substr($digits, -10);
    }

    /**
     * Find customer by normalized phone number
     */
    public static function findByPhone(string $phone): ?array {
        $clean = self::normalizePhone($phone);
        if (empty($clean)) {
            return null;
        }

        $pdo = Database::getConnection();
        // Check exact match, clean 10-digit, and +91 variations
        $stmt = $pdo->prepare("
            SELECT * FROM `customers` 
            WHERE `phone` = ? 
               OR `phone` = ? 
               OR `phone` = ? 
               OR `phone` LIKE ? 
            LIMIT 1
        ");
        $stmt->execute([
            $phone,
            $clean,
            '+91 ' . $clean,
            '%' . $clean
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find customer by email address
     */
    public static function findByEmail(string $email): ?array {
        $trimmed = strtolower(trim($email));
        if (empty($trimmed)) {
            return null;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `customers` WHERE LOWER(`email`) = ? LIMIT 1");
        $stmt->execute([$trimmed]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find customer by identifier (phone or email)
     */
    public static function findByIdentifier(string $identifier): ?array {
        $trimmed = trim($identifier);
        if (str_contains($trimmed, '@')) {
            return self::findByEmail($trimmed);
        }
        return self::findByPhone($trimmed);
    }

    /**
     * Find customer by ID
     */
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `customers` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Check if phone exists (excluding optional ID for profile updates)
     */
    public static function phoneExists(string $phone, ?int $excludeId = null): bool {
        $clean = self::normalizePhone($phone);
        if (empty($clean)) {
            return false;
        }

        $pdo = Database::getConnection();
        $sql = "SELECT `id`, `password_hash` FROM `customers` WHERE (`phone` = ? OR `phone` = ? OR `phone` LIKE ?)";
        $params = [$phone, $clean, '%' . $clean];

        if ($excludeId !== null) {
            $sql .= " AND `id` != ?";
            $params[] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (bool)$row;
    }

    /**
     * Check if email exists (excluding optional ID for profile updates)
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool {
        $trimmed = strtolower(trim($email));
        if (empty($trimmed)) {
            return false;
        }

        $pdo = Database::getConnection();
        $sql = "SELECT `id` FROM `customers` WHERE LOWER(`email`) = ?";
        $params = [$trimmed];

        if ($excludeId !== null) {
            $sql .= " AND `id` != ?";
            $params[] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Create a new customer record with password hash
     */
    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $cleanPhone = self::normalizePhone($data['phone']);
        $formattedPhone = '+91 ' . $cleanPhone;

        $stmt = $pdo->prepare("
            INSERT INTO `customers` (
                `name`, `phone`, `email`, `password_hash`, `address`, `city`, `state`, `pincode`, `status`, `created_at`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ");

        $stmt->execute([
            trim($data['name']),
            $formattedPhone,
            !empty($data['email']) ? strtolower(trim($data['email'])) : null,
            $data['password_hash'],
            trim($data['address'] ?? ''),
            trim($data['city'] ?? 'Bhopal'),
            trim($data['state'] ?? 'Madhya Pradesh'),
            trim($data['pincode'] ?? '462016')
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Activate / upgrade an existing guest customer record by setting their password and email
     */
    public static function activateAccount(int $id, array $data): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE `customers` SET
                `name` = ?,
                `email` = ?,
                `password_hash` = ?,
                `status` = 'active',
                `updated_at` = NOW()
            WHERE `id` = ?
        ");

        return $stmt->execute([
            trim($data['name']),
            !empty($data['email']) ? strtolower(trim($data['email'])) : null,
            $data['password_hash'],
            $id
        ]);
    }

    /**
     * Update customer password
     */
    public static function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE `customers` SET `password_hash` = ?, `updated_at` = NOW() WHERE `id` = ?");
        return $stmt->execute([$hash, $id]);
    }

    /**
     * Update customer profile info
     */
    public static function updateProfile(int $id, array $data): bool {
        $pdo = Database::getConnection();
        $fields = [];
        $params = [];

        if (isset($data['name'])) {
            $fields[] = "`name` = ?";
            $params[] = trim($data['name']);
        }
        if (array_key_exists('email', $data)) {
            $fields[] = "`email` = ?";
            $params[] = !empty($data['email']) ? strtolower(trim($data['email'])) : null;
        }
        if (isset($data['address'])) {
            $fields[] = "`address` = ?";
            $params[] = trim($data['address']);
        }
        if (isset($data['city'])) {
            $fields[] = "`city` = ?";
            $params[] = trim($data['city']);
        }
        if (isset($data['state'])) {
            $fields[] = "`state` = ?";
            $params[] = trim($data['state']);
        }
        if (isset($data['pincode'])) {
            $fields[] = "`pincode` = ?";
            $params[] = trim($data['pincode']);
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "`updated_at` = NOW()";
        $params[] = $id;

        $sql = "UPDATE `customers` SET " . implode(', ', $fields) . " WHERE `id` = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Update last login timestamp
     */
    public static function updateLastLogin(int $id): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE `customers` SET `last_login_at` = NOW() WHERE `id` = ?");
        $stmt->execute([$id]);
    }

    /**
     * Check if customer login is throttled (max 5 failed attempts in 15 minutes)
     */
    public static function isLoginThrottled(string $identifier, string $ip): bool {
        $pdo = Database::getConnection();
        try {
            // Check account-specific failed attempts (5 in 15 min)
            $cleanIdent = str_contains($identifier, '@') ? strtolower(trim($identifier)) : self::normalizePhone($identifier);
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM `customer_login_attempts`
                WHERE (`identifier` = ? OR `identifier` = ?)
                  AND `success` = 0 
                  AND `attempted_at` > (NOW() - INTERVAL 15 MINUTE)
            ");
            $stmt->execute([$identifier, $cleanIdent]);
            if ((int)$stmt->fetchColumn() >= 5) {
                return true;
            }

            // Check broad IP spamming (30 failed attempts across all accounts in 15 min)
            $stmtIp = $pdo->prepare("
                SELECT COUNT(*) FROM `customer_login_attempts`
                WHERE `ip_address` = ?
                  AND `success` = 0 
                  AND `attempted_at` > (NOW() - INTERVAL 15 MINUTE)
            ");
            $stmtIp->execute([$ip]);
            return (int)$stmtIp->fetchColumn() >= 30;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Clear failed login attempts after successful authentication
     */
    public static function clearLoginAttempts(string $identifier, string $ip): void {
        $pdo = Database::getConnection();
        try {
            $cleanIdent = str_contains($identifier, '@') ? strtolower(trim($identifier)) : self::normalizePhone($identifier);
            $stmt = $pdo->prepare("DELETE FROM `customer_login_attempts` WHERE `identifier` = ? OR `identifier` = ?");
            $stmt->execute([$identifier, $cleanIdent]);
        } catch (Exception $e) {
            // non-blocking
        }
    }

    /**
     * Record customer login attempt
     */
    public static function recordLoginAttempt(string $identifier, string $ip, bool $success): void {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO `customer_login_attempts` (`identifier`, `ip_address`, `success`, `attempted_at`) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$identifier, $ip, $success ? 1 : 0]);
        } catch (Exception $e) {
            // Non-blocking log
            error_log("Failed to record customer login attempt: " . $e->getMessage());
        }
    }

    /**
     * Get orders for a specific customer (Order Ownership Enforcement)
     */
    public static function getCustomerOrders(int $customerId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM `orders` 
            WHERE `customer_id` = ? 
            ORDER BY `id` DESC
        ");
        $stmt->execute([$customerId]);
        $orders = $stmt->fetchAll();

        foreach ($orders as &$o) {
            $o['items'] = Order::getOrderItems($o['id']);
            $o['history'] = Order::getOrderHistory($o['id']);
        }

        return $orders;
    }

    /**
     * Get single order strictly verified for ownership by authenticated customer
     */
    public static function getCustomerOrderById(int $customerId, int $orderId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM `orders` 
            WHERE `id` = ? AND `customer_id` = ? 
            LIMIT 1
        ");
        $stmt->execute([$orderId, $customerId]);
        $order = $stmt->fetch();

        if ($order) {
            $order['items'] = Order::getOrderItems($order['id']);
            $order['history'] = Order::getOrderHistory($order['id']);
            return $order;
        }

        return null;
    }

    /**
     * For Admin Customer Management: list all customers with order counts & total spent
     */
    public static function getAll(array $filters = [], int $limit = 20, int $offset = 0): array {
        $pdo = Database::getConnection();
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(`c`.`name` LIKE ? OR `c`.`phone` LIKE ? OR `c`.`email` LIKE ?)";
            $s = '%' . trim($filters['search']) . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['status'])) {
            $where[] = "`c`.`status` = ?";
            $params[] = $filters['status'];
        }

        $sql = "
            SELECT 
                `c`.*,
                COUNT(`o`.`id`) AS `total_orders`,
                COALESCE(SUM(CASE WHEN `o`.`order_status` != 'cancelled' THEN `o`.`total_amount` ELSE 0 END), 0) AS `total_spent`
            FROM `customers` `c`
            LEFT JOIN `orders` `o` ON `c`.`id` = `o`.`customer_id`
        ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " GROUP BY `c`.`id` ORDER BY `c`.`id` DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count all customers for pagination
     */
    public static function countAll(array $filters = []): int {
        $pdo = Database::getConnection();
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(`name` LIKE ? OR `phone` LIKE ? OR `email` LIKE ?)";
            $s = '%' . trim($filters['search']) . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['status'])) {
            $where[] = "`status` = ?";
            $params[] = $filters['status'];
        }

        $sql = "SELECT COUNT(*) FROM `customers`";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
