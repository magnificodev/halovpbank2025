<?php
require_once 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

$userToken = $input['user_token'] ?? null;
$stationId = $input['station_id'] ?? null;
$verifyHash = $input['verify_hash'] ?? null;

if (!$userToken || !$stationId || !$verifyHash) {
    sendJsonResponse(['error' => 'Thiếu thông tin cần thiết'], 400);
}

// Validate station ID
if (!array_key_exists($stationId, STATIONS)) {
    sendJsonResponse(['error' => ERRORS['INVALID_STATION']], 400);
}

// Validate verify hash
if (!validateVerifyHash($stationId, $verifyHash)) {
    sendJsonResponse(['error' => ERRORS['INVALID_VERIFY_HASH']], 400);
}

try {
    $db = new Database();

    // Get user
    $user = $db->fetch(
        "SELECT id FROM users WHERE user_token = ?",
        [$userToken]
    );

    if (!$user) {
        sendJsonResponse(['error' => ERRORS['USER_NOT_FOUND']], 404);
    }

    // Check if station already completed
    $existingProgress = $db->fetch(
        "SELECT id FROM user_progress WHERE user_id = ? AND station_id = ?",
        [$user['id'], $stationId]
    );

    if ($existingProgress) {
        sendJsonResponse(['error' => ERRORS['STATION_ALREADY_COMPLETED']], 400);
    }

    // Mark station as completed
    $db->query(
        "INSERT INTO user_progress (user_id, station_id) VALUES (?, ?)",
        [$user['id'], $stationId]
    );

    // Log scan event for auditing
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
    try {
        $db->query(
            "INSERT INTO scan_logs (user_id, station_id, ip_address, user_agent) VALUES (?, ?, ?, ?)",
            [$user['id'], $stationId, $ip, substr((string)$ua, 0, 255)]
        );
    } catch (Exception $e) {
        // ignore logging errors to not block user flow
    }

    // Get updated progress
    $completedStations = $db->fetchAll(
        "SELECT station_id FROM user_progress WHERE user_id = ?",
        [$user['id']]
    );

    $completedStationIds = array_column($completedStations, 'station_id');

    // Check if user can claim reward (new logic: >= 2 in first 4 + HALLO_SHOP)
    $firstFourStations = ['HALLO_GLOW', 'HALLO_SOLUTION', 'HALLO_SUPER_SINH_LOI', 'HALLO_WIN'];
    $requiredShopStation = 'HALLO_SHOP';

    // Count completed stations in first 4
    $completedFirstFour = 0;
    foreach ($firstFourStations as $stationId) {
        if (in_array($stationId, $completedStationIds)) {
            $completedFirstFour++;
        }
    }

    // Check if HALLO_SHOP is completed
    $isShopCompleted = in_array($requiredShopStation, $completedStationIds);

    // New logic: >= 2 in first 4 stations AND HALLO_SHOP must be completed
    $canClaimReward = $completedFirstFour >= 2 && $isShopCompleted;

    sendJsonResponse([
        'success' => true,
        'message' => 'Hoàn thành trạm ' . STATIONS[$stationId] . '!',
        'station_id' => $stationId,
        'station_name' => STATIONS[$stationId],
        'completed_count' => $completedCount,
        'can_claim_reward' => $canClaimReward
    ]);

} catch (Exception $e) {
    error_log("Check station error: " . $e->getMessage());
    sendJsonResponse(['error' => 'Có lỗi xảy ra, vui lòng thử lại'], 500);
}
?>
