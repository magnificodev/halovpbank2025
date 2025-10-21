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
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#0b0f19 0%,#1a1f3a 100%);color:#fff;min-height:100vh;transition:background .3s,color .3s}
        header{display:flex;justify-content:space-between;align-items:center;padding:20px 30px;background:rgba(21,26,44,0.95);backdrop-filter:blur(10px);border-bottom:1px solid rgba(0,255,136,0.2)}
        .logo{font-size:24px;font-weight:bold;color:#00ff88;text-shadow:0 0 10px rgba(0,255,136,0.5)}
        .user-info{display:flex;align-items:center;gap:15px}
        .user-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(45deg,#00ff88,#00cc6a);display:flex;align-items:center;justify-content:center;font-weight:bold;color:#000}
        a{color:#00ff88;text-decoration:none;transition:all 0.3s ease}
        a:hover{color:#00cc6a;text-shadow:0 0 8px rgba(0,255,136,0.6)}
        .toggle{padding:8px 14px;border-radius:20px;border:1px solid rgba(0,255,136,.4);background:rgba(0,255,136,.1);color:#00ff88;cursor:pointer}
        .logout-btn{padding:10px 18px;border-radius:24px;background:linear-gradient(45deg,#0ea5a3,#0b7a6e);border:1px solid rgba(14,165,163,.6);color:#fff;box-shadow:0 6px 14px rgba(14,165,163,.25);display:inline-block;margin-left:10px}
        .logout-btn:hover{background:linear-gradient(45deg,#0b7a6e,#075e57);border-color:#0ea5a3;color:#fff}
        .nav{padding:30px;background:rgba(21,26,44,0.3);border-bottom:1px solid rgba(255,255,255,0.1)}
        .nav a{display:inline-block;padding:12px 24px;margin-right:10px;background:rgba(0,255,136,0.1);border:1px solid rgba(0,255,136,0.3);border-radius:25px;transition:all 0.3s ease;font-weight:500}
        .nav a:hover{background:rgba(0,255,136,0.2);border-color:#00ff88;transform:translateY(-2px);box-shadow:0 5px 15px rgba(0,255,136,0.3)}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:25px;padding:30px;max-width:1400px;margin:0 auto}
        .card{background:rgba(21,26,44,0.8);border:1px solid rgba(0,255,136,0.2);border-radius:20px;padding:30px;position:relative;overflow:hidden;transition:all 0.3s ease;backdrop-filter:blur(10px)}
        .card:hover{transform:translateY(-5px);border-color:#00ff88;box-shadow:0 15px 35px rgba(0,255,136,0.2)}
        .card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#00ff88,#00cc6a)}
        .card h3{margin:0 0 15px;font-size:18px;color:#00ff88;font-weight:600}
        .card .number{font-size:36px;font-weight:bold;color:#fff;margin-bottom:10px;text-shadow:0 0 20px rgba(255,255,255,0.3)}
        .card .label{color:#ccc;font-size:14px;text-transform:uppercase;letter-spacing:1px}
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;padding:30px;max-width:1400px;margin:0 auto}
        .stat-item{background:rgba(21,26,44,0.6);border-radius:15px;padding:20px;text-align:center;border:1px solid rgba(255,255,255,0.1)}
        .stat-number{font-size:28px;font-weight:bold;color:#00ff88;margin-bottom:5px}
        .stat-label{color:#ccc;font-size:12px;text-transform:uppercase}
        /* Light mode overrides */
        body.light{background:#f8fafc;color:#1e293b}
        body.light header{background:#ffffff;border-bottom:1px solid #e2e8f0}
        body.light .logo{color:#059669;text-shadow:none}
        body.light a{color:#059669}
        body.light a:hover{color:#047857}
        body.light .nav{background:#f8fafc;border-bottom:1px solid #e2e8f0}
        body.light .nav a{background:#ffffff;border-color:#d1d5db;color:#059669}
        body.light .nav a:hover{background:#f0fdf4;border-color:#059669;color:#047857}
        body.light .card{background:#ffffff;border-color:#e2e8f0;box-shadow:0 4px 6px rgba(0,0,0,0.05)}
        body.light .card h3{color:#374151}
        body.light .card .number{color:#1e293b;text-shadow:none}
        body.light .label{color:#6b7280}
        body.light .stat-item{background:#ffffff;border-color:#e2e8f0}
        body.light .stat-number{color:#059669}
        body.light .toggle{background:#f0fdf4;border-color:#059669;color:#047857;font-weight:600}
        body.light .toggle:hover{background:#dcfce7;border-color:#047857;color:#065f46}
        body.light .logout-btn{background:#065f46;border-color:#059669;color:#fff}
        body.light .logout-btn:hover{background:#047857}
        body.light .user-info .user-avatar{background:linear-gradient(45deg,#059669,#047857);color:#ffffff}
        body.light .user-info a{color:#6b7280;font-size:12px}
        body.light .user-info a:hover{color:#374151}
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


