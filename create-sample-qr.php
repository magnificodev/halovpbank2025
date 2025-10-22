<?php
require_once 'api/db.php';

$db = new Database();

// Create sample QR codes for testing
$stations = ['HALLO_GLOW', 'HALLO_SOLUTION', 'HALLO_WIN', 'HALLO_SHOP', 'HALLO_EXPERIENCE'];

foreach ($stations as $station) {
    // Generate verify hash
    $verifyHash = hash('sha256', $station . time() . rand());

    // Create QR URL
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . '://' . $host . dirname($_SERVER['REQUEST_URI']);
    $qrUrl = $baseUrl . '/game.php?station=' . urlencode($station) . '&verify=' . $verifyHash;

    try {
        $id = $db->insert("INSERT INTO qr_codes (station_id, qr_url, verify_hash, notes, created_by) VALUES (?, ?, ?, ?, ?)", [
            $station,
            $qrUrl,
            $verifyHash,
            "Sample QR code for $station",
            'admin'
        ]);

        echo "✅ Created QR code for $station (ID: $id)\n";
    } catch (Exception $e) {
        echo "❌ Error creating QR for $station: " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Sample QR codes created successfully!\n";
echo "Visit: http://localhost/halovpbank2025/admin/qr-codes.php\n";
?>
