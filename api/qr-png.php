<?php
// Simple PNG QR test
require_once '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;

$data = $_GET['data'] ?? 'Test QR Code';
$size = (int)($_GET['size'] ?? 300);

try {
    $qrCode = QrCode::create($data)
        ->setSize($size)
        ->setMargin(10)
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
        ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin());

    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    
    // Save to file first
    $filename = 'qr_' . time() . '.png';
    $result->saveToFile($filename);
    
    // Set headers
    header('Content-Type: image/png');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    
    // Output the file
    readfile($filename);
    
    // Clean up
    unlink($filename);
    
} catch (Exception $e) {
    header('Content-Type: text/plain');
    echo 'Error: ' . $e->getMessage();
}
?>
