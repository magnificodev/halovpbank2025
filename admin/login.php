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
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{--bg:#f6f7f9;--surface:#ffffff;--text:#111827;--muted:#6b7280;--border:#e5e7eb;--accent:#10b981;--accent-600:#059669}
        body{background:var(--bg);color:var(--text);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
        .login{width:360px;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:24px 24px 20px;box-shadow:none}
        .brand{display:none}
        h2{margin:0 0 12px;font-size:22px;line-height:1.2;font-weight:800;color:var(--text)}
        .hint{margin:0 0 16px;font-size:12px;color:var(--muted);opacity:.9}
        .error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px;border-radius:8px;margin-bottom:12px;font-size:13px}
        .form-group{margin-bottom:12px}
        label{display:block;margin:0 0 6px;font-size:12px;color:var(--muted);font-weight:600}
        /* Increase specificity to override global styles */
        .login .form-group input{width:100%;padding:12px 12px 12px 40px;border:1px solid var(--border);border-radius:10px;background:#ffffff;color:var(--text);font-size:14px;appearance:none;box-shadow:none !important;text-align:left;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
        .login .form-group input::placeholder{font-size:13px;text-align:left;font-style:italic;font-weight:400;color:var(--muted);font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
        .login .form-group input:focus{outline:none;border-color:#22c55e;box-shadow:0 0 0 2px rgba(34,197,94,.25) !important}
        button{width:100%;padding:12px 14px;border:none;border-radius:10px;background:#22c55e;color:#fff;font-weight:700;cursor:pointer;transition:background .2s,opacity .2s,box-shadow .2s;box-shadow:0 1px 1px rgba(0,0,0,.02)}
        button:hover{background:#16a34a}
        button:focus-visible{outline:none;box-shadow:0 0 0 2px rgba(34,197,94,.35)}
        button:active{opacity:.9}
        /* Dark mode */
        body.dark{background:#0f172a;color:#e5e7eb}
        body.dark .login{background:#0b1220;border-color:#1f2937;box-shadow:none}
        body.dark .brand,body.dark label,body.dark .hint{color:#94a3b8}
        body.dark .login .form-group input::placeholder{color:#94a3b8}
        body.dark h2{color:#e5e7eb}
        body.dark .login .form-group input{background:#111827;border-color:#1f2937;color:#e5e7eb;box-shadow:none !important;text-align:left;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
        body.dark input::placeholder{color:#94a3b8;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
        body.dark button{background:#22c55e;border:none}
        body.dark button:hover{background:#16a34a}
    </style>
    <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'none'">
</head>
<body>
    <div class="login">
        <div class="brand">VPBank</div>
        <h2 class="font-extrabold text-slate-900 dark:text-slate-100">Đăng nhập quản trị</h2>
        <p class="hint text-slate-500 dark:text-slate-400">Đăng nhập để truy cập bảng điều khiển.</p>
        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <!-- user icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a4 4 0 100 8 4 4 0 000-8zM2 16a6 6 0 1112 0v1a1 1 0 01-1 1H3a1 1 0 01-1-1v-1z" clip-rule="evenodd"/></svg>
                    </span>
                    <input id="username" type="text" name="username" placeholder="Nhập tên đăng nhập" required>
                </div>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <!-- lock icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1a5 5 0 00-5 5v3H6a2 2 0 00-2 2v9a2 2 0 002 2h12a2 2 0 002-2V11a2 2 0 00-2-2h-1V6a5 5 0 00-5-5zm3 8H9V6a3 3 0 116 0v3z"/></svg>
                    </span>
                    <input id="password" type="password" name="password" placeholder="Nhập mật khẩu" required>
                </div>
            </div>
            <button type="submit" id="loginSubmit" class="group relative">
                <span class="inline-flex items-center justify-center gap-2">
                    <span class="sr-only group-[.is-loading]:not-sr-only">Đang đăng nhập...</span>
                    <span class="group-[.is-loading]:hidden">Đăng nhập</span>
                    <svg class="hidden group-[.is-loading]:inline-block animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle><path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4"></path></svg>
                </span>
            </button>
        </form>
    </div>
    <script>
        (function(){
            const form=document.querySelector('form');
            const btn=document.getElementById('loginSubmit');
            if(form && btn){
                form.addEventListener('submit',()=>{btn.classList.add('is-loading');btn.disabled=true;});
            }
        })();
    </script>
</body>
</html>


