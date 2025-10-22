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
<<<<<<< HEAD
            
            if (!$stationId || !$verifyHash) {
                throw new Exception('Station ID and verify hash are required');
            }
            
            $result = $qrService->generateStationQR($stationId, $verifyHash, $filename, $size);
            
=======

            if (!$stationId || !$verifyHash) {
                throw new Exception('Station ID and verify hash are required');
            }

            $result = $qrService->generateStationQR($stationId, $verifyHash, $filename, $size);

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            break;
<<<<<<< HEAD
            
=======

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
        case 'output':
            $data = $_GET['data'] ?? '';
            $format = $_GET['format'] ?? 'png';
            $size = $_GET['size'] ?? 300;
<<<<<<< HEAD
            
            if (!$data) {
                throw new Exception('Data parameter is required');
            }
            
            $qrService->outputQRCode($data, $format, $size);
            break;
            
        case 'list':
            $qrCodes = $qrService->listQRCodes();
            
=======

            if (!$data) {
                throw new Exception('Data parameter is required');
            }

            $qrService->outputQRCode($data, $format, $size);
            break;

        case 'list':
            $qrCodes = $qrService->listQRCodes();

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
            echo json_encode([
                'success' => true,
                'data' => $qrCodes
            ]);
            break;
<<<<<<< HEAD
            
        case 'info':
            $filename = $_GET['filename'] ?? '';
            
            if (!$filename) {
                throw new Exception('Filename is required');
            }
            
            $info = $qrService->getQRCodeInfo($filename);
            
            if (!$info) {
                throw new Exception('QR code not found');
            }
            
=======

        case 'info':
            $filename = $_GET['filename'] ?? '';

            if (!$filename) {
                throw new Exception('Filename is required');
            }

            $info = $qrService->getQRCodeInfo($filename);

            if (!$info) {
                throw new Exception('QR code not found');
            }

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
            echo json_encode([
                'success' => true,
                'data' => $info
            ]);
            break;
<<<<<<< HEAD
            
        case 'delete':
            $filename = $_GET['filename'] ?? '';
            
            if (!$filename) {
                throw new Exception('Filename is required');
            }
            
            $deleted = $qrService->deleteQRCode($filename);
            
=======

        case 'delete':
            $filename = $_GET['filename'] ?? '';

            if (!$filename) {
                throw new Exception('Filename is required');
            }

            $deleted = $qrService->deleteQRCode($filename);

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
            echo json_encode([
                'success' => $deleted,
                'message' => $deleted ? 'QR code deleted successfully' : 'Failed to delete QR code'
            ]);
            break;
<<<<<<< HEAD
            
        default:
            throw new Exception('Invalid action');
    }
    
=======

        default:
            throw new Exception('Invalid action');
    }

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
