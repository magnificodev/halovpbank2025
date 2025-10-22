<?php
require_once 'db.php';
require_once '../vendor/autoload.php';
require_once '../src/QRCodeService.php';

use VPBank\QRCodeService;

header('Content-Type: application/json');

$db = new Database();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            switch ($action) {
                case 'create':
                    $stationId = $data['station_id'] ?? '';
                    $notes = $data['notes'] ?? '';
                    $expiresAt = $data['expires_at'] ?? null;
                    
                    if (!$stationId) {
                        throw new Exception('Station ID is required');
                    }
                    
                    // Generate verify hash
                    $verifyHash = hash('sha256', $stationId . time() . rand());
                    
                    // Create QR URL
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'];
                    $baseUrl = $protocol . '://' . $host . dirname($_SERVER['REQUEST_URI'], 2);
                    $qrUrl = $baseUrl . '/game.php?station=' . urlencode($stationId) . '&verify=' . $verifyHash;
                    
                    // Generate QR code using Endroid QR-Code
                    $qrService = new QRCodeService('assets/qr-codes/', '../assets/qr-codes/');
                    $qrFilename = 'qr_' . $stationId . '_' . substr($verifyHash, 0, 8) . '.png';
                    
                    try {
                        $qrResult = $qrService->generateStationQR($stationId, $verifyHash, $qrFilename);
                    } catch (Exception $e) {
                        throw new Exception('QR Code generation failed: ' . $e->getMessage());
                    }
                    
                    $id = $db->insert("INSERT INTO qr_codes (station_id, qr_url, verify_hash, notes, expires_at, created_by, qr_filename) VALUES (?, ?, ?, ?, ?, ?, ?)", [
                        $stationId,
                        $qrUrl,
                        $verifyHash,
                        $notes,
                        $expiresAt,
                        'admin',
                        $qrFilename
                    ]);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'id' => $id,
                            'station_id' => $stationId,
                            'qr_url' => $qrUrl,
                            'verify_hash' => $verifyHash,
                            'qr_filename' => $qrFilename,
                            'qr_file_url' => $qrResult['url'],
                            'qr_method' => $qrResult['method'],
                            'qr_size' => $qrResult['size'],
                            'status' => 'active'
                        ]
                    ]);
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        default:
            throw new Exception('Invalid method');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
