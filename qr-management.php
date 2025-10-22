<?php
require_once 'db.php';
require_once '../src/QRCodeService.php';

use VPBank\QRCodeService;

header('Content-Type: application/json');

$db = new Database();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'list':
                    $page = (int)($_GET['page'] ?? 1);
                    $limit = (int)($_GET['limit'] ?? 10);
                    $status = $_GET['status'] ?? '';
                    $station = $_GET['station'] ?? '';

                    $offset = ($page - 1) * $limit;

                    $where = "WHERE 1=1";
                    $params = [];

                    if ($status) {
                        $where .= " AND status = ?";
                        $params[] = $status;
                    }

                    if ($station) {
                        $where .= " AND station_id = ?";
                        $params[] = $station;
                    }

                    // Get total count
                    $countQuery = "SELECT COUNT(*) as total FROM qr_codes $where";
                    $totalResult = $db->fetchOne($countQuery, $params);
                    $total = $totalResult['total'];

                    // Get QR codes
                    $query = "SELECT * FROM qr_codes $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
                    $params[] = $limit;
                    $params[] = $offset;

                    $qrCodes = $db->fetchAll($query, $params);

                    echo json_encode([
                        'success' => true,
                        'data' => $qrCodes,
                        'pagination' => [
                            'page' => $page,
                            'limit' => $limit,
                            'total' => $total,
                            'pages' => ceil($total / $limit)
                        ]
                    ]);
                    break;

                case 'get':
                    $id = (int)($_GET['id'] ?? 0);
                    if (!$id) {
                        throw new Exception('QR Code ID is required');
                    }

                    $qrCode = $db->fetchOne("SELECT * FROM qr_codes WHERE id = ?", [$id]);
                    if (!$qrCode) {
                        throw new Exception('QR Code not found');
                    }

                    echo json_encode([
                        'success' => true,
                        'data' => $qrCode
                    ]);
                    break;

                default:
                    throw new Exception('Invalid action');
            }
            break;

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

                    // Generate QR code file
                    $qrService = new QRCodeService('assets/qr-codes/', '../assets/qr-codes/');
                    $qrFilename = 'qr_' . $stationId . '_' . substr($verifyHash, 0, 8) . '.png';
                    $qrResult = $qrService->generateStationQR($stationId, $verifyHash, $qrFilename);

                    $id = $db->insert("INSERT INTO qr_codes (station_id, qr_url, verify_hash, notes, expires_at, created_by, qr_filename) VALUES (?, ?, ?, ?, ?, ?, ?)", [
                        $stationId,
                        $qrUrl,
                        $verifyHash,
                        $notes,
                        $expiresAt,
                        'admin', // You can get this from session
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
                            'status' => 'active'
                        ]
                    ]);
                    break;

                default:
                    throw new Exception('Invalid action');
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)($data['id'] ?? 0);

            if (!$id) {
                throw new Exception('QR Code ID is required');
            }

            switch ($action) {
                case 'update':
                    $status = $data['status'] ?? '';
                    $notes = $data['notes'] ?? '';
                    $expiresAt = $data['expires_at'] ?? null;

                    $updateFields = [];
                    $params = [];

                    if ($status) {
                        $updateFields[] = "status = ?";
                        $params[] = $status;
                    }

                    if ($notes !== '') {
                        $updateFields[] = "notes = ?";
                        $params[] = $notes;
                    }

                    if ($expiresAt !== null) {
                        $updateFields[] = "expires_at = ?";
                        $params[] = $expiresAt;
                    }

                    if (empty($updateFields)) {
                        throw new Exception('No fields to update');
                    }

                    $params[] = $id;
                    $query = "UPDATE qr_codes SET " . implode(', ', $updateFields) . " WHERE id = ?";

                    $db->execute($query, $params);

                    echo json_encode([
                        'success' => true,
                        'message' => 'QR Code updated successfully'
                    ]);
                    break;

                default:
                    throw new Exception('Invalid action');
            }
            break;

        case 'DELETE':
            $id = (int)($_GET['id'] ?? 0);

            if (!$id) {
                throw new Exception('QR Code ID is required');
            }

            switch ($action) {
                case 'delete':
                    // Soft delete - set status to 'deleted'
                    $db->execute("UPDATE qr_codes SET status = 'deleted' WHERE id = ?", [$id]);

                    echo json_encode([
                        'success' => true,
                        'message' => 'QR Code deleted successfully'
                    ]);
                    break;

                case 'permanent':
                    // Hard delete - remove from database
                    $db->execute("DELETE FROM qr_codes WHERE id = ?", [$id]);

                    echo json_encode([
                        'success' => true,
                        'message' => 'QR Code permanently deleted'
                    ]);
                    break;

                default:
                    throw new Exception('Invalid action');
            }
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
