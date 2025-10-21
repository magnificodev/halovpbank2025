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
        .login{width:360px;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:24px 24px 20px;box-shadow:none}
        .brand{margin:0 0 4px;font-size:14px;color:var(--muted);text-transform:uppercase;letter-spacing:.4px}
        h2{margin:0 0 12px;font-size:20px;line-height:1.2;font-weight:700;color:var(--text)}
        .hint{margin:0 0 16px;font-size:12px;color:var(--muted)}
        .error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px;border-radius:8px;margin-bottom:12px;font-size:13px}
        .form-group{margin-bottom:12px}
        label{display:block;margin:0 0 6px;font-size:12px;color:var(--muted);font-weight:600}
        /* Increase specificity to override global styles */
        .login .form-group input{width:100%;padding:12px;border:1px solid var(--border);border-radius:8px;background:#ffffff;color:var(--text);font-size:14px;appearance:none;box-shadow:none !important}
        .login .form-group input:focus{outline:none;border-color:var(--accent-600);box-shadow:0 0 0 2px rgba(16,185,129,.15) !important}
        button{width:100%;padding:12px 14px;border:none;border-radius:8px;background:var(--accent);color:#fff;font-weight:700;cursor:pointer;transition:background .2s,opacity .2s;box-shadow:none}
        button:hover{background:var(--accent-600)}
        button:active{opacity:.9}
        /* Dark mode */
        body.dark{background:#0f172a;color:#e5e7eb}
        body.dark .login{background:#0b1220;border-color:#1f2937;box-shadow:none}
        body.dark .brand,body.dark label,body.dark .hint{color:#94a3b8}
        body.dark h2{color:#e5e7eb}
        body.dark .login .form-group input{background:#111827;border-color:#1f2937;color:#e5e7eb;box-shadow:none !important}
        body.dark input::placeholder{color:#94a3b8}
        body.dark button{background:#059669;border:none}
        body.dark button:hover{background:#10b981}
    </style>
    <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'none'">
</head>
<body>
    <div class="login">
        <div class="brand">VPBank</div>
        <h2>Admin Login</h2>
        <p class="hint">Đăng nhập để truy cập bảng điều khiển.</p>
        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" placeholder="Nhập username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>


