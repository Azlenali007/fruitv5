-- Migration: 004_delivery_areas.sql
-- N.A Fresh Fruits & Coconuts
-- Implements delivery areas with polygon boundaries, per-area delivery charges,
-- and records coordinates snapshot on orders.

CREATE TABLE IF NOT EXISTS `delivery_areas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) UNIQUE,
    `delivery_charge` DECIMAL(10,2) NOT NULL DEFAULT 40.00,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
    `boundary_geojson` LONGTEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_delivery_areas_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add location coordinates and delivery area snapshots to orders
ALTER TABLE `orders`
    ADD COLUMN IF NOT EXISTS `delivery_area_id` INT NULL AFTER `delivery_address`,
    ADD COLUMN IF NOT EXISTS `delivery_area_name` VARCHAR(150) NULL AFTER `delivery_area_id`,
    ADD COLUMN IF NOT EXISTS `latitude` DECIMAL(10,8) NULL AFTER `delivery_area_name`,
    ADD COLUMN IF NOT EXISTS `longitude` DECIMAL(11,8) NULL AFTER `latitude`;

-- Prepopulate the 35 Jaora (MP) localities as preliminary, inactive areas with NO fake coordinates
INSERT INTO `delivery_areas` (`name`, `slug`, `delivery_charge`, `status`, `boundary_geojson`) VALUES
('Topkhana', 'topkhana', 40.00, 'inactive', NULL),
('Maharpura', 'maharpura', 40.00, 'inactive', NULL),
('Bag Nana Sahab', 'bag-nana-sahab', 40.00, 'inactive', NULL),
('Station Road', 'station-road', 40.00, 'inactive', NULL),
('Shastri Colony', 'shastri-colony', 40.00, 'inactive', NULL),
('Industrial Area', 'industrial-area', 40.00, 'inactive', NULL),
('Chopati', 'chopati', 40.00, 'inactive', NULL),
('Fort Area', 'fort-area', 40.00, 'inactive', NULL),
('Civil Lines', 'civil-lines', 40.00, 'inactive', NULL),
('Brahmpuri', 'brahmpuri', 40.00, 'inactive', NULL),
('Ratanpipali Road', 'ratanpipali-road', 40.00, 'inactive', NULL),
('Badavada Naka', 'badavada-naka', 40.00, 'inactive', NULL),
('Piploda Road', 'piploda-road', 40.00, 'inactive', NULL),
('Sitamau Road', 'sitamau-road', 40.00, 'inactive', NULL),
('Dhodhar Naka', 'dhodhar-naka', 40.00, 'inactive', NULL),
('Kumharwada', 'kumharwada', 40.00, 'inactive', NULL),
('Khatriwada', 'khatriwada', 40.00, 'inactive', NULL),
('Sarafa Bazar', 'sarafa-bazar', 40.00, 'inactive', NULL),
('Gandhi Road', 'gandhi-road', 40.00, 'inactive', NULL),
('Subhash Marg', 'subhash-marg', 40.00, 'inactive', NULL),
('Jawahar Path', 'jawahar-path', 40.00, 'inactive', NULL),
('Nai Abadi', 'nai-abadi', 40.00, 'inactive', NULL),
('Railway Colony', 'railway-colony', 40.00, 'inactive', NULL),
('Patidar Colony', 'patidar-colony', 40.00, 'inactive', NULL),
('Shanti Nagar', 'shanti-nagar', 40.00, 'inactive', NULL),
('Teachers Colony', 'teachers-colony', 40.00, 'inactive', NULL),
('Anand Colony', 'anand-colony', 40.00, 'inactive', NULL),
('Geeta Colony', 'geeta-colony', 40.00, 'inactive', NULL),
('Vikas Nagar', 'vikas-nagar', 40.00, 'inactive', NULL),
('Arihant Nagar', 'arihant-nagar', 40.00, 'inactive', NULL),
('Vardhaman Nagar', 'vardhaman-nagar', 40.00, 'inactive', NULL),
('Krishi Upaj Mandi', 'krishi-upaj-mandi', 40.00, 'inactive', NULL),
('Bus Stand Area', 'bus-stand-area', 40.00, 'inactive', NULL),
('Court Area', 'court-area', 40.00, 'inactive', NULL),
('Sanjay Complex', 'sanjay-complex', 40.00, 'inactive', NULL)
ON DUPLICATE KEY UPDATE `updated_at` = NOW();
