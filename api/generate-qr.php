<?php
require_once 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if admin is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    sendJsonResponse(['error' => 'Unauthorized'], 401);
}

$stationId = $_GET['station'] ?? $_POST['station'] ?? null;

if (!$stationId) {
    sendJsonResponse(['error' => 'Station ID is required'], 400);
}

// Validate station ID
if (!array_key_exists($stationId, STATIONS)) {
    sendJsonResponse(['error' => 'Invalid station ID'], 400);
}

try {
    // Generate verify hash for the station
    $verifyHash = generateVerifyHash($stationId);
    
    // Get base URL (HTTPS for production, HTTP for localhost)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . '://' . $host . dirname($_SERVER['REQUEST_URI'], 2);
    
    // Generate QR URL
    $qrUrl = $baseUrl . '/game.php?station=' . urlencode($stationId) . '&verify=' . $verifyHash;
    
    // Generate QR code data (you can use a QR library like phpqrcode)
    // For now, we'll return the URL and let frontend generate QR
    $qrData = [
        'station_id' => $stationId,
        'station_name' => STATIONS[$stationId],
        'verify_hash' => $verifyHash,
        'qr_url' => $qrUrl,
        'generated_at' => date('Y-m-d H:i:s')
    ];
    
    // Log QR generation for admin tracking
    try {
        $db = new Database();
        $db->query(
            "INSERT INTO qr_generation_logs (station_id, verify_hash, generated_by, ip_address) VALUES (?, ?, ?, ?)",
            [$stationId, $verifyHash, $_SESSION['admin_username'] ?? 'admin', $_SERVER['REMOTE_ADDR'] ?? null]
        );
    } catch (Exception $e) {
        // ignore logging errors
    }
    
    sendJsonResponse([
        'success' => true,
        'data' => $qrData
    ]);

} catch (Exception $e) {
    error_log("Generate QR error: " . $e->getMessage());
    sendJsonResponse(['error' => 'Có lỗi xảy ra khi tạo QR code'], 500);
}
?>
