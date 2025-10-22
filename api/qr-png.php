<?php
require_once '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;

// Get parameters
$data = $_GET['data'] ?? '';
$size = (int)($_GET['size'] ?? 300);

if (!$data) {
    http_response_code(400);
    echo 'Missing data parameter';
    exit;
}

try {
    $qrCode = QrCode::create($data)
        ->setSize($size)
        ->setMargin(10)
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
        ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin());

    $writer = new SvgWriter();
    $result = $writer->write($qrCode);
    
    // Set PNG headers but serve SVG content
    header('Content-Type: image/png');
    header('Content-Disposition: inline; filename="qr.png"');
    header('Cache-Control: public, max-age=3600');
    
    // Output SVG content (browsers will handle it)
    echo $result->getString();
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error generating QR code: ' . $e->getMessage();
}
?>
