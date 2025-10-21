<?php
require_once '_auth.php';
require_once '../api/db.php';

$db = new Database();

$totalUsers = (int)$db->fetch("SELECT COUNT(*) AS c FROM users")['c'];
$completed3Result = $db->fetch("SELECT COUNT(DISTINCT user_id) AS c FROM user_progress GROUP BY user_id HAVING COUNT(*) >= 3");
$completed3 = $completed3Result ? (int)$completed3Result['c'] : 0;
$giftIssued = (int)$db->fetch("SELECT COUNT(*) AS c FROM gift_codes WHERE user_id IS NOT NULL")['c'];
$scansTodayResult = $db->fetch("SELECT COUNT(*) AS c FROM scan_logs WHERE DATE(created_at)=CURDATE()");
$scansToday = $scansTodayResult ? (int)$scansTodayResult['c'] : 0;

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f6f7f9;color:#111827;min-height:100vh}
        header{display:flex;justify-content:space-between;align-items:center;padding:16px 24px;background:#ffffff;border-bottom:1px solid #e5e7eb}
        .logo{font-size:18px;font-weight:700;color:#111827}
        .user-info{display:flex;align-items:center;gap:12px}
        .user-avatar{width:36px;height:36px;border-radius:50%;background:#10b981;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff}
        a{color:#059669;text-decoration:none}
        a:hover{color:#10b981}
        .toggle{padding:8px 12px;border-radius:12px;border:1px solid #e5e7eb;background:#eef2f7;color:#111827;cursor:pointer}
        .logout-btn{padding:8px 14px;border-radius:10px;background:#059669;border:1px solid #059669;color:#fff;display:inline-block;margin-left:10px}
        .logout-btn:hover{background:#10b981}
        .nav{padding:16px 24px;background:transparent;border-bottom:1px solid #e5e7eb}
        .nav a{display:inline-block;padding:10px 14px;margin-right:8px;background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;transition:background .2s}
        .nav a:hover{background:#f3f4f6}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;padding:16px 24px;max-width:1200px;margin:0 auto}
        .card{background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;transition:box-shadow .2s}
        .card:hover{box-shadow:0 2px 8px rgba(17,24,39,.08)}
        .card h3{margin:0 0 8px;font-size:14px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.4px}
        .card .number{font-size:28px;font-weight:700;color:#111827;margin-bottom:4px}
        .card .label{color:#6b7280;font-size:12px;text-transform:uppercase}
        /* Dark mode overrides */
        body.dark{background:#0f172a;color:#e5e7eb}
        body.dark header{background:#0b1220;border-bottom:1px solid #1f2937}
        body.dark .logo{color:#a7f3d0}
        body.dark a{color:#a7f3d0}
        body.dark a:hover{color:#34d399}
        body.dark .toggle{background:#0b1220;border-color:#1f2937;color:#e5e7eb}
        body.dark .logout-btn{background:#065f46;border-color:#065f46}
        body.dark .logout-btn:hover{background:#059669}
        body.dark .nav{border-bottom:1px solid #1f2937}
        body.dark .nav a{background:#0b1220;border-color:#1f2937;color:#e5e7eb}
        body.dark .nav a:hover{background:#111827}
        body.dark .card{background:#111827;border-color:#1f2937}
        body.dark .card h3{color:#94a3b8}
        body.dark .card .number{color:#e5e7eb}
        body.dark .card .label{color:#94a3b8}
        @media (max-width: 768px){
            header{padding:15px 20px}
            .nav{padding:20px}
            .grid{padding:20px;grid-template-columns:1fr}
            .card{padding:20px}
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">🎮 VPBank Admin</div>
        <div class="user-info">
            <button id="themeToggle" class="toggle">🌙 Dark</button>
            <div class="user-avatar">A</div>
            <div>
                <div style="font-size:14px;color:#00ff88"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                <a href="logout.php" class="logout-btn">Đăng xuất</a>
            </div>
        </div>
    </header>
    <div class="nav">
        <a href="users.php">👥 Người chơi</a>
        <a href="gifts.php">🎁 Mã quà</a>
        <a href="logs.php">📊 Log quét</a>
    </div>
    <div class="grid">
        <div class="card">
            <h3>👥 Tổng người chơi</h3>
            <div class="number"><?php echo number_format($totalUsers); ?></div>
            <div class="label">Đã đăng ký</div>
        </div>
        <div class="card">
            <h3>✅ Đủ điều kiện</h3>
            <div class="number"><?php echo number_format($completed3); ?></div>
            <div class="label">Hoàn thành ≥3 trạm</div>
        </div>
        <div class="card">
            <h3>🎁 Quà đã phát</h3>
            <div class="number"><?php echo number_format($giftIssued); ?></div>
            <div class="label">Mã quà đã claim</div>
        </div>
        <div class="card">
            <h3>📱 Quét hôm nay</h3>
            <div class="number"><?php echo number_format($scansToday); ?></div>
            <div class="label">Lượt quét QR</div>
        </div>
    </div>
    <script>
        (function(){
            const key='admin_theme';
            const saved=localStorage.getItem(key)||'dark';
            if(saved==='light') {
                document.body.classList.add('light');
                const mobileFrame = document.querySelector('.mobile-frame');
                if (mobileFrame) mobileFrame.classList.add('light');
            }
            const btn=document.getElementById('themeToggle');
            function render(){btn.textContent=document.body.classList.contains('light')?'☀️ Light':'🌙 Dark';}
            render();
            btn.addEventListener('click',()=>{
                document.body.classList.toggle('light');
                const mobileFrame = document.querySelector('.mobile-frame');
                if (mobileFrame) mobileFrame.classList.toggle('light');
                localStorage.setItem(key, document.body.classList.contains('light')?'light':'dark');
                render();
            });
        })();
    </script>
</body>
</html>


