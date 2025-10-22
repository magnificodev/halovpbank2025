<?php
require_once 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;

echo "Debug QR Generation...\n";

$qrDirectory = 'assets/qr-codes/';
echo "Directory: $qrDirectory\n";
echo "Directory exists: " . (is_dir($qrDirectory) ? 'YES' : 'NO') . "\n";
echo "Directory writable: " . (is_writable($qrDirectory) ? 'YES' : 'NO') . "\n";

if (!is_dir($qrDirectory)) {
    echo "Creating directory...\n";
    mkdir($qrDirectory, 0755, true);
    echo "Directory created: " . (is_dir($qrDirectory) ? 'YES' : 'NO') . "\n";
}

try {
    $qrCode = QrCode::create('Hello World')
        ->setSize(200)
        ->setMargin(10)
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
        ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin());

    $writer = new SvgWriter();
    $result = $writer->write($qrCode);
    
    $filename = 'debug_test.svg';
    $filePath = $qrDirectory . $filename;
    
    echo "Saving to: $filePath\n";
    $result->saveToFile($filePath);
    
    echo "File exists: " . (file_exists($filePath) ? 'YES' : 'NO') . "\n";
    echo "File size: " . filesize($filePath) . " bytes\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
