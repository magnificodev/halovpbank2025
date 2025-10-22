<?php
require_once 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;

echo "Testing PNG QR Generation...\n";

// Check if GD is available
if (!extension_loaded('gd')) {
    echo "❌ GD extension not loaded\n";
    echo "Available extensions:\n";
    $extensions = get_loaded_extensions();
    foreach ($extensions as $ext) {
        if (strpos($ext, 'gd') !== false || strpos($ext, 'imagick') !== false) {
            echo "  - $ext\n";
        }
    }
    exit;
}

echo "✅ GD extension is available\n";

try {
    $qrCode = QrCode::create('Hello World PNG')
        ->setSize(200)
        ->setMargin(10)
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
        ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin());

    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    
    // Save to file
    $result->saveToFile('test_png_qr.png');
    
    echo "✅ PNG QR Code generated successfully!\n";
    echo "� File: test_png_qr.png\n";
    echo "� Size: " . filesize('test_png_qr.png') . " bytes\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
