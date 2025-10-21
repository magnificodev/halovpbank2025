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
    <script src="https://unpkg.com/heroicons@2.0.18/24/outline/index.js" type="module"></script>
    <style>
        :root{--accent:#10b981;--accent-600:#059669}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f6f7f9;color:#111827;min-height:100vh}
        header{display:flex;justify-content:space-between;align-items:center;padding:16px 24px;background:#ffffff;border-bottom:1px solid #e5e7eb}
        .logo{font-size:18px;font-weight:700;color:#111827}
        .user-info{display:flex;align-items:center;gap:12px}
        a{color:var(--accent-600);text-decoration:none}
        a:hover{color:var(--accent)}
        .toggle{padding:8px;border-radius:12px;border:1px solid #e5e7eb;background:#eef2f7;color:#111827;cursor:pointer;display:flex;align-items:center;justify-content:center;width:36px;height:36px}
        .logout-btn{padding:10px 20px;border-radius:10px;background:var(--accent);border:1px solid var(--accent);color:#fff;display:flex;align-items:center;gap:6px;text-decoration:none;font-size:14px;font-weight:600;cursor:pointer;border:none;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}
        .logout-btn:hover{background:var(--accent-600)}
        /* Logout Dialog */
        .logout-dialog{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center}
        .logout-dialog.show{display:flex}
        .dialog-content{background:#fff;border-radius:12px;padding:24px;max-width:400px;width:90%;box-shadow:0 10px 25px rgba(0,0,0,0.2)}
        .dialog-header{display:flex;align-items:center;gap:12px;margin-bottom:16px}
        .dialog-icon{width:24px;height:24px;color:#ef4444}
        .dialog-title{font-size:18px;font-weight:600;color:#111827}
        .dialog-message{color:#6b7280;margin-bottom:24px;line-height:1.5}
        .dialog-actions{display:flex;gap:12px;justify-content:flex-end}
        .btn{padding:8px 16px;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;border:none;transition:all 0.2s}
        .btn-secondary{background:#f3f4f6;color:#374151;border:1px solid #d1d5db}
        .btn-secondary:hover{background:#e5e7eb}
        .btn-danger{background:#ef4444;color:#fff}
        .btn-danger:hover{background:#dc2626}
        /* Layout with sidebar */
        .layout{display:flex;min-height:calc(100vh - 58px)}
        .sidebar{width:220px;background:#ffffff;border-right:1px solid #e5e7eb;padding:16px}
        .nav-group{display:flex;flex-direction:column;gap:8px}
        .nav-link{display:flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid #e5e7eb;border-radius:10px;background:#ffffff;color:#111827}
        .nav-link:hover{background:#f3f4f6}
        .nav-link.active{border-color:#059669;background:#ecfdf5;color:#065f46}
        .nav-link svg{width:20px;height:20px;flex-shrink:0;color:inherit}
        .card h3 svg{width:16px;height:16px;color:#6b7280;margin-right:6px}
        .logo{display:flex;align-items:center;gap:8px}
        .logo svg{width:20px;height:20px;color:#059669;flex-shrink:0}
        .content{flex:1;padding:16px 24px}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;max-width:1200px}
        .card{background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;transition:box-shadow .2s}
        .card:hover{box-shadow:0 2px 8px rgba(17,24,39,.08)}
        .card h3{margin:0 0 8px;font-size:14px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;gap:6px}
        .card h3 svg{margin-right:0}
        .card .number{font-size:28px;font-weight:700;color:#111827;margin-bottom:4px}
        .card .label{color:#6b7280;font-size:12px;text-transform:uppercase}
        /* Dark mode overrides */
        body.dark{background:#0f172a;color:#e5e7eb}
        body.dark header{background:#0b1220;border-bottom:1px solid #1f2937}
        body.dark .logo{color:#a7f3d0}
        body.dark a{color:#a7f3d0}
        body.dark a:hover{color:#34d399}
        body.dark .toggle{background:#0b1220;border-color:#1f2937;color:#e5e7eb}
        body.dark .logout-btn{background:var(--accent-600);border-color:var(--accent-600)}
        body.dark .logout-btn:hover{background:var(--accent)}
        body.dark .dialog-content{background:#111827;border:1px solid #1f2937}
        body.dark .dialog-title{color:#e5e7eb}
        body.dark .dialog-message{color:#94a3b8}
        body.dark .btn-secondary{background:#1f2937;color:#e5e7eb;border-color:#374151}
        body.dark .btn-secondary:hover{background:#374151}
        body.dark .sidebar{background:#0b1220;border-right:1px solid #1f2937}
        body.dark .nav-link{background:#0b1220;border-color:#1f2937;color:#e5e7eb}
        body.dark .nav-link:hover{background:#111827}
        body.dark .nav-link.active{background:#0f291f;border-color:#065f46;color:#a7f3d0}
        body.dark .card h3 svg{color:#94a3b8}
        body.dark .logo svg{color:#a7f3d0}
        body.dark .card{background:#111827;border-color:#1f2937}
        body.dark .card h3{color:#94a3b8}
        body.dark .card .number{color:#e5e7eb}
        body.dark .card .label{color:#94a3b8}
        @media (max-width: 768px){
            header{padding:15px 20px}
            .layout{flex-direction:column}
            .sidebar{width:100%;border-right:none;border-bottom:1px solid #e5e7eb}
            .content{padding:20px}
            .grid{grid-template-columns:1fr}
            .card{padding:20px}
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            VPBank Admin
        </div>
        <div class="user-info">
            <button id="themeToggle" class="toggle">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
            </button>
            <button class="logout-btn" onclick="showLogoutDialog()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Đăng xuất
            </button>
        </div>
    </header>
    <div class="layout">
        <aside class="sidebar">
            <div class="nav-group">
                <a class="nav-link active" href="index.php">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                    </svg>
                    Dashboard
                </a>
                <a class="nav-link" href="users.php">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Người chơi
                </a>
                <a class="nav-link" href="gifts.php">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                    </svg>
                    Mã quà
                </a>
                <a class="nav-link" href="logs.php">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Log quét
                </a>
                <a class="nav-link" href="logout.php">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Đăng xuất
                </a>
            </div>
        </aside>
        <main class="content">
            <div class="grid">
        <div class="card">
            <h3>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Tổng người chơi
            </h3>
            <div class="number"><?php echo number_format($totalUsers); ?></div>
            <div class="label">Đã đăng ký</div>
        </div>
        <div class="card">
            <h3>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Đủ điều kiện
            </h3>
            <div class="number"><?php echo number_format($completed3); ?></div>
            <div class="label">Hoàn thành ≥3 trạm</div>
        </div>
        <div class="card">
            <h3>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                </svg>
                Quà đã phát
            </h3>
            <div class="number"><?php echo number_format($giftIssued); ?></div>
            <div class="label">Mã quà đã claim</div>
        </div>
        <div class="card">
            <h3>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Quét hôm nay
            </h3>
            <div class="number"><?php echo number_format($scansToday); ?></div>
            <div class="label">Lượt quét QR</div>
        </div>
            </div>
        </main>
    </div>

    <!-- Logout Dialog -->
    <div id="logoutDialog" class="logout-dialog">
        <div class="dialog-content">
            <div class="dialog-header">
                <svg class="dialog-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="dialog-title">Xác nhận đăng xuất</h3>
            </div>
            <p class="dialog-message">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống quản trị không?</p>
            <div class="dialog-actions">
                <button class="btn btn-secondary" onclick="hideLogoutDialog()">Hủy</button>
                <button class="btn btn-danger" onclick="confirmLogout()">Đăng xuất</button>
            </div>
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
            function render(){
                const isLight = document.body.classList.contains('light');
                btn.innerHTML = isLight ?
                    '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>' :
                    '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>';
            }
            render();
            btn.addEventListener('click',()=>{
                document.body.classList.toggle('light');
                const mobileFrame = document.querySelector('.mobile-frame');
                if (mobileFrame) mobileFrame.classList.toggle('light');
                localStorage.setItem(key, document.body.classList.contains('light')?'light':'dark');
                render();
            });
        })();

        // Logout Dialog Functions
        function showLogoutDialog() {
            document.getElementById('logoutDialog').classList.add('show');
        }

        function hideLogoutDialog() {
            document.getElementById('logoutDialog').classList.remove('show');
        }

        function confirmLogout() {
            window.location.href = 'logout.php';
        }

        // Close dialog when clicking outside
        document.getElementById('logoutDialog').addEventListener('click', function(e) {
            if (e.target === this) {
                hideLogoutDialog();
            }
        });

        // Close dialog with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideLogoutDialog();
            }
        });
    </script>
</body>
</html>


