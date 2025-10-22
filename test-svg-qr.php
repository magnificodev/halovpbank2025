<?php
require_once 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;

echo "Testing Endroid QR-Code with SVG...\n";

try {
    $qrCode = QrCode::create('Hello World')
        ->setSize(200)
        ->setMargin(10)
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
        ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin());

    $writer = new SvgWriter();
    $result = $writer->write($qrCode);
    
    // Save to file
    $result->saveToFile('test_qr.svg');
    
    echo "✅ QR Code generated successfully!\n";
    echo "�� File: test_qr.svg\n";
    echo "� Size: " . filesize('test_qr.svg') . " bytes\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
