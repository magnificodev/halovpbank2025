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

// Validate input
if (empty($input['full_name']) || empty($input['phone']) || empty($input['email'])) {
    sendJsonResponse(['error' => 'Tất cả các trường đều bắt buộc'], 400);
}

$fullName = trim($input['full_name']);
$phone = trim($input['phone']);
$email = trim($input['email']);

// Validate phone number (10-11 digits)
if (!preg_match('/^[0-9]{10,11}$/', $phone)) {
    sendJsonResponse(['error' => 'Số điện thoại phải có 10-11 chữ số'], 400);
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(['error' => 'Email không hợp lệ'], 400);
}

try {
    $db = new Database();

    // Check if phone already exists
    $existingUser = $db->fetch(
        "SELECT id, user_token FROM users WHERE phone = ?",
        [$phone]
    );

    if ($existingUser) {
        // User already exists, return existing token
        sendJsonResponse([
            'success' => true,
            'user_token' => $existingUser['user_token'],
            'message' => 'Chào mừng bạn quay lại!'
        ]);
    }

    // Generate unique token
    $userToken = generateUserToken();

    // Insert new user
    $db->query(
        "INSERT INTO users (full_name, phone, email, user_token) VALUES (?, ?, ?, ?)",
        [$fullName, $phone, $email, $userToken]
    );

    sendJsonResponse([
        'success' => true,
        'user_token' => $userToken,
        'message' => 'Đăng ký thành công!'
    ]);

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    sendJsonResponse(['error' => 'Có lỗi xảy ra, vui lòng thử lại'], 500);
}
?>
