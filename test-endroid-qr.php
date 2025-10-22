<?php
// Test Endroid QR-Code Integration
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Endroid QR-Code Test</h1>";

// Test 1: Check if vendor directory exists
echo "<h2>1. Composer Check</h2>";
if (is_dir('vendor')) {
    echo "✅ vendor directory exists<br>";

    if (is_file('vendor/autoload.php')) {
        echo "✅ autoload.php exists<br>";
    } else {
        echo "❌ autoload.php not found<br>";
    }

    if (is_dir('vendor/endroid')) {
        echo "✅ endroid directory exists<br>";
    } else {
        echo "❌ endroid directory not found<br>";
    }
} else {
    echo "❌ vendor directory not found<br>";
    echo "Please run: composer install<br>";
}

// Test 2: Test QRCodeService
echo "<h2>2. QRCodeService Test</h2>";
try {
    require_once 'vendor/autoload.php';
    require_once 'src/QRCodeService.php';

    use VPBank\QRCodeService;

    $qrService = new QRCodeService('assets/qr-codes/', '../assets/qr-codes/');
    echo "✅ QRCodeService instantiated successfully<br>";

    // Test QR generation
    $testData = 'Test QR Code - ' . date('Y-m-d H:i:s');
    $result = $qrService->generateQRCode($testData, 'test_endroid.png', 'png', 200);

    echo "✅ QR Code generated successfully<br>";
    echo "📁 File: " . $result['filename'] . "<br>";
    echo "📏 Size: " . $result['size'] . " bytes<br>";
    echo "🔧 Method: " . $result['method'] . "<br>";

    if (file_exists('assets/qr-codes/' . $result['filename'])) {
        echo "✅ QR Code file saved successfully<br>";
        echo "🖼️ <img src='assets/qr-codes/" . $result['filename'] . "' alt='Test QR Code' style='max-width: 200px;'><br>";
    } else {
        echo "❌ QR Code file not found<br>";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 3: Test API endpoint
echo "<h2>3. API Test</h2>";
$testData = [
    'station_id' => 'HALLO_TEST',
    'notes' => 'Endroid QR Code Test',
    'expires_at' => null
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/halovpbank2025/api/endroid-qr-generator.php?action=create');
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
echo "Response Body:<br><pre>" . htmlspecialchars($body) . "</pre>";

// Test 4: Test QR code output
echo "<h2>4. QR Code Output Test</h2>";
echo "Direct QR Code Output:<br>";
echo "<img src='api/endroid-qr-generator.php?action=output&data=Hello%20Endroid%20QR-Code&size=150' alt='Direct QR Output'><br>";

echo "<br><a href='admin/qr-codes.php'>Go to QR Codes Admin</a>";
?>
