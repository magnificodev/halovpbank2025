<?php
require_once '_auth.php';
require_once '../api/db.php';
$db = new Database();

$export = $_GET['export'] ?? '';

if ($export === 'csv') {
    // Export to CSV
    if (ob_get_length()) { ob_end_clean(); }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="gifts_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    // UTF-8 BOM for Excel
    echo "\xEF\xBB\xBF";
    fputcsv($output, ['ID', 'Mã quà', 'Trạng thái', 'Người nhận', 'Thời gian claim']);

    $gifts = $db->fetchAll("SELECT id, code, user_id, claimed_at FROM gift_codes ORDER BY id DESC");

    foreach ($gifts as $gift) {
        $status = !empty($gift['user_id']) ? 'Đã phát' : 'Chưa phát';
        $user = !empty($gift['user_id']) ? $gift['user_id'] : '';
        $claimed_at = $gift['claimed_at'] ?? '';

        fputcsv($output, [
            $gift['id'],
            $gift['code'],
            $status,
            $user,
            $claimed_at
        ]);
    }
    fclose($output);
    exit;
}

$gifts = $db->fetchAll("SELECT id, code, user_id, claimed_at FROM gift_codes ORDER BY id DESC LIMIT 300");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Mã quà</title>
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
        .badge{padding:6px 10px;border-radius:10px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.4px}
        .ok{background:#10b981;color:#fff}
        .pending{background:#ef4444;color:#fff}
        .gift-code{font-family:monospace;font-size:14px;font-weight:700;color:#059669;letter-spacing:2px}
        .user-id{color:#059669;font-weight:600}
        .date{color:#6b7280;font-size:12px}
        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:16px}
        .stat-card{background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;text-align:center}
        .stat-number{font-size:20px;font-weight:700;color:#111827;margin-bottom:4px}
        .stat-label{color:#6b7280;font-size:12px;text-transform:uppercase}
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
        body.dark .user-id,body.dark .gift-code{color:#a7f3d0}
        body.dark .date{color:#94a3b8}
        body.dark .stat-card{background:#111827;border-color:#1f2937}
        body.dark .stat-number{color:#e5e7eb}
        body.dark .stat-label{color:#94a3b8}
        @media (max-width: 768px){
            .wrap{padding:20px}
            table{font-size:14px}
            th,td{padding:10px}
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">🎁 Mã quà tặng</div>
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
                <div class="stat-number"><?php echo count($gifts); ?></div>
                <div class="stat-label">Tổng mã quà</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($gifts, fn($g) => !empty($g['user_id']))); ?></div>
                <div class="stat-label">Đã phát</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($gifts, fn($g) => empty($g['user_id']))); ?></div>
                <div class="stat-label">Chưa phát</div>
            </div>

        </div>
        <div class="toolbar">
            <a class="csv-btn" href="?export=csv">📥 Xuất CSV</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>🎁 Mã quà</th>
                    <th>📊 Trạng thái</th>
                    <th>👤 Người nhận</th>
                    <th>📅 Thời gian</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($gifts as $g): $claimed = !empty($g['user_id']); ?>
                <tr>
                    <td><span class="user-id">#<?php echo (int)$g['id']; ?></span></td>
                    <td><span class="gift-code"><?php echo htmlspecialchars($g['code']); ?></span></td>
                    <td>
                        <?php if ($claimed): ?>
                            <span class="badge ok">✅ ĐÃ PHÁT</span>
                        <?php else: ?>
                            <span class="badge pending">⏳ CHƯA PHÁT</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($claimed): ?>
                            <span class="user-id">#<?php echo (int)$g['user_id']; ?></span>
                        <?php else: ?>
                            <span style="color:#999">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($claimed && $g['claimed_at']): ?>
                            <span class="date"><?php echo date('d/m/Y H:i', strtotime($g['claimed_at'])); ?></span>
                        <?php else: ?>
                            <span style="color:#999">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>


