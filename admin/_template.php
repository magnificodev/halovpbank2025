<?php
// Common admin template with sidebar, topbar, and dark mode
function renderAdminHeader($currentPage = '') {
    $pages = [
        'index' => ['title' => 'Dashboard', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z'],
        'users' => ['title' => 'Người chơi', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        'gifts' => ['title' => 'Mã quà', 'icon' => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7'],
        'qr-codes' => ['title' => 'QR Codes', 'icon' => 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z'],
        'logs' => ['title' => 'Log quét', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z']
    ];
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($pages[$currentPage]['title'] ?? 'Admin') ?> - VPBank Admin</title>
        <!-- Heroicons removed due to CORS issues -->
        <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js" defer></script>
        <link rel="stylesheet" href="../assets/css/base.css">
        <link rel="stylesheet" href="../assets/css/admin.css">
        <style>
            :root{--accent:#10b981;--accent-600:#059669;--header-height:74px}
            *{margin:0;padding:0;box-sizing:border-box}
            body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f6f7f9;color:#111827;min-height:100vh;line-height:1.5}
            header{display:flex;justify-content:space-between;align-items:center;padding:16px 24px;background:#ffffff;border-bottom:1px solid #e5e7eb;height:var(--header-height)}
            .logo{font-size:18px;font-weight:700;color:#111827;display:flex;align-items:center;gap:8px}
            .logo svg{width:20px;height:20px;color:var(--accent);flex-shrink:0}
            .user-info{display:flex;align-items:center;gap:12px}
            a{color:var(--accent-600);text-decoration:none}
            .toggle{padding:8px;border-radius:12px;border:1px solid #e5e7eb;background:#eef2f7;color:#111827;cursor:pointer;display:flex;align-items:center;justify-content:center;width:36px;height:36px;transition:all 0.2s ease}
            .logout-btn{padding:10px 20px;border-radius:10px;background:var(--accent);border:1px solid var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;gap:6px;text-decoration:none;font-size:14px;font-weight:600;cursor:pointer;border:none;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;line-height:1.5;transition:all 0.2s ease}
            .logout-btn:hover{background:var(--accent-600);transform:translateY(-1px);box-shadow:0 4px 8px rgba(16,185,129,0.3)}
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
            .layout{display:flex;min-height:calc(100vh - var(--header-height))}
            .sidebar{width:240px;background:#ffffff;border-right:1px solid #e5e7eb;padding:16px}
            .nav-group{display:flex;flex-direction:column;gap:8px}
            .nav-link{display:flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid #e5e7eb;border-radius:10px;background:#ffffff;color:#111827;transition:all 0.2s ease}
            .nav-link:hover{background:#f3f4f6;transform:translateX(2px)}
            .nav-link.active{border-color:#059669;background:#ecfdf5;color:#065f46;transform:translateX(2px)}
            .nav-link svg{width:20px;height:20px;flex-shrink:0;color:inherit}
            .content{flex:1;padding:16px 24px}
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
            body.dark .logo svg{color:#a7f3d0}
            /* Dark mode table styles */
            body.dark table{background:#111827;border:1px solid #1f2937}
            body.dark th{background:#0f172a;color:#e5e7eb;border-color:#374151}
            body.dark td{color:#94a3b8;border-color:#374151}
            body.dark tr:hover td{background:#1f2937}
            body.dark .search-input{background:#0b1220;color:#e5e7eb;border-color:#1f2937}
            body.dark .search-input::placeholder{color:#94a3b8}
            body.dark .csv-btn{background:#059669;border-color:#059669;color:#ffffff}
            body.dark .csv-btn:hover{background:#10b981;color:#ffffff}
            body.dark .stat-card{background:#111827;border-color:#1f2937}
            body.dark .stat-number{color:#e5e7eb}
            body.dark .stat-label{color:#94a3b8}
            body.dark .pagination a{color:#e5e7eb;background:#111827;border-color:#1f2937}
            body.dark .pagination a:hover{background:#1f2937;border-color:#059669;color:#a7f3d0}
            body.dark .pagination .current{background:#059669;color:#ffffff;border-color:#059669}
            body.dark .pagination .disabled{color:#6b7280;background:#0f172a}
            body.dark .pagination .disabled:hover{background:#0f172a;border-color:#1f2937;color:#6b7280}
            body.dark .pagination-info{color:#94a3b8}
            /* Smooth transitions for all nav links */
            .nav-link{transition:all 0.3s ease}
            @media (max-width: 768px){
                header{padding:15px 20px}
                .layout{flex-direction:column}
                .sidebar{width:100%;border-right:none;border-bottom:1px solid #e5e7eb}
                .content{padding:20px}
            }
            @media (min-width: 769px){
                .sidebar{width:240px}
            }
        </style>
    </head>
    <body class="admin-page">
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
                    <?php foreach ($pages as $pageId => $pageData): ?>
                        <a class="nav-link <?= $currentPage === $pageId ? 'active' : '' ?>" href="<?= $pageId ?>.php">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $pageData['icon'] ?>"></path>
                            </svg>
                            <?= $pageData['title'] ?>
                        </a>
                    <?php endforeach; ?>
                    <a class="nav-link" href="#" onclick="showLogoutDialog(); return false;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Đăng xuất
                    </a>
                </div>
            </aside>
            <main class="content">
    <?php
}

function renderAdminFooter() {
    ?>
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
                <p class="dialog-message">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?</p>
                <div class="dialog-actions">
                    <button class="btn btn-secondary" onclick="hideLogoutDialog()">Hủy</button>
                    <button class="btn btn-danger" onclick="confirmLogout()">Đăng xuất</button>
                </div>
            </div>
        </div>

        <script>
            // Theme toggle functionality
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;

            // Load saved theme (check both keys for compatibility)
            const savedTheme = localStorage.getItem('admin-theme') || localStorage.getItem('admin_theme') || 'light';
            if (savedTheme === 'dark') {
                body.classList.add('dark');
                updateThemeIcon('sun');
            } else {
                updateThemeIcon('moon');
            }

            themeToggle.addEventListener('click', function() {
                body.classList.toggle('dark');
                const isDark = body.classList.contains('dark');
                localStorage.setItem('admin-theme', isDark ? 'dark' : 'light');
                updateThemeIcon(isDark ? 'sun' : 'moon');
            });

            function updateThemeIcon(icon) {
                const iconPath = icon === 'sun'
                    ? 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z'
                    : 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z';

                themeToggle.innerHTML = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"></path>
                </svg>`;
            }

            // Logout dialog functionality
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
        </script>
    </body>
    </html>
    <?php
}
?>
