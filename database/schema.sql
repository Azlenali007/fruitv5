-- MySQL Database Schema for N.A Fresh Fruits & Coconuts
-- Production-ready, fully normalized, strict constraints, transactions-ready

-- 1. Admin Users Table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'superadmin',
    `last_login_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Login Attempts Table (Security Throttling)
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `success` TINYINT(1) NOT NULL DEFAULT 0,
    INDEX `idx_ip_time` (`ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Settings Table (Store & Delivery Settings)
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key_name` VARCHAR(60) NOT NULL UNIQUE,
    `value` TEXT NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Products Table
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `tagline` VARCHAR(255) NOT NULL DEFAULT 'Pure • Natural • Healthy',
    `description` TEXT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 40.00,
    `stock_quantity` INT NOT NULL DEFAULT 100,
    `is_available` TINYINT(1) NOT NULL DEFAULT 1,
    `image_url` VARCHAR(255) NOT NULL,
    `gallery_json` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_availability` (`is_available`),
    INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Customers Table
CREATE TABLE IF NOT EXISTS `customers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) NULL,
    `password_hash` VARCHAR(255) NULL,
    `address` TEXT NULL,
    `city` VARCHAR(80) NULL,
    `state` VARCHAR(80) NULL,
    `pincode` VARCHAR(10) NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'active',
    `last_login_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_phone` (`phone`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(30) NOT NULL UNIQUE,
    `customer_id` INT UNSIGNED NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `fulfilment_method` ENUM('delivery', 'pickup') NOT NULL DEFAULT 'delivery',
    `coupon_id` INT UNSIGNED NULL,
    `coupon_code` VARCHAR(50) NULL,
    `coupon_type` VARCHAR(20) NULL,
    `delivery_address` TEXT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `original_delivery_charge` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `delivery_discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `delivery_charge` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `payment_method` ENUM('cod', 'online') NOT NULL DEFAULT 'cod',
    `payment_status` ENUM('pending', 'paid', 'refunded', 'failed') NOT NULL DEFAULT 'pending',
    `order_status` ENUM('pending', 'confirmed', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    `tracking_hash` VARCHAR(64) NOT NULL UNIQUE,
    `estimated_delivery` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `idempotency_key` VARCHAR(64) UNIQUE NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_order_num` (`order_number`),
    INDEX `idx_status` (`order_status`),
    INDEX `idx_customer_phone` (`customer_phone`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_tracking_hash` (`tracking_hash`),
    CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Order Items Table (Stores immutable historical price and product snapshot)
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `product_name` VARCHAR(150) NOT NULL,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Order Status History Table
CREATE TABLE IF NOT EXISTS `order_status_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `old_status` VARCHAR(30) NULL,
    `new_status` VARCHAR(30) NOT NULL,
    `comment` VARCHAR(255) NULL,
    `changed_by` VARCHAR(50) NOT NULL DEFAULT 'System',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_history_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. OTP / Verification Table (Rate-limited, hash-stored)
CREATE TABLE IF NOT EXISTS `otp_verifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `phone` VARCHAR(20) NOT NULL,
    `otp_code_hash` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
    `verified` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_phone_expiry` (`phone`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Customer Login Attempts Table (Security Throttling)
CREATE TABLE IF NOT EXISTS `customer_login_attempts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `identifier` VARCHAR(100) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `success` TINYINT(1) NOT NULL DEFAULT 0,
    INDEX `idx_ident_time` (`identifier`, `attempted_at`),
    INDEX `idx_ip_time` (`ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Coupons Table
CREATE TABLE IF NOT EXISTS `coupons` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `type` ENUM('percentage', 'fixed', 'free_delivery') NOT NULL DEFAULT 'percentage',
    `discount_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `min_order_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `max_discount_amount` DECIMAL(10,2) NULL,
    `start_date` DATETIME NULL,
    `expiry_date` DATETIME NULL,
    `max_uses` INT UNSIGNED NULL,
    `current_uses` INT UNSIGNED NOT NULL DEFAULT 0,
    `max_uses_per_customer` INT UNSIGNED NULL DEFAULT 1,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_coupon_code` (`code`),
    INDEX `idx_coupon_active` (`is_active`),
    INDEX `idx_coupon_dates` (`start_date`, `expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Coupon Usage Audit Table
CREATE TABLE IF NOT EXISTS `coupon_usage` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `coupon_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `delivery_discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `used_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_cu_coupon` (`coupon_id`),
    INDEX `idx_cu_order` (`order_id`),
    INDEX `idx_cu_customer` (`customer_id`),
    INDEX `idx_cu_phone` (`customer_phone`),
    CONSTRAINT `fk_cu_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cu_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cu_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Payment Transactions Table
CREATE TABLE IF NOT EXISTS `payment_transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `order_number` VARCHAR(30) NOT NULL,
    `gateway` VARCHAR(30) NOT NULL DEFAULT 'razorpay',
    `gateway_order_id` VARCHAR(60) NOT NULL,
    `gateway_payment_id` VARCHAR(60) NULL,
    `gateway_signature` VARCHAR(255) NULL,
    `payment_attempt_ref` VARCHAR(64) NOT NULL UNIQUE,
    `amount_paise` BIGINT UNSIGNED NOT NULL,
    `amount_rupees` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
    `status` ENUM('initiated', 'authorized', 'captured', 'failed', 'refunded', 'partially_refunded') NOT NULL DEFAULT 'initiated',
    `signature_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `environment` ENUM('test', 'live') NOT NULL DEFAULT 'test',
    `payment_method_details` VARCHAR(100) NULL,
    `error_code` VARCHAR(60) NULL,
    `error_description` TEXT NULL,
    `raw_response_json` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pt_order` (`order_id`),
    INDEX `idx_pt_order_num` (`order_number`),
    INDEX `idx_pt_gateway_order` (`gateway_order_id`),
    INDEX `idx_pt_gateway_payment` (`gateway_payment_id`),
    INDEX `idx_pt_status` (`status`),
    INDEX `idx_pt_created_at` (`created_at`),
    CONSTRAINT `fk_pt_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Payment Webhook Events Table
CREATE TABLE IF NOT EXISTS `payment_webhook_events` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `event_id` VARCHAR(100) NOT NULL UNIQUE,
    `gateway` VARCHAR(30) NOT NULL DEFAULT 'razorpay',
    `event_type` VARCHAR(80) NOT NULL,
    `entity_id` VARCHAR(80) NULL,
    `status` ENUM('received', 'processed', 'ignored', 'failed') NOT NULL DEFAULT 'received',
    `payload_json` MEDIUMTEXT NOT NULL,
    `error_message` TEXT NULL,
    `received_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `processed_at` DATETIME NULL,
    INDEX `idx_pwe_event` (`event_id`),
    INDEX `idx_pwe_type` (`event_type`),
    INDEX `idx_pwe_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


