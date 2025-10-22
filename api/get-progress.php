<?php
require_once 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$userToken = $_GET['token'] ?? $_POST['token'] ?? null;

if (!$userToken) {
    sendJsonResponse(['error' => 'Token không được cung cấp'], 400);
}

try {
    $db = new Database();

    // Get user info
    $user = $db->fetch(
        "SELECT id, full_name, phone, email FROM users WHERE user_token = ?",
        [$userToken]
    );

    if (!$user) {
        sendJsonResponse(['error' => ERRORS['USER_NOT_FOUND']], 404);
    }

    // Get completed stations
    $completedStations = $db->fetchAll(
        "SELECT station_id FROM user_progress WHERE user_id = ?",
        [$user['id']]
    );

    $completedStationIds = array_column($completedStations, 'station_id');

    // Build stations status
    $stations = [];
    foreach (STATIONS as $stationId => $stationName) {
        $stations[] = [
            'id' => $stationId,
            'name' => $stationName,
            'completed' => in_array($stationId, $completedStationIds)
        ];
    }

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

    // Calculate total completed count
    $completedCount = count($completedStationIds);

    // Check if user already claimed reward (check user_progress table)
    $claimedReward = $db->fetch(
        "SELECT station_id FROM user_progress WHERE user_id = ? AND station_id = 'CLAIMED_REWARD'",
        [$user['id']]
    );

    sendJsonResponse([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'phone' => $user['phone'],
            'email' => $user['email']
        ],
        'stations' => $stations,
        'completed_count' => $completedCount,
        'required_count' => REQUIRED_STATIONS,
        'can_claim_reward' => $canClaimReward,
        'has_claimed_reward' => !empty($claimedReward),
        'reward_code' => null // No gift code needed anymore
    ]);

} catch (Exception $e) {
    error_log("Get progress error: " . $e->getMessage());
    sendJsonResponse(['error' => 'Có lỗi xảy ra, vui lòng thử lại'], 500);
}
?>
