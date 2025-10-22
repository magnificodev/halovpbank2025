-- VPBank Solution Day Game Database Setup for DirectAdmin
-- Run this script in phpMyAdmin or MySQL command line

-- Create database (with vpbankgame_ prefix)
CREATE DATABASE IF NOT EXISTS vpbankgame_halovpbank CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vpbankgame_halovpbank;

-- Create user (run these commands separately)
-- CREATE USER 'vpbankgame_halovpbank'@'localhost' IDENTIFIED BY 'VpBank2025!@#';
-- GRANT ALL PRIVILEGES ON vpbankgame_halovpbank.* TO 'vpbankgame_halovpbank'@'localhost';
-- FLUSH PRIVILEGES;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    user_token VARCHAR(64) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User progress tracking
CREATE TABLE IF NOT EXISTS user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id VARCHAR(50) NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_station (user_id, station_id)
);

-- Gift codes table
CREATE TABLE IF NOT EXISTS gift_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(255) NOT NULL UNIQUE,
    user_id INT NULL,
    claimed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Scan logs table
CREATE TABLE IF NOT EXISTS scan_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- QR generation logs table
CREATE TABLE IF NOT EXISTS qr_generation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id VARCHAR(50) NOT NULL,
    qr_url TEXT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some sample gift codes
INSERT INTO gift_codes (code) VALUES
('VPB2025001'),
('VPB2025002'),
('VPB2025003'),
('VPB2025004'),
('VPB2025005'),
('VPB2025006'),
('VPB2025007'),
('VPB2025008'),
('VPB2025009'),
('VPB2025010');
