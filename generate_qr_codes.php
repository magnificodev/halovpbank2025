<?php
require_once 'config.php';

// Configuration
$BASE_URL = 'https://yourdomain.com'; // Update this with your actual domain
$QR_CODES_DIR = 'assets/images/qr-codes/';

// Create QR codes directory if it doesn't exist
if (!is_dir($QR_CODES_DIR)) {
    mkdir($QR_CODES_DIR, 0755, true);
}

// Station configurations
$stations = [
    'HALLO_GLOW' => [
        'name' => 'HALLO GLOW',
        'description' => 'Chụp hình check in'
    ],
    'HALLO_SOLUTION' => [
        'name' => 'HALLO SOLUTION',
        'description' => 'Thử thách game công nghệ "không chạm"'
    ],
    'HALLO_SUPER_SINH_LOI' => [
        'name' => 'HALLO SUPER SINH LỜI PREMIER',
        'description' => 'Thử thách hứng tiền lời'
    ],
    'HALLO_SHOP' => [
        'name' => 'HALLO SHOP',
        'description' => 'Trải nghiệm các giải pháp thanh toán từ VPBank'
    ],
    'HALLO_WIN' => [
        'name' => 'HALLO WIN',
        'description' => 'Trò chơi đuổi hình bắt chữ vui nhộn'
    ]
];

echo "Generating QR codes for VPBank Solution Day Game...\n\n";

foreach ($stations as $stationId => $stationInfo) {
    // Generate verification hash
    $verifyHash = hash('sha256', $stationId . GAME_SECRET_KEY);

    // Create QR code URL
    $qrUrl = $BASE_URL . "/game.php?station=" . urlencode($stationId) . "&verify=" . $verifyHash;

    echo "Station: {$stationInfo['name']}\n";
    echo "Description: {$stationInfo['description']}\n";
    echo "QR URL: $qrUrl\n";
    echo "Verify Hash: $verifyHash\n";
    echo "---\n";

    // Generate QR code using Google Charts API (free service)
    $qrImageUrl = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($qrUrl);

    // Download and save QR code image
    $qrImageData = file_get_contents($qrImageUrl);
    if ($qrImageData) {
        $filename = strtolower($stationId) . '_qr.png';
        $filepath = $QR_CODES_DIR . $filename;

        if (file_put_contents($filepath, $qrImageData)) {
            echo "QR code saved: $filepath\n\n";
        } else {
            echo "Failed to save QR code: $filepath\n\n";
        }
    } else {
        echo "Failed to generate QR code image\n\n";
    }
}

echo "QR code generation completed!\n";
echo "Generated files are in: $QR_CODES_DIR\n";
echo "\nNext steps:\n";
echo "1. Print the QR code images\n";
echo "2. Place them at the respective stations\n";
echo "3. Test the scanning functionality\n";
?>
