-- Freelance Marketplace Database Schema
-- Created: 2026-01-24

CREATE DATABASE IF NOT EXISTS freelance_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE freelance_marketplace;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client', 'freelancer') NOT NULL DEFAULT 'client',
    status ENUM('pending', 'active', 'suspended_7days', 'suspended_permanent') NOT NULL DEFAULT 'pending',
    suspended_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Freelancer Profiles Table
CREATE TABLE IF NOT EXISTS freelancer_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bio TEXT,
    category VARCHAR(100),
    skills TEXT,
    profile_pic VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    portfolio_images TEXT,
    privacy_settings JSON,
    profile_completeness INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_category (category),
    INDEX idx_rating (rating),
    INDEX idx_profile_completeness (profile_completeness)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gigs Table
CREATE TABLE IF NOT EXISTS gigs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    freelancer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    budget DECIMAL(10,2) NOT NULL,
    delivery_time INT NOT NULL COMMENT 'in days',
    status ENUM('active', 'inactive', 'deleted') NOT NULL DEFAULT 'active',
    deactivated_by_admin BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_freelancer_id (freelancer_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_budget (budget)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gig_id INT NOT NULL,
    client_id INT NOT NULL,
    freelancer_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'in_progress', 'completed', 'canceled') NOT NULL DEFAULT 'pending',
    budget DECIMAL(10,2) NOT NULL,
    delivery_time INT NOT NULL,
    client_notes TEXT,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    FOREIGN KEY (gig_id) REFERENCES gigs(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_gig_id (gig_id),
    INDEX idx_client_id (client_id),
    INDEX idx_freelancer_id (freelancer_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bids Table (Optional - for client bidding on gigs)
CREATE TABLE IF NOT EXISTS bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gig_id INT NOT NULL,
    client_id INT NOT NULL,
    message TEXT,
    bid_amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gig_id) REFERENCES gigs(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_gig_id (gig_id),
    INDEX idx_client_id (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    freelancer_id INT NOT NULL,
    client_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_freelancer_id (freelancer_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages Table (Basic messaging system)
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    order_id INT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_sender_id (sender_id),
    INDEX idx_receiver_id (receiver_id),
    INDEX idx_order_id (order_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO categories (name, description, icon) VALUES
('Web Development', 'Website design and development services', 'fa-code'),
('Mobile Apps', 'iOS and Android app development', 'fa-mobile-alt'),
('Graphic Design', 'Logo, branding, and visual design', 'fa-paint-brush'),
('Content Writing', 'Articles, blogs, and copywriting', 'fa-pen'),
('Digital Marketing', 'SEO, social media, and online marketing', 'fa-chart-line'),
('Video Editing', 'Video production and editing services', 'fa-video'),
('Photography', 'Product and portrait photography', 'fa-camera'),
('Voice Over', 'Professional voice recording services', 'fa-microphone');

-- Insert default admin user (password: admin123)
INSERT INTO users (name, username, email, password, role, status) VALUES
('Admin', 'admin', 'admin@freelancehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Insert sample freelancers
INSERT INTO users (name, username, email, password, role, status) VALUES
('John Smith', 'johnsmith', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'freelancer', 'active'),
('Sarah Johnson', 'sarahj', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'freelancer', 'active'),
('Mike Wilson', 'mikew', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'freelancer', 'active'),
('Emily Davis', 'emilyd', 'emily@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'freelancer', 'active'),
('David Brown', 'davidb', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'freelancer', 'active');

-- Insert freelancer profiles
INSERT INTO freelancer_profiles (user_id, bio, category, skills, rating, total_reviews) VALUES
(2, 'Experienced web developer specializing in modern web technologies', 'Web Development', 'HTML, CSS, JavaScript, PHP, MySQL', 4.8, 25),
(3, 'Creative graphic designer with 5+ years of experience', 'Graphic Design', 'Photoshop, Illustrator, InDesign, Branding', 4.6, 18),
(4, 'Mobile app developer for iOS and Android platforms', 'Mobile Apps', 'React Native, Flutter, Swift, Kotlin', 4.9, 32),
(5, 'Professional content writer and copywriter', 'Content Writing', 'SEO Writing, Blog Posts, Technical Writing', 4.7, 15),
(6, 'Digital marketing expert specializing in SEO and social media', 'Digital Marketing', 'SEO, Google Ads, Social Media Marketing, Analytics', 4.5, 20);

-- Insert sample gigs
INSERT INTO gigs (freelancer_id, title, description, category, budget, delivery_time, status) VALUES
(2, 'Modern E-commerce Website', 'Complete e-commerce website with payment integration, admin panel, and responsive design', 'Web Development', 1500.00, 14, 'active'),
(2, 'WordPress Blog Setup', 'Custom WordPress blog with theme customization and SEO optimization', 'Web Development', 800.00, 7, 'active'),
(3, 'Logo Design Package', 'Professional logo design with multiple concepts and file formats', 'Graphic Design', 300.00, 5, 'active'),
(3, 'Brand Identity Design', 'Complete brand identity including logo, business cards, and letterhead', 'Graphic Design', 600.00, 10, 'active'),
(4, 'iOS Mobile App Development', 'Native iOS app development with custom features and UI/UX design', 'Mobile Apps', 2500.00, 21, 'active'),
(4, 'Cross-platform Mobile App', 'React Native app that works on both iOS and Android platforms', 'Mobile Apps', 1800.00, 18, 'active'),
(5, 'SEO Content Writing', 'High-quality SEO optimized articles for your blog or website', 'Content Writing', 200.00, 7, 'active'),
(5, 'Technical Documentation', 'Comprehensive technical documentation and user manuals', 'Content Writing', 400.00, 10, 'active'),
(6, 'SEO Optimization Service', 'Complete SEO audit and optimization for better search rankings', 'Digital Marketing', 500.00, 14, 'active'),
(6, 'Social Media Marketing', 'Social media strategy and content creation for brand growth', 'Digital Marketing', 350.00, 30, 'active');

-- Insert sample clients
INSERT INTO users (name, username, email, password, role, status) VALUES
('Alice Cooper', 'alicec', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'active'),
('Bob Martin', 'bobm', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'active');

-- Create trigger to update freelancer rating
DELIMITER //
CREATE TRIGGER update_freelancer_rating AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    DECLARE avg_rating DECIMAL(3,2);
    DECLARE review_count INT;
    
    SELECT AVG(rating), COUNT(*) INTO avg_rating, review_count
    FROM reviews
    WHERE freelancer_id = NEW.freelancer_id;
    
    UPDATE freelancer_profiles
    SET rating = avg_rating, total_reviews = review_count
    WHERE user_id = NEW.freelancer_id;
END//
DELIMITER ;
