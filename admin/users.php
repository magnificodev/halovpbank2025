<?php
require_once '_auth.php';
require_once '../api/db.php';
$db = new Database();

$q = trim($_GET['q'] ?? '');
$export = $_GET['export'] ?? '';

if ($export === 'csv') {
    // Export to CSV
    if (ob_get_length()) { ob_end_clean(); }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="users_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    // UTF-8 BOM for Excel
    echo "\xEF\xBB\xBF";
    fputcsv($output, ['ID', 'Họ tên', 'Số điện thoại', 'Email', 'Ngày tạo']);

    if ($q) {
        $users = $db->fetchAll("SELECT * FROM users WHERE full_name LIKE ? OR phone LIKE ? OR email LIKE ? ORDER BY id DESC", ["%$q%", "%$q%", "%$q%"]);
    } else {
        $users = $db->fetchAll("SELECT * FROM users ORDER BY id DESC");
    }

    foreach ($users as $user) {
        // Force Excel to keep leading zeros by using formula style
        $excelPhone = '="' . $user['phone'] . '"';
        fputcsv($output, [
            $user['id'],
            $user['full_name'],
            $excelPhone,
            $user['email'],
            $user['created_at']
        ]);
    }
    fclose($output);
    exit;
}

if ($q) {
    $users = $db->fetchAll("SELECT * FROM users WHERE full_name LIKE ? OR phone LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT 200", ["%$q%", "%$q%", "%$q%"]);
} else {
    $users = $db->fetchAll("SELECT * FROM users ORDER BY id DESC LIMIT 200");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Người chơi</title>
    <style>
        :root{--accent:#10b981;--accent-600:#059669}
        *{margin:0;padding:0;box-sizing:border-box}
         body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f6f7f9;color:#111827;min-height:100vh;line-height:1.5}
        .logo{display:flex;align-items:center;gap:8px;font-size:18px;font-weight:700;color:#111827}
        .logo svg{width:20px;height:20px;color:var(--accent);flex-shrink:0}
        th{background:#f9fafb;color:#374151;padding:12px;text-align:left;font-weight:600;text-transform:uppercase;font-size:12px;letter-spacing:.4px}
        th svg{width:16px;height:16px;color:#6b7280;margin-right:6px;vertical-align:middle}
        header{display:flex;justify-content:space-between;align-items:center;padding:16px 24px;background:#ffffff;border-bottom:1px solid #e5e7eb}
        .user-info{display:flex;align-items:center;gap:12px}
        a{color:var(--accent-600);text-decoration:none}
        a:hover{color:var(--accent)}
         .logout-btn{padding:10px 20px;border-radius:10px;background:var(--accent);border:1px solid var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;gap:6px;text-decoration:none;font-size:14px;font-weight:600;cursor:pointer;border:none;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;line-height:1.5}
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
        .content{flex:1;padding:16px 24px}
        .wrap{padding:0;max-width:none;margin:0}
         .search-box{margin-bottom:20px;display:flex;gap:15px;align-items:center}
         .search-container{position:relative;display:flex;align-items:center}
         .search-input{padding:10px 14px 10px 40px;border:1px solid #e5e7eb;border-radius:10px;background:#ffffff;color:#111827;width:300px;font-size:14px}
         .search-input:focus{outline:none;border-color:#059669;box-shadow:0 0 0 3px rgba(16,185,129,.15)}
         .search-input::placeholder{color:#9ca3af}
         .search-icon{position:absolute;left:12px;width:16px;height:16px;color:#6b7280;pointer-events:none}
         .csv-btn{padding:10px 16px;background:#10b981;border:1px solid #10b981;border-radius:10px;color:#ffffff;font-weight:600;cursor:pointer;transition:background .2s ease;display:inline-flex;align-items:center;justify-content:center;text-align:center;width:160px;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;font-size:14px;letter-spacing:0;line-height:1}
         .csv-btn:hover{background:#059669}
         .search-status{color:#6b7280;font-size:12px;margin-top:8px;text-align:left}
        table{width:100%;border-collapse:collapse;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb}
        th{background:#f9fafb;color:#374151;padding:12px;text-align:left;font-weight:600;text-transform:uppercase;font-size:12px;letter-spacing:.4px}
        td{padding:12px;border-bottom:1px solid #f1f5f9;transition:background .2s ease}
        tr:hover td{background:#f9fafb}
        .user-id{color:#059669;font-weight:600}
        .user-name{font-weight:500}
        .user-phone{color:#ccc;font-family:monospace}
        .user-email{color:#999;font-size:13px}
        .user-date{color:#999;font-size:12px}
        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:16px}
        .stat-card{background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;text-align:center}
        .stat-card h3{margin:0 0 8px;font-size:14px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;justify-content:center;gap:6px}
        .stat-card h3 svg{margin-right:0}
        .stat-number{font-size:20px;font-weight:700;color:#111827;margin-bottom:4px}
        .stat-label{color:#6b7280;font-size:12px;text-transform:uppercase}
        /* Light mode */
        body.light{background:#f8fafc;color:#1e293b}
        body.light header{background:#ffffff;border-bottom:1px solid #e2e8f0}
        body.light .logo{color:#059669;text-shadow:none}
        body.light a{color:#059669;background:#f8fafc;border-color:#d1d5db}
        body.light a:hover{color:#047857;background:#f0fdf4;border-color:#059669}
        body.light .logout-btn{background:#065f46;border-color:#059669;color:#ffffff !important}
        body.light .logout-btn:hover{background:#047857;color:#ffffff !important}
        body.light table{background:#ffffff;border-color:#e2e8f0}
        body.light th{background:#f1f5f9;color:#374151}
        body.light td{border-bottom:1px solid #e5e7eb}
        body.light .user-id{color:#059669}
        body.light .user-name{color:#1e293b}
        body.light .user-phone{color:#6b7280}
        body.light .user-email{color:#6b7280}
        body.light .user-date{color:#6b7280}
        body.light .stat-card{background:#ffffff;border-color:#e2e8f0}
        body.light .stat-number{color:#059669}
        body.light .stat-label{color:#6b7280}
         body.light .search-input{background:#ffffff;color:#1e293b;border-color:#d1d5db}
         body.light .search-input:focus{border-color:#059669;box-shadow:0 0 0 3px rgba(5,150,105,0.1)}
         body.light .search-input::placeholder{color:#9ca3af}
         body.light .csv-btn{background:linear-gradient(45deg,#059669,#047857);color:#ffffff}
         body.light .csv-btn:hover{background:linear-gradient(45deg,#047857,#065f46)}
        /* Dark mode overrides */
        body.dark{background:#0f172a;color:#e5e7eb}
        body.dark header{background:#0b1220;border-bottom:1px solid #1f2937}
        body.dark .logo{color:#a7f3d0}
        body.dark .logo svg{color:#a7f3d0}
        body.dark a{color:#a7f3d0}
        body.dark a:hover{color:#34d399}
        body.dark .logout-btn{background:var(--accent-600);border-color:var(--accent-600)}
        body.dark .logout-btn:hover{background:var(--accent)}
        body.dark .dialog-content{background:#111827;border:1px solid #1f2937}
        body.dark .dialog-title{color:#e5e7eb}
        body.dark .dialog-message{color:#94a3b8}
        body.dark .btn-secondary{background:#1f2937;color:#e5e7eb;border-color:#374151}
        body.dark .btn-secondary:hover{background:#374151}
        body.dark table{background:#111827;border-color:#1f2937}
        body.dark th{background:#0f172a;color:#e5e7eb}
        body.dark td{border-bottom:1px solid #1f2937}
        body.dark .user-id{color:#a7f3d0}
        body.dark .user-name{color:#e5e7eb}
        body.dark .user-phone,body.dark .user-email,body.dark .user-date{color:#94a3b8}
        body.dark .stat-card{background:#111827;border-color:#1f2937}
        body.dark .stat-number{color:#e5e7eb}
        body.dark .stat-label{color:#94a3b8}
         body.dark .search-input{background:#0b1220;color:#e5e7eb;border-color:#1f2937}
         body.dark .search-input::placeholder{color:#94a3b8}
         body.dark .csv-btn{background:#059669;border-color:#059669}
         body.dark .csv-btn:hover{background:#10b981}
        /* Dark mode sidebar */
        body.dark .sidebar{background:#0b1220;border-right:1px solid #1f2937}
        body.dark .nav-link{background:#0b1220;border-color:#1f2937;color:#e5e7eb}
        body.dark .nav-link:hover{background:#111827}
        body.dark .nav-link.active{background:#0f291f;border-color:#065f46;color:#a7f3d0}
        @media (max-width: 768px){
            .layout{flex-direction:column}
            .sidebar{width:100%;border-right:none;border-bottom:1px solid #e5e7eb}
            .content{padding:20px}
             .search-box{flex-direction:column;align-items:stretch}
             .search-input{width:100%}
            table{font-size:14px}
            th,td{padding:10px}
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            VPBank Admin
        </div>
        <div class="user-info">
            <button class="logout-btn" onclick="showLogoutDialog()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Đăng xuất
            </button>
        </div>
    </header>
    <script>
        (function(){
            const key='admin_theme';
            if((localStorage.getItem(key)||'light')==='dark') {
                document.body.classList.add('dark');
            }
        })();
    </script>
    <div class="layout">
        <aside class="sidebar">
            <div class="nav-group">
                <a class="nav-link" href="index.php">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                    </svg>
                    Dashboard
                </a>
                <a class="nav-link active" href="users.php">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Người chơi
                </a>
                <a class="nav-link" href="gifts.php">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                    </svg>
                    Mã quà
                </a>
                <a class="nav-link" href="logs.php">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Log quét
                </a>
                <a class="nav-link" href="logout.php">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Đăng xuất
                </a>
            </div>
        </aside>
        <main class="content">
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($users); ?></div>
                    <div class="stat-label">Kết quả tìm kiếm</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $q ? '🔍' : '📋'; ?></div>
                    <div class="stat-label"><?php echo $q ? 'Đang tìm kiếm' : 'Tất cả'; ?></div>
                </div>
            </div>
         <form class="search-box">
             <div class="search-container">
                 <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                 </svg>
                 <input type="text" name="q" class="search-input" placeholder="Tìm tên, số điện thoại, email..." value="<?php echo htmlspecialchars($q); ?>">
             </div>
             <a href="?export=csv<?php echo $q ? '&q=' . urlencode($q) : ''; ?>" class="csv-btn" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                 <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                 </svg>
                 Xuất CSV
             </a>
         </form>
         <div class="search-status">
             <?php if ($q): ?>
                 Hiển thị <?php echo count($users); ?> kết quả tìm kiếm cho "<?php echo htmlspecialchars($q); ?>"
             <?php else: ?>
                 Hiển thị tất cả <?php echo count($users); ?> người dùng
             <?php endif; ?>
         </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Tên
                    </th>
                    <th>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Phone
                    </th>
                    <th>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Email
                    </th>
                    <th>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Ngày tạo
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><span class="user-id">#<?php echo (int)$u['id']; ?></span></td>
                    <td><span class="user-name"><?php echo htmlspecialchars($u['full_name']); ?></span></td>
                    <td><span class="user-phone"><?php echo htmlspecialchars($u['phone']); ?></span></td>
                    <td><span class="user-email"><?php echo htmlspecialchars($u['email']); ?></span></td>
                    <td><span class="user-date"><?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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


