-- VPBank Solution Day Game Database Schema
-- Run this SQL to create the database and tables

CREATE DATABASE IF NOT EXISTS vpbank_game CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vpbank_game;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL,
    user_token VARCHAR(64) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_token (user_token)
);

-- User progress tracking
CREATE TABLE user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id VARCHAR(50) NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_station (user_id, station_id),
    INDEX idx_user_id (user_id),
    INDEX idx_station_id (station_id)
);

-- Gift codes table
CREATE TABLE gift_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NULL,
    claimed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_user_id (user_id)
);

-- Scan logs for audit
CREATE TABLE IF NOT EXISTS scan_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id VARCHAR(50) NOT NULL,
    ip_address VARCHAR(64) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_station_id (station_id),
    INDEX idx_created_at (created_at)
);

-- Insert some sample gift codes (you can add more)
INSERT INTO gift_codes (code) VALUES
('ABC12345'),
('DEF67890'),
('GHI11111'),
('JKL22222'),
('MNO33333'),
('PQR44444'),
('STU55555'),
('VWX66666'),
('YZA77777'),
('BCD88888'),
('EFG99999'),
('HIJ00000'),
('KLM11111'),
('NOP22222'),
('QRS33333'),
('TUV44444'),
('WXY55555'),
('ZAB66666'),
('CDE77777'),
('FGH88888');
