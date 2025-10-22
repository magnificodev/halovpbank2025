-- VPBank Solution Day Game Database Setup
-- Run this in phpMyAdmin or MySQL command line

-- Create database
CREATE DATABASE IF NOT EXISTS vpbankgame_halovpbank CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vpbankgame_halovpbank;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    user_token VARCHAR(64) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_progress table
CREATE TABLE IF NOT EXISTS user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id VARCHAR(50) NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_station (user_id, station_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create gift_codes table
CREATE TABLE IF NOT EXISTS gift_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NULL,
    claimed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create scan_logs table
CREATE TABLE IF NOT EXISTS scan_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id VARCHAR(50) NOT NULL,
    qr_data TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create qr_generation_logs table
CREATE TABLE IF NOT EXISTS qr_generation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id VARCHAR(50) NOT NULL,
    qr_data TEXT NOT NULL,
    generated_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user for database access
CREATE USER IF NOT EXISTS 'vpbankgame_halovpbank'@'localhost' IDENTIFIED BY 'VpBank2025!@#';
GRANT ALL PRIVILEGES ON vpbankgame_halovpbank.* TO 'vpbankgame_halovpbank'@'localhost';
FLUSH PRIVILEGES;
