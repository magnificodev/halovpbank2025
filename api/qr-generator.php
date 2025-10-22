<?php
require_once '../config.php';
require_once '../src/QRCodeService.php';

use VPBank\QRCodeService;

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? '';
    $qrService = new QRCodeService('assets/qr-codes/', '../assets/qr-codes/');

    switch ($action) {
        case 'generate':
            $data = json_decode(file_get_contents('php://input'), true);
            $stationId = $data['station_id'] ?? '';
            $verifyHash = $data['verify_hash'] ?? '';
            $filename = $data['filename'] ?? null;
            $size = $data['size'] ?? 400;

            if (!$stationId || !$verifyHash) {
                throw new Exception('Station ID and verify hash are required');
            }

            $result = $qrService->generateStationQR($stationId, $verifyHash, $filename, $size);

            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            break;

        case 'output':
            $data = $_GET['data'] ?? '';
            $format = $_GET['format'] ?? 'png';
            $size = $_GET['size'] ?? 300;

            if (!$data) {
                throw new Exception('Data parameter is required');
            }

            $qrService->outputQRCode($data, $format, $size);
            break;

        case 'list':
            $qrCodes = $qrService->listQRCodes();

            echo json_encode([
                'success' => true,
                'data' => $qrCodes
            ]);
            break;

        case 'info':
            $filename = $_GET['filename'] ?? '';

            if (!$filename) {
                throw new Exception('Filename is required');
            }

            $info = $qrService->getQRCodeInfo($filename);

            if (!$info) {
                throw new Exception('QR code not found');
            }

            echo json_encode([
                'success' => true,
                'data' => $info
            ]);
            break;

        case 'delete':
            $filename = $_GET['filename'] ?? '';

            if (!$filename) {
                throw new Exception('Filename is required');
            }

            $deleted = $qrService->deleteQRCode($filename);

            echo json_encode([
                'success' => $deleted,
                'message' => $deleted ? 'QR code deleted successfully' : 'Failed to delete QR code'
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
