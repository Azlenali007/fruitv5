<?php
/**
 * Order Service
 * N.A Fresh Fruits & Coconuts
 * Enforces mandatory customer authentication, server-side validation,
 * independent backend amount calculation, COD / Razorpay availability checks,
 * and transactional order / payment creation.
 */

class OrderService {
    public static function processCheckout(array $postData): array {
        // 1. Mandatory Customer Authentication Check
        if (!CustomerAuthService::check()) {
            return [
                'success' => false,
                'auth_required' => true,
                'error' => 'Please log in or create an account to place your order.'
            ];
        }

        $customer = CustomerAuthService::user();
        if (!$customer || $customer['status'] !== 'active') {
            CustomerAuthService::logout();
            return [
                'success' => false,
                'auth_required' => true,
                'error' => 'Your customer account is currently inactive. Please log in again.'
            ];
        }

        // 2. CSRF Security Check
        if (!verify_csrf_token($postData[CSRF_TOKEN_KEY] ?? '')) {
            return [
                'success' => false,
                'error' => 'Security token expired. Please refresh and try again.'
            ];
        }

        // 3. Payment Method Availability Validation
        $codEnabled = RazorpayService::isCodEnabled();
        $rzpEnabled = RazorpayService::isRazorpayEnabled();

        if (!$codEnabled && !$rzpEnabled) {
            return [
                'success' => false,
                'error' => 'Payment is temporarily unavailable on our store. Please contact customer support.'
            ];
        }

        $requestedMethod = trim($postData['payment_method'] ?? 'cod');
        if ($requestedMethod === 'razorpay' || $requestedMethod === 'online') {
            $paymentMethod = 'online';
        } else {
            $paymentMethod = 'cod';
        }

        if ($paymentMethod === 'cod' && !$codEnabled) {
            return [
                'success' => false,
                'error' => 'Cash on Delivery is currently disabled by store management. Please choose online payment.'
            ];
        }

        if ($paymentMethod === 'online' && !$rzpEnabled) {
            return [
                'success' => false,
                'error' => 'Razorpay online payment is currently disabled. Please select Cash on Delivery.'
            ];
        }

        // 4. Customer Info: prefill/confirm with authenticated account
        $name = trim($postData['customer_name'] ?? '');
        if (empty($name)) {
            $name = $customer['name'];
        }

        $phone = trim($postData['customer_phone'] ?? '');
        if (empty($phone)) {
            $phone = $customer['phone'];
        }

        $fulfilment = trim($postData['fulfilment_method'] ?? 'delivery');
        if ($fulfilment === 'pickup') {
            return ['success' => false, 'error' => 'Shop pickup is not available. All orders are delivered directly to your doorstep.'];
        }
        $fulfilment = 'delivery';
        $address = trim($postData['delivery_address'] ?? '');

        if (empty($name) || mb_strlen($name) < 2) {
            return ['success' => false, 'error' => 'Please enter your full name.'];
        }

        // Clean Indian phone number
        $cleanPhone = Customer::normalizePhone($phone);
        if (strlen($cleanPhone) < 10) {
            return ['success' => false, 'error' => 'Please enter a valid 10-digit mobile number.'];
        }

        if (empty($address) || mb_strlen($address) < 8) {
            return ['success' => false, 'error' => 'Please provide a complete delivery address including street and locality.'];
        }

        // Validate Delivery Area & Coordinates
        $lat = isset($postData['latitude']) && is_numeric($postData['latitude']) ? (float)$postData['latitude'] : null;
        $lng = isset($postData['longitude']) && is_numeric($postData['longitude']) ? (float)$postData['longitude'] : null;
        $areaId = !empty($postData['delivery_area_id']) ? (int)$postData['delivery_area_id'] : null;

        $eligibility = AreaService::checkDeliveryEligibility($lat, $lng, $areaId);
        if (!$eligibility['deliverable']) {
            return [
                'success' => false,
                'error' => $eligibility['message'] ?: 'Sorry, delivery is not available at your location.'
            ];
        }

        // 5. Cart Validation
        $cart = CartService::getCart();
        if (empty($cart['items'])) {
            return ['success' => false, 'error' => 'Your cart is empty. Please add fresh coconut water before checking out.'];
        }

        // 6. Build order data strictly tying to authenticated customer ID
        $couponCode = !empty($postData['coupon_code']) ? trim($postData['coupon_code']) : ($_SESSION[CartService::COUPON_SESSION_KEY] ?? null);

        // For Razorpay online orders, initial order status is 'pending' until signature verified
        $initialOrderStatus = ($paymentMethod === 'cod') ? 'confirmed' : 'pending';

        $orderData = [
            'customer_id' => (int)$customer['id'],
            'customer_name' => $name,
            'customer_phone' => '+91 ' . $cleanPhone,
            'fulfilment_method' => 'delivery',
            'coupon_code' => $couponCode,
            'delivery_address' => $address,
            'delivery_area_id' => $eligibility['area_id'] ?? $areaId,
            'delivery_area_name' => $eligibility['area_name'] ?? trim($postData['delivery_area_name'] ?? ''),
            'latitude' => $lat,
            'longitude' => $lng,
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending',
            'order_status' => $initialOrderStatus,
            'idempotency_key' => $postData['idempotency_key'] ?? null,
            'notes' => trim($postData['notes'] ?? '')
        ];


        // 7. Place Order Transactionally
        $result = Order::createOrder($orderData, $cart['items']);

        if (!$result['success']) {
            return $result;
        }

        $orderId = (int)$result['order_id'];
        $orderNumber = $result['order_number'];
        $trackingHash = $result['tracking_hash'];
        $totalAmount = (float)$result['total_amount'];

        // 8. Handle Razorpay Payment Gateway Flow
        if ($paymentMethod === 'online') {
            // Independent backend amount calculation in integer paise (never trust JS)
            $amountPaise = (int)round($totalAmount * 100);

            $notes = [
                'order_id' => (string)$orderId,
                'order_number' => $orderNumber,
                'customer_name' => $name,
                'customer_phone' => '+91 ' . $cleanPhone
            ];

            // Create order with Razorpay Orders API
            $rzpRes = RazorpayService::createOrder($totalAmount, $orderNumber, $notes);

            if (!$rzpRes['success']) {
                // Cancel internal order and release reserved stock safely
                Order::updateStatus($orderId, 'cancelled', 'Gateway order creation failed: ' . ($rzpRes['error'] ?? 'Unknown error'), 'System');
                return [
                    'success' => false,
                    'error' => 'Could not initiate Razorpay payment: ' . ($rzpRes['error'] ?? 'Please try again.')
                ];
            }

            $rzpOrderId = $rzpRes['order']['id'];
            $attemptRef = 'att_' . bin2hex(random_bytes(16));

            // Link Razorpay Order ID to orders record
            $pdo = Database::getConnection();
            $stmtUp = $pdo->prepare("UPDATE `orders` SET `razorpay_order_id` = ? WHERE `id` = ?");
            $stmtUp->execute([$rzpOrderId, $orderId]);

            // Record transaction attempt in payment_transactions table
            PaymentTransaction::create([
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'gateway' => PaymentTransaction::GATEWAY_RAZORPAY,
                'gateway_order_id' => $rzpOrderId,
                'payment_attempt_ref' => $attemptRef,
                'amount_paise' => $amountPaise,
                'amount_rupees' => $totalAmount,
                'currency' => 'INR',
                'status' => PaymentTransaction::STATUS_INITIATED,
                'environment' => RazorpayService::getEnvironment(),
                'raw_response_json' => $rzpRes['order']
            ]);

            // DO NOT clear cart yet for online payment until payment is verified.
            // Return public checkout parameters needed by official Razorpay standard modal
            return [
                'success' => true,
                'requires_razorpay' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'tracking_hash' => $trackingHash,
                'attempt_reference' => $attemptRef,
                'razorpay_order_id' => $rzpOrderId,
                'razorpay_key_id' => RazorpayService::getKeyId(),
                'amount' => $amountPaise,
                'amount_paise' => $amountPaise,
                'amount_rupees' => $totalAmount,
                'currency' => 'INR',
                'customer_name' => $name,
                'customer_phone' => '+91 ' . $cleanPhone,
                'customer_email' => $customer['email'] ?? '',
                'store_name' => Setting::get('store_name', 'N.A Fresh Fruits & Coconuts')
            ];
        }

        // 9. COD Success Flow: Cart is cleared immediately
        CartService::clear();

        return [
            'success' => true,
            'requires_razorpay' => false,
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'tracking_hash' => $trackingHash,
            'total_amount' => $totalAmount
        ];
    }
}
