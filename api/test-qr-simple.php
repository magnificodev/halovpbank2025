<?php
require_once '../vendor/autoload.php';
require_once '../src/QRCodeService.php';

use VPBank\QRCodeService;

header('Content-Type: application/json');

try {
    $qrService = new QRCodeService('assets/qr-codes/', '../assets/qr-codes/');
    
    $testData = 'Test QR Code - ' . date('Y-m-d H:i:s');
    $result = $qrService->generateQRCode($testData, 'test_simple.svg', 'svg', 200);
    
    echo json_encode([
        'success' => true,
        'message' => 'QR Code generated successfully',
        'data' => $result
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
