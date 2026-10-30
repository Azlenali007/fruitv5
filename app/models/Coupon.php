<?php
/**
 * Coupon Model
 * N.A Fresh Fruits & Coconuts
 * Manages coupon creation, validation, discount calculations, and usage tracking.
 */

class Coupon {
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';
    public const TYPE_FREE_DELIVERY = 'free_delivery';

    /**
     * Normalize coupon code: uppercase, trim, alphanumeric with underscores and hyphens.
     */
    public static function normalizeCode(string $code): string {
        $code = trim($code);
        $code = strtoupper($code);
        return preg_replace('/[^A-Z0-9_\-]/', '', $code);
    }

    /**
     * Find coupon by code (case-insensitive).
     */
    public static function findByCode(string $code): ?array {
        $cleanCode = self::normalizeCode($code);
        if (empty($cleanCode)) {
            return null;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `coupons` WHERE `code` = ? LIMIT 1");
        $stmt->execute([$cleanCode]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find coupon by ID.
     */
    public static function getById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `coupons` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get all coupons with optional filtering and search.
     */
    public static function getAll(array $filters = [], int $limit = 50, int $offset = 0): array {
        $pdo = Database::getConnection();
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(`code` LIKE ? OR `name` LIKE ? OR `description` LIKE ?)";
            $s = '%' . trim($filters['search']) . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['status'])) {
            $now = date('Y-m-d H:i:s');
            if ($filters['status'] === 'active') {
                $where[] = "`is_active` = 1 AND (`expiry_date` IS NULL OR `expiry_date` >= ?) AND (`start_date` IS NULL OR `start_date` <= ?)";
                $params[] = $now;
                $params[] = $now;
            } else if ($filters['status'] === 'expired') {
                $where[] = "`expiry_date` IS NOT NULL AND `expiry_date` < ?";
                $params[] = $now;
            } else if ($filters['status'] === 'disabled') {
                $where[] = "`is_active` = 0";
            }
        }

        if (!empty($filters['type'])) {
            $where[] = "`type` = ?";
            $params[] = $filters['type'];
        }

        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM `coupon_usage` cu WHERE cu.`coupon_id` = c.`id`) as total_redemptions,
                (SELECT COALESCE(SUM(`discount_amount` + `delivery_discount`), 0) FROM `coupon_usage` cu WHERE cu.`coupon_id` = c.`id`) as total_discount_given
                FROM `coupons` c";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY c.`id` DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count coupons matching filter.
     */
    public static function countAll(array $filters = []): int {
        $pdo = Database::getConnection();
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(`code` LIKE ? OR `name` LIKE ? OR `description` LIKE ?)";
            $s = '%' . trim($filters['search']) . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['status'])) {
            $now = date('Y-m-d H:i:s');
            if ($filters['status'] === 'active') {
                $where[] = "`is_active` = 1 AND (`expiry_date` IS NULL OR `expiry_date` >= ?) AND (`start_date` IS NULL OR `start_date` <= ?)";
                $params[] = $now;
                $params[] = $now;
            } else if ($filters['status'] === 'expired') {
                $where[] = "`expiry_date` IS NOT NULL AND `expiry_date` < ?";
                $params[] = $now;
            } else if ($filters['status'] === 'disabled') {
                $where[] = "`is_active` = 0";
            }
        }

        if (!empty($filters['type'])) {
            $where[] = "`type` = ?";
            $params[] = $filters['type'];
        }

        $sql = "SELECT COUNT(*) FROM `coupons`";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Create a new coupon.
     */
    public static function create(array $data): array {
        $code = self::normalizeCode($data['code'] ?? '');
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');
        $type = trim($data['type'] ?? self::TYPE_PERCENTAGE);
        $discountValue = (float)($data['discount_value'] ?? 0);
        $minOrderAmount = max(0.0, (float)($data['min_order_amount'] ?? 0));
        $maxDiscount = !empty($data['max_discount_amount']) ? (float)$data['max_discount_amount'] : null;
        $startDate = !empty($data['start_date']) ? $data['start_date'] : null;
        $expiryDate = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
        $maxUses = !empty($data['max_uses']) ? (int)$data['max_uses'] : null;
        $maxUsesPerCust = !empty($data['max_uses_per_customer']) ? (int)$data['max_uses_per_customer'] : null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        // Validation
        if (empty($code) || strlen($code) < 3) {
            return ['success' => false, 'error' => 'Coupon code must be at least 3 alphanumeric characters.'];
        }

        if (empty($name)) {
            return ['success' => false, 'error' => 'Please enter a coupon title/name.'];
        }

        if (!in_array($type, [self::TYPE_PERCENTAGE, self::TYPE_FIXED, self::TYPE_FREE_DELIVERY])) {
            return ['success' => false, 'error' => 'Invalid coupon type selected.'];
        }

        if ($type === self::TYPE_PERCENTAGE) {
            if ($discountValue <= 0 || $discountValue > 100) {
                return ['success' => false, 'error' => 'Percentage discount must be between 1% and 100%.'];
            }
        } else if ($type === self::TYPE_FIXED) {
            if ($discountValue <= 0) {
                return ['success' => false, 'error' => 'Fixed discount value must be greater than zero.'];
            }
        } else if ($type === self::TYPE_FREE_DELIVERY) {
            $discountValue = 0.00;
        }

        if ($startDate && $expiryDate && strtotime($startDate) > strtotime($expiryDate)) {
            return ['success' => false, 'error' => 'Expiry date must be after the start date.'];
        }

        // Duplicate check
        $existing = self::findByCode($code);
        if ($existing) {
            return ['success' => false, 'error' => "A coupon with code '{$code}' already exists."];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO `coupons` (
                `code`, `name`, `description`, `type`, `discount_value`,
                `min_order_amount`, `max_discount_amount`, `start_date`, `expiry_date`,
                `max_uses`, `current_uses`, `max_uses_per_customer`, `is_active`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)
        ");

        $stmt->execute([
            $code, $name, $description, $type, $discountValue,
            $minOrderAmount, $maxDiscount, $startDate, $expiryDate,
            $maxUses, $maxUsesPerCust, $isActive
        ]);

        $id = (int)$pdo->lastInsertId();
        return ['success' => true, 'coupon_id' => $id];
    }

    /**
     * Update an existing coupon.
     */
    public static function update(int $id, array $data): array {
        $coupon = self::getById($id);
        if (!$coupon) {
            return ['success' => false, 'error' => 'Coupon not found.'];
        }

        $code = self::normalizeCode($data['code'] ?? $coupon['code']);
        $name = trim($data['name'] ?? $coupon['name']);
        $description = trim($data['description'] ?? '');
        $type = trim($data['type'] ?? $coupon['type']);
        $discountValue = (float)($data['discount_value'] ?? $coupon['discount_value']);
        $minOrderAmount = max(0.0, (float)($data['min_order_amount'] ?? 0));
        $maxDiscount = !empty($data['max_discount_amount']) ? (float)$data['max_discount_amount'] : null;
        $startDate = !empty($data['start_date']) ? $data['start_date'] : null;
        $expiryDate = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
        $maxUses = !empty($data['max_uses']) ? (int)$data['max_uses'] : null;
        $maxUsesPerCust = !empty($data['max_uses_per_customer']) ? (int)$data['max_uses_per_customer'] : null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        if (empty($code) || strlen($code) < 3) {
            return ['success' => false, 'error' => 'Coupon code must be at least 3 alphanumeric characters.'];
        }

        if (empty($name)) {
            return ['success' => false, 'error' => 'Please enter a coupon title/name.'];
        }

        if (!in_array($type, [self::TYPE_PERCENTAGE, self::TYPE_FIXED, self::TYPE_FREE_DELIVERY])) {
            return ['success' => false, 'error' => 'Invalid coupon type selected.'];
        }

        if ($type === self::TYPE_PERCENTAGE) {
            if ($discountValue <= 0 || $discountValue > 100) {
                return ['success' => false, 'error' => 'Percentage discount must be between 1% and 100%.'];
            }
        } else if ($type === self::TYPE_FIXED) {
            if ($discountValue <= 0) {
                return ['success' => false, 'error' => 'Fixed discount value must be greater than zero.'];
            }
        } else if ($type === self::TYPE_FREE_DELIVERY) {
            $discountValue = 0.00;
        }

        if ($startDate && $expiryDate && strtotime($startDate) > strtotime($expiryDate)) {
            return ['success' => false, 'error' => 'Expiry date must be after the start date.'];
        }

        // Duplicate check on other coupons
        $existing = self::findByCode($code);
        if ($existing && (int)$existing['id'] !== $id) {
            return ['success' => false, 'error' => "A coupon with code '{$code}' already exists."];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE `coupons` SET
                `code` = ?,
                `name` = ?,
                `description` = ?,
                `type` = ?,
                `discount_value` = ?,
                `min_order_amount` = ?,
                `max_discount_amount` = ?,
                `start_date` = ?,
                `expiry_date` = ?,
                `max_uses` = ?,
                `max_uses_per_customer` = ?,
                `is_active` = ?,
                `updated_at` = NOW()
            WHERE `id` = ?
        ");

        $stmt->execute([
            $code, $name, $description, $type, $discountValue,
            $minOrderAmount, $maxDiscount, $startDate, $expiryDate,
            $maxUses, $maxUsesPerCust, $isActive, $id
        ]);

        return ['success' => true];
    }

    /**
     * Toggle active/inactive status.
     */
    public static function toggleStatus(int $id): bool {
        $coupon = self::getById($id);
        if (!$coupon) {
            return false;
        }

        $newStatus = $coupon['is_active'] ? 0 : 1;
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE `coupons` SET `is_active` = ?, `updated_at` = NOW() WHERE `id` = ?");
        return $stmt->execute([$newStatus, $id]);
    }

    /**
     * Delete or safely deactivate a coupon.
     */
    public static function delete(int $id): bool {
        $pdo = Database::getConnection();
        // Check if coupon has usage records
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `coupon_usage` WHERE `coupon_id` = ?");
        $stmtCheck->execute([$id]);
        $usageCount = (int)$stmtCheck->fetchColumn();

        if ($usageCount > 0) {
            // Keep record for audit, just mark inactive
            $stmtDeactivate = $pdo->prepare("UPDATE `coupons` SET `is_active` = 0, `updated_at` = NOW() WHERE `id` = ?");
            return $stmtDeactivate->execute([$id]);
        }

        $stmtDel = $pdo->prepare("DELETE FROM `coupons` WHERE `id` = ?");
        return $stmtDel->execute([$id]);
    }

    /**
     * Full server-side coupon validation against business conditions.
     */
    public static function validateCoupon(
        string $code,
        float $subtotal,
        ?int $customerId = null,
        string $customerPhone = '',
        string $fulfilment = 'delivery'
    ): array {
        $cleanCode = self::normalizeCode($code);
        if (empty($cleanCode)) {
            return ['valid' => false, 'error' => 'Please enter a coupon code.'];
        }

        $coupon = self::findByCode($cleanCode);
        if (!$coupon) {
            return ['valid' => false, 'error' => "Coupon code '{$cleanCode}' is invalid."];
        }

        // 1. Active status check
        if ((int)$coupon['is_active'] !== 1) {
            return ['valid' => false, 'error' => "Coupon '{$cleanCode}' is currently disabled."];
        }

        // 2. Start Date check
        $now = time();
        if (!empty($coupon['start_date']) && strtotime($coupon['start_date']) > $now) {
            $formattedStart = date('d M Y, h:i A', strtotime($coupon['start_date']));
            return ['valid' => false, 'error' => "Coupon '{$cleanCode}' is valid from {$formattedStart}."];
        }

        // 3. Expiry Date check
        if (!empty($coupon['expiry_date']) && strtotime($coupon['expiry_date']) < $now) {
            return ['valid' => false, 'error' => "Coupon '{$cleanCode}' expired on " . date('d M Y', strtotime($coupon['expiry_date'])) . "."];
        }

        // 4. Minimum order amount check
        $minOrder = (float)$coupon['min_order_amount'];
        if ($minOrder > 0 && $subtotal < $minOrder) {
            $diff = $minOrder - $subtotal;
            return [
                'valid' => false,
                'error' => "Minimum order of ₹" . number_format($minOrder, 2) . " required for coupon '{$cleanCode}'. Add ₹" . number_format($diff, 2) . " more items."
            ];
        }

        // 5. Total global usage limit check
        if (!empty($coupon['max_uses']) && (int)$coupon['current_uses'] >= (int)$coupon['max_uses']) {
            return ['valid' => false, 'error' => "Coupon '{$cleanCode}' has reached its maximum global usage limit."];
        }

        // 6. Per customer usage limit check
        $maxPerCust = !empty($coupon['max_uses_per_customer']) ? (int)$coupon['max_uses_per_customer'] : null;
        if ($maxPerCust !== null && ($customerId || !empty($customerPhone))) {
            $cleanPhone = Customer::normalizePhone($customerPhone);
            $pdo = Database::getConnection();
            $params = [$coupon['id']];
            $conds = [];

            if ($customerId) {
                $conds[] = "`customer_id` = ?";
                $params[] = $customerId;
            }

            if (!empty($cleanPhone)) {
                $conds[] = "`customer_phone` = ?";
                $params[] = $cleanPhone;

                $conds[] = "REPLACE(REPLACE(`customer_phone`, ' ', ''), '-', '') LIKE ?";
                $params[] = '%' . $cleanPhone;
            }

            if (!empty($conds)) {
                $sql = "SELECT COUNT(*) FROM `coupon_usage` WHERE `coupon_id` = ? AND (" . implode(' OR ', $conds) . ")";
                $stmtCount = $pdo->prepare($sql);
                $stmtCount->execute($params);
                $custUses = (int)$stmtCount->fetchColumn();

                if ($custUses >= $maxPerCust) {
                    return [
                        'valid' => false,
                        'error' => "You have already used coupon '{$cleanCode}' the maximum allowed {$maxPerCust} time(s)."
                    ];
                }
            }
        }

        return [
            'valid' => true,
            'coupon' => $coupon
        ];
    }

    /**
     * Calculate discount amount and delivery discount based on subtotal & delivery charge.
     */
    public static function calculateDiscount(array $coupon, float $subtotal, float $deliveryCharge): array {
        $type = $coupon['type'];
        $discountAmount = 0.00;
        $deliveryDiscount = 0.00;
        $message = '';

        if ($type === self::TYPE_FREE_DELIVERY) {
            $deliveryDiscount = $deliveryCharge;
            $discountAmount = 0.00;
            $message = 'Free Delivery applied! (Saved ₹' . number_format($deliveryDiscount, 2) . ')';
        } else if ($type === self::TYPE_PERCENTAGE) {
            $percent = (float)$coupon['discount_value'];
            $calculated = ($percent * $subtotal) / 100.0;
            if (!empty($coupon['max_discount_amount']) && (float)$coupon['max_discount_amount'] > 0) {
                $cap = (float)$coupon['max_discount_amount'];
                if ($calculated > $cap) {
                    $calculated = $cap;
                }
            }
            // Discount can never exceed subtotal
            $discountAmount = min($calculated, $subtotal);
            $deliveryDiscount = 0.00;
            $message = $percent . '% discount applied! (Saved ₹' . number_format($discountAmount, 2) . ')';
        } else if ($type === self::TYPE_FIXED) {
            $fixed = (float)$coupon['discount_value'];
            // Fixed discount can never exceed subtotal
            $discountAmount = min($fixed, $subtotal);
            $deliveryDiscount = 0.00;
            $message = 'Flat ₹' . number_format($discountAmount, 2) . ' discount applied!';
        }

        $effectiveDelivery = max(0.00, $deliveryCharge - $deliveryDiscount);
        $finalTotal = max(0.00, $subtotal - $discountAmount) + $effectiveDelivery;

        return [
            'coupon_id' => (int)$coupon['id'],
            'coupon_code' => $coupon['code'],
            'coupon_type' => $type,
            'discount_amount' => round($discountAmount, 2),
            'delivery_discount' => round($deliveryDiscount, 2),
            'effective_delivery_charge' => round($effectiveDelivery, 2),
            'original_delivery_charge' => round($deliveryCharge, 2),
            'subtotal' => round($subtotal, 2),
            'final_total' => round($finalTotal, 2),
            'total_savings' => round($discountAmount + $deliveryDiscount, 2),
            'benefit_message' => $message
        ];
    }

    /**
     * Record coupon usage inside the order creation database transaction.
     */
    public static function recordUsage(
        int $couponId,
        int $orderId,
        ?int $customerId,
        string $phone,
        float $discountAmount,
        float $deliveryDiscount,
        PDO $pdo
    ): bool {
        // 1. Insert into coupon_usage
        $cleanPhone = Customer::normalizePhone($phone);
        $stmt = $pdo->prepare("
            INSERT INTO `coupon_usage` (
                `coupon_id`, `order_id`, `customer_id`, `customer_phone`,
                `discount_amount`, `delivery_discount`, `used_at`
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $couponId,
            $orderId,
            $customerId,
            $cleanPhone ?: $phone,
            $discountAmount,
            $deliveryDiscount
        ]);

        // 2. Atomically increment current_uses
        $stmtInc = $pdo->prepare("UPDATE `coupons` SET `current_uses` = `current_uses` + 1, `updated_at` = NOW() WHERE `id` = ?");
        $stmtInc->execute([$couponId]);

        return true;
    }

    /**
     * Get detailed usage statistics and order history for a coupon.
     */
    public static function getUsageDetails(int $couponId): array {
        $pdo = Database::getConnection();

        $stmtStats = $pdo->prepare("
            SELECT 
                COUNT(*) as total_uses,
                COUNT(DISTINCT `customer_phone`) as unique_customers,
                COALESCE(SUM(`discount_amount`), 0) as total_product_discount,
                COALESCE(SUM(`delivery_discount`), 0) as total_delivery_discount,
                COALESCE(SUM(`discount_amount` + `delivery_discount`), 0) as total_saved
            FROM `coupon_usage`
            WHERE `coupon_id` = ?
        ");
        $stmtStats->execute([$couponId]);
        $stats = $stmtStats->fetch() ?: [];

        $stmtOrders = $pdo->prepare("
            SELECT 
                cu.*,
                o.`order_number`,
                o.`customer_name`,
                o.`total_amount`,
                o.`order_status`,
                o.`created_at` as order_created_at
            FROM `coupon_usage` cu
            JOIN `orders` o ON cu.`order_id` = o.`id`
            WHERE cu.`coupon_id` = ?
            ORDER BY cu.`id` DESC
            LIMIT 50
        ");
        $stmtOrders->execute([$couponId]);
        $orders = $stmtOrders->fetchAll();

        return [
            'stats' => $stats,
            'orders' => $orders
        ];
    }
}
