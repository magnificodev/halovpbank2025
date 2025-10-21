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
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#0b0f19 0%,#1a1f3a 100%);color:#fff;min-height:100vh;transition:background .3s,color .3s}
        header{display:flex;justify-content:space-between;align-items:center;padding:20px 30px;background:rgba(21,26,44,0.95);backdrop-filter:blur(10px);border-bottom:1px solid rgba(0,255,136,0.2)}
        .logo{font-size:24px;font-weight:bold;color:#00ff88;text-shadow:0 0 10px rgba(0,255,136,0.5)}
        a{color:#00ff88;text-decoration:none;transition:all 0.3s ease;padding:8px 16px;border-radius:20px;background:rgba(0,255,136,0.1);border:1px solid rgba(0,255,136,0.3)}
        a:hover{color:#00cc6a;background:rgba(0,255,136,0.2);transform:translateY(-2px);box-shadow:0 5px 15px rgba(0,255,136,0.3)}
        .wrap{padding:30px;max-width:1400px;margin:0 auto}
        .search-box{margin-bottom:30px;display:flex;gap:15px;align-items:center}
        input{padding:12px 20px;border:none;border-radius:25px;background:rgba(21,26,44,0.8);color:#fff;border:1px solid rgba(0,255,136,0.3);width:300px;font-size:14px}
        input:focus{outline:none;border-color:#00ff88;box-shadow:0 0 15px rgba(0,255,136,0.3)}
        input::placeholder{color:#999}
        .search-btn{padding:12px 24px;background:linear-gradient(45deg,#00ff88,#00cc6a);border:none;border-radius:25px;color:#000;font-weight:bold;cursor:pointer;transition:all 0.3s ease}
        .search-btn:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(0,255,136,0.4)}
        table{width:100%;border-collapse:collapse;background:rgba(21,26,44,0.6);border-radius:15px;overflow:hidden;border:1px solid rgba(0,255,136,0.2)}
        th{background:rgba(0,255,136,0.1);color:#00ff88;padding:15px;text-align:left;font-weight:600;text-transform:uppercase;font-size:12px;letter-spacing:1px}
        td{padding:15px;border-bottom:1px solid rgba(255,255,255,0.1);transition:all 0.3s ease}
        tr:hover td{background:rgba(0,255,136,0.05)}
        .user-id{color:#00ff88;font-weight:bold}
        .user-name{font-weight:500}
        .user-phone{color:#ccc;font-family:monospace}
        .user-email{color:#999;font-size:13px}
        .user-date{color:#999;font-size:12px}
        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px}
        .stat-card{background:rgba(21,26,44,0.8);border:1px solid rgba(0,255,136,0.2);border-radius:15px;padding:20px;text-align:center}
        .stat-number{font-size:24px;font-weight:bold;color:#00ff88;margin-bottom:5px}
        .stat-label{color:#ccc;font-size:12px;text-transform:uppercase}
        /* Light mode */
        body.light{background:linear-gradient(135deg,#f8fafc 0%,#eef2f7 100%);color:#111}
        body.light header{background:#ffffff;border-bottom:1px solid #cbd5e1}
        body.light .logo{color:#0a7f5a;text-shadow:none}
        body.light a{color:#0a7f5a;background:#ffffff;border-color:#cbd5e1}
        body.light table{background:#ffffff;border-color:#cbd5e1}
        body.light th{background:#f1f5f9;color:#065f46}
        body.light td{border-bottom:1px solid #cbd5e1}
        @media (max-width: 768px){
            .wrap{padding:20px}
            .search-box{flex-direction:column;align-items:stretch}
            input{width:100%}
            table{font-size:14px}
            th,td{padding:10px}
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">👥 Người chơi</div>
        <div>
            <a href="index.php">⬅ Dashboard</a>
            <a href="logout.php">Đăng xuất</a>
        </div>
    </header>
    <script>
        (function(){
            const key='admin_theme';
            if((localStorage.getItem(key)||'dark')==='light') document.body.classList.add('light');
        })();
    </script>
    <div class="wrap">
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
            <input type="text" name="q" placeholder="🔍 Tìm tên, số điện thoại, email..." value="<?php echo htmlspecialchars($q); ?>">
            <button type="submit" class="search-btn">Tìm kiếm</button>
            <a href="?export=csv<?php echo $q ? '&q=' . urlencode($q) : ''; ?>" class="search-btn" style="text-decoration:none;display:inline-block;margin-left:10px;">📥 Xuất CSV</a>
        </form>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>👤 Tên</th>
                    <th>📱 Phone</th>
                    <th>📧 Email</th>
                    <th>📅 Ngày tạo</th>
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
    </div>
</body>
</html>


