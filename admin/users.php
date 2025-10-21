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
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f6f7f9;color:#111827;min-height:100vh}
        .logo svg{width:20px;height:20px;color:#059669}
        th svg{width:16px;height:16px;color:#6b7280;margin-right:6px}
        header{display:flex;justify-content:space-between;align-items:center;padding:16px 24px;background:#ffffff;border-bottom:1px solid #e5e7eb}
        .logo{font-size:18px;font-weight:600;color:#111827}
        a{color:#059669;text-decoration:none;transition:color .2s ease;padding:8px 12px;border-radius:10px;background:#ffffff;border:1px solid #e5e7eb}
        a:hover{color:#10b981;background:#f3f4f6}
        .logout-btn{padding:10px 18px;border-radius:24px;background:linear-gradient(45deg,#0ea5a3,#0b7a6e);border:1px solid rgba(14,165,163,.6);color:#fff;box-shadow:0 6px 14px rgba(14,165,163,.25)}
        .logout-btn:hover{background:linear-gradient(45deg,#0b7a6e,#075e57);border-color:#0ea5a3;color:#fff}
        .wrap{padding:16px 24px;max-width:1200px;margin:0 auto}
        .search-box{margin-bottom:30px;display:flex;gap:15px;align-items:center}
        input{padding:10px 14px;border:1px solid #e5e7eb;border-radius:10px;background:#ffffff;color:#111827;width:300px;font-size:14px}
        input:focus{outline:none;border-color:#059669;box-shadow:0 0 0 3px rgba(16,185,129,.15)}
        input::placeholder{color:#9ca3af}
        .search-btn{padding:10px 16px;background:#10b981;border:1px solid #10b981;border-radius:10px;color:#ffffff;font-weight:600;cursor:pointer;transition:background .2s ease;display:inline-flex;align-items:center;justify-content:center;text-align:center;width:160px;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;font-size:14px;letter-spacing:0;line-height:1}
        .search-btn:hover{background:#059669}
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
        body.light input{background:#ffffff;color:#1e293b;border-color:#d1d5db}
        body.light input:focus{border-color:#059669;box-shadow:0 0 0 3px rgba(5,150,105,0.1)}
        body.light input::placeholder{color:#9ca3af}
        body.light .search-btn{background:linear-gradient(45deg,#059669,#047857);color:#ffffff}
        body.light .search-btn:hover{background:linear-gradient(45deg,#047857,#065f46)}
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
        body.dark .user-id{color:#a7f3d0}
        body.dark .user-name{color:#e5e7eb}
        body.dark .user-phone,body.dark .user-email,body.dark .user-date{color:#94a3b8}
        body.dark .stat-card{background:#111827;border-color:#1f2937}
        body.dark .stat-number{color:#e5e7eb}
        body.dark .stat-label{color:#94a3b8}
        body.dark input{background:#0b1220;color:#e5e7eb;border-color:#1f2937}
        body.dark input::placeholder{color:#94a3b8}
        body.dark .search-btn{background:#059669;border-color:#059669}
        body.dark .search-btn:hover{background:#10b981}
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
        <div class="logo">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Người chơi
        </div>
        <div>
            <a href="index.php">⬅ Dashboard</a>
            <a href="logout.php" class="logout-btn">Đăng xuất</a>
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
            <a href="?export=csv<?php echo $q ? '&q=' . urlencode($q) : ''; ?>" class="search-btn" style="text-decoration:none;display:inline-block;">📥 Xuất CSV</a>
        </form>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>👤 Tên</th>
                    <th>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Phone
                    </th>
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


