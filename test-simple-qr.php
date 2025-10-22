<?php
require_once 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;

echo "Testing Endroid QR-Code...\n";

try {
    $qrCode = QrCode::create('Hello World')
        ->setSize(200)
        ->setMargin(10)
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
        ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin());

    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    
    // Save to file
    $result->saveToFile('test_qr.png');
    
    echo "âœ… QR Code generated successfully!\n";
    echo "í³ File: test_qr.png\n";
    echo "í³ Size: " . filesize('test_qr.png') . " bytes\n";
    
} catch (Exception $e) {
    echo "âŒ Error: " . $e->getMessage() . "\n";
}
?>
