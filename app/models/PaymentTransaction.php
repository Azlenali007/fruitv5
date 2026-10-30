<?php
/**
 * Payment Transaction Model
 * N.A Fresh Fruits & Coconuts
 * Manages gateway transactions, payment attempts, audit logs, and webhook event deduplication.
 */

class PaymentTransaction {
    public const STATUS_INITIATED          = 'initiated';
    public const STATUS_AUTHORIZED         = 'authorized';
    public const STATUS_CAPTURED           = 'captured';
    public const STATUS_FAILED             = 'failed';
    public const STATUS_REFUNDED           = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    public const GATEWAY_RAZORPAY = 'razorpay';

    /**
     * Create a new payment attempt record
     */
    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO `payment_transactions` (
                `order_id`, `order_number`, `gateway`, `gateway_order_id`,
                `gateway_payment_id`, `gateway_signature`, `payment_attempt_ref`,
                `amount_paise`, `amount_rupees`, `currency`, `status`,
                `signature_verified`, `environment`, `payment_method_details`,
                `error_code`, `error_description`, `raw_response_json`, `created_at`
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, NOW()
            )
        ");

        $stmt->execute([
            (int)$data['order_id'],
            $data['order_number'],
            $data['gateway'] ?? self::GATEWAY_RAZORPAY,
            $data['gateway_order_id'],
            $data['gateway_payment_id'] ?? null,
            $data['gateway_signature'] ?? null,
            $data['payment_attempt_ref'],
            (int)$data['amount_paise'],
            (float)$data['amount_rupees'],
            $data['currency'] ?? 'INR',
            $data['status'] ?? self::STATUS_INITIATED,
            !empty($data['signature_verified']) ? 1 : 0,
            $data['environment'] ?? 'test',
            $data['payment_method_details'] ?? null,
            $data['error_code'] ?? null,
            $data['error_description'] ?? null,
            isset($data['raw_response_json']) ? (is_array($data['raw_response_json']) ? json_encode($data['raw_response_json']) : (string)$data['raw_response_json']) : null
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Format transaction row with friendly aliases
     */
    private static function formatRow(?array $row): ?array {
        if (!$row) return null;
        $row['amount'] = (float)($row['amount_rupees'] ?? 0);
        $row['attempt_reference'] = $row['payment_attempt_ref'] ?? '';
        $row['error_message'] = $row['error_description'] ?? '';
        return $row;
    }

    /**
     * Get transaction by internal attempt reference
     */
    public static function getByAttemptRef(string $ref): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `payment_transactions` WHERE `payment_attempt_ref` = ? LIMIT 1");
        $stmt->execute([$ref]);
        $row = $stmt->fetch();
        return self::formatRow($row ?: null);
    }

    public static function getByAttemptReference(string $ref): ?array {
        return self::getByAttemptRef($ref);
    }

    /**
     * Get transaction by Razorpay Order ID
     */
    public static function getByGatewayOrderId(string $gatewayOrderId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `payment_transactions` WHERE `gateway_order_id` = ? ORDER BY `id` DESC LIMIT 1");
        $stmt->execute([$gatewayOrderId]);
        $row = $stmt->fetch();
        return self::formatRow($row ?: null);
    }

    /**
     * Get transaction by Razorpay Payment ID
     */
    public static function getByGatewayPaymentId(string $gatewayPaymentId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `payment_transactions` WHERE `gateway_payment_id` = ? ORDER BY `id` DESC LIMIT 1");
        $stmt->execute([$gatewayPaymentId]);
        $row = $stmt->fetch();
        return self::formatRow($row ?: null);
    }

    /**
     * Get transaction by internal primary key ID
     */
    public static function getById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `payment_transactions` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return self::formatRow($row ?: null);
    }

    /**
     * Get all transactions for an order
     */
    public static function getByOrderId(int $orderId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `payment_transactions` WHERE `order_id` = ? ORDER BY `id` DESC");
        $stmt->execute([$orderId]);
        $rows = $stmt->fetchAll();
        return array_map([self::class, 'formatRow'], $rows);
    }

    /**
     * Update transaction status & gateway details
     */
    public static function updateStatus(int $id, string $status, array $extra = []): bool {
        $pdo = Database::getConnection();
        $fields = ['`status` = ?'];
        $params = [$status];

        // Map convenient aliases
        if (isset($extra['error_message']) && !isset($extra['error_description'])) {
            $extra['error_description'] = $extra['error_message'];
        }
        if (isset($extra['raw_response']) && !isset($extra['raw_response_json'])) {
            $extra['raw_response_json'] = $extra['raw_response'];
        }
        if (isset($extra['signature']) && !isset($extra['gateway_signature'])) {
            $extra['gateway_signature'] = $extra['signature'];
        }
        if (isset($extra['payment_id']) && !isset($extra['gateway_payment_id'])) {
            $extra['gateway_payment_id'] = $extra['payment_id'];
        }

        $allowedExtra = [
            'gateway_payment_id',
            'gateway_signature',
            'signature_verified',
            'payment_method_details',
            'error_code',
            'error_description',
            'raw_response_json'
        ];

        foreach ($allowedExtra as $col) {
            if (array_key_exists($col, $extra)) {
                $fields[] = "`$col` = ?";
                $val = $extra[$col];
                if ($col === 'raw_response_json' && is_array($val)) {
                    $val = json_encode($val);
                }
                $params[] = $val;
            }
        }

        $params[] = $id;
        $sql = "UPDATE `payment_transactions` SET " . implode(', ', $fields) . ", `updated_at` = NOW() WHERE `id` = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Update transaction by attempt reference
     */
    public static function updateByPaymentRef(string $attemptRef, array $data): bool {
        $tx = self::getByAttemptRef($attemptRef);
        if (!$tx) {
            return false;
        }
        $status = $data['status'] ?? $tx['status'];
        return self::updateStatus($tx['id'], $status, $data);
    }

    /**
     * Query transactions for admin listing with search & filters
     */
    public static function getAll(array $filters = [], int $limit = 25, int $offset = 0): array {
        $pdo = Database::getConnection();
        $where = [];
        $params = [];

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[] = "pt.`status` = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['gateway'])) {
            $where[] = "pt.`gateway` = ?";
            $params[] = $filters['gateway'];
        }

        if (!empty($filters['environment'])) {
            $where[] = "pt.`environment` = ?";
            $params[] = $filters['environment'];
        }

        if (!empty($filters['search'])) {
            $s = '%' . trim($filters['search']) . '%';
            $where[] = "(pt.`order_number` LIKE ? OR pt.`gateway_order_id` LIKE ? OR pt.`gateway_payment_id` LIKE ? OR o.`customer_name` LIKE ? OR o.`customer_phone` LIKE ?)";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['date_from'])) {
            $where[] = "pt.`created_at` >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "pt.`created_at` <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $sql = "
            SELECT 
                pt.*,
                o.`customer_name`,
                o.`customer_phone`,
                o.`order_status`
            FROM `payment_transactions` pt
            LEFT JOIN `orders` o ON pt.`order_id` = o.`id`
        ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY pt.`id` DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        $formatted = array_map([self::class, 'formatRow'], $rows);
        return [
            'items' => $formatted,
            'total' => self::countAll($filters)
        ];
    }

    /**
     * Count transactions for pagination
     */
    public static function countAll(array $filters = []): int {
        $pdo = Database::getConnection();
        $where = [];
        $params = [];

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[] = "pt.`status` = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['gateway'])) {
            $where[] = "pt.`gateway` = ?";
            $params[] = $filters['gateway'];
        }

        if (!empty($filters['environment'])) {
            $where[] = "pt.`environment` = ?";
            $params[] = $filters['environment'];
        }

        if (!empty($filters['search'])) {
            $s = '%' . trim($filters['search']) . '%';
            $where[] = "(pt.`order_number` LIKE ? OR pt.`gateway_order_id` LIKE ? OR pt.`gateway_payment_id` LIKE ? OR o.`customer_name` LIKE ? OR o.`customer_phone` LIKE ?)";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['date_from'])) {
            $where[] = "pt.`created_at` >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "pt.`created_at` <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $sql = "
            SELECT COUNT(*) 
            FROM `payment_transactions` pt
            LEFT JOIN `orders` o ON pt.`order_id` = o.`id`
        ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // =========================================================================
    // Webhook Event Deduplication & Audit
    // =========================================================================

    /**
     * Check if a webhook event ID has already been successfully processed
     */
    public static function isWebhookEventProcessed(string $eventId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT `status` FROM `payment_webhook_events` WHERE `event_id` = ? LIMIT 1");
        $stmt->execute([$eventId]);
        $status = $stmt->fetchColumn();
        return $status === 'processed';
    }

    /**
     * Record an incoming webhook event (or return existing record ID)
     */
    public static function recordWebhookEvent(
        string $eventId,
        string $eventType,
        $entityIdOrPayload = null,
        string $status = 'received',
        ?string $payload = null,
        ?string $errorMessage = null
    ): int {
        $entityId = null;
        if ($payload === null) {
            if (is_string($entityIdOrPayload) && (str_starts_with(trim($entityIdOrPayload), '{') || str_starts_with(trim($entityIdOrPayload), '['))) {
                $payload = $entityIdOrPayload;
                $entityId = null;
                $status = 'received';
            } else {
                $entityId = $entityIdOrPayload;
                $payload = '';
            }
        } else {
            $entityId = $entityIdOrPayload;
        }

        $pdo = Database::getConnection();
        $stmtCheck = $pdo->prepare("SELECT `id`, `status` FROM `payment_webhook_events` WHERE `event_id` = ? LIMIT 1");
        $stmtCheck->execute([$eventId]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            return (int)$existing['id'];
        }

        $stmt = $pdo->prepare("
            INSERT INTO `payment_webhook_events` (
                `event_id`, `gateway`, `event_type`, `entity_id`,
                `status`, `payload_json`, `error_message`, `received_at`
            ) VALUES (?, 'razorpay', ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $eventId,
            $eventType,
            $entityId,
            $status,
            $payload,
            $errorMessage
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Mark webhook event as processed
     */
    public static function markWebhookEventProcessed(string $eventId, ?string $errorMessage = null): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE `payment_webhook_events`
            SET `status` = 'processed', `error_message` = ?, `processed_at` = NOW()
            WHERE `event_id` = ?
        ");
        return $stmt->execute([$errorMessage, $eventId]);
    }
}
