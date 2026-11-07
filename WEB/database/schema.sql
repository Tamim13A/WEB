-- Real Estate Listing & Inquiry Portal Database Schema
-- CIS324 Fall 2026 - Student: 241-16-034, Section: 19_A
-- Normalized to 3NF with referential integrity

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table: users
-- Stores all users: Admin, Agent, Customer
-- --------------------------------------------------------
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin','agent','customer') NOT NULL DEFAULT 'customer',
    `phone` VARCHAR(20) DEFAULT NULL,
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `last_login_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: property_categories
-- Property types: Apartment, House, Commercial, Land, etc.
-- --------------------------------------------------------
CREATE TABLE `property_categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(60) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_slug` (`slug`),
    KEY `idx_categories_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: properties
-- Core property listings owned by agents
-- --------------------------------------------------------
CREATE TABLE `properties` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `price` DECIMAL(15,2) NOT NULL,
    `price_type` ENUM('sale','rent') NOT NULL,
    `status` ENUM('pending','approved','rejected','sold','rented') NOT NULL DEFAULT 'pending',
    `address` VARCHAR(300) NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `state` VARCHAR(100) DEFAULT NULL,
    `postal_code` VARCHAR(20) DEFAULT NULL,
    `country` VARCHAR(100) NOT NULL DEFAULT 'Bangladesh',
    `latitude` DECIMAL(10,8) DEFAULT NULL,
    `longitude` DECIMAL(11,8) DEFAULT NULL,
    `bedrooms` TINYINT UNSIGNED DEFAULT 0,
    `bathrooms` TINYINT UNSIGNED DEFAULT 0,
    `area_sqft` INT UNSIGNED DEFAULT NULL,
    `year_built` SMALLINT UNSIGNED DEFAULT NULL,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `approved_at` TIMESTAMP NULL DEFAULT NULL,
    `approved_by` INT UNSIGNED DEFAULT NULL,
    `rejected_reason` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_properties_user` (`user_id`),
    KEY `idx_properties_category` (`category_id`),
    KEY `idx_properties_status` (`status`),
    KEY `idx_properties_price_type` (`price_type`),
    KEY `idx_properties_city` (`city`),
    KEY `idx_properties_price` (`price`),
    KEY `idx_properties_featured` (`is_featured`),
    KEY `idx_properties_created` (`created_at`),
    CONSTRAINT `fk_properties_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_properties_category` FOREIGN KEY (`category_id`) REFERENCES `property_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_properties_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: property_images
-- Multiple images per property
-- --------------------------------------------------------
CREATE TABLE `property_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id` INT UNSIGNED NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(50) NOT NULL,
    `file_size` INT UNSIGNED NOT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_images_property` (`property_id`),
    KEY `idx_images_primary` (`property_id`, `is_primary`),
    KEY `idx_images_order` (`property_id`, `display_order`),
    CONSTRAINT `fk_images_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: inquiries
-- Customer inquiries on properties
-- --------------------------------------------------------
CREATE TABLE `inquiries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('new','contacted','negotiating','closed','rejected') NOT NULL DEFAULT 'new',
    `agent_response` TEXT DEFAULT NULL,
    `responded_at` TIMESTAMP NULL DEFAULT NULL,
    `responded_by` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_inquiries_property` (`property_id`),
    KEY `idx_inquiries_user` (`user_id`),
    KEY `idx_inquiries_status` (`status`),
    KEY `idx_inquiries_created` (`created_at`),
    CONSTRAINT `fk_inquiries_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_inquiries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_inquiries_responded_by` FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: favourites
-- Customer saved favourite properties (Many-to-Many)
-- --------------------------------------------------------
CREATE TABLE `favourites` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `property_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_favourite_user_property` (`user_id`, `property_id`),
    KEY `idx_favourites_user` (`user_id`),
    KEY `idx_favourites_property` (`property_id`),
    CONSTRAINT `fk_favourites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_favourites_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- View: v_property_listing
-- Optimized listing with agent name, primary image, category
-- --------------------------------------------------------
CREATE OR REPLACE VIEW `v_property_listing` AS
SELECT
    p.`id`,
    p.`user_id`,
    u.`name` AS `agent_name`,
    u.`email` AS `agent_email`,
    u.`phone` AS `agent_phone`,
    p.`category_id`,
    c.`name` AS `category_name`,
    c.`slug` AS `category_slug`,
    p.`title`,
    p.`description`,
    p.`price`,
    p.`price_type`,
    p.`status`,
    p.`address`,
    p.`city`,
    p.`state`,
    p.`postal_code`,
    p.`country`,
    p.`latitude`,
    p.`longitude`,
    p.`bedrooms`,
    p.`bathrooms`,
    p.`area_sqft`,
    p.`year_built`,
    p.`is_featured`,
    p.`views_count`,
    p.`created_at`,
    p.`updated_at`,
    pi.`file_path` AS `primary_image`,
    pi.`file_name` AS `primary_image_name`
FROM `properties` p
INNER JOIN `users` u ON p.`user_id` = u.`id`
INNER JOIN `property_categories` c ON p.`category_id` = c.`id`
LEFT JOIN `property_images` pi ON pi.`property_id` = p.`id` AND pi.`is_primary` = 1
WHERE p.`status` = 'approved' AND u.`is_active` = 1;

-- --------------------------------------------------------
-- Stored Procedure: sp_increment_property_views
-- Safely increment view counter
-- --------------------------------------------------------
DELIMITER //
CREATE PROCEDURE `sp_increment_property_views` (IN p_property_id INT UNSIGNED)
BEGIN
    UPDATE `properties` SET `views_count` = `views_count` + 1 WHERE `id` = p_property_id;
END //
DELIMITER ;

-- --------------------------------------------------------
-- Trigger: Ensure only one primary image per property
-- --------------------------------------------------------
DELIMITER //
CREATE TRIGGER `trg_single_primary_image` BEFORE INSERT ON `property_images`
FOR EACH ROW
BEGIN
    IF NEW.`is_primary` = 1 THEN
        UPDATE `property_images` SET `is_primary` = 0 WHERE `property_id` = NEW.`property_id`;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER `trg_single_primary_image_update` BEFORE UPDATE ON `property_images`
FOR EACH ROW
BEGIN
    IF NEW.`is_primary` = 1 AND OLD.`is_primary` = 0 THEN
        UPDATE `property_images` SET `is_primary` = 0 WHERE `property_id` = NEW.`property_id` AND `id` != NEW.`id`;
    END IF;
END //
DELIMITER ;