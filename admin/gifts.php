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
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#0b0f19 0%,#1a1f3a 100%);color:#fff;min-height:100vh;transition:background .3s,color .3s}
        header{display:flex;justify-content:space-between;align-items:center;padding:20px 30px;background:rgba(21,26,44,0.95);backdrop-filter:blur(10px);border-bottom:1px solid rgba(0,255,136,0.2)}
        .logo{font-size:24px;font-weight:bold;color:#00ff88;text-shadow:0 0 10px rgba(0,255,136,0.5)}
        a{color:#00ff88;text-decoration:none;transition:all 0.3s ease;padding:8px 16px;border-radius:20px;background:rgba(0,255,136,0.1);border:1px solid rgba(0,255,136,0.3)}
        a:hover{color:#00cc6a;background:rgba(0,255,136,0.2);transform:translateY(-2px);box-shadow:0 5px 15px rgba(0,255,136,0.3)}
        .logout-btn{padding:10px 18px;border-radius:24px;background:linear-gradient(45deg,#0ea5a3,#0b7a6e);border:1px solid rgba(14,165,163,.6);color:#fff;box-shadow:0 6px 14px rgba(14,165,163,.25)}
        .logout-btn:hover{background:linear-gradient(45deg,#0b7a6e,#075e57);border-color:#0ea5a3;color:#fff}
        .wrap{padding:30px;max-width:1400px;margin:0 auto}
        .toolbar{display:flex;gap:10px;align-items:center;justify-content:flex-start;margin:0 0 15px}
        .csv-btn{padding:12px 24px;background:linear-gradient(45deg,#00ff88,#00cc6a);border:none;border-radius:25px;color:#000;font-weight:700;display:inline-flex;align-items:center;justify-content:center;text-align:center;width:160px;border:1px solid rgba(0,255,136,0.3);box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;letter-spacing:0;line-height:1}
        .csv-btn:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(0,255,136,0.4)}
        table{width:100%;border-collapse:collapse;background:rgba(21,26,44,0.6);border-radius:15px;overflow:hidden;border:1px solid rgba(0,255,136,0.2)}
        th{background:rgba(0,255,136,0.1);color:#00ff88;padding:15px;text-align:left;font-weight:600;text-transform:uppercase;font-size:12px;letter-spacing:1px}
        td{padding:15px;border-bottom:1px solid rgba(255,255,255,0.1);transition:all 0.3s ease}
        tr:hover td{background:rgba(0,255,136,0.05)}
        .badge{padding:6px 12px;border-radius:20px;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:0.5px}
        .ok{background:linear-gradient(45deg,#00ff88,#00cc6a);color:#000;box-shadow:0 2px 8px rgba(0,255,136,0.3)}
        .pending{background:linear-gradient(45deg,#ff6b6b,#ee5a52);color:#fff;box-shadow:0 2px 8px rgba(255,107,107,0.3)}
        .gift-code{font-family:monospace;font-size:16px;font-weight:bold;color:#00ff88;letter-spacing:2px}
        .user-id{color:#00ff88;font-weight:bold}
        .date{color:#999;font-size:12px}
        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px}
        .stat-card{background:rgba(21,26,44,0.8);border:1px solid rgba(0,255,136,0.2);border-radius:15px;padding:20px;text-align:center}
        .stat-number{font-size:24px;font-weight:bold;color:#00ff88;margin-bottom:5px}
        .stat-label{color:#ccc;font-size:12px;text-transform:uppercase}
        /* Light mode */
        body.light{background:#f8fafc;color:#1e293b}
        body.light header{background:#ffffff;border-bottom:1px solid #e2e8f0}
        body.light .logo{color:#059669;text-shadow:none}
        body.light a{color:#059669;background:#f8fafc;border-color:#d1d5db}
        body.light .logout-btn{background:#065f46;border-color:#059669;color:#ffffff !important}
        body.light .logout-btn:hover{background:#047857;color:#ffffff !important}
        body.light a:hover{color:#047857;background:#f0fdf4;border-color:#059669}
        body.light table{background:#ffffff;border-color:#e2e8f0}
        body.light th{background:#f1f5f9;color:#374151}
        body.light td{border-bottom:1px solid #e5e7eb}
        body.light .user-id{color:#059669}
        body.light .gift-code{color:#059669}
        body.light .badge.ok{background:linear-gradient(45deg,#059669,#047857);color:#ffffff}
        body.light .badge.pending{background:linear-gradient(45deg,#dc2626,#b91c1c);color:#ffffff}
        body.light .date{color:#6b7280}
        body.light .stat-card{background:#ffffff;border-color:#e2e8f0}
        body.light .stat-number{color:#059669}
        body.light .stat-label{color:#6b7280}
        body.light .csv-btn{background:linear-gradient(45deg,#059669,#047857);color:#ffffff;border-color:#bbf7d0}
        body.light .csv-btn:hover{background:linear-gradient(45deg,#047857,#065f46)}
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


