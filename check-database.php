<?php
require_once 'api/db.php';

$db = new Database();

echo "🔍 Checking database tables...\n\n";

// Check if qr_codes table exists
try {
    $result = $db->fetchOne("SHOW TABLES LIKE 'qr_codes'");
    if ($result) {
        echo "✅ qr_codes table exists\n";
        
        // Check table structure
        $columns = $db->fetchAll("DESCRIBE qr_codes");
        echo "📋 Table structure:\n";
        foreach ($columns as $column) {
            echo "  - {$column['Field']} ({$column['Type']})\n";
        }
    } else {
        echo "❌ qr_codes table does not exist\n";
        echo "📝 Creating qr_codes table...\n";
        
        // Create the table
        $createTable = "
        CREATE TABLE qr_codes (
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
        ";
        
        $db->execute($createTable);
        echo "✅ qr_codes table created successfully\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎉 Database check complete!\n";
echo "Visit: http://localhost/halovpbank2025/admin/qr-codes.php\n";
?>
