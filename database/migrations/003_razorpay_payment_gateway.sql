-- ==============================================================================
-- Migration: 003_razorpay_payment_gateway.sql
-- Project: N.A Fresh Fruits & Coconuts
-- Purpose: Real Razorpay Payment Gateway integration tables, order columns,
--          payment webhook event logging, and payment method settings.
-- ==============================================================================

-- 1. Ensure columns on orders table for Razorpay tracking
ALTER TABLE `orders`
    ADD COLUMN IF NOT EXISTS `razorpay_order_id` VARCHAR(60) NULL AFTER `payment_status`,
    ADD COLUMN IF NOT EXISTS `razorpay_payment_id` VARCHAR(60) NULL AFTER `razorpay_order_id`,
    ADD COLUMN IF NOT EXISTS `razorpay_signature` VARCHAR(255) NULL AFTER `razorpay_payment_id`,
    ADD INDEX IF NOT EXISTS `idx_rzp_order` (`razorpay_order_id`),
    ADD INDEX IF NOT EXISTS `idx_rzp_payment` (`razorpay_payment_id`);

-- 2. Modify payment_status enum on orders if needed to support all standard statuses
ALTER TABLE `orders`
    MODIFY COLUMN `payment_status` ENUM('pending', 'authorized', 'captured', 'paid', 'refunded', 'partially_refunded', 'failed') NOT NULL DEFAULT 'pending';

-- 3. Create payment_transactions Table
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

-- 4. Create payment_webhook_events Table (for deduplication and event idempotency)
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

-- 5. Seed Payment Settings into settings table if not already present
INSERT INTO `settings` (`key_name`, `value`, `description`)
SELECT 'cod_enabled', '1', 'Enable Cash on Delivery at checkout (1 = ON, 0 = OFF)'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key_name` = 'cod_enabled');

INSERT INTO `settings` (`key_name`, `value`, `description`)
SELECT 'razorpay_enabled', '0', 'Enable Razorpay Online Payments (1 = ON, 0 = OFF)'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key_name` = 'razorpay_enabled');

INSERT INTO `settings` (`key_name`, `value`, `description`)
SELECT 'razorpay_environment', 'test', 'Razorpay environment mode: test or live'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key_name` = 'razorpay_environment');

INSERT INTO `settings` (`key_name`, `value`, `description`)
SELECT 'razorpay_key_id', '', 'Razorpay Public Key ID (rzp_test_... or rzp_live_...)'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key_name` = 'razorpay_key_id');

INSERT INTO `settings` (`key_name`, `value`, `description`)
SELECT 'razorpay_key_secret', '', 'Razorpay Private Key Secret (Stored encrypted server-side)'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key_name` = 'razorpay_key_secret');

INSERT INTO `settings` (`key_name`, `value`, `description`)
SELECT 'razorpay_webhook_secret', '', 'Razorpay Webhook Secret for signature validation'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key_name` = 'razorpay_webhook_secret');
