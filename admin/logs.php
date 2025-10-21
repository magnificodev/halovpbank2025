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
        header{display:flex;justify-content:space-between;align-items:center;padding:16px 24px;background:#ffffff;border-bottom:1px solid #e5e7eb}
        .logo{font-size:18px;font-weight:600;color:#111827}
        a{color:#059669;text-decoration:none;transition:color .2s ease;padding:8px 12px;border-radius:10px;background:#ffffff;border:1px solid #e5e7eb}
        a:hover{color:#10b981;background:#f3f4f6}
        .logout-btn{padding:8px 14px;border-radius:10px;background:#059669;border:1px solid #059669;color:#fff}
        .logout-btn:hover{background:#10b981}
        .wrap{padding:16px 24px;max-width:1200px;margin:0 auto}
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
        .stat-number{font-size:20px;font-weight:700;color:#111827;margin-bottom:4px}
        .stat-label{color:#6b7280;font-size:12px;text-transform:uppercase}
        /* Light mode */
        /* Light is default in flat theme */
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
        <div class="logo">📊 Log quét QR</div>
        <div>
            <a href="index.php">⬅ Dashboard</a>
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
    <div class="wrap">
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
            <a class="csv-btn" href="?export=csv">📥 Xuất CSV</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>👤 Người dùng</th>
                    <th>📍 Trạm</th>
                    <th>🌐 IP</th>
                    <th>🔍 User-Agent</th>
                    <th>⏰ Thời gian</th>
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
    </div>
</body>
</html>


