<?php
/**
 * Automated Verification & Testing Suite
 * N.A Fresh Fruits & Coconuts
 */
require_once __DIR__ . '/../app/bootstrap.php';

$pdo = Database::getConnection();
$passCount = 0;
$failCount = 0;

function it(string $testName, bool $condition, string $detail = '') {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$testName}\n";
    } else {
        $failCount++;
        echo "  [FAIL] {$testName} - {$detail}\n";
    }
}

echo "========================================================\n";
echo "RUNNING N.A FRESH FRUITS & COCONUTS TEST SUITE\n";
echo "========================================================\n\n";

// ---------------------------------------------------------
// 1. DATABASE & INITIAL SEED TESTS
// ---------------------------------------------------------
echo "1. DATABASE INTEGRITY TESTS:\n";
$stmt = $pdo->query("SELECT * FROM `products` WHERE `slug` = 'fresh-coconut-water' LIMIT 1");
$prod = $stmt->fetch();
it("Product 'Fresh Coconut Water' exists in MySQL", $prod !== false);
it("Product price is ₹40.00", (float)$prod['price'] === 40.00);
it("Product stock is > 0", (int)$prod['stock_quantity'] > 0);

$stmtOrder = $pdo->query("SELECT * FROM `orders` WHERE `order_number` = 'NA1024'");
$order1024 = $stmtOrder->fetch();
it("Reference order #NA1024 exists in database", $order1024 !== false);
it("Order #NA1024 total is ₹120.00", (float)$order1024['total_amount'] === 120.00);

// ---------------------------------------------------------
// 2. CART SERVICE TESTS
// ---------------------------------------------------------
echo "\n2. CART & PRICING TESTS:\n";
CartService::clear();
it("Cart is initially empty", CartService::getCart()['count'] === 0);

CartService::addItem($prod['id'], 2);
$cart = CartService::getCart();
it("Added 2 coconuts to cart", $cart['count'] === 2);
it("Cart subtotal is ₹80.00", (float)$cart['subtotal'] === 80.00);

CartService::updateQuantity($prod['id'], 3);
it("Cart quantity updated to 3", CartService::getCart()['count'] === 3);

$totalsDelivery = CartService::calculateCheckoutTotals('delivery');
it("Delivery totals calculated: subtotal 120 + delivery 40 = 160", $totalsDelivery['total'] === 160.00);

$totalsPickup = CartService::calculateCheckoutTotals('pickup');
it("Pickup totals calculated: delivery fee is 0", $totalsPickup['delivery_charge'] === 0.00 && $totalsPickup['total'] === 120.00);

// ---------------------------------------------------------
// 3. SERVER-SIDE ORDER CREATION & STOCK ATOMICITY
// ---------------------------------------------------------
echo "\n3. ORDER CREATION & INVENTORY ATOMICITY TESTS:\n";
$initialStock = (int)Product::getById($prod['id'])['stock_quantity'];

$orderData = [
    'customer_name' => 'Automated Test User',
    'customer_phone' => '9876543210',
    'fulfilment_method' => 'delivery',
    'delivery_address' => 'Test Street 42, Bhopal',
    'payment_method' => 'cod',
    'idempotency_key' => 'test_idem_' . uniqid()
];

$itemsToBuy = [
    ['product_id' => $prod['id'], 'quantity' => 2]
];

$created = Order::createOrder($orderData, $itemsToBuy);
it("Order created successfully via transaction", $created['success'] === true);

$newStock = (int)Product::getById($prod['id'])['stock_quantity'];
it("Stock atomically decremented by 2 (from {$initialStock} to {$newStock})", $newStock === ($initialStock - 2));

// Test idempotency protection
$duplicateAttempt = Order::createOrder($orderData, $itemsToBuy);
it("Idempotent duplicate request does not create new order", !empty($duplicateAttempt['is_duplicate']));

// Test Order status cancellation & stock restoration
$orderId = $created['order_id'];
$cancelled = Order::updateStatus($orderId, 'cancelled', 'Test cancellation', 'TestRunner');
it("Order status updated to cancelled", $cancelled === true);

$restoredStock = (int)Product::getById($prod['id'])['stock_quantity'];
it("Stock restored after cancellation (back to {$initialStock})", $restoredStock === $initialStock);

// Repeat cancellation should not restore stock twice
Order::updateStatus($orderId, 'cancelled', 'Repeat cancellation', 'TestRunner');
$stillStock = (int)Product::getById($prod['id'])['stock_quantity'];
it("Double cancellation does not double-restore stock", $stillStock === $initialStock);

// ---------------------------------------------------------
// 4. ADMIN AUTHENTICATION & SECURITY TESTS
// ---------------------------------------------------------
echo "\n4. ADMIN AUTHENTICATION & SECURITY TESTS:\n";
Admin::clearLoginAttempts('127.0.0.1');
$badLogin = AuthService::login('admin', 'wrong_password_xyz', '127.0.0.1');
it("Invalid password rejected", $badLogin['success'] === false);

Admin::clearLoginAttempts('127.0.0.1');
$goodLogin = AuthService::login('admin', 'admin12345', '127.0.0.1');
it("Valid admin credentials accepted", $goodLogin['success'] === true);
it("AuthService::check() reports authenticated", AuthService::check() === true);

AuthService::logout();
it("AuthService::logout() terminates session", AuthService::check() === false);

// ---------------------------------------------------------
// 5. SECURITY & CSRF TESTS
// ---------------------------------------------------------
echo "\n5. SECURITY & INPUT VALIDATION TESTS:\n";
$token = csrf_token();
it("CSRF token generated", !empty($token));
it("Valid CSRF token passes verify_csrf_token", verify_csrf_token($token) === true);
it("Tampered CSRF token rejected", verify_csrf_token('invalid_token_123') === false);

// Output escaping
$xss = "<script>alert('xss')</script>";
it("e() escapes XSS HTML entities", e($xss) === "&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;");

// ---------------------------------------------------------
// 6. DASHBOARD & SALES REPORT METRICS
// ---------------------------------------------------------
echo "\n6. REPORTING METRICS TESTS:\n";
$stats = Order::getDashboardStats();
it("Dashboard stats returned", is_array($stats) && isset($stats['sales_today']));
it("Dashboard trend has 7 days", count($stats['trend_dates']) === 7);

// ---------------------------------------------------------
// 7. CUSTOMER AUTHENTICATION & MANAGEMENT TEST SUITE
// ---------------------------------------------------------
echo "\n";
require_once __DIR__ . '/test_customer_auth.php';
$passCount += $authPass;
$failCount += $authFail;

// ---------------------------------------------------------
// 8. COUPONS & WHATSAPP POPUP TEST SUITE
// ---------------------------------------------------------
echo "\n";
require_once __DIR__ . '/test_coupons_and_whatsapp.php';
$passCount += $cPass;
$failCount += $cFail;

// ---------------------------------------------------------
// 9. RAZORPAY PAYMENT GATEWAY TEST SUITE
// ---------------------------------------------------------
echo "\n";
require_once __DIR__ . '/test_razorpay.php';
$passCount += $rpPass;
$failCount += $rpFail;

echo "\n========================================================\n";
echo "OVERALL SYSTEM SUMMARY: {$passCount} PASSED, {$failCount} FAILED\n";
echo "========================================================\n";

if ($failCount > 0) {
    exit(1);
}
