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

if (!$userToken) {
    sendJsonResponse(['error' => 'Token không được cung cấp'], 400);
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

    // Check if user already claimed reward
    $existingReward = $db->fetch(
        "SELECT code FROM gift_codes WHERE user_id = ? AND claimed_at IS NOT NULL",
        [$user['id']]
    );

    if ($existingReward) {
        sendJsonResponse(['error' => ERRORS['REWARD_ALREADY_CLAIMED']], 400);
    }

    // Check if user completed enough stations (new logic: >= 2 in first 4 + HALLO_SHOP)
    $completedStations = $db->fetchAll(
        "SELECT station_id FROM user_progress WHERE user_id = ?",
        [$user['id']]
    );

    $completedStationIds = array_column($completedStations, 'station_id');

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
    if (!($completedFirstFour >= 2 && $isShopCompleted)) {
        sendJsonResponse(['error' => ERRORS['INSUFFICIENT_STATIONS']], 400);
    }

    // Get an available gift code
    $availableCode = $db->fetch(
        "SELECT id, code FROM gift_codes WHERE user_id IS NULL ORDER BY RAND() LIMIT 1"
    );

    if (!$availableCode) {
        sendJsonResponse(['error' => 'Hết mã quà tặng'], 400);
    }

    // Assign gift code to user
    $db->query(
        "UPDATE gift_codes SET user_id = ?, claimed_at = NOW() WHERE id = ?",
        [$user['id'], $availableCode['id']]
    );

    sendJsonResponse([
        'success' => true,
        'message' => 'Chúc mừng bạn đã nhận được quà!',
        'gift_code' => $availableCode['code']
    ]);

} catch (Exception $e) {
    error_log("Claim reward error: " . $e->getMessage());
    sendJsonResponse(['error' => 'Có lỗi xảy ra, vui lòng thử lại'], 500);
}
?>
