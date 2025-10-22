<?php
require_once '../vendor/autoload.php';
require_once '../src/QRCodeService.php';

use VPBank\QRCodeService;

header('Content-Type: application/json');

try {
    $qrService = new QRCodeService('assets/qr-codes/', '../assets/qr-codes/');
    
    $stationId = 'HALLO_TEST';
    $verifyHash = hash('sha256', $stationId . time() . rand());
    
    // Create QR URL
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . '://' . $host . dirname($_SERVER['REQUEST_URI'], 2);
    $qrUrl = $baseUrl . '/game.php?station=' . urlencode($stationId) . '&verify=' . $verifyHash;
    
    $qrFilename = 'qr_' . $stationId . '_' . substr($verifyHash, 0, 8) . '.svg';
    $result = $qrService->generateStationQR($stationId, $verifyHash, $qrFilename);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'station_id' => $stationId,
            'qr_url' => $qrUrl,
            'verify_hash' => $verifyHash,
            'qr_filename' => $qrFilename,
            'qr_file_url' => $result['url'],
            'qr_method' => $result['method'],
            'qr_size' => $result['size'],
            'status' => 'active'
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
