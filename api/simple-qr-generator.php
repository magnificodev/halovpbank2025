<?php
require_once 'db.php';

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

                    $qrCodes = $db->fetchAll("SELECT * FROM qr_codes $where ORDER BY created_at DESC LIMIT ? OFFSET ?",
                        array_merge($params, [$limit, $offset]));

                    $total = $db->fetchOne("SELECT COUNT(*) as count FROM qr_codes $where", $params)['count'];

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

                    // Generate QR code using Google Charts API
                    $qrImageUrl = 'https://chart.googleapis.com/chart?chs=400x400&chld=L|0&cht=qr&chl=' . urlencode($qrUrl);

                    // Generate filename
                    $qrFilename = 'qr_' . $stationId . '_' . substr($verifyHash, 0, 8) . '.png';

                    // Download and save QR code image
                    $qrImageData = file_get_contents($qrImageUrl);
                    $qrFilePath = '../assets/qr-codes/' . $qrFilename;

                    // Create directory if not exists
                    if (!is_dir('../assets/qr-codes/')) {
                        mkdir('../assets/qr-codes/', 0755, true);
                    }

                    file_put_contents($qrFilePath, $qrImageData);

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
                            'qr_file_url' => 'assets/qr-codes/' . $qrFilename,
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

                    if (!$status) {
                        throw new Exception('Status is required');
                    }

                    $db->execute("UPDATE qr_codes SET status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                        [$status, $notes, $id]);

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

            // Get QR code info before deletion
            $qrCode = $db->fetchOne("SELECT qr_filename FROM qr_codes WHERE id = ?", [$id]);

            // Delete QR code file if exists
            if ($qrCode && !empty($qrCode['qr_filename'])) {
                $filePath = '../assets/qr-codes/' . $qrCode['qr_filename'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $db->execute("DELETE FROM qr_codes WHERE id = ?", [$id]);

            echo json_encode([
                'success' => true,
                'message' => 'QR Code deleted successfully'
            ]);
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
