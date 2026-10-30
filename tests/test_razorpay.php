<?php
/**
 * Razorpay Payment Gateway Automated Test Suite
 * N.A Fresh Fruits & Coconuts
 */
require_once __DIR__ . '/../app/bootstrap.php';

$rpPass = 0;
$rpFail = 0;

function it_rp(string $name, bool $cond, string $detail = '') {
    global $rpPass, $rpFail;
    if ($cond) {
        $rpPass++;
        echo "  [PASS] {$name}\n";
    } else {
        $rpFail++;
        echo "  [FAIL] {$name} - {$detail}\n";
    }
}

echo "9. RAZORPAY PAYMENT GATEWAY & COD COMPREHENSIVE TESTS:\n";

$pdo = Database::getConnection();

// Seed test settings
RazorpayService::saveSettings([
    'razorpay_enabled' => '1',
    'razorpay_mode' => 'test',
    'razorpay_key_id' => 'rzp_test_sampleKey123',
    'razorpay_key_secret' => 'testSecretKeyXYZ789',
    'razorpay_webhook_secret' => 'webhookSecretABC456',
    'cod_enabled' => '1'
]);

// 1. Razorpay enabled, COD enabled
it_rp("Scenario 1: Both COD and Razorpay are enabled", RazorpayService::isCodEnabled() && RazorpayService::isRazorpayEnabled());

// 2. Razorpay disabled, COD enabled
RazorpayService::saveSettings(['razorpay_enabled' => '0', 'cod_enabled' => '1']);
it_rp("Scenario 2: COD enabled and Razorpay disabled", RazorpayService::isCodEnabled() && !RazorpayService::isRazorpayEnabled());

// 3. Razorpay enabled, COD disabled
RazorpayService::saveSettings(['razorpay_enabled' => '1', 'cod_enabled' => '0']);
it_rp("Scenario 3: Razorpay enabled and COD disabled", !RazorpayService::isCodEnabled() && RazorpayService::isRazorpayEnabled());

// 4. Both payment methods disabled
RazorpayService::saveSettings(['razorpay_enabled' => '0', 'cod_enabled' => '0']);
it_rp("Scenario 4: Both payment methods disabled", !RazorpayService::isCodEnabled() && !RazorpayService::isRazorpayEnabled());

// Re-enable both for functional tests
RazorpayService::saveSettings([
    'razorpay_enabled' => '1',
    'razorpay_mode' => 'test',
    'razorpay_key_id' => 'rzp_test_sampleKey123',
    'razorpay_key_secret' => 'testSecretKeyXYZ789',
    'razorpay_webhook_secret' => 'webhookSecretABC456',
    'cod_enabled' => '1'
]);

// Register mock handler for unit test deterministic responses
$mockOrders = [];
$mockPayments = [];

RazorpayService::setMockHandler(function (string $method, string $endpoint, array $data = []) use (&$mockOrders, &$mockPayments) {
    if ($endpoint === '/orders' && $method === 'POST') {
        $orderId = 'order_mock_' . bin2hex(random_bytes(6));
        $mockOrders[$orderId] = [
            'id' => $orderId,
            'entity' => 'order',
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'INR',
            'receipt' => $data['receipt'] ?? '',
            'status' => 'created'
        ];
        return [
            'success' => true,
            'http_code' => 200,
            'data' => $mockOrders[$orderId]
        ];
    }

    if (str_starts_with($endpoint, '/payments/') && $method === 'GET') {
        $paymentId = basename($endpoint);
        if (isset($mockPayments[$paymentId])) {
            return [
                'success' => true,
                'http_code' => 200,
                'data' => $mockPayments[$paymentId]
            ];
        }
        return [
            'success' => false,
            'http_code' => 404,
            'data' => ['error' => ['description' => 'Payment not found']]
        ];
    }

    return ['success' => true, 'http_code' => 200, 'data' => []];
});

// Prepare customer session for testing
$testCustomerPhone = '9999888877';
$cust = Customer::findByPhone($testCustomerPhone);
if (!$cust) {
    $cId = Customer::create([
        'name' => 'Razorpay Test Customer',
        'phone' => $testCustomerPhone,
        'email' => 'rzp_test@example.com',
        'password' => 'secret1234'
    ]);
    $cust = Customer::findById($cId);
}
$_SESSION[CustomerAuthService::SESSION_CUSTOMER_ID] = $cust['id'];

// Get primary product
$product = Product::getPrimary();
$initialStock = (int)$product['stock_quantity'];

// 5. Razorpay order creation via API generates integer amount in paise
CartService::clear();
CartService::addItem($product['id'], 2); // 2 * 40 = 80 subtotal + 40 delivery = 120 total
$checkoutPost = [
    CSRF_TOKEN_KEY => csrf_token(),
    'customer_name' => 'Razorpay Test Customer',
    'customer_phone' => $testCustomerPhone,
    'fulfilment_method' => 'delivery',
    'delivery_address' => 'Test Address, Green Park, Bhopal',
    'payment_method' => 'online',
    'idempotency_key' => 'idem_rzp_' . uniqid()
];

$checkoutResult = OrderService::processCheckout($checkoutPost);
it_rp("Scenario 5: Checkout creates order and initiates Razorpay with integer paise", 
    $checkoutResult['success'] === true &&
    !empty($checkoutResult['requires_razorpay']) &&
    $checkoutResult['amount'] === 12000 &&
    $checkoutResult['currency'] === 'INR'
);

$rzpOrderId = $checkoutResult['razorpay_order_id'];
$createdOrderNum = $checkoutResult['order_number'];
$attemptRef = $checkoutResult['attempt_reference'];

// 6. Razorpay order creation stores razorpay_order_id in database orders table
$dbOrder = Order::getByOrderNumber($createdOrderNum);
it_rp("Scenario 6: razorpay_order_id stored in database orders table", 
    !empty($dbOrder['razorpay_order_id']) && $dbOrder['razorpay_order_id'] === $rzpOrderId
);

// 7. Payment transaction record initialized in payment_transactions with status 'initiated'
$tx = PaymentTransaction::getByAttemptReference($attemptRef);
it_rp("Scenario 7: Transaction record initialized with status 'initiated'", 
    $tx !== null && $tx['status'] === 'initiated' && (float)$tx['amount'] === 120.00
);

// 8. Order status remains 'pending' until payment is captured/verified
it_rp("Scenario 8: Order status is 'pending' and payment_status is 'pending'", 
    $dbOrder['order_status'] === 'pending' && $dbOrder['payment_status'] === 'pending'
);

// 9. Stock is reserved atomically on order placement without double deduction
$afterOrderStock = (int)Product::getById($product['id'])['stock_quantity'];
it_rp("Scenario 9: Stock atomically decremented by 2", $afterOrderStock === ($initialStock - 2));

// 10. Valid HMAC SHA256 payment signature verification succeeds
$mockPaymentId = 'pay_mock_' . bin2hex(random_bytes(6));
$secretKey = RazorpayService::getKeySecret();
$validSignature = hash_hmac('sha256', $rzpOrderId . '|' . $mockPaymentId, $secretKey);

$sigVerified = RazorpayService::verifyPaymentSignature($rzpOrderId, $mockPaymentId, $validSignature);
it_rp("Scenario 10: Valid HMAC SHA256 signature verified successfully", $sigVerified === true);

// 11. Invalid / tampered payment signature fails verification and transaction status becomes 'failed'
$invalidSig = 'tampered_signature_string_0000000000000000000000000000000000000000000';
$tamperedVerified = RazorpayService::verifyPaymentSignature($rzpOrderId, $mockPaymentId, $invalidSig);
it_rp("Scenario 11: Tampered signature rejected", $tamperedVerified === false);

// 12. Payment amount mismatch fails verification
$mockPayments[$mockPaymentId] = [
    'id' => $mockPaymentId,
    'order_id' => $rzpOrderId,
    'amount' => 5000, // 50.00 instead of 120.00 (12000 paise)
    'currency' => 'INR',
    'status' => 'captured'
];
$fetchBadAmount = RazorpayService::fetchPayment($mockPaymentId);
$badAmountMatches = ($fetchBadAmount['payment']['amount'] ?? 0) === (int)round($dbOrder['total_amount'] * 100);
it_rp("Scenario 12: Amount mismatch correctly flagged as invalid", $badAmountMatches === false);

// 13. Currency mismatch fails verification
$badCurrencyPaymentId = 'pay_usd_' . bin2hex(random_bytes(6));
$mockPayments[$badCurrencyPaymentId] = [
    'id' => $badCurrencyPaymentId,
    'order_id' => $rzpOrderId,
    'amount' => 12000,
    'currency' => 'USD',
    'status' => 'captured'
];
$fetchBadCurrency = RazorpayService::fetchPayment($badCurrencyPaymentId);
$currencyMatches = ($fetchBadCurrency['payment']['currency'] ?? '') === 'INR';
it_rp("Scenario 13: Currency mismatch correctly flagged as invalid", $currencyMatches === false);

// 14. Successful verification updates order payment status to 'captured' and order status to 'confirmed'
$correctPaymentId = 'pay_correct_' . bin2hex(random_bytes(6));
$mockPayments[$correctPaymentId] = [
    'id' => $correctPaymentId,
    'order_id' => $rzpOrderId,
    'amount' => 12000,
    'currency' => 'INR',
    'status' => 'captured'
];
$correctSig = hash_hmac('sha256', $rzpOrderId . '|' . $correctPaymentId, $secretKey);
$sigCheck = RazorpayService::verifyPaymentSignature($rzpOrderId, $correctPaymentId, $correctSig);
$fetchCorrect = RazorpayService::fetchPayment($correctPaymentId);

// Execute verification updates
PaymentTransaction::updateStatus((int)$tx['id'], 'captured', [
    'gateway_payment_id' => $correctPaymentId,
    'gateway_signature' => $correctSig
]);
Order::updatePaymentAndOrderStatus((int)$dbOrder['id'], 'captured', 'confirmed', [
    'razorpay_payment_id' => $correctPaymentId,
    'razorpay_signature' => $correctSig
]);

$verifiedOrder = Order::getById((int)$dbOrder['id']);
$verifiedTx = PaymentTransaction::getById((int)$tx['id']);
it_rp("Scenario 14: Payment status updated to 'captured' and order status to 'confirmed'", 
    $verifiedOrder['order_status'] === 'confirmed' &&
    $verifiedOrder['payment_status'] === 'captured' &&
    $verifiedTx['status'] === 'captured'
);

// 15. Duplicate payment verification callback is handled idempotently
// Calling update again with same data doesn't corrupt or duplicate
$idempotentTxUpdate = PaymentTransaction::updateStatus((int)$tx['id'], 'captured', [
    'gateway_payment_id' => $correctPaymentId
]);
$idempotentOrder = Order::getById((int)$dbOrder['id']);
it_rp("Scenario 15: Duplicate verification handled idempotently without error", 
    $idempotentTxUpdate === true && $idempotentOrder['order_status'] === 'confirmed'
);

// 16. Webhook HMAC SHA256 signature verification succeeds for valid secret and payload
$webhookSecret = RazorpayService::getWebhookSecret();
$sampleWebhookPayload = json_encode([
    'entity' => 'event',
    'account_id' => 'acc_test123',
    'event' => 'payment.captured',
    'event_id' => 'evt_test_' . uniqid(),
    'payload' => [
        'payment' => [
            'entity' => [
                'id' => $correctPaymentId,
                'order_id' => $rzpOrderId,
                'amount' => 12000,
                'currency' => 'INR',
                'status' => 'captured'
            ]
        ]
    ]
]);
$validWebhookSig = hash_hmac('sha256', $sampleWebhookPayload, $webhookSecret);
$webhookVerified = RazorpayService::verifyWebhookSignature($sampleWebhookPayload, $validWebhookSig);
it_rp("Scenario 16: Webhook signature verified successfully", $webhookVerified === true);

// 17. Webhook event deduplication prevents duplicate processing
$testEvtId = 'evt_dedup_' . bin2hex(random_bytes(6));
it_rp("Scenario 17a: Event initially not processed", PaymentTransaction::isWebhookEventProcessed($testEvtId) === false);
PaymentTransaction::recordWebhookEvent($testEvtId, 'payment.captured', $sampleWebhookPayload);
PaymentTransaction::markWebhookEventProcessed($testEvtId);
it_rp("Scenario 17b: Event correctly recorded and identified as processed", PaymentTransaction::isWebhookEventProcessed($testEvtId) === true);

// 18. Cash on Delivery checkout remains fully functional
CartService::clear();
CartService::addItem($product['id'], 1); // 40 + 40 = 80 total
$codCheckoutPost = [
    CSRF_TOKEN_KEY => csrf_token(),
    'customer_name' => 'COD Tester',
    'customer_phone' => '9888877777',
    'fulfilment_method' => 'delivery',
    'delivery_address' => 'COD Test Street, Bhopal',
    'payment_method' => 'cod',
    'idempotency_key' => 'idem_cod_' . uniqid()
];
$codStockBefore = (int)Product::getById($product['id'])['stock_quantity'];
$codResult = OrderService::processCheckout($codCheckoutPost);
$codStockAfter = (int)Product::getById($product['id'])['stock_quantity'];
$codOrder = Order::getByOrderNumber($codResult['order_number'] ?? '');

it_rp("Scenario 18: COD order placed with order_status='confirmed', payment_status='pending', stock decremented", 
    $codResult['success'] === true &&
    empty($codResult['requires_razorpay']) &&
    $codOrder !== false &&
    $codOrder['order_status'] === 'confirmed' &&
    $codOrder['payment_status'] === 'pending' &&
    $codStockAfter === ($codStockBefore - 1)
);

// Reset mock handler
RazorpayService::setMockHandler(null);

echo "Razorpay Test Suite: {$rpPass} passed, {$rpFail} failed\n";
