-- VPBank Solution Day Game Database Setup
-- Complete database schema for VPBank Game System
-- Run this in phpMyAdmin or MySQL command line

-- Create database
CREATE DATABASE IF NOT EXISTS vpbankgame_halovpbank CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vpbankgame_halovpbank;

-- Users table - Store user information
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    user_token VARCHAR(64) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_email (email),
    INDEX idx_token (user_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User progress tracking - Track station completions
CREATE TABLE IF NOT EXISTS user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id VARCHAR(50) NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_station (user_id, station_id),
    INDEX idx_user_id (user_id),
    INDEX idx_station_id (station_id),
    INDEX idx_completed_at (completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gift codes table - Manage reward codes
CREATE TABLE IF NOT EXISTS gift_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NULL,
    claimed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_user_id (user_id),
    INDEX idx_claimed_at (claimed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QR codes management table - Manage QR codes for stations
CREATE TABLE IF NOT EXISTS qr_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id VARCHAR(50) NOT NULL,
    qr_url TEXT NOT NULL,
    verify_hash VARCHAR(64) NOT NULL UNIQUE,
    qr_filename VARCHAR(255),
    status ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    notes TEXT,
    scan_count INT DEFAULT 0,
    last_scan_at TIMESTAMP NULL,
    INDEX idx_station_id (station_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_verify_hash (verify_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scan logs table - Track QR code scans
CREATE TABLE IF NOT EXISTS scan_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id VARCHAR(50) NOT NULL,
    qr_code_id INT NULL,
    qr_data TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (qr_code_id) REFERENCES qr_codes(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_station_id (station_id),
    INDEX idx_qr_code_id (qr_code_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QR generation logs table - Track QR code generation
CREATE TABLE IF NOT EXISTS qr_generation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id VARCHAR(50) NOT NULL,
    qr_data TEXT NOT NULL,
    generated_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_station_id (station_id),
    INDEX idx_generated_by (generated_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data (optional)
-- Uncomment the following lines if you want to insert sample data

-- Sample stations data
-- INSERT INTO qr_codes (station_id, qr_url, verify_hash, status, created_by) VALUES
-- ('HALLO_GLOW', 'http://localhost/halovpbank2025/game.php?station=HALLO_GLOW&verify=sample1', 'sample_hash_1', 'active', 'Admin'),
-- ('HALLO_SOLUTION', 'http://localhost/halovpbank2025/game.php?station=HALLO_SOLUTION&verify=sample2', 'sample_hash_2', 'active', 'Admin'),
-- ('HALLO_WIN', 'http://localhost/halovpbank2025/game.php?station=HALLO_WIN&verify=sample3', 'sample_hash_3', 'active', 'Admin'),
-- ('HALLO_SHOP', 'http://localhost/halovpbank2025/game.php?station=HALLO_SHOP&verify=sample4', 'active', 'Admin');

-- Grant privileges (adjust username and host as needed)
-- GRANT ALL PRIVILEGES ON vpbankgame_halovpbank.* TO 'vpbankgame_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Database setup complete
SELECT 'VPBank Game Database setup completed successfully!' as status;