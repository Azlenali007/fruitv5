<?php
/**
 * Order Model
 * N.A Fresh Fruits & Coconuts
 */

class Order {
    public static function createOrder(array $orderData, array $cartItems): array {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            // 1. Check idempotency if key provided
            if (!empty($orderData['idempotency_key'])) {
                $stmtCheck = $pdo->prepare("SELECT `id`, `order_number`, `tracking_hash` FROM `orders` WHERE `idempotency_key` = ?");
                $stmtCheck->execute([$orderData['idempotency_key']]);
                $existing = $stmtCheck->fetch();
                if ($existing) {
                    $pdo->rollBack();
                    return [
                        'success' => true,
                        'order_id' => $existing['id'],
                        'order_number' => $existing['order_number'],
                        'tracking_hash' => $existing['tracking_hash'],
                        'is_duplicate' => true
                    ];
                }
            }

            // 2. Validate product & stock using pessimistic locking (FOR UPDATE)
            $subtotal = 0.00;
            $verifiedItems = [];

            foreach ($cartItems as $item) {
                $productId = (int)$item['product_id'];
                $quantity = max(1, (int)$item['quantity']);

                $stmtProd = $pdo->prepare("SELECT * FROM `products` WHERE `id` = ? FOR UPDATE");
                $stmtProd->execute([$productId]);
                $product = $stmtProd->fetch();

                if (!$product) {
                    throw new Exception("Product no longer available in store.");
                }

                if (!$product['is_available']) {
                    throw new Exception("Item '{$product['name']}' is currently marked unavailable.");
                }

                if ($product['stock_quantity'] < $quantity) {
                    throw new Exception("Insufficient stock for '{$product['name']}'. Only {$product['stock_quantity']} left.");
                }

                $itemPrice = (float)$product['price'];
                $lineTotal = $itemPrice * $quantity;
                $subtotal += $lineTotal;

                $verifiedItems[] = [
                    'product_id' => $productId,
                    'product_name' => $product['name'],
                    'unit_price' => $itemPrice,
                    'quantity' => $quantity,
                    'subtotal' => $lineTotal
                ];

                // Atomically deduct stock
                $stmtDeduct = $pdo->prepare("UPDATE `products` SET `stock_quantity` = `stock_quantity` - ?, `updated_at` = NOW() WHERE `id` = ?");
                $stmtDeduct->execute([$quantity, $productId]);
            }

            // 3. Validate fulfilment method & payment method
            $fulfilment = $orderData['fulfilment_method'] ?? 'delivery';
            if ($fulfilment === 'pickup') {
                throw new Exception("Shop pickup is no longer available. All orders are delivered directly to your doorstep.");
            }
            $fulfilment = 'delivery';

            $paymentMethod = $orderData['payment_method'] ?? 'cod';
            if ($paymentMethod === 'cod' && !RazorpayService::isCodEnabled()) {
                throw new Exception("Cash on Delivery is currently disabled by store management. Please select online payment.");
            }

            $deliveryEnabled = Setting::get('local_delivery_enabled', '1') === '1';
            if (!$deliveryEnabled) {
                throw new Exception("Local delivery is currently paused by store manager.");
            }

            $minOrder = (float)Setting::get('min_order_amount', '40.00');
            if ($subtotal < $minOrder) {
                throw new Exception("Minimum order amount for delivery is " . CURRENCY_SYMBOL . number_format($minOrder, 2));
            }

            $deliveryLat = isset($orderData['latitude']) && is_numeric($orderData['latitude']) ? (float)$orderData['latitude'] : null;
            $deliveryLng = isset($orderData['longitude']) && is_numeric($orderData['longitude']) ? (float)$orderData['longitude'] : null;
            $deliveryAreaId = !empty($orderData['delivery_area_id']) ? (int)$orderData['delivery_area_id'] : null;
            $deliveryAreaName = !empty($orderData['delivery_area_name']) ? trim($orderData['delivery_area_name']) : null;

            $areaCheck = AreaService::checkDeliveryEligibility($deliveryLat, $deliveryLng, $deliveryAreaId);
            if (!$areaCheck['deliverable']) {
                throw new Exception($areaCheck['message'] ?: "Sorry, delivery is not available at your location.");
            }

            if (!empty($areaCheck['area_id'])) {
                $deliveryAreaId = (int)$areaCheck['area_id'];
                $deliveryAreaName = $areaCheck['area_name'];
            }

            $areaCharge = (float)$areaCheck['delivery_charge'];
            $freeAbove = (float)Setting::get('free_delivery_above', '500.00');
            if ($subtotal >= $freeAbove) {
                $originalDeliveryCharge = 0.00;
            } else {
                $originalDeliveryCharge = $areaCharge;
            }


            // Coupon validation & discount computation
            $couponId = null;
            $couponCode = null;
            $couponType = null;
            $discountAmount = 0.00;
            $deliveryDiscount = 0.00;

            if (!empty($orderData['coupon_code'])) {
                $rawCouponCode = Coupon::normalizeCode($orderData['coupon_code']);
                $stmtCpn = $pdo->prepare("SELECT * FROM `coupons` WHERE `code` = ? FOR UPDATE");
                $stmtCpn->execute([$rawCouponCode]);
                $coupon = $stmtCpn->fetch();

                if (!$coupon) {
                    throw new Exception("Coupon code '{$rawCouponCode}' is invalid.");
                }

                if ((int)$coupon['is_active'] !== 1) {
                    throw new Exception("Coupon '{$rawCouponCode}' is currently disabled.");
                }

                $nowTime = time();
                if (!empty($coupon['start_date']) && strtotime($coupon['start_date']) > $nowTime) {
                    throw new Exception("Coupon '{$rawCouponCode}' is not active yet.");
                }

                if (!empty($coupon['expiry_date']) && strtotime($coupon['expiry_date']) < $nowTime) {
                    throw new Exception("Coupon '{$rawCouponCode}' has expired.");
                }

                if ((float)$coupon['min_order_amount'] > 0 && $subtotal < (float)$coupon['min_order_amount']) {
                    throw new Exception("Minimum order amount of ₹" . number_format($coupon['min_order_amount'], 2) . " required for coupon '{$rawCouponCode}'.");
                }

                if (!empty($coupon['max_uses']) && (int)$coupon['current_uses'] >= (int)$coupon['max_uses']) {
                    throw new Exception("Coupon '{$rawCouponCode}' has reached its maximum global usage limit.");
                }

                // Check per-customer limit
                $maxPerCust = !empty($coupon['max_uses_per_customer']) ? (int)$coupon['max_uses_per_customer'] : null;
                if ($maxPerCust !== null) {
                    $cPhoneClean = Customer::normalizePhone($orderData['customer_phone'] ?? '');
                    $cId = !empty($orderData['customer_id']) ? (int)$orderData['customer_id'] : 0;

                    $stmtCustUses = $pdo->prepare("
                        SELECT COUNT(*) FROM `coupon_usage` 
                        WHERE `coupon_id` = ? AND (
                            (? > 0 AND `customer_id` = ?) 
                            OR `customer_phone` = ?
                            OR REPLACE(REPLACE(`customer_phone`, ' ', ''), '-', '') LIKE ?
                        )
                    ");
                    $stmtCustUses->execute([$coupon['id'], $cId, $cId, $cPhoneClean, '%' . $cPhoneClean]);
                    $custUses = (int)$stmtCustUses->fetchColumn();

                    if ($custUses >= $maxPerCust) {
                        throw new Exception("You have already used coupon '{$rawCouponCode}' the maximum allowed {$maxPerCust} time(s).");
                    }
                }

                // Calculate discounts
                $calc = Coupon::calculateDiscount($coupon, $subtotal, $originalDeliveryCharge);
                $couponId = (int)$coupon['id'];
                $couponCode = $coupon['code'];
                $couponType = $coupon['type'];
                $discountAmount = $calc['discount_amount'];
                $deliveryDiscount = $calc['delivery_discount'];
            }

            $effectiveDeliveryCharge = max(0.00, $originalDeliveryCharge - $deliveryDiscount);
            $totalAmount = max(0.00, $subtotal - $discountAmount) + $effectiveDeliveryCharge;

            // 4. Generate unique readable order number (e.g. NA1025, NA1026)
            $stmtLast = $pdo->query("SELECT `id` FROM `orders` ORDER BY `id` DESC LIMIT 1");
            $lastId = (int)$stmtLast->fetchColumn();
            $stmtCheckNum = $pdo->prepare("SELECT COUNT(*) FROM `orders` WHERE `order_number` = ?");
            do {
                $lastId++;
                $orderNumber = 'NA' . (1000 + $lastId);
                $stmtCheckNum->execute([$orderNumber]);
                $numExists = (int)$stmtCheckNum->fetchColumn() > 0;
            } while ($numExists);

            $trackingHash = bin2hex(random_bytes(24));
            $estimatedDelivery = $fulfilment === 'delivery' ? 'Today, ' . date('g:i A', strtotime('+45 minutes')) . ' - ' . date('g:i A', strtotime('+120 minutes')) : 'Ready for pickup in 15 minutes';

            // 5. Connect order with authenticated customer record
            $customerId = !empty($orderData['customer_id']) ? (int)$orderData['customer_id'] : null;
            if ($customerId) {
                // Update customer address if provided
                if (!empty($orderData['delivery_address'])) {
                    $stmtUpCust = $pdo->prepare("UPDATE `customers` SET `address` = ?, `updated_at` = NOW() WHERE `id` = ?");
                    $stmtUpCust->execute([$orderData['delivery_address'], $customerId]);
                }
            } else if (!empty($orderData['customer_phone'])) {
                // Fallback for automated tests / legacy calls
                $stmtCust = $pdo->prepare("SELECT `id` FROM `customers` WHERE `phone` = ? LIMIT 1");
                $stmtCust->execute([$orderData['customer_phone']]);
                $cId = $stmtCust->fetchColumn();
                if ($cId) {
                    $customerId = (int)$cId;
                    if (!empty($orderData['delivery_address'])) {
                        $stmtUpCust = $pdo->prepare("UPDATE `customers` SET `address` = ?, `name` = ?, `updated_at` = NOW() WHERE `id` = ?");
                        $stmtUpCust->execute([$orderData['delivery_address'], $orderData['customer_name'], $customerId]);
                    }
                } else {
                    $stmtInsCust = $pdo->prepare("INSERT INTO `customers` (`name`, `phone`, `address`) VALUES (?, ?, ?)");
                    $stmtInsCust->execute([$orderData['customer_name'], $orderData['customer_phone'], $orderData['delivery_address'] ?? '']);
                    $customerId = (int)$pdo->lastInsertId();
                }
            }

            $paymentStatus = $orderData['payment_status'] ?? 'pending';
            $orderStatus = $orderData['order_status'] ?? ($paymentMethod === 'cod' ? 'confirmed' : 'pending');
            $rzpOrderId = $orderData['razorpay_order_id'] ?? null;

            // 6. Insert Order with delivery area & GPS coordinates snapshot
            $stmtOrder = $pdo->prepare("
                INSERT INTO `orders` (
                    `order_number`, `customer_id`, `customer_name`, `customer_phone`,
                    `fulfilment_method`, `coupon_id`, `coupon_code`, `coupon_type`,
                    `delivery_address`, `delivery_area_id`, `delivery_area_name`, `latitude`, `longitude`,
                    `subtotal`, `discount_amount`, `original_delivery_charge`,
                    `delivery_discount`, `delivery_charge`, `total_amount`,
                    `payment_method`, `payment_status`, `order_status`, `razorpay_order_id`,
                    `tracking_hash`, `estimated_delivery`, `notes`, `idempotency_key`, `created_at`
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmtOrder->execute([
                $orderNumber,
                $customerId,
                $orderData['customer_name'],
                $orderData['customer_phone'],
                $fulfilment,
                $couponId,
                $couponCode,
                $couponType,
                $orderData['delivery_address'] ?? '',
                $deliveryAreaId,
                $deliveryAreaName,
                $deliveryLat,
                $deliveryLng,
                $subtotal,
                $discountAmount,
                $originalDeliveryCharge,
                $deliveryDiscount,
                $effectiveDeliveryCharge,
                $totalAmount,
                $paymentMethod,
                $paymentStatus,
                $orderStatus,
                $rzpOrderId,
                $trackingHash,
                $estimatedDelivery,
                $orderData['notes'] ?? '',
                $orderData['idempotency_key'] ?? null
            ]);

            $orderId = (int)$pdo->lastInsertId();

            // Record coupon usage in coupon_usage audit table and increment usage counter
            if ($couponId) {
                Coupon::recordUsage(
                    $couponId,
                    $orderId,
                    $customerId,
                    $orderData['customer_phone'],
                    $discountAmount,
                    $deliveryDiscount,
                    $pdo
                );
            }

            // 7. Insert Order Items (Immutable price snapshot)
            $stmtItem = $pdo->prepare("
                INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `subtotal`)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($verifiedItems as $vi) {
                $stmtItem->execute([
                    $orderId,
                    $vi['product_id'],
                    $vi['product_name'],
                    $vi['unit_price'],
                    $vi['quantity'],
                    $vi['subtotal']
                ]);
            }

            // 8. Record initial status history
            $initialComment = ($paymentMethod === 'cod') 
                ? 'Order placed successfully by customer (Cash on Delivery)'
                : 'Order initiated by customer (Awaiting online payment)';

            $stmtHist = $pdo->prepare("
                INSERT INTO `order_status_history` (`order_id`, `old_status`, `new_status`, `comment`, `changed_by`)
                VALUES (?, NULL, ?, ?, 'Customer')
            ");
            $stmtHist->execute([$orderId, $orderStatus, $initialComment]);

            $pdo->commit();

            return [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'tracking_hash' => $trackingHash,
                'total_amount' => $totalAmount
            ];
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Order creation failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public static function getById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if ($order) {
            $order['items'] = self::getOrderItems($order['id']);
            $order['history'] = self::getOrderHistory($order['id']);
        }
        return $order ?: null;
    }

    public static function getByTrackingHash(string $hash): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `tracking_hash` = ? LIMIT 1");
        $stmt->execute([$hash]);
        $order = $stmt->fetch();
        if ($order) {
            $order['items'] = self::getOrderItems($order['id']);
            $order['history'] = self::getOrderHistory($order['id']);
        }
        return $order ?: null;
    }

    public static function getByOrderNumber(string $orderNumber): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `order_number` = ? LIMIT 1");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch();
        if ($order) {
            $order['items'] = self::getOrderItems($order['id']);
            $order['history'] = self::getOrderHistory($order['id']);
        }
        return $order ?: null;
    }

    public static function getByRazorpayOrderId(string $rzpOrderId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `razorpay_order_id` = ? LIMIT 1");
        $stmt->execute([$rzpOrderId]);
        $order = $stmt->fetch();
        if ($order) {
            $order['items'] = self::getOrderItems($order['id']);
            $order['history'] = self::getOrderHistory($order['id']);
        }
        return $order ?: null;
    }

    /**
     * Atomically update payment and fulfillment status with transaction, audit log, and optional Razorpay IDs
     */
    public static function updatePaymentAndOrderStatus(
        int $orderId,
        string $paymentStatus,
        ?string $orderStatus = null,
        $extraOrPaymentId = null,
        ?string $rzpSignature = null,
        string $comment = 'Payment status updated'
    ): bool {
        $rzpPaymentId = null;
        if (is_array($extraOrPaymentId)) {
            $rzpPaymentId = $extraOrPaymentId['razorpay_payment_id'] ?? null;
            $rzpSignature = $extraOrPaymentId['razorpay_signature'] ?? null;
            $comment = $extraOrPaymentId['comment'] ?? $comment;
        } elseif (is_string($extraOrPaymentId)) {
            $rzpPaymentId = $extraOrPaymentId;
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmtOrder = $pdo->prepare("SELECT `id`, `order_status`, `payment_status`, `razorpay_order_id` FROM `orders` WHERE `id` = ? FOR UPDATE");
            $stmtOrder->execute([$orderId]);
            $order = $stmtOrder->fetch();

            if (!$order) {
                throw new Exception("Order not found.");
            }

            $oldOrderStatus = $order['order_status'];
            $oldPaymentStatus = $order['payment_status'];

            // If orderStatus not specified, maintain current or default to confirmed for captured
            if ($orderStatus === null) {
                $orderStatus = ($paymentStatus === 'captured' || $paymentStatus === 'paid') ? 'confirmed' : $oldOrderStatus;
            }

            // Stock restoration rule if order transitions to cancelled from non-cancelled
            if ($orderStatus === 'cancelled' && $oldOrderStatus !== 'cancelled') {
                $items = self::getOrderItems($orderId);
                $stmtRestore = $pdo->prepare("UPDATE `products` SET `stock_quantity` = `stock_quantity` + ?, `updated_at` = NOW() WHERE `id` = ?");
                foreach ($items as $item) {
                    $stmtRestore->execute([$item['quantity'], $item['product_id']]);
                }
            }

            // Update order
            $stmtUp = $pdo->prepare("
                UPDATE `orders` 
                SET `payment_status` = ?,
                    `order_status` = ?,
                    `razorpay_payment_id` = COALESCE(?, `razorpay_payment_id`),
                    `razorpay_signature` = COALESCE(?, `razorpay_signature`),
                    `updated_at` = NOW()
                WHERE `id` = ?
            ");
            $stmtUp->execute([$paymentStatus, $orderStatus, $rzpPaymentId, $rzpSignature, $orderId]);

            // Add history record
            $histComment = $comment . " (Payment: {$paymentStatus}, Order: {$orderStatus})";
            $stmtHist = $pdo->prepare("
                INSERT INTO `order_status_history` (`order_id`, `old_status`, `new_status`, `comment`, `changed_by`)
                VALUES (?, ?, ?, ?, 'System/Payment')
            ");
            $stmtHist->execute([$orderId, $oldOrderStatus, $orderStatus, $histComment]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Failed to update payment/order status: " . $e->getMessage());
            return false;
        }
    }

    public static function getByPhone(string $phone): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `customer_phone` = ? OR `customer_phone` = ? ORDER BY `id` DESC LIMIT 20");
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $withPlus = '+91 ' . substr($cleanPhone, -10);
        $stmt->execute([$phone, $withPlus]);
        $orders = $stmt->fetchAll();
        foreach ($orders as &$o) {
            $o['items'] = self::getOrderItems($o['id']);
        }
        return $orders;
    }

    /**
     * Get all orders strictly belonging to a specific customer ID
     */
    public static function getByCustomerId(int $customerId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `customer_id` = ? ORDER BY `id` DESC");
        $stmt->execute([$customerId]);
        $orders = $stmt->fetchAll();
        foreach ($orders as &$o) {
            $o['items'] = self::getOrderItems($o['id']);
            $o['history'] = self::getOrderHistory($o['id']);
        }
        return $orders;
    }

    /**
     * Retrieve single order strictly checking ownership by authenticated customer
     */
    public static function getCustomerOrderById(int $customerId, int $orderId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `id` = ? AND `customer_id` = ? LIMIT 1");
        $stmt->execute([$orderId, $customerId]);
        $order = $stmt->fetch();
        if ($order) {
            $order['items'] = self::getOrderItems($order['id']);
            $order['history'] = self::getOrderHistory($order['id']);
            return $order;
        }
        return null;
    }

    public static function getOrderItems(int $orderId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `order_items` WHERE `order_id` = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public static function getOrderHistory(int $orderId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `order_status_history` WHERE `order_id` = ? ORDER BY `id` ASC");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $orderId, string $newStatus, string $comment = '', string $changedBy = 'Admin'): bool {
        $validStatuses = ['pending', 'confirmed', 'delivered', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            return false;
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            // Lock order row
            $stmtOrder = $pdo->prepare("SELECT `id`, `order_status`, `payment_method`, `payment_status` FROM `orders` WHERE `id` = ? FOR UPDATE");
            $stmtOrder->execute([$orderId]);
            $order = $stmtOrder->fetch();

            if (!$order) {
                throw new Exception("Order not found.");
            }

            $oldStatus = $order['order_status'];
            if ($oldStatus === $newStatus) {
                $pdo->commit();
                return true;
            }

            // Stock restoration rule: if order is cancelled and was not cancelled before, restore stock
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $items = self::getOrderItems($orderId);
                $stmtRestore = $pdo->prepare("UPDATE `products` SET `stock_quantity` = `stock_quantity` + ?, `updated_at` = NOW() WHERE `id` = ?");
                foreach ($items as $item) {
                    $stmtRestore->execute([$item['quantity'], $item['product_id']]);
                }
            }

            // If an order was cancelled and now changed back to confirmed/delivered, re-check and re-deduct
            if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                $items = self::getOrderItems($orderId);
                $stmtDeduct = $pdo->prepare("UPDATE `products` SET `stock_quantity` = `stock_quantity` - ?, `updated_at` = NOW() WHERE `id` = ?");
                foreach ($items as $item) {
                    $stmtDeduct->execute([$item['quantity'], $item['product_id']]);
                }
            }

            // Update order status
            $paymentStatus = $order['payment_status'];
            if ($newStatus === 'delivered' && $order['payment_method'] === 'cod') {
                $paymentStatus = 'paid';
            }

            $stmtUpdate = $pdo->prepare("UPDATE `orders` SET `order_status` = ?, `payment_status` = ?, `updated_at` = NOW() WHERE `id` = ?");
            $stmtUpdate->execute([$newStatus, $paymentStatus, $orderId]);

            // Add history
            $stmtHist = $pdo->prepare("
                INSERT INTO `order_status_history` (`order_id`, `old_status`, `new_status`, `comment`, `changed_by`)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtHist->execute([$orderId, $oldStatus, $newStatus, $comment, $changedBy]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Failed to update order status: " . $e->getMessage());
            return false;
        }
    }

    public static function getAll(array $filters = [], int $limit = 25, int $offset = 0): array {
        $pdo = Database::getConnection();
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "`order_status` = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(`order_number` LIKE ? OR `customer_name` LIKE ? OR `customer_phone` LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['date_from'])) {
            $where[] = "`created_at` >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "`created_at` <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $sql = "SELECT * FROM `orders`";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY `id` DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        foreach ($orders as &$o) {
            $o['items'] = self::getOrderItems($o['id']);
        }

        return $orders;
    }

    public static function countAll(array $filters = []): int {
        $pdo = Database::getConnection();
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "`order_status` = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(`order_number` LIKE ? OR `customer_name` LIKE ? OR `customer_phone` LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['date_from'])) {
            $where[] = "`created_at` >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "`created_at` <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $sql = "SELECT COUNT(*) FROM `orders`";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function getDashboardStats(): array {
        $pdo = Database::getConnection();

        // 1. Orders today
        $today = date('Y-m-d');
        $stmtToday = $pdo->prepare("SELECT COUNT(*) FROM `orders` WHERE DATE(`created_at`) = ?");
        $stmtToday->execute([$today]);
        $ordersToday = (int)$stmtToday->fetchColumn();

        // 2. Sales today (Excluding cancelled orders)
        $stmtSalesToday = $pdo->prepare("SELECT COALESCE(SUM(`total_amount`), 0) FROM `orders` WHERE DATE(`created_at`) = ? AND `order_status` != 'cancelled'");
        $stmtSalesToday->execute([$today]);
        $salesToday = (float)$stmtSalesToday->fetchColumn();

        // 3. Status breakdown
        $stmtCounts = $pdo->query("SELECT `order_status`, COUNT(*) as cnt FROM `orders` GROUP BY `order_status`");
        $counts = ['pending' => 0, 'confirmed' => 0, 'delivered' => 0, 'cancelled' => 0];
        while ($r = $stmtCounts->fetch()) {
            if (isset($counts[$r['order_status']])) {
                $counts[$r['order_status']] = (int)$r['cnt'];
            }
        }

        // 4. Total revenue all time
        $stmtRev = $pdo->query("SELECT COALESCE(SUM(`total_amount`), 0) FROM `orders` WHERE `order_status` != 'cancelled'");
        $totalRevenue = (float)$stmtRev->fetchColumn();

        // 5. Total coconuts sold
        $stmtItemsSold = $pdo->query("SELECT COALESCE(SUM(oi.`quantity`), 0) FROM `order_items` oi JOIN `orders` o ON oi.`order_id` = o.`id` WHERE o.`order_status` != 'cancelled'");
        $totalCoconutsSold = (int)$stmtItemsSold->fetchColumn();

        // 6. 7-Day sales trend for Chart.js
        $trendDates = [];
        $trendSales = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $dLabel = date('D, M j', strtotime("-$i days"));
            $stmtD = $pdo->prepare("SELECT COALESCE(SUM(`total_amount`), 0) FROM `orders` WHERE DATE(`created_at`) = ? AND `order_status` != 'cancelled'");
            $stmtD->execute([$d]);
            $trendDates[] = $dLabel;
            $trendSales[] = (float)$stmtD->fetchColumn();
        }

        return [
            'orders_today' => $ordersToday,
            'sales_today' => $salesToday,
            'pending_orders' => $counts['pending'],
            'confirmed_orders' => $counts['confirmed'],
            'delivered_orders' => $counts['delivered'],
            'cancelled_orders' => $counts['cancelled'],
            'total_revenue' => $totalRevenue,
            'coconuts_sold' => $totalCoconutsSold,
            'trend_dates' => $trendDates,
            'trend_sales' => $trendSales
        ];
    }
}
