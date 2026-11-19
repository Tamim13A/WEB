CREATE DATABASE IF NOT EXISTS `realestate_portal` DEFAULT CHARACTER SET utf8mb4;
USE `realestate_portal`;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `inquiries`, `properties`, `users`;
SET FOREIGN_KEY_CHECKS = 1;
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'agent', 'customer') DEFAULT 'customer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE `properties` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `price` DECIMAL(15,2) NOT NULL,
    `price_type` ENUM('sale', 'rent') DEFAULT 'sale',
    `city` VARCHAR(100) NOT NULL,
    `image` VARCHAR(255) DEFAULT '/assets/images/placeholder.svg',
    `description` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE `inquiries` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `property_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `message` TEXT NOT NULL,
    `reply` TEXT DEFAULT NULL,
    `status` ENUM('pending', 'replied') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
INSERT INTO `users` VALUES 
(1, 'Tamim', 'admin@realestate.test', '$2y$10$8.ICQHCyCPzS.xygPO4cfuHsHZb6Kuxynn8/uUHOU1.7gY.UhSIXa', 'admin', NOW()),
(2, 'Tonmoy', 'agent1@realestate.test', '$2y$10$8.ICQHCyCPzS.xygPO4cfuHsHZb6Kuxynn8/uUHOU1.7gY.UhSIXa', 'agent', NOW()),
(3, 'Tonu', 'customer1@realestate.test', '$2y$10$8.ICQHCyCPzS.xygPO4cfuHsHZb6Kuxynn8/uUHOU1.7gY.UhSIXa', 'customer', NOW()),
(4, 'Tusher', 'tusher@realestate.test', '$2y$10$8.ICQHCyCPzS.xygPO4cfuHsHZb6Kuxynn8/uUHOU1.7gY.UhSIXa', 'agent', NOW()),
(5, 'Sosso', 'sosso@realestate.test', '$2y$10$8.ICQHCyCPzS.xygPO4cfuHsHZb6Kuxynn8/uUHOU1.7gY.UhSIXa', 'customer', NOW()),
(6, 'Marjuk', 'marjuk@realestate.test', '$2y$10$8.ICQHCyCPzS.xygPO4cfuHsHZb6Kuxynn8/uUHOU1.7gY.UhSIXa', 'customer', NOW());
INSERT INTO `properties` VALUES 
(1, 2, 'Luxury 3BR Apartment in Gulshan', 15000000, 'sale', 'Dhaka', '/assets/images/uploads/prop1.jpg', 'Spacious 3BR apartment with parking and elevator.', NOW()),
(2, 2, 'Modern Duplex House in Banani', 32000000, 'sale', 'Dhaka', '/assets/images/uploads/prop2.jpg', 'Modern 4BR duplex with private garden and rooftop.', NOW()),
(3, 2, 'Cozy 2BR Flat for Rent in Dhanmondi', 45000, 'rent', 'Dhaka', '/assets/images/uploads/prop3.jpg', 'Furnished apartment near lake and parks.', NOW()),
(4, 2, 'Commercial Office Floor in Motijheel', 180000, 'rent', 'Dhaka', '/assets/images/uploads/prop4.jpg', 'Open corporate floor with central AC and backup power.', NOW()),
(5, 2, 'Lakeview Penthouse in Uttara', 24500000, 'sale', 'Dhaka', '/assets/images/uploads/prop5.jpg', 'Top-floor penthouse with panoramic terrace and lake view.', NOW()),
(6, 2, 'Furnished Studio in Bashundhara R/A', 28000, 'rent', 'Dhaka', '/assets/images/uploads/prop6.jpg', 'Minimalist studio apartment near universities and malls.', NOW()),
(7, 2, 'Family Villa with Lawn in Baridhara', 55000000, 'sale', 'Dhaka', '/assets/images/uploads/prop7.jpg', 'Independent upscale villa with green lawn and security.', NOW()),
(8, 4, 'Sea-Facing 3BR Condo in Agrabad', 12800000, 'sale', 'Chattogram', '/assets/images/uploads/prop8.jpg', 'Bright coastal condominium with balcony views.', NOW()),
(9, 4, 'Modern 2BR Flat in Nasirabad', 32000, 'rent', 'Chattogram', '/assets/images/uploads/prop9.jpg', 'Quiet family apartment close to schools and markets.', NOW()),
(10, 4, 'Retail Showroom Space in Mirpur', 95000, 'rent', 'Dhaka', '/assets/images/uploads/prop10.jpg', 'Ground-floor glass storefront on main road.', NOW()),
(11, 4, 'Eco Garden House in Shahjalal Upashahar', 18500000, 'sale', 'Sylhet', '/assets/images/uploads/prop11.jpg', 'Peaceful garden home surrounded by lush greenery.', NOW()),
(12, 4, 'Tidy 2BR Apartment in Mohammadpur', 22000, 'rent', 'Dhaka', '/assets/images/uploads/prop12.jpg', 'Well-ventilated 2BR flat suitable for families or students.', NOW()),
(13, 4, 'Newly Built 4BR Triplex in Padma R/A', 21000000, 'sale', 'Rajshahi', '/assets/images/uploads/prop13.jpg', 'Brand new 3-story triplex with modern architectural finish.', NOW());
INSERT INTO `inquiries` VALUES
(1, 1, 3, 'Is the Gulshan apartment price negotiable?', 'Yes, please call me to schedule a visit.', 'replied', NOW()),
(2, 3, 5, 'When is this flat available for moving in?', NULL, 'pending', NOW());
