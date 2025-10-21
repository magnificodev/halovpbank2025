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
        :root{--bg:#f6f7f9;--surface:#ffffff;--text:#111827;--muted:#6b7280;--border:#e5e7eb;--accent:#10b981;--accent-600:#059669}
        body{background:var(--bg);color:var(--text);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
        .login{width:360px;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:24px;box-shadow:0 2px 12px rgba(17,24,39,.06)}
        h2{margin:0 0 16px;font-size:18px;font-weight:700;color:var(--text)}
        .error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px;border-radius:8px;margin-bottom:12px;font-size:14px}
        .form-group{margin-bottom:12px}
        input{width:100%;padding:12px 14px;border:1px solid var(--border);border-radius:10px;background:#fff;color:var(--text)}
        input:focus{outline:none;border-color:var(--accent-600);box-shadow:0 0 0 3px rgba(16,185,129,.15)}
        button{width:100%;padding:12px 14px;border:1px solid var(--accent);border-radius:10px;background:var(--accent);color:#fff;font-weight:700;cursor:pointer}
        button:hover{background:var(--accent-600)}
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


