<?php
/**
 * Customer Authentication & Security Test Suite
 * N.A Fresh Fruits & Coconuts
 */
require_once __DIR__ . '/../app/bootstrap.php';

$pdo = Database::getConnection();
$authPass = 0;
$authFail = 0;

function testAssert(string $testName, bool $condition, string $detail = '') {
    global $authPass, $authFail;
    if ($condition) {
        $authPass++;
        echo "  [PASS] {$testName}\n";
    } else {
        $authFail++;
        echo "  [FAIL] {$testName} - {$detail}\n";
    }
}

echo "========================================================\n";
echo "RUNNING CUSTOMER AUTHENTICATION & SECURITY TESTS\n";
echo "========================================================\n\n";

// ---------------------------------------------------------
// 1. PHONE NUMBER NORMALIZATION
// ---------------------------------------------------------
echo "1. PHONE NORMALIZATION TESTS:\n";
testAssert("10-digit raw phone remains 10 digits", Customer::normalizePhone('9826012345') === '9826012345');
testAssert("+91 formatted phone stripped to 10 digits", Customer::normalizePhone('+91 98260 12345') === '9826012345');
testAssert("91-prefixed digits normalized", Customer::normalizePhone('919826012345') === '9826012345');
testAssert("Dashes and parentheses removed", Customer::normalizePhone('+91 (982) 601-2345') === '9826012345');

// ---------------------------------------------------------
// 2. REGISTRATION VALIDATION & PASSWORD SECURITY
// ---------------------------------------------------------
echo "\n2. REGISTRATION & PASSWORD VALIDATION TESTS:\n";
$uniqueSuffix = time() . '_' . rand(100, 999);
$testPhone = '99' . substr(strval(time()), -8); // Valid 10-digit mobile
$testEmail = 'cust_' . $uniqueSuffix . '@example.com';
$testPassword = 'SecurePassword!123';

// Short password rejection
$shortPassRes = CustomerAuthService::register([
    'name' => 'Short Pass User',
    'phone' => '9800000001',
    'password' => '123',
    'password_confirm' => '123',
], '127.0.0.1');
testAssert("Short password (<6 chars) rejected", $shortPassRes['success'] === false);

// Password confirmation mismatch
$mismatchRes = CustomerAuthService::register([
    'name' => 'Mismatch User',
    'phone' => '9800000002',
    'password' => 'Pass1234',
    'password_confirm' => 'DifferentPass1234',
], '127.0.0.1');
testAssert("Password confirmation mismatch rejected", $mismatchRes['success'] === false);

// Invalid phone format
$invalidPhoneRes = CustomerAuthService::register([
    'name' => 'Bad Phone User',
    'phone' => '12345',
    'password' => 'Pass1234',
    'password_confirm' => 'Pass1234',
], '127.0.0.1');
testAssert("Invalid mobile length rejected", $invalidPhoneRes['success'] === false);

// Successful registration
$regRes = CustomerAuthService::register([
    'name' => 'Amit Verma',
    'phone' => $testPhone,
    'email' => $testEmail,
    'password' => $testPassword,
    'password_confirm' => $testPassword,
    'address' => 'Flat 402, Green Meadows',
    'city' => 'Bhopal',
    'pincode' => '462001'
], '127.0.0.1');
testAssert("Valid customer registration succeeds", $regRes['success'] === true);
$testCustomerId = $regRes['customer_id'] ?? 0;
testAssert("Customer ID generated (>0)", $testCustomerId > 0);

// Verify password hashing in DB
$cRecord = Customer::findById($testCustomerId);
testAssert("Password hash stored using BCRYPT/Argon2", !empty($cRecord['password_hash']) && str_starts_with($cRecord['password_hash'], '$2y$'));
testAssert("Plaintext password matches stored hash", password_verify($testPassword, $cRecord['password_hash']));

// Duplicate phone rejection
$dupPhoneRes = CustomerAuthService::register([
    'name' => 'Duplicate Phone User',
    'phone' => $testPhone,
    'email' => 'other_' . $uniqueSuffix . '@example.com',
    'password' => 'Pass1234',
    'password_confirm' => 'Pass1234',
], '127.0.0.1');
testAssert("Duplicate phone registration rejected", $dupPhoneRes['success'] === false);

// Duplicate email rejection
$dupEmailRes = CustomerAuthService::register([
    'name' => 'Duplicate Email User',
    'phone' => '97' . substr(strval(time()), -8),
    'email' => $testEmail,
    'password' => 'Pass1234',
    'password_confirm' => 'Pass1234',
], '127.0.0.1');
testAssert("Duplicate email registration rejected", $dupEmailRes['success'] === false);

// ---------------------------------------------------------
// 3. LOGIN, THROTTLING & SESSION MANAGEMENT
// ---------------------------------------------------------
echo "\n3. LOGIN & BRUTE FORCE PROTECTION TESTS:\n";
// Clear session first
CustomerAuthService::logout();
testAssert("Customer session cleared on logout", CustomerAuthService::check() === false);

// Wrong password rejection
$badPass = CustomerAuthService::login($testPhone, 'wrong_password', '127.0.0.1');
testAssert("Wrong customer password rejected", $badPass['success'] === false);

// Legacy customer without password cannot log in
$legacyLogin = CustomerAuthService::login('+91 98765 43210', 'any_password', '127.0.0.1');
testAssert("Legacy guest customer without password cannot log in directly", $legacyLogin['success'] === false);

// Successful login via Phone
$goodLoginPhone = CustomerAuthService::login($testPhone, $testPassword, '127.0.0.1');
testAssert("Login via mobile phone succeeds", $goodLoginPhone['success'] === true);
testAssert("CustomerAuthService::check() reports true", CustomerAuthService::check() === true);
testAssert("CustomerAuthService::id() returns registered ID", CustomerAuthService::id() === $testCustomerId);

// Logout & Successful login via Email
CustomerAuthService::logout();
$goodLoginEmail = CustomerAuthService::login($testEmail, $testPassword, '127.0.0.1');
testAssert("Login via email address succeeds", $goodLoginEmail['success'] === true);
CustomerAuthService::logout();

// Rate limiting / Throttling test
$throttlePhone = '9111111111';
for ($i = 0; $i < 5; $i++) {
    Customer::recordLoginAttempt($throttlePhone, '10.0.0.1', false);
}
testAssert("Customer throttling triggers after 5 failed attempts", Customer::isLoginThrottled($throttlePhone, '10.0.0.1') === true);

// ---------------------------------------------------------
// 4. MANDATORY LOGIN CHECKOUT ENFORCEMENT & ORDER LINKING
// ---------------------------------------------------------
echo "\n4. MANDATORY CHECKOUT AUTH & ORDER PRIVACY TESTS:\n";

// Populate cart
$prodStmt = $pdo->query("SELECT * FROM `products` WHERE `slug` = 'fresh-coconut-water' LIMIT 1");
$prod = $prodStmt->fetch();
CartService::clear();
CartService::addItem($prod['id'], 2);

// Case A: Guest checkout (NOT logged in) MUST BE BLOCKED
CustomerAuthService::logout();
$guestCheckoutRes = OrderService::processCheckout([
    'customer_name' => 'Unauthenticated Guest',
    'customer_phone' => '9888888888',
    'fulfilment_method' => 'delivery',
    'delivery_address' => 'Some address',
    'payment_method' => 'cod',
    'idempotency_key' => 'idem_guest_' . uniqid()
]);
testAssert("Unauthenticated guest checkout is BLOCKED", $guestCheckoutRes['success'] === false && !empty($guestCheckoutRes['auth_required']));

// Case B: Logged-in Customer Checkout
CustomerAuthService::login($testPhone, $testPassword, '127.0.0.1');
testAssert("Customer is logged in for checkout", CustomerAuthService::check() === true);

CartService::clear();
CartService::addItem($prod['id'], 2);

$token = csrf_token();

$authCheckoutRes = OrderService::processCheckout([
    CSRF_TOKEN_KEY => $token,
    'customer_name' => 'Amit Verma',
    'customer_phone' => $testPhone,
    'customer_email' => $testEmail,
    'fulfilment_method' => 'delivery',
    'delivery_address' => 'Flat 402, Green Meadows, Arera Colony',
    'delivery_city' => 'Bhopal',
    'delivery_pincode' => '462001',
    'payment_method' => 'cod',
    'idempotency_key' => 'idem_auth_' . uniqid()
]);

testAssert("Authenticated checkout succeeds", $authCheckoutRes['success'] === true);
$newOrderNum = $authCheckoutRes['order_number'] ?? '';
$newOrderRecord = Order::getByOrderNumber($newOrderNum);
testAssert("Order is linked to customer_id {$testCustomerId}", (int)$newOrderRecord['customer_id'] === $testCustomerId);

// ---------------------------------------------------------
// 5. PAYMENT SAFETY & STATUS INITIALIZATION
// ---------------------------------------------------------
echo "\n5. PAYMENT SAFETY VERIFICATION TESTS:\n";
CartService::clear();
CartService::addItem($prod['id'], 1);

$onlineOrderRes = OrderService::processCheckout([
    CSRF_TOKEN_KEY => $token,
    'customer_name' => 'Amit Verma',
    'customer_phone' => $testPhone,
    'fulfilment_method' => 'delivery',
    'delivery_address' => 'Flat 402, Green Meadows, Arera Colony, Bhopal',
    'payment_method' => 'online',
    'idempotency_key' => 'idem_online_' . uniqid()
]);

testAssert("Online order placed", $onlineOrderRes['success'] === true, $onlineOrderRes['error'] ?? 'no error');
$onlineOrderRecord = Order::getByOrderNumber($onlineOrderRes['order_number'] ?? '');
testAssert("Online payment status initialized to 'pending' (NEVER 'paid')", ($onlineOrderRecord['payment_status'] ?? '') === 'pending', 'Actual status: ' . ($onlineOrderRecord['payment_status'] ?? 'null'));

// ---------------------------------------------------------
// 6. ORDER PRIVACY & CUSTOMER ISOLATION
// ---------------------------------------------------------
echo "\n6. ORDER PRIVACY & ACCESS CONTROL TESTS:\n";
// Customer A (Amit Verma) can access their order
$ownedOrder = Customer::getCustomerOrderById($testCustomerId, (int)$newOrderRecord['id']);
testAssert("Customer can retrieve their own order", !empty($ownedOrder) && $ownedOrder['order_number'] === $newOrderNum);

// Customer B cannot access Customer A's order
$otherCustomerId = 999999;
$stolenOrder = Customer::getCustomerOrderById($otherCustomerId, (int)$newOrderRecord['id']);
testAssert("Customer B cannot access Customer A's order by ID", empty($stolenOrder));

// Customer orders list includes the new orders
$myOrders = Customer::getCustomerOrders($testCustomerId);
testAssert("Customer orders query returns records", count($myOrders) >= 2);

// ---------------------------------------------------------
// 7. ADMIN CUSTOMER MANAGEMENT QUERIES
// ---------------------------------------------------------
echo "\n7. ADMIN CUSTOMER MANAGEMENT TESTS:\n";
$adminCustCount = Customer::countAll();
testAssert("Customer count is >= 2", $adminCustCount >= 2);

$adminList = Customer::getAll([], 10, 0);
testAssert("Customer list query returns rows", !empty($adminList));

$filteredList = Customer::getAll(['search' => 'Amit'], 10, 0);
testAssert("Search customer by name returns matching record", count($filteredList) >= 1 && str_contains($filteredList[0]['name'], 'Amit'));

echo "\n========================================================\n";
echo "CUSTOMER AUTH SUITE SUMMARY: {$authPass} PASSED, {$authFail} FAILED\n";
echo "========================================================\n";

if ($authFail > 0) {
    exit(1);
}
