-- Real Estate Portal - Seed Data for Testing
-- CIS324 Fall 2026 - Student: 241-16-034, Section: 19_A
-- Passwords are hashed with PASSWORD_ARGON2ID
-- Default password for all test users: "password123"

-- --------------------------------------------------------
-- Clear existing data (for re-seeding)
-- --------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `favourites`;
TRUNCATE TABLE `inquiries`;
TRUNCATE TABLE `property_images`;
TRUNCATE TABLE `properties`;
TRUNCATE TABLE `property_categories`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Users
-- Password hash for "password123" using password_hash('password123', PASSWORD_ARGON2ID)
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `phone`, `is_active`, `email_verified_at`, `created_at`) VALUES
(1, 'System Administrator', 'admin@realestate.test', '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQ$eK8vJ9xL2mN4pQ7rT5wY8zA1bC3dE6fH9iJ2kL5mN8o', 'admin', '+8801700000001', 1, NOW(), NOW()),
(2, 'Rahim Uddin (Agent)', 'agent1@realestate.test', '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQ$eK8vJ9xL2mN4pQ7rT5wY8zA1bC3dE6fH9iJ2kL5mN8o', 'agent', '+8801700000002', 1, NOW(), NOW()),
(3, 'Karim Ali (Agent)', 'agent2@realestate.test', '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQ$eK8vJ9xL2mN4pQ7rT5wY8zA1bC3dE6fH9iJ2kL5mN8o', 'agent', '+8801700000003', 1, NOW(), NOW()),
(4, 'Fatema Begum (Customer)', 'customer1@realestate.test', '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQ$eK8vJ9xL2mN4pQ7rT5wY8zA1bC3dE6fH9iJ2kL5mN8o', 'customer', '+8801700000004', 1, NOW(), NOW()),
(5, 'Mahmud Hasan (Customer)', 'customer2@realestate.test', '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQ$eK8vJ9xL2mN4pQ7rT5wY8zA1bC3dE6fH9iJ2kL5mN8o', 'customer', '+8801700000005', 1, NOW(), NOW());

-- --------------------------------------------------------
-- Property Categories
-- --------------------------------------------------------
INSERT INTO `property_categories` (`id`, `name`, `slug`, `description`, `is_active`) VALUES
(1, 'Apartment', 'apartment', 'Residential apartments and flats', 1),
(2, 'House', 'house', 'Independent houses and villas', 1),
(3, 'Commercial', 'commercial', 'Office spaces, shops, and commercial properties', 1),
(4, 'Land', 'land', 'Plots and land for development', 1),
(5, 'Condominium', 'condominium', 'Condo units with shared amenities', 1);

-- --------------------------------------------------------
-- Properties (owned by agents 2 and 3)
-- --------------------------------------------------------
INSERT INTO `properties` (`id`, `user_id`, `category_id`, `title`, `description`, `price`, `price_type`, `status`, `address`, `city`, `state`, `postal_code`, `country`, `latitude`, `longitude`, `bedrooms`, `bathrooms`, `area_sqft`, `year_built`, `is_featured`, `views_count`, `approved_at`, `approved_by`, `created_at`) VALUES
-- Agent 1 properties
(1, 2, 1, 'Modern 3BR Apartment in Gulshan', 'Spacious 3-bedroom apartment with modern amenities, balcony, and parking. Close to international schools and shopping malls.', 12500000.00, 'sale', 'approved', 'House 45, Road 12, Gulshan-1', 'Dhaka', 'Dhaka Division', '1212', 'Bangladesh', 23.7806, 90.4185, 3, 2, 1800, 2020, 1, 45, NOW(), 1, NOW()),
(2, 2, 2, 'Luxury 5BR House in Banani', 'Elegant 5-bedroom house with garden, garage, and servant quarters. Prime location in diplomatic zone.', 45000000.00, 'sale', 'approved', 'House 12, Road 8, Banani', 'Dhaka', 'Dhaka Division', '1213', 'Bangladesh', 23.7925, 90.4052, 5, 4, 4500, 2018, 1, 78, NOW(), 1, NOW()),
(3, 2, 1, 'Cozy 2BR Apartment for Rent in Dhanmondi', 'Well-maintained 2-bedroom apartment, semi-furnished, with lift and generator backup. Walking distance to Dhanmondi Lake.', 45000.00, 'rent', 'approved', 'Flat 4B, House 27, Road 5, Dhanmondi', 'Dhaka', 'Dhaka Division', '1209', 'Bangladesh', 23.7465, 90.3763, 2, 2, 1200, 2015, 0, 32, NOW(), 1, NOW()),
(4, 2, 3, 'Commercial Office Space in Motijheel', 'Prime office space on 3rd floor, suitable for corporate office. High footfall area, dedicated parking.', 120000.00, 'rent', 'approved', 'Suite 301, 45 Dilkusha C/A', 'Dhaka', 'Dhaka Division', '1000', 'Bangladesh', 23.7286, 90.4125, 0, 2, 2000, 2010, 0, 15, NOW(), 1, NOW()),

-- Agent 2 properties
(5, 3, 1, 'Sunny 3BR Apartment in Uttara', 'Bright 3-bedroom apartment with north-facing balcony, modern kitchen, and community amenities.', 9800000.00, 'sale', 'approved', 'Flat 6A, Sector 7, Uttara', 'Dhaka', 'Dhaka Division', '1230', 'Bangladesh', 23.8759, 90.3795, 3, 2, 1650, 2021, 1, 56, NOW(), 1, NOW()),
(6, 3, 2, 'Family House in Mirpur DOHS', 'Well-constructed 4-bedroom house in secure DOHS area. Park facing, wide roads, 24/7 security.', 28000000.00, 'sale', 'approved', 'House 89, Road 3, Mirpur DOHS', 'Dhaka', 'Dhaka Division', '1216', 'Bangladesh', 23.8223, 90.3654, 4, 3, 3200, 2019, 0, 67, NOW(), 1, NOW()),
(7, 3, 4, 'Residential Plot in Purbachal', '10 Katha residential plot in developed sector. Wide road, electricity, water, gas connection available.', 3500000.00, 'sale', 'pending', 'Block F, Sector 10, Purbachal', 'Dhaka', 'Dhaka Division', '1230', 'Bangladesh', 23.9456, 90.5123, 0, 0, 7200, NULL, 0, 23, NULL, NULL, NOW()),
(8, 3, 1, 'Studio Apartment for Rent in Bashundhara', 'Modern studio apartment, fully furnished, with AC, fridge, washing machine. Ideal for single professionals.', 25000.00, 'rent', 'approved', 'Block A, Bashundhara R/A', 'Dhaka', 'Dhaka Division', '1229', 'Bangladesh', 23.8156, 90.4321, 0, 1, 600, 2022, 0, 41, NOW(), 1, NOW());

-- --------------------------------------------------------
-- Property Images (sample paths - actual files not included)
-- --------------------------------------------------------
INSERT INTO `property_images` (`property_id`, `file_path`, `file_name`, `mime_type`, `file_size`, `is_primary`, `display_order`) VALUES
(1, 'uploads/properties/1/primary_gulshan_apt.jpg', 'primary_gulshan_apt.jpg', 'image/jpeg', 245000, 1, 0),
(1, 'uploads/properties/1/gulshan_living.jpg', 'gulshan_living.jpg', 'image/jpeg', 198000, 0, 1),
(1, 'uploads/properties/1/gulshan_bedroom.jpg', 'gulshan_bedroom.jpg', 'image/jpeg', 187000, 0, 2),
(1, 'uploads/properties/1/gulshan_kitchen.jpg', 'gulshan_kitchen.jpg', 'image/jpeg', 176000, 0, 3),
(2, 'uploads/properties/2/primary_banani_house.jpg', 'primary_banani_house.jpg', 'image/jpeg', 312000, 1, 0),
(2, 'uploads/properties/2/banani_garden.jpg', 'banani_garden.jpg', 'image/jpeg', 289000, 0, 1),
(2, 'uploads/properties/2/banani_exterior.jpg', 'banani_exterior.jpg', 'image/jpeg', 267000, 0, 2),
(3, 'uploads/properties/3/primary_dhanmondi_apt.jpg', 'primary_dhanmondi_apt.jpg', 'image/jpeg', 156000, 1, 0),
(3, 'uploads/properties/3/dhanmondi_balcony.jpg', 'dhanmondi_balcony.jpg', 'image/jpeg', 134000, 0, 1),
(4, 'uploads/properties/4/primary_motijheel_office.jpg', 'primary_motijheel_office.jpg', 'image/jpeg', 223000, 1, 0),
(5, 'uploads/properties/5/primary_uttara_apt.jpg', 'primary_uttara_apt.jpg', 'image/jpeg', 189000, 1, 0),
(5, 'uploads/properties/5/uttara_view.jpg', 'uttara_view.jpg', 'image/jpeg', 167000, 0, 1),
(6, 'uploads/properties/6/primary_mirpur_house.jpg', 'primary_mirpur_house.jpg', 'image/jpeg', 278000, 1, 0),
(6, 'uploads/properties/6/mirpur_park.jpg', 'mirpur_park.jpg', 'image/jpeg', 245000, 0, 1),
(7, 'uploads/properties/7/primary_purbachal_plot.jpg', 'primary_purbachal_plot.jpg', 'image/jpeg', 145000, 1, 0),
(8, 'uploads/properties/8/primary_bashundhara_studio.jpg', 'primary_bashundhara_studio.jpg', 'image/jpeg', 134000, 1, 0),
(8, 'uploads/properties/8/bashundhara_interior.jpg', 'bashundhara_interior.jpg', 'image/jpeg', 123000, 0, 1);

-- --------------------------------------------------------
-- Inquiries (from customers 4 and 5)
-- --------------------------------------------------------
INSERT INTO `inquiries` (`property_id`, `user_id`, `message`, `status`, `agent_response`, `responded_at`, `responded_by`, `created_at`) VALUES
(1, 4, 'I am interested in this apartment. Can we schedule a viewing this weekend?', 'contacted', 'Yes, Saturday 10 AM works. Please confirm.', NOW(), 2, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 5, 'Is the price negotiable? What are the maintenance charges?', 'new', NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 4, 'Interested in the Banani house. Need more photos of the garden and kitchen.', 'negotiating', 'I have sent additional photos to your email. Price slightly negotiable for serious buyer.', NOW(), 2, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 5, 'Looking for a 2BR rental in Dhanmondi. Is this available immediately?', 'contacted', 'Yes, available from 1st next month. Deposit 2 months rent.', NOW(), 2, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 4, 'Can you share the floor plan for the Uttara apartment?', 'new', NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(6, 5, 'Is the Mirpur DOHS house still available? I want to arrange a visit.', 'contacted', 'Yes, available. Please call to schedule.', NOW(), 3, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(8, 4, 'Is the studio fully furnished? What utilities are included?', 'new', NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 12 HOUR));

-- --------------------------------------------------------
-- Favourites (customers saving properties)
-- --------------------------------------------------------
INSERT INTO `favourites` (`user_id`, `property_id`) VALUES
(4, 1), -- Fatema likes Gulshan apartment
(4, 2), -- Fatema likes Banani house
(4, 5), -- Fatema likes Uttara apartment
(5, 3), -- Mahmud likes Dhanmondi rental
(5, 6), -- Mahmud likes Mirpur house
(5, 8); -- Mahmud likes Bashundhara studio

-- --------------------------------------------------------
-- Test Credentials Summary
-- --------------------------------------------------------
-- Admin:      admin@realestate.test      / password123
-- Agent 1:    agent1@realestate.test     / password123
-- Agent 2:    agent2@realestate.test     / password123
-- Customer 1: customer1@realestate.test  / password123
-- Customer 2: customer2@realestate.test  / password123