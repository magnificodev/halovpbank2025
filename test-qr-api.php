<?php
// Test QR Code API
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Test QR Code API</h1>";

// Test 1: Check if database table exists
echo "<h2>1. Database Check</h2>";
try {
    require_once 'api/db.php';
    $db = new Database();

    $result = $db->fetchOne("SHOW TABLES LIKE 'qr_codes'");
    if ($result) {
        echo "✅ qr_codes table exists<br>";

        // Check columns
        $columns = $db->fetchAll("DESCRIBE qr_codes");
        echo "📋 Table columns:<br>";
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})<br>";
        }
    } else {
        echo "❌ qr_codes table does not exist<br>";
        echo "Please run: <a href='check-database.php'>check-database.php</a><br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 2: Test API endpoint
echo "<h2>2. API Test</h2>";
$testData = [
    'station_id' => 'HALLO_TEST',
    'notes' => 'Test QR code',
    'expires_at' => null
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/halovpbank2025/api/simple-qr-generator.php?action=create');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

echo "HTTP Code: $httpCode<br>";
echo "Headers:<br><pre>" . htmlspecialchars($headers) . "</pre>";
echo "Response Body:<br><pre>" . htmlspecialchars($body) . "</pre>";

// Test 3: Check if QR code file was created
echo "<h2>3. File Check</h2>";
$qrDir = 'assets/qr-codes/';
if (is_dir($qrDir)) {
    echo "✅ QR codes directory exists<br>";
    $files = scandir($qrDir);
    $qrFiles = array_filter($files, function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'png';
    });

    if (count($qrFiles) > 0) {
        echo "✅ Found " . count($qrFiles) . " QR code files:<br>";
        foreach ($qrFiles as $file) {
            echo "- <a href='$qrDir$file' target='_blank'>$file</a><br>";
        }
    } else {
        echo "❌ No QR code files found<br>";
    }
} else {
    echo "❌ QR codes directory does not exist<br>";
}

echo "<br><a href='admin/qr-codes.php'>Go to QR Codes Admin</a>";
?>
