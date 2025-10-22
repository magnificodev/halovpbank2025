<?php
require_once '../vendor/autoload.php';
require_once '../src/QRCodeService.php';

use VPBank\QRCodeService;

// Get parameters
$data = $_GET['data'] ?? '';
$size = (int)($_GET['size'] ?? 300);

if (!$data) {
    http_response_code(400);
    echo 'Missing data parameter';
    exit;
}

try {
    $qrService = new QRCodeService('assets/qr-codes/', '../assets/qr-codes/');
    
    // Generate SVG QR code
    $result = $qrService->generateQRCode($data, 'temp_qr.svg', 'svg', $size);
    
    // Read SVG content
    $svgContent = file_get_contents($result['filepath']);
    
    // Set PNG headers but serve SVG content
    header('Content-Type: image/png');
    header('Content-Disposition: inline; filename="qr.png"');
    header('Cache-Control: public, max-age=3600');
    
    // For now, just output the SVG content
    // In a real implementation, you would convert SVG to PNG here
    echo $svgContent;
    
    // Clean up temp file
    unlink($result['filepath']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error generating QR code: ' . $e->getMessage();
}
?>
