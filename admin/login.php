<?php
require_once '../config.php';
session_start();

// If already logged in, go to dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $hash = hash('sha256', $password . GAME_SECRET_KEY);
    if ($username === ADMIN_USERNAME && hash_equals(ADMIN_PASSWORD_HASH, $hash)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = ADMIN_USERNAME;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Sai tài khoản hoặc mật khẩu';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body{background:#0b0f19;color:#fff;font-family:Arial,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
        .login{width:360px;background:#151a2c;border-radius:12px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.4)}
        h2{margin:0 0 16px}
        .error{background:rgba(255,71,87,.2);border:1px solid #ff4757;color:#ff4757;padding:10px;border-radius:8px;margin-bottom:12px}
        .form-group{margin-bottom:12px}
        input{width:100%;padding:12px 14px;border:none;border-radius:8px}
        button{width:100%;padding:12px 14px;border:none;border-radius:8px;background:#00cc6a;color:#fff;font-weight:bold;cursor:pointer}
    </style>
    <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'none'">
</head>
<body>
    <div class="login">
        <h2>Admin Login</h2>
        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>


