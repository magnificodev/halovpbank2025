<?php
require_once '_auth.php';
require_once '../api/db.php';
require_once '_template.php';

$db = new Database();

$q = trim($_GET['q'] ?? '');
$export = $_GET['export'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5;

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

// Get total count for pagination
if ($q) {
    $totalCount = $db->fetch("SELECT COUNT(*) as count FROM users WHERE full_name LIKE ? OR phone LIKE ? OR email LIKE ?", ["%$q%", "%$q%", "%$q%"])['count'];
    $users = $db->fetchAll("SELECT * FROM users WHERE full_name LIKE ? OR phone LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?", ["%$q%", "%$q%", "%$q%", $perPage, ($page - 1) * $perPage]);
} else {
    $totalCount = $db->fetch("SELECT COUNT(*) as count FROM users")['count'];
    $users = $db->fetchAll("SELECT * FROM users ORDER BY id DESC LIMIT ? OFFSET ?", [$perPage, ($page - 1) * $perPage]);
}

$totalPages = ceil($totalCount / $perPage);

renderAdminHeader('users');
?>

<style>
    .search-box{margin-bottom:8px;display:flex;gap:15px;align-items:center}
    .search-container{position:relative;display:flex;align-items:center}
    .search-input{padding:10px 14px 10px 40px;border:1px solid #e5e7eb;border-radius:10px;background:#ffffff;color:#111827;width:300px;font-size:14px}
    .search-input:focus{outline:none;border-color:#059669;box-shadow:0 0 0 3px rgba(16,185,129,.15)}
    .search-input::placeholder{color:#9ca3af}
    .search-icon{position:absolute;left:12px;width:16px;height:16px;color:#6b7280;pointer-events:none}
    .csv-btn{padding:10px 16px;background:#10b981;border:1px solid #10b981;border-radius:10px;color:#ffffff;font-weight:600;cursor:pointer;transition:all 0.2s ease;display:inline-flex;align-items:center;justify-content:center;text-align:center;width:160px;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;font-size:14px;letter-spacing:0;line-height:1}
    .csv-btn:hover{background:#059669;transform:translateY(-1px);box-shadow:0 4px 8px rgba(16,185,129,0.3)}
    .search-status{color:#6b7280;font-size:12px;margin-bottom:16px;text-align:left}
    table{width:100%;border-collapse:separate;background:#ffffff;border-radius:12px;overflow:hidden}
    th{background:#f9fafb;color:#374151;padding:12px;text-align:left;font-weight:600;text-transform:uppercase;font-size:12px;letter-spacing:.4px}
    th svg{width:14px;height:14px;color:#6b7280;margin-right:6px;vertical-align:middle}
    td{padding:12px;transition:background .2s ease}
    tr:hover td{background:#f9fafb}
    .user-id{color:#059669;font-weight:600;font-size:14px}
    .user-name{font-weight:600;color:#111827;font-size:14px}
    .user-phone{color:#6b7280;font-family:monospace;font-size:13px;background:#f8fafc;padding:4px 8px;border-radius:6px;display:inline-block}
    .user-email{color:#6b7280;font-size:13px;word-break:break-all}
    .user-date{color:#6b7280;font-size:12px;font-weight:500}
    .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:16px}
    .stat-card{background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;text-align:center;transition:all 0.3s ease}
    .stat-card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(17,24,39,.12)}
    .stat-card h3{margin:0 0 8px;font-size:14px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;justify-content:center;gap:6px}
    .stat-card h3 svg{margin-right:0}
    .stat-number{font-size:20px;font-weight:700;color:#111827;margin-bottom:4px}
    .stat-label{color:#6b7280;font-size:12px;text-transform:uppercase}
    /* Pagination */
    .pagination{display:flex;justify-content:center;align-items:center;gap:8px;margin:20px 0;padding:16px 0}
    .pagination a,.pagination span{display:flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 8px;border:1px solid #e5e7eb;border-radius:8px;text-decoration:none;font-size:14px;font-weight:500;transition:all 0.2s}
    .pagination a{color:#374151;background:#ffffff}
    .pagination a:hover{background:#f3f4f6;border-color:#059669;color:#059669}
    .pagination .current{background:#059669;color:#ffffff;border-color:#059669}
    .pagination .disabled{color:#9ca3af;background:#f9fafb;cursor:not-allowed}
    .pagination .disabled:hover{background:#f9fafb;border-color:#e5e7eb;color:#9ca3af}
    .pagination-info{color:#6b7280;font-size:14px;margin-right:16px}

    /* Dark mode overrides */
    body.dark .user-id{color:#a7f3d0}
    body.dark .user-name{color:#e5e7eb}
    body.dark .user-phone{color:#94a3b8;background:#1f2937}
    body.dark .user-email,body.dark .user-date{color:#94a3b8}

    @media (max-width: 768px){
        .search-box{flex-direction:column;align-items:stretch}
        .search-input{width:100%}
        table{font-size:14px}
        th,td{padding:10px}
    }
</style>

<div class="stats">
    <div class="stat-card">
        <div class="stat-number"><?php echo number_format((int)$totalCount); ?></div>
        <div class="stat-label">Người dùng hiện có</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php
            $completed3CountRow = $db->fetch("SELECT COUNT(*) AS c FROM (SELECT user_id FROM user_progress GROUP BY user_id HAVING COUNT(*) >= 3) t");
            echo number_format($completed3CountRow ? (int)$completed3CountRow['c'] : 0);
        ?></div>
        <div class="stat-label">Đã hoàn thành ≥3</div>
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
        (trang <?php echo $page; ?>/<?php echo $totalPages; ?>)
    <?php else: ?>
        Hiển thị <?php echo count($users); ?> người dùng (trang <?php echo $page; ?>/<?php echo $totalPages; ?>)
    <?php endif; ?>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>
                <div style="display: inline-flex; align-items: center;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 6px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Tên
                </div>
            </th>
            <th>
                <div style="display: inline-flex; align-items: center;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 6px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Phone
                </div>
            </th>
            <th>
                <div style="display: inline-flex; align-items: center;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 6px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Email
                </div>
            </th>
            <th>
                <div style="display: inline-flex; align-items: center;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 6px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Ngày tạo
                </div>
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

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=1<?php echo $q ? '&q=' . urlencode($q) : ''; ?>">«</a>
        <a href="?page=<?php echo $page - 1; ?><?php echo $q ? '&q=' . urlencode($q) : ''; ?>">‹</a>
    <?php else: ?>
        <span class="disabled">«</span>
        <span class="disabled">‹</span>
    <?php endif; ?>

    <?php
    $startPage = max(1, $page - 2);
    $endPage = min($totalPages, $page + 2);

    if ($startPage > 1): ?>
        <a href="?page=1<?php echo $q ? '&q=' . urlencode($q) : ''; ?>">1</a>
        <?php if ($startPage > 2): ?>
            <span class="disabled">...</span>
        <?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <?php if ($i == $page): ?>
            <span class="current"><?php echo $i; ?></span>
        <?php else: ?>
            <a href="?page=<?php echo $i; ?><?php echo $q ? '&q=' . urlencode($q) : ''; ?>"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($endPage < $totalPages): ?>
        <?php if ($endPage < $totalPages - 1): ?>
            <span class="disabled">...</span>
        <?php endif; ?>
        <a href="?page=<?php echo $totalPages; ?><?php echo $q ? '&q=' . urlencode($q) : ''; ?>"><?php echo $totalPages; ?></a>
    <?php endif; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page + 1; ?><?php echo $q ? '&q=' . urlencode($q) : ''; ?>">›</a>
        <a href="?page=<?php echo $totalPages; ?><?php echo $q ? '&q=' . urlencode($q) : ''; ?>">»</a>
    <?php else: ?>
        <span class="disabled">›</span>
        <span class="disabled">»</span>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php renderAdminFooter(); ?>
