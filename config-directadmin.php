<?php
// Database configuration for DirectAdmin (with vpbankgame_ prefix)
define('DB_HOST', 'localhost');
define('DB_NAME', 'vpbankgame_halovpbank');
define('DB_USER', 'vpbankgame_halovpbank');
define('DB_PASS', 'VpBank2025!@#');

// Game configuration
define('GAME_SECRET_KEY', 'vpbank_solution_day_2025_secret_key');
define('REQUIRED_STATIONS', 3); // Minimum stations to complete for reward
define('GIFT_CODE_LENGTH', 8);

// Admin (single account)
define('ADMIN_USERNAME', 'admin');
// Store a SHA256 hash of the password combined with secret key. Default password: Admin@2025
// Change this by regenerating hash: hash('sha256', 'YOUR_PASSWORD' . GAME_SECRET_KEY)
define('ADMIN_PASSWORD_HASH', hash('sha256', 'Admin@2025' . GAME_SECRET_KEY));

// Station IDs
define('STATIONS', [
    'HALLO_GLOW' => 'HALLO GLOW',
    'HALLO_SOLUTION' => 'HALLO SOLUTION',
    'HALLO_SUPER_SINH_LOI' => 'HALLO SUPER SINH LỜI PREMIER',
    'HALLO_SHOP' => 'HALLO SHOP',
    'HALLO_WIN' => 'HALLO WIN'
]);

// Error messages
define('ERRORS', [
    'INVALID_TOKEN' => 'Token không hợp lệ',
    'STATION_ALREADY_COMPLETED' => 'Trạm này đã được hoàn thành',
    'INVALID_STATION' => 'Trạm không hợp lệ',
    'INVALID_VERIFY_HASH' => 'Mã xác thực không hợp lệ',
    'USER_NOT_FOUND' => 'Không tìm thấy người dùng',
    'REWARD_ALREADY_CLAIMED' => 'Bạn đã nhận quà rồi',
    'INSUFFICIENT_STATIONS' => 'Chưa hoàn thành đủ trạm để nhận quà'
]);
?>
