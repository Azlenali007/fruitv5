<?php
/**
 * Delivery Areas, COD Enforcement & Shop Pickup Removal Test Suite
 * N.A Fresh Fruits & Coconuts
 */
require_once __DIR__ . '/../app/bootstrap.php';

$pdo = Database::getConnection();
$areaPass = 0;
$areaFail = 0;

function it_area(string $testName, bool $condition, string $detail = '') {
    global $areaPass, $areaFail;
    if ($condition) {
        $areaPass++;
        echo "  [PASS] {$testName}\n";
    } else {
        $areaFail++;
        echo "  [FAIL] {$testName} - {$detail}\n";
    }
}

echo "========================================================\n";
echo "10. DELIVERY AREAS, COD TOGGLE & PICKUP REMOVAL TESTS\n";
echo "========================================================\n\n";

// -------------------------------------------------------------
// A. COD TOGGLE & STRICT BACKEND ENFORCEMENT
// -------------------------------------------------------------
echo "A. COD ON/OFF TOGGLE & ENFORCEMENT:\n";

// 1. Turn COD OFF via settings
$saveOff = RazorpayService::saveSettings([
    'is_form_submission' => 1,
    'cod_enabled' => 0,
    'razorpay_enabled' => 1,
    'environment' => 'test'
]);
it_area("Admin turned COD OFF in settings", $saveOff['success'] === true && !RazorpayService::isCodEnabled());
it_area("Setting::isCodEnabled() returns false when COD is off", Setting::isCodEnabled() === false);

// Setup customer login session for order tests
$testCustomerPhone = '9876599999';
$testCust = Customer::findByPhone($testCustomerPhone);
if (!$testCust) {
    $cRes = Customer::create([
        'name' => 'Area Test Customer',
        'phone' => $testCustomerPhone,
        'password' => 'password123'
    ]);
    $testCust = Customer::findById((int)$cRes['id']);
}
$_SESSION[CustomerAuthService::SESSION_CUSTOMER_ID] = (int)$testCust['id'];
$_SESSION[CustomerAuthService::SESSION_CUSTOMER_PHONE] = $testCustomerPhone;
$_SESSION[CustomerAuthService::SESSION_CUSTOMER_NAME] = $testCust['name'];

// Seed cart with 1 coconut
CartService::clear();
$stmtProd = $pdo->query("SELECT id FROM products WHERE slug = 'fresh-coconut-water' LIMIT 1");
$prodId = (int)$stmtProd->fetchColumn();
CartService::addItem($prodId, 1);

// Test submission of COD order while COD is OFF
$orderCodOff = OrderService::processCheckout([
    CSRF_TOKEN_KEY => csrf_token(),
    'customer_name' => 'Area Test Customer',
    'customer_phone' => $testCustomerPhone,
    'fulfilment_method' => 'delivery',
    'delivery_address' => 'Station Road, Jaora, MP',
    'payment_method' => 'cod'
]);
it_area("OrderService strictly rejects COD order when COD is disabled", $orderCodOff['success'] === false);
it_area("Appropriate rejection message returned", str_contains(strtolower($orderCodOff['error']), 'disabled') || str_contains(strtolower($orderCodOff['error']), 'cash on delivery'));

// Direct model call validation test
$modelCodRes = Order::createOrder([
    'customer_id' => (int)$testCust['id'],
    'customer_name' => 'Area Test Customer',
    'customer_phone' => $testCustomerPhone,
    'fulfilment_method' => 'delivery',
    'delivery_address' => 'Station Road, Jaora, MP',
    'payment_method' => 'cod'
], [['product_id' => $prodId, 'quantity' => 1]]);
it_area("Order::createOrder rejects tampered direct COD order when disabled", $modelCodRes['success'] === false && str_contains(strtolower($modelCodRes['error'] ?? ''), 'cash on delivery'));

// Turn COD back ON
$saveOn = RazorpayService::saveSettings([
    'is_form_submission' => 1,
    'cod_enabled' => 1,
    'razorpay_enabled' => 1,
    'environment' => 'test'
]);
it_area("Admin turned COD ON in settings", $saveOn['success'] === true && RazorpayService::isCodEnabled());
it_area("Setting::isCodEnabled() returns true when COD is on", Setting::isCodEnabled() === true);

// -------------------------------------------------------------
// B. SHOP PICKUP COMPLETE REMOVAL & REJECTION
// -------------------------------------------------------------
echo "\nB. SHOP PICKUP REMOVAL & BACKEND REJECTION:\n";

$pickupOrder = OrderService::processCheckout([
    CSRF_TOKEN_KEY => csrf_token(),
    'customer_name' => 'Area Test Customer',
    'customer_phone' => $testCustomerPhone,
    'fulfilment_method' => 'pickup',
    'delivery_address' => 'Store Pickup',
    'payment_method' => 'cod'
]);
it_area("OrderService rejects pickup order submissions", $pickupOrder['success'] === false);
it_area("Rejection states pickup is not available", str_contains(strtolower($pickupOrder['error']), 'pickup') || str_contains(strtolower($pickupOrder['error']), 'doorstep'));

$modelPickupRes = Order::createOrder([
    'customer_id' => (int)$testCust['id'],
    'customer_name' => 'Area Test Customer',
    'customer_phone' => $testCustomerPhone,
    'fulfilment_method' => 'pickup',
    'delivery_address' => 'Store Pickup',
    'payment_method' => 'cod'
], [['product_id' => $prodId, 'quantity' => 1]]);
it_area("Order::createOrder rejects direct pickup attempt", $modelPickupRes['success'] === false && str_contains(strtolower($modelPickupRes['error'] ?? ''), 'pickup'));

// -------------------------------------------------------------
// C. PREPOPULATED 35 JAORA LOCALITIES
// -------------------------------------------------------------
echo "\nC. PREPOPULATED 35 JAORA LOCALITIES VERIFICATION:\n";

$expectedLocalities = [
    'Topkhana', 'Maharpura', 'Bag Nana Sahab', 'Station Road', 'Shastri Colony',
    'Industrial Area', 'Chopati', 'Fort Area', 'Civil Lines', 'Brahmpuri',
    'Ratanpipali Road', 'Badavada Naka', 'Piploda Road', 'Sitamau Road', 'Dhodhar Naka',
    'Kumharwada', 'Khatriwada', 'Sarafa Bazar', 'Gandhi Road', 'Subhash Marg',
    'Jawahar Path', 'Nai Abadi', 'Railway Colony', 'Patidar Colony', 'Shanti Nagar',
    'Teachers Colony', 'Anand Colony', 'Geeta Colony', 'Vikas Nagar', 'Arihant Nagar',
    'Vardhaman Nagar', 'Krishi Upaj Mandi', 'Bus Stand Area', 'Court Area', 'Sanjay Complex'
];

$stmtCount = $pdo->query("SELECT COUNT(*) FROM `delivery_areas`");
$totalAreasCount = (int)$stmtCount->fetchColumn();
it_area("delivery_areas table contains at least 35 localities (found: {$totalAreasCount})", $totalAreasCount >= 35);

$stmtNames = $pdo->query("SELECT name, status, boundary_geojson, delivery_charge FROM `delivery_areas`");
$existingLocalities = $stmtNames->fetchAll(PDO::FETCH_ASSOC);
$existingNameMap = [];
foreach ($existingLocalities as $loc) {
    $existingNameMap[$loc['name']] = $loc;
}

$all35Present = true;
$all35InactiveInitially = true;
$all35NoFakeCoords = true;

foreach ($expectedLocalities as $locName) {
    if (!isset($existingNameMap[$locName])) {
        $all35Present = false;
        break;
    }
    // Check preliminary status is inactive
    if ($existingNameMap[$locName]['status'] !== 'inactive') {
        $all35InactiveInitially = false;
    }
    // Check no fake coordinates
    if (!empty($existingNameMap[$locName]['boundary_geojson'])) {
        $all35NoFakeCoords = false;
    }
}

it_area("All 35 requested Jaora localities are present in MySQL database", $all35Present);
it_area("All 35 localities are seeded with status 'inactive'", $all35InactiveInitially);
it_area("All 35 localities are preliminary with NO fake coordinates (boundary_geojson is null)", $all35NoFakeCoords);

// -------------------------------------------------------------
// D. DELIVERY AREA CRUD & POINT-IN-POLYGON MATCHING
// -------------------------------------------------------------
echo "\nD. DELIVERY AREA CRUD & SPATIAL POINT-IN-POLYGON TESTS:\n";

// Create custom delivery zone with polygon enclosing Jaora central area (23.890 to 23.895 N, 75.130 to 75.135 E)
$testGeoJson = json_encode([
    'type' => 'Polygon',
    'coordinates' => [
        [
            [75.130000, 23.890000],
            [75.135000, 23.890000],
            [75.135000, 23.895000],
            [75.130000, 23.895000],
            [75.130000, 23.890000] // closed ring
        ]
    ]
]);

// Clean any leftover test zones from previous runs
$pdo->exec("DELETE FROM `orders` WHERE `delivery_area_name` LIKE '%Test Zone%'");
$pdo->exec("DELETE FROM `delivery_areas` WHERE `name` LIKE '%Test Zone%'");

$createArea = DeliveryArea::create([
    'name' => 'Jaora Central Test Zone',
    'delivery_charge' => 35.00,
    'status' => 'active',
    'boundary_geojson' => $testGeoJson
]);

it_area("Created new active delivery zone with polygon GeoJSON", $createArea['success'] === true);
$testAreaId = (int)$createArea['id'];

// Test Point-in-Polygon directly:
// Inside point: 23.892, 75.132
$polygonPoints = AreaService::parsePolygonPoints($testGeoJson);
it_area("Parsed GeoJSON into valid polygon vertices list", is_array($polygonPoints) && count($polygonPoints) >= 3);

$insideCheck = AreaService::isPointInPolygon(23.892000, 75.132000, $polygonPoints);
it_area("Ray-casting: Coordinate [23.892, 75.132] is correctly detected INSIDE polygon", $insideCheck === true);

// Outside point: 23.950, 75.200 (miles outside)
$outsideCheck = AreaService::isPointInPolygon(23.950000, 75.200000, $polygonPoints);
it_area("Ray-casting: Coordinate [23.950, 75.200] is correctly detected OUTSIDE polygon", $outsideCheck === false);

// Test AreaService::checkDeliveryEligibility with GPS coordinates
$eligInside = AreaService::checkDeliveryEligibility(23.892000, 75.132000);
it_area("Eligibility check for inside coordinates returns deliverable = true", $eligInside['deliverable'] === true);
it_area("Eligibility correctly identifies area name 'Jaora Central Test Zone'", $eligInside['area_name'] === 'Jaora Central Test Zone');
it_area("Eligibility assigns configured delivery charge ₹35.00", (float)$eligInside['delivery_charge'] === 35.00);

// Eligibility for outside coordinates
$eligOutside = AreaService::checkDeliveryEligibility(23.950000, 75.200000);
it_area("Eligibility check for outside coordinates returns deliverable = false", $eligOutside['deliverable'] === false);
it_area("Eligibility error message contains unavailable notification", str_contains(strtolower($eligOutside['message']), 'not available'));

// Test manual area fallback selection
$eligFallback = AreaService::checkDeliveryEligibility(null, null, $testAreaId);
it_area("Manual locality fallback selection returns deliverable = true", $eligFallback['deliverable'] === true);
it_area("Manual fallback retrieves configured area charge ₹35.00", (float)$eligFallback['delivery_charge'] === 35.00);

// -------------------------------------------------------------
// E. ORDER PLACEMENT WITH COORDINATES SNAPSHOT
// -------------------------------------------------------------
echo "\nE. ORDER PLACEMENT SNAPSHOT & HISTORICAL RATE PRESERVATION:\n";

CartService::clear();
CartService::addItem($prodId, 1); // ₹40.00 item
$orderSnap = OrderService::processCheckout([
    CSRF_TOKEN_KEY => csrf_token(),
    'customer_name' => 'Area Test Customer',
    'customer_phone' => $testCustomerPhone,
    'fulfilment_method' => 'delivery',
    'delivery_address' => 'Near Central Chowk, Jaora',
    'latitude' => 23.892000,
    'longitude' => 75.132000,
    'payment_method' => 'cod'
]);
it_area("Order successfully placed with map coordinates inside active area", $orderSnap['success'] === true);

$placedOrderNum = $orderSnap['order_number'];
$stmtPlaced = $pdo->prepare("SELECT * FROM `orders` WHERE `order_number` = ?");
$stmtPlaced->execute([$placedOrderNum]);
$savedOrder = $stmtPlaced->fetch();

it_area("Order snapshot saved delivery_area_id", (int)$savedOrder['delivery_area_id'] === $testAreaId);
it_area("Order snapshot saved delivery_area_name = 'Jaora Central Test Zone'", $savedOrder['delivery_area_name'] === 'Jaora Central Test Zone');
it_area("Order snapshot saved latitude", abs((float)$savedOrder['latitude'] - 23.892) < 0.001);
it_area("Order snapshot saved longitude", abs((float)$savedOrder['longitude'] - 75.132) < 0.001);
it_area("Order snapshot recorded area delivery charge of ₹35.00", (float)$savedOrder['original_delivery_charge'] === 35.00);
it_area("Order total amount is ₹75.00 (₹40 product + ₹35 delivery)", (float)$savedOrder['total_amount'] === 75.00);

// Historical rate protection: Update area delivery fee to ₹60.00
DeliveryArea::update($testAreaId, [
    'name' => 'Jaora Central Test Zone',
    'delivery_charge' => 60.00,
    'status' => 'active'
]);
$stmtVerifyHistorical = $pdo->prepare("SELECT original_delivery_charge, total_amount FROM `orders` WHERE `order_number` = ?");
$stmtVerifyHistorical->execute([$placedOrderNum]);
$historicalOrder = $stmtVerifyHistorical->fetch();
it_area("Historical protection: Existing order delivery charge remains ₹35.00 after rate change", (float)$historicalOrder['original_delivery_charge'] === 35.00);
it_area("Historical protection: Existing order total amount remains ₹75.00", (float)$historicalOrder['total_amount'] === 75.00);

// Test safe deactivation when orders exist
$delResult = DeliveryArea::delete($testAreaId);
it_area("Safe area deletion: deactivates instead of hard delete when referenced by order", $delResult['success'] === true && $delResult['action'] === 'deactivated');

// Cleanup test records
$pdo->exec("DELETE FROM `orders` WHERE `delivery_area_name` LIKE '%Test Zone%'");
$pdo->exec("DELETE FROM `delivery_areas` WHERE `name` LIKE '%Test Zone%'");

// -------------------------------------------------------------
// F. PUBLIC ADMIN LOGIN LINK REMOVAL CHECK
// -------------------------------------------------------------
echo "\nF. PUBLIC ADMIN LINK REMOVAL CHECK:\n";

$headerContent = file_get_contents(__DIR__ . '/../app/views/layouts/header.php');
$footerContent = file_get_contents(__DIR__ . '/../app/views/layouts/footer.php');
$loginContent = file_get_contents(__DIR__ . '/../app/views/customer/login.php');

it_area("Header layout contains no visible link to /admin", !str_contains($headerContent, 'href="/admin"'));
it_area("Footer layout contains no visible link to /admin", !str_contains($footerContent, 'href="/admin"'));
it_area("Footer contains no 'Admin Login' anchor text", !str_contains($footerContent, 'Admin Login'));
it_area("Customer login view contains no link to /admin", !str_contains($loginContent, 'href="/admin"'));

// -------------------------------------------------------------
// G. PAGE TRANSITION LOADER CODE CHECK
// -------------------------------------------------------------
echo "\nG. PAGE TRANSITION LOADER CODE CHECK:\n";

it_area("Header layout contains page-loader element", str_contains($headerContent, 'id="page-loader"'));
it_area("Header script intercepts links and prevents double-clicking", str_contains($headerContent, 'data-navigating'));
it_area("Header script handles BFCache via pageshow event", str_contains($headerContent, 'pageshow'));
it_area("Header styles respect prefers-reduced-motion", str_contains($headerContent, 'prefers-reduced-motion: reduce'));

echo "\n--- DELIVERY AREAS & CHECKOUT TEST RESULTS: {$areaPass} PASSED, {$areaFail} FAILED ---\n";
