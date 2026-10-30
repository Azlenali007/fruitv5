-- ==============================================================================
-- Migration: 001_customer_authentication.sql
-- Project: N.A Fresh Fruits & Coconuts
-- Purpose: Adds password-based authentication, optional email, and account status
--          to the existing customers table without resetting or deleting any data.
-- ==============================================================================

-- 1. Add authentication & profile columns safely
ALTER TABLE `customers`
    ADD COLUMN IF NOT EXISTS `email` VARCHAR(100) NULL AFTER `phone`,
    ADD COLUMN IF NOT EXISTS `password_hash` VARCHAR(255) NULL AFTER `email`,
    ADD COLUMN IF NOT EXISTS `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active' AFTER `pincode`,
    ADD COLUMN IF NOT EXISTS `last_login_at` DATETIME NULL AFTER `status`;

-- 2. Add Customer Login Throttling Table
CREATE TABLE IF NOT EXISTS `customer_login_attempts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `identifier` VARCHAR(100) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `success` TINYINT(1) NOT NULL DEFAULT 0,
    INDEX `idx_cust_login_throttle` (`identifier`, `ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
