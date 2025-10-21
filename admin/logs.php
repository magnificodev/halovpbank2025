<?php
require_once '_auth.php';
require_once '../api/db.php';
$db = new Database();

$export = $_GET['export'] ?? '';

if ($export === 'csv') {
    // Export to CSV
    if (ob_get_length()) { ob_end_clean(); }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="scan_logs_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    // UTF-8 BOM for Excel
    echo "\xEF\xBB\xBF";
    fputcsv($output, ['ID', 'Người dùng', 'User ID', 'Trạm', 'IP', 'User-Agent', 'Thời gian']);

    $logs = $db->fetchAll("SELECT sl.*, u.full_name FROM scan_logs sl JOIN users u ON u.id=sl.user_id ORDER BY sl.id DESC");

    foreach ($logs as $log) {
        fputcsv($output, [
            $log['id'],
            $log['full_name'],
            $log['user_id'],
            $log['station_id'],
            $log['ip_address'],
            $log['user_agent'],
            $log['created_at']
        ]);
    }
    fclose($output);
    exit;
}

$logs = $db->fetchAll("SELECT sl.*, u.full_name FROM scan_logs sl JOIN users u ON u.id=sl.user_id ORDER BY sl.id DESC LIMIT 300");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Logs</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f6f7f9;color:#111827;min-height:100vh}
        .logo{display:flex;align-items:center;gap:8px}
        .logo svg{width:20px;height:20px;color:#059669;flex-shrink:0}
        th{background:#f9fafb;color:#374151;padding:12px;text-align:left;font-weight:600;text-transform:uppercase;font-size:12px;letter-spacing:.4px;display:flex;align-items:center;gap:6px}
        th svg{width:16px;height:16px;color:#6b7280;flex-shrink:0}
        .logo{font-size:18px;font-weight:600;color:#111827}
        a{color:#059669;text-decoration:none;transition:color .2s ease;padding:8px 12px;border-radius:10px;background:#ffffff;border:1px solid #e5e7eb}
        a:hover{color:#10b981;background:#f3f4f6}
        .logout-btn{padding:8px 14px;border-radius:10px;background:#059669;border:1px solid #059669;color:#fff}
        .logout-btn:hover{background:#10b981}
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
        .toolbar{display:flex;gap:10px;align-items:center;justify-content:flex-start;margin:0 0 12px}
        .csv-btn{padding:10px 16px;background:#10b981;border:1px solid #10b981;border-radius:10px;color:#ffffff;font-weight:600;display:inline-flex;align-items:center;justify-content:center;text-align:center;width:160px;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;font-size:14px;letter-spacing:0;line-height:1}
        .csv-btn:hover{background:#059669}
        table{width:100%;border-collapse:collapse;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb}
        th{background:#f9fafb;color:#374151;padding:12px;text-align:left;font-weight:600;text-transform:uppercase;font-size:12px;letter-spacing:.4px}
        td{padding:12px;border-bottom:1px solid #f1f5f9;transition:background .2s ease}
        tr:hover td{background:#f9fafb}
        .log-id{color:#059669;font-weight:600}
        .user-name{font-weight:600;color:#111827}
        .user-id{color:#059669;font-size:12px}
        .station{background:#ecfdf5;color:#059669;padding:4px 8px;border-radius:10px;font-size:11px;font-weight:700;text-transform:uppercase;border:1px solid #bbf7d0}
        .ip{font-family:monospace;color:#6b7280;font-size:12px}
        .ua{max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#6b7280;font-size:12px}
        .date{color:#6b7280;font-size:12px}
        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:16px}
        .stat-card{background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;text-align:center}
        .stat-card h3{margin:0 0 8px;font-size:14px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;justify-content:center;gap:6px}
        .stat-card h3 svg{margin-right:0}
        .stat-number{font-size:20px;font-weight:700;color:#111827;margin-bottom:4px}
        .stat-label{color:#6b7280;font-size:12px;text-transform:uppercase}
        /* Light mode */
        /* Dark mode overrides */
        body.dark{background:#0f172a;color:#e5e7eb}
        body.dark header{background:#0b1220;border-bottom:1px solid #1f2937}
        body.dark .logo{color:#a7f3d0}
        body.dark a{color:#a7f3d0;background:#0b1220;border-color:#1f2937}
        body.dark a:hover{background:#111827}
        body.dark .logout-btn{background:#065f46;border-color:#065f46}
        body.dark .logout-btn:hover{background:#059669}
        body.dark table{background:#111827;border-color:#1f2937}
        body.dark th{background:#0f172a;color:#e5e7eb}
        body.dark td{border-bottom:1px solid #1f2937}
        body.dark .log-id{color:#a7f3d0}
        body.dark .user-name{color:#e5e7eb}
        body.dark .user-id{color:#a7f3d0}
        body.dark .station{background:#0b1220;color:#a7f3d0;border-color:#1f2937}
        body.dark .ip,body.dark .ua,body.dark .date{color:#94a3b8}
        @media (max-width: 768px){
            .wrap{padding:20px}
            table{font-size:14px}
            th,td{padding:10px}
            .ua{max-width:200px}
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
        <div>
            <a href="logout.php" class="logout-btn">Đăng xuất</a>
        </div>
    </header>
    <script>
        (function(){
            const key='admin_theme';
            if((localStorage.getItem(key)||'dark')==='light') {
                document.body.classList.add('light');
                const mobileFrame = document.querySelector('.mobile-frame');
                if (mobileFrame) mobileFrame.classList.add('light');
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
                <a class="nav-link" href="users.php">
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
                <a class="nav-link active" href="logs.php">
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
                    <div class="stat-number"><?php echo count($logs); ?></div>
                    <div class="stat-label">Tổng lượt quét</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count(array_unique(array_column($logs, 'user_id'))); ?></div>
                    <div class="stat-label">Người dùng quét</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count(array_unique(array_column($logs, 'station_id'))); ?></div>
                    <div class="stat-label">Trạm được quét</div>
                </div>
            </div>
        <div class="toolbar">
            <a class="csv-btn" href="?export=csv" style="display:inline-flex;align-items:center;gap:6px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Xuất CSV
            </a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Người dùng
                    </th>
                    <th>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Trạm
                    </th>
                    <th>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                        </svg>
                        IP
                    </th>
                    <th>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        User-Agent
                    </th>
                    <th>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Thời gian
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $l): ?>
                <tr>
                    <td><span class="log-id">#<?php echo (int)$l['id']; ?></span></td>
                    <td>
                        <div class="user-name"><?php echo htmlspecialchars($l['full_name']); ?></div>
                        <div class="user-id">#<?php echo (int)$l['user_id']; ?></div>
                    </td>
                    <td><span class="station"><?php echo htmlspecialchars($l['station_id']); ?></span></td>
                    <td><span class="ip"><?php echo htmlspecialchars($l['ip_address']); ?></span></td>
                    <td class="ua" title="<?php echo htmlspecialchars($l['user_agent']); ?>"><?php echo htmlspecialchars($l['user_agent']); ?></td>
                    <td><span class="date"><?php echo date('d/m/Y H:i:s', strtotime($l['created_at'])); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </main>
    </div>
</body>
</html>


