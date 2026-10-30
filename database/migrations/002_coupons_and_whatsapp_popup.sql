-- ==============================================================================
-- Migration: 002_coupons_and_whatsapp_popup.sql
-- Project: N.A Fresh Fruits & Coconuts
-- Purpose: Adds Coupon Code System tables & order columns, and seeds WhatsApp Channel settings.
-- ==============================================================================

-- 1. Create Coupons Table
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

-- 2. Alter Orders Table to Snapshot Applied Coupon Data Safely
ALTER TABLE `orders`
    ADD COLUMN IF NOT EXISTS `coupon_id` INT UNSIGNED NULL AFTER `fulfilment_method`,
    ADD COLUMN IF NOT EXISTS `coupon_code` VARCHAR(50) NULL AFTER `coupon_id`,
    ADD COLUMN IF NOT EXISTS `coupon_type` VARCHAR(20) NULL AFTER `coupon_code`,
    ADD COLUMN IF NOT EXISTS `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `subtotal`,
    ADD COLUMN IF NOT EXISTS `original_delivery_charge` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `discount_amount`,
    ADD COLUMN IF NOT EXISTS `delivery_discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `original_delivery_charge`;

-- 3. Create Coupon Usage Audit Table
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

-- 4. Seed Initial WhatsApp Channel Popup Settings (if not already set)
INSERT INTO `settings` (`key_name`, `value`, `description`) 
SELECT 'whatsapp_popup_enabled', '1', 'Show WhatsApp channel popup on storefront'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key_name` = 'whatsapp_popup_enabled');

INSERT INTO `settings` (`key_name`, `value`, `description`) 
SELECT 'whatsapp_channel_url', 'https://whatsapp.com/channel/0029VaNAFreshFruits', 'Official WhatsApp channel invite link'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key_name` = 'whatsapp_channel_url');

INSERT INTO `settings` (`key_name`, `value`, `description`) 
SELECT 'whatsapp_popup_heading', 'Join Our WhatsApp Channel', 'Header for the WhatsApp channel modal'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key_name` = 'whatsapp_popup_heading');

INSERT INTO `settings` (`key_name`, `value`, `description`) 
SELECT 'whatsapp_popup_message', 'Get real-time fresh coconut harvest alerts, special seasonal discounts, and quick delivery updates straight on WhatsApp!', 'Message body for WhatsApp channel modal'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key_name` = 'whatsapp_popup_message');

INSERT INTO `settings` (`key_name`, `value`, `description`) 
SELECT 'whatsapp_popup_button_text', 'Join WhatsApp Channel', 'Button label for WhatsApp channel popup'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key_name` = 'whatsapp_popup_button_text');

INSERT INTO `settings` (`key_name`, `value`, `description`) 
SELECT 'whatsapp_popup_frequency', 'session', 'Display frequency: session, daily, weekly, or always'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key_name` = 'whatsapp_popup_frequency');

-- 5. Seed Initial Example Coupons
INSERT INTO `coupons` (`code`, `name`, `description`, `type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `max_uses`, `max_uses_per_customer`, `is_active`)
SELECT 'FREEDELIVERY', 'Free Doorstep Delivery', 'Enjoy free delivery on any order above ₹80', 'free_delivery', 0.00, 80.00, NULL, 500, 2, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `coupons` WHERE `code` = 'FREEDELIVERY');

INSERT INTO `coupons` (`code`, `name`, `description`, `type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `max_uses`, `max_uses_per_customer`, `is_active`)
SELECT 'SAVE10', '10% Healthy Hydration', 'Get 10% discount on fresh coconut water up to ₹50', 'percentage', 10.00, 100.00, 50.00, 1000, 3, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `coupons` WHERE `code` = 'SAVE10');

INSERT INTO `coupons` (`code`, `name`, `description`, `type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `max_uses`, `max_uses_per_customer`, `is_active`)
SELECT 'FLAT50', 'Flat ₹50 Super Saver', 'Get flat ₹50 off on orders of ₹200 or more', 'fixed', 50.00, 200.00, NULL, 200, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `coupons` WHERE `code` = 'FLAT50');
