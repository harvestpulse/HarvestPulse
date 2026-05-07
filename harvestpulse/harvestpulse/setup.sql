-- HarvestPulse Database Setup
-- Run this in phpMyAdmin or MySQL CLI before starting the app

CREATE DATABASE IF NOT EXISTS harvestpulse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE harvestpulse;

-- ─── USERS ───────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('farmer','consumer') NOT NULL DEFAULT 'consumer',
    location VARCHAR(100),
    avatar_initials VARCHAR(3),
    avatar_color VARCHAR(7) DEFAULT '#1a7a4a',
    avatar_text_color VARCHAR(7) DEFAULT '#ffffff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─── LISTINGS ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    emoji VARCHAR(5) NOT NULL DEFAULT '🌿',
    title VARCHAR(150) NOT NULL,
    description TEXT,
    weight_kg DECIMAL(6,1),
    reserve_price DECIMAL(8,2) NOT NULL,
    current_bid DECIMAL(8,2) NOT NULL,
    retail_value DECIMAL(8,2),
    status ENUM('live','sold','expired') DEFAULT 'live',
    ends_at DATETIME NOT NULL,
    pickup_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(id)
);

-- ─── BIDS ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    bidder_id INT NOT NULL,
    amount DECIMAL(8,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id),
    FOREIGN KEY (bidder_id) REFERENCES users(id)
);

-- ─── SEED DATA ────────────────────────────────────────────────────────────────

-- Farmers
INSERT INTO users (name, email, phone, password_hash, role, location, avatar_initials, avatar_color, avatar_text_color) VALUES
('Mama Nkosi', 'nkosi@farm.co.za', '0821234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'Bronkhorstspruit', 'MN', '#0f4f2e', '#ffffff'),
('Baba Dlamini', 'dlamini@farm.co.za', '0831234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'Heidelberg', 'BD', '#78350f', '#ffffff'),
('Oupa Sithole', 'sithole@farm.co.za', '0841234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'Bapsfontein', 'OS', '#1e3a5f', '#ffffff');

-- Consumers
INSERT INTO users (name, email, phone, password_hash, role, location, avatar_initials, avatar_color, avatar_text_color) VALUES
('Thandi Mokoena', 'thandi@mail.co.za', '0761234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Tembisa', 'TM', '#e8f5ee', '#085041'),
('Sipho Mabaso', 'sipho@mail.co.za', '0771234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Soweto', 'SM', '#fef3c7', '#633806'),
('Priya Naidoo', 'priya@mail.co.za', '0781234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Midrand', 'PN', '#eef2ff', '#3730a3'),
('Lungelo Khumalo', 'lungelo@mail.co.za', '0791234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Mamelodi', 'LK', '#fce7f3', '#9d174d'),
('Zanele Dube', 'zanele@mail.co.za', '0751234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Alex', 'ZD', '#fee2e2', '#991b1b');

-- Live Listings (ends_at set dynamically via procedure below)
INSERT INTO listings (farmer_id, emoji, title, description, weight_kg, reserve_price, current_bid, retail_value, status, ends_at, pickup_notes) VALUES
(1, '🍅', 'Mixed Tomatoes', 'Fresh mixed tomatoes, picked this morning. Juicy and ripe.', 12.0, 45.00, 68.00, 140.00, 'live', DATE_ADD(NOW(), INTERVAL 8 MINUTE), 'Farm gate pickup or Bronkhorstspruit Mall Pargo point'),
(2, '🌽', 'Sweet Corn', 'Hand-picked sweet corn, harvested at 5am. Naturally sweet.', 20.0, 40.00, 55.00, 100.00, 'live', DATE_ADD(NOW(), INTERVAL 22 MINUTE), 'Heidelberg CBD collection point'),
(3, '🥬', 'Fresh Spinach', 'Certified organic spinach bundles. No pesticides.', 8.0, 30.00, 42.00, 88.00, 'live', DATE_ADD(NOW(), INTERVAL 4 MINUTE), 'Bapsfontein farm gate — GPS: -26.03, 28.40'),
(1, '🎃', 'Butternut Squash', 'Large butternuts, perfect for winter soups. Thick flesh.', 15.0, 60.00, 95.00, 180.00, 'live', DATE_ADD(NOW(), INTERVAL 45 MINUTE), 'Farm gate pickup, call ahead'),
(2, '🥕', 'Baby Carrots', 'Triple-washed baby carrots. Ready to eat. Great for schools.', 10.0, 50.00, 78.00, 130.00, 'live', DATE_ADD(NOW(), INTERVAL 12 MINUTE), 'Heidelberg Shoprite parking lot drop'),
(3, '🥦', 'Broccoli Heads', 'Frost-grown broccoli. Dense, nutrient-rich heads.', 6.0, 25.00, 36.00, 72.00, 'live', DATE_ADD(NOW(), INTERVAL 60 MINUTE), 'Bapsfontein farm gate');

-- Seed Bids for live listings
INSERT INTO bids (listing_id, bidder_id, amount, created_at) VALUES
(1, 4, 45.00, DATE_SUB(NOW(), INTERVAL 12 MINUTE)),
(1, 5, 52.00, DATE_SUB(NOW(), INTERVAL 8 MINUTE)),
(1, 6, 60.00, DATE_SUB(NOW(), INTERVAL 5 MINUTE)),
(1, 4, 68.00, DATE_SUB(NOW(), INTERVAL 2 MINUTE)),
(2, 7, 40.00, DATE_SUB(NOW(), INTERVAL 20 MINUTE)),
(2, 5, 48.00, DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
(2, 6, 55.00, DATE_SUB(NOW(), INTERVAL 6 MINUTE)),
(3, 4, 30.00, DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(3, 7, 35.00, DATE_SUB(NOW(), INTERVAL 20 MINUTE)),
(3, 5, 38.00, DATE_SUB(NOW(), INTERVAL 10 MINUTE)),
(3, 6, 40.00, DATE_SUB(NOW(), INTERVAL 5 MINUTE)),
(3, 4, 42.00, DATE_SUB(NOW(), INTERVAL 1 MINUTE)),
(4, 5, 65.00, DATE_SUB(NOW(), INTERVAL 40 MINUTE)),
(4, 7, 95.00, DATE_SUB(NOW(), INTERVAL 10 MINUTE)),
(5, 6, 55.00, DATE_SUB(NOW(), INTERVAL 25 MINUTE)),
(5, 4, 65.00, DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
(5, 7, 70.00, DATE_SUB(NOW(), INTERVAL 8 MINUTE)),
(5, 5, 78.00, DATE_SUB(NOW(), INTERVAL 3 MINUTE)),
(6, 6, 36.00, DATE_SUB(NOW(), INTERVAL 50 MINUTE));

-- Historical sold listings
INSERT INTO listings (farmer_id, emoji, title, description, weight_kg, reserve_price, current_bid, retail_value, status, ends_at) VALUES
(1, '🍅', 'Cherry Tomatoes', 'Sweet cherry tomatoes, 5kg punnet.', 5.0, 35.00, 124.00, 200.00, 'sold', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, '🥬', 'Mixed Greens', 'Assorted leafy greens bundle.', 6.0, 28.00, 78.00, 120.00, 'sold', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, '🌽', 'Bulk Corn (30 cobs)', 'Bulk order sweet corn for spaza shops.', 30.0, 100.00, 185.00, 300.00, 'sold', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, '🥕', 'Carrots 5kg', 'Washed and graded carrots.', 5.0, 30.00, 65.00, 100.00, 'sold', DATE_SUB(NOW(), INTERVAL 4 DAY));
