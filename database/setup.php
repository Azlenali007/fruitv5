<?php
/**
 * Database Migration and Seeder for N.A Fresh Fruits & Coconuts
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../app/config/config.php';

try {
    // 1. Connect without DB first to ensure DB exists
    $pdoRoot = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

    // 2. Connect to the database
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // 3. Execute schema
    $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schemaSql);

    // 4. Seed Settings
    $defaultSettings = [
        'store_name' => 'N.A Fresh Fruits & Coconuts',
        'store_tagline' => 'Fresh tender coconut water straight from farm',
        'store_phone' => '+91 98765 43210',
        'store_email' => 'support@nafruitsandcoconuts.com',
        'store_address' => '123, Green Park, Near Main Road, Bhopal, Madhya Pradesh - 462016',
        'currency_symbol' => '₹',
        'local_delivery_enabled' => '1',
        'shop_pickup_enabled' => '1',
        'delivery_charge' => '40.00',
        'min_order_amount' => '40.00',
        'free_delivery_above' => '500.00',
        'service_areas' => 'Bhopal, MP Nagar, Arera Colony, Kolar Road, Green Park, Shahpura',
        'store_open_time' => '07:00 AM',
        'store_close_time' => '09:00 PM',
        'stock_deduction_policy' => 'order_placement',
        'whatsapp_popup_enabled' => '1',
        'whatsapp_channel_url' => 'https://whatsapp.com/channel/0029VaNAFreshFruits',
        'whatsapp_popup_heading' => 'Join Our WhatsApp Channel',
        'whatsapp_popup_message' => 'Get real-time fresh coconut harvest alerts, special seasonal discounts, and quick delivery updates straight on WhatsApp!',
        'whatsapp_popup_button_text' => 'Join WhatsApp Channel',
        'whatsapp_popup_frequency' => 'session',
        'cod_enabled' => '1',
        'razorpay_enabled' => '0',
        'razorpay_environment' => 'test',
        'razorpay_key_id' => '',
        'razorpay_key_secret' => '',
        'razorpay_webhook_secret' => '',
    ];

    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `settings` WHERE `key_name` = ?");
    $stmtInsert = $pdo->prepare("INSERT INTO `settings` (`key_name`, `value`, `description`) VALUES (?, ?, ?)");

    foreach ($defaultSettings as $key => $val) {
        $stmtCheck->execute([$key]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmtInsert->execute([$key, $val, 'Default setting for ' . $key]);
        }
    }

    // 5. Seed Admin User
    // Default admin: admin / admin12345
    $stmtAdmin = $pdo->prepare("SELECT COUNT(*) FROM `admins` WHERE `username` = ?");
    $stmtAdmin->execute(['admin']);
    if ($stmtAdmin->fetchColumn() == 0) {
        $passHash = password_hash('admin12345', PASSWORD_BCRYPT);
        $stmtCreateAdmin = $pdo->prepare("
            INSERT INTO `admins` (`username`, `email`, `password_hash`, `name`, `role`)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtCreateAdmin->execute([
            'admin',
            'admin@nafruitsandcoconuts.com',
            $passHash,
            'Store Manager',
            'superadmin'
        ]);
        echo "Default admin created: username 'admin', password 'admin12345'\n";
    }

    // 6. Seed Product (Fresh Coconut Water - only fresh coconut water!)
    $stmtProd = $pdo->prepare("SELECT COUNT(*) FROM `products` WHERE `slug` = ?");
    $stmtProd->execute(['fresh-coconut-water']);
    if ($stmtProd->fetchColumn() == 0) {
        $gallery = json_encode([
            '/assets/images/product-main.png',
            '/assets/images/thumb-1.png',
            '/assets/images/thumb-2.png',
            '/assets/images/thumb-3.png',
            '/assets/images/thumb-4.png'
        ]);

        $stmtCreateProd = $pdo->prepare("
            INSERT INTO `products` (`name`, `slug`, `tagline`, `description`, `price`, `stock_quantity`, `is_available`, `image_url`, `gallery_json`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtCreateProd->execute([
            'Fresh Coconut Water',
            'fresh-coconut-water',
            'Pure • Natural • Healthy',
            'Fresh tender coconut water, straight from nature. No added sugar, no preservatives, no chemicals. Just pure and natural hydration. Naturally packed with potassium, magnesium, and electrolytes.',
            40.00,
            150,
            1,
            '/assets/images/product-main.png',
            $gallery
        ]);
        echo "Fresh Coconut Water product seeded successfully.\n";
    }

    // 7. Seed Sample Customer & Order #NA1024 (Matches the uploaded reference image)
    $stmtOrderCheck = $pdo->prepare("SELECT COUNT(*) FROM `orders` WHERE `order_number` = ?");
    $stmtOrderCheck->execute(['NA1024']);
    if ($stmtOrderCheck->fetchColumn() == 0) {
        // Customer
        $stmtCust = $pdo->prepare("
            INSERT INTO `customers` (`name`, `phone`, `address`, `city`, `state`, `pincode`)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtCust->execute([
            'Rahul Sharma',
            '+91 98765 43210',
            '123, Green Park, Near Main Road',
            'Bhopal',
            'Madhya Pradesh',
            '462016'
        ]);
        $customerId = $pdo->lastInsertId();

        $trackingHash = hash('sha256', 'NA1024' . microtime(true) . 'salt_coconuts');

        $stmtCreateOrder = $pdo->prepare("
            INSERT INTO `orders` (
                `order_number`, `customer_id`, `customer_name`, `customer_phone`, 
                `fulfilment_method`, `delivery_address`, `subtotal`, `delivery_charge`, 
                `total_amount`, `payment_method`, `payment_status`, `order_status`, 
                `tracking_hash`, `estimated_delivery`, `notes`, `created_at`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmtCreateOrder->execute([
            'NA1024',
            $customerId,
            'Rahul Sharma',
            '+91 98765 43210',
            'delivery',
            '123, Green Park, Near Main Road, Bhopal, Madhya Pradesh - 462016',
            80.00,
            40.00,
            120.00,
            'cod',
            'pending',
            'confirmed',
            $trackingHash,
            'Today, 4:30 PM - 6:30 PM',
            'Leave with security if not available'
        ]);
        $orderId = $pdo->lastInsertId();

        // Order item
        $stmtItem = $pdo->prepare("
            INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `subtotal`)
            VALUES (?, 1, 'Fresh Coconut Water', 40.00, 2, 80.00)
        ");
        $stmtItem->execute([$orderId]);

        // Status history
        $stmtHist = $pdo->prepare("
            INSERT INTO `order_status_history` (`order_id`, `old_status`, `new_status`, `comment`, `changed_by`)
            VALUES (?, 'pending', 'confirmed', 'Order confirmed by store manager', 'admin')
        ");
        $stmtHist->execute([$orderId]);

        echo "Reference order #NA1024 seeded successfully.\n";
    }

    // 8. Seed Default Coupons
    $defaultCoupons = [
        [
            'code' => 'FREEDELIVERY',
            'name' => 'Free Doorstep Delivery',
            'description' => 'Enjoy free delivery on any order above ₹80',
            'type' => 'free_delivery',
            'discount_value' => 0.00,
            'min_order_amount' => 80.00,
            'max_discount_amount' => null,
            'max_uses' => 500,
            'max_uses_per_customer' => 2,
            'is_active' => 1
        ],
        [
            'code' => 'SAVE10',
            'name' => '10% Healthy Hydration',
            'description' => 'Get 10% discount on fresh coconut water up to ₹50',
            'type' => 'percentage',
            'discount_value' => 10.00,
            'min_order_amount' => 100.00,
            'max_discount_amount' => 50.00,
            'max_uses' => 1000,
            'max_uses_per_customer' => 3,
            'is_active' => 1
        ],
        [
            'code' => 'FLAT50',
            'name' => 'Flat ₹50 Super Saver',
            'description' => 'Get flat ₹50 off on orders of ₹200 or more',
            'type' => 'fixed',
            'discount_value' => 50.00,
            'min_order_amount' => 200.00,
            'max_discount_amount' => null,
            'max_uses' => 200,
            'max_uses_per_customer' => 1,
            'is_active' => 1
        ]
    ];

    $stmtCouponCheck = $pdo->prepare("SELECT COUNT(*) FROM `coupons` WHERE `code` = ?");
    $stmtCouponInsert = $pdo->prepare("
        INSERT INTO `coupons` (`code`, `name`, `description`, `type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `max_uses`, `max_uses_per_customer`, `is_active`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($defaultCoupons as $c) {
        $stmtCouponCheck->execute([$c['code']]);
        if ($stmtCouponCheck->fetchColumn() == 0) {
            $stmtCouponInsert->execute([
                $c['code'], $c['name'], $c['description'], $c['type'],
                $c['discount_value'], $c['min_order_amount'], $c['max_discount_amount'],
                $c['max_uses'], $c['max_uses_per_customer'], $c['is_active']
            ]);
        }
    }
    echo "Default coupons verified/seeded successfully.\n";

    // Record installation lock file
    $lockData = json_encode([
        'installed_at' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'source' => 'setup.php',
        'verified' => true
    ], JSON_PRETTY_PRINT);
    @mkdir(__DIR__ . '/../storage', 0755, true);
    @file_put_contents(INSTALL_LOCK_FILE, $lockData);

    echo "Database setup and seeding completed successfully.\n";
} catch (Exception $e) {
    echo "Database Setup Error: " . $e->getMessage() . "\n";
    exit(1);
}
