-- QR Management System
-- Add this to your existing database

-- QR Codes table for managing QR codes
CREATE TABLE IF NOT EXISTS qr_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id VARCHAR(50) NOT NULL,
    qr_url TEXT NOT NULL,
    verify_hash VARCHAR(64) NOT NULL UNIQUE,
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
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update scan_logs to reference qr_codes
ALTER TABLE scan_logs
ADD COLUMN qr_code_id INT NULL,
ADD FOREIGN KEY (qr_code_id) REFERENCES qr_codes(id) ON DELETE SET NULL;

-- Add index for better performance
ALTER TABLE scan_logs ADD INDEX idx_qr_code_id (qr_code_id);
