<?php
require_once 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$token = $_GET['token'] ?? null;

if (!$token) {
    sendJsonResponse(['error' => 'Thiếu token'], 400);
}

try {
    $db = new Database();
    
    // Get user by token
    $user = $db->fetch("SELECT id, user_token, full_name, phone, email, created_at FROM users WHERE user_token = ?", [$token]);
    
    if (!$user) {
        sendJsonResponse(['error' => 'Không tìm thấy người dùng'], 404);
    }
    
    sendJsonResponse([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'phone' => $user['phone'],
            'email' => $user['email'],
            'created_at' => $user['created_at']
        ]
    ]);

} catch (Exception $e) {
    error_log("Get user error: " . $e->getMessage());
    sendJsonResponse(['error' => 'Có lỗi xảy ra khi lấy thông tin người dùng'], 500);
}
?>
