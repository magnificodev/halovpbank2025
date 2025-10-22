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
    .search-form {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        align-items: end;
    }
    
    .search-input {
        flex: 1;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
    }
    
    .search-input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
    }
    
    .btn {
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .btn-primary {
        background: var(--accent);
        color: #fff;
    }
    
    .btn-primary:hover {
        background: var(--accent-600);
        transform: translateY(-1px);
    }
    
    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }
    
    .btn-secondary:hover {
        background: #e5e7eb;
    }
    
    .table-container {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 24px;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th {
        background: #f9fafb;
        color: #374151;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: .4px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .table th svg {
        width: 14px;
        height: 14px;
        color: #6b7280;
        margin-right: 6px;
        vertical-align: middle;
    }
    
    .table td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        color: #6b7280;
    }
    
    .table tbody tr:hover {
        background: #f8fafc;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 24px;
    }
    
    .pagination a, .pagination span {
        padding: 8px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .pagination a {
        color: var(--accent-600);
        border: 1px solid #e5e7eb;
        background: #fff;
    }
    
    .pagination a:hover {
        background: #f3f4f6;
        transform: translateY(-1px);
    }
    
    .pagination .current {
        background: var(--accent);
        color: #fff;
        border: 1px solid var(--accent);
    }
    
    .pagination .disabled {
        color: #9ca3af;
        cursor: not-allowed;
        background: #f9fafb;
    }
    
    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(17,24,39,.12);
        transform: translateY(-2px);
    }
    
    .stat-card h3 {
        margin: 0 0 8px;
        font-size: 14px;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .stat-card h3 svg {
        width: 16px;
        height: 16px;
        color: #6b7280;
    }
    
    .stat-card .number {
        font-size: 28px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }
    
    .stat-card .label {
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
    }
    
    /* Dark mode overrides */
    body.dark .search-input {
        background: #1f2937;
        border-color: #374151;
        color: #e5e7eb;
    }
    
    body.dark .search-input:focus {
        border-color: var(--accent);
    }
    
    body.dark .table-container {
        background: #111827;
        border-color: #1f2937;
    }
    
    body.dark .table th {
        background: #1f2937;
        color: #e5e7eb;
        border-color: #374151;
    }
    
    body.dark .table td {
        color: #94a3b8;
        border-color: #374151;
    }
    
    body.dark .table tbody tr:hover {
        background: #1f2937;
    }
    
    body.dark .pagination a {
        background: #1f2937;
        border-color: #374151;
        color: #e5e7eb;
    }
    
    body.dark .pagination a:hover {
        background: #374151;
    }
    
    body.dark .stat-card {
        background: #111827;
        border-color: #1f2937;
    }
    
    body.dark .stat-card h3 {
        color: #94a3b8;
    }
    
    body.dark .stat-card .number {
        color: #e5e7eb;
    }
    
    body.dark .stat-card .label {
        color: #94a3b8;
    }
    
    @media (max-width: 768px) {
        .search-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .stats {
            grid-template-columns: 1fr;
        }
        
        .table-container {
            overflow-x: auto;
        }
    }
</style>

<div class="stats">
    <div class="stat-card">
        <h3>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            TỔNG NGƯỜI CHƠI
        </h3>
        <div class="number"><?= $totalCount ?></div>
        <div class="label">ĐÃ ĐĂNG KÝ</div>
    </div>
</div>

<form method="GET" class="search-form">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Tìm kiếm theo tên, số điện thoại hoặc email..." class="search-input">
    <button type="submit" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        Tìm kiếm
    </button>
    <a href="?export=csv<?= $q ? '&q=' . urlencode($q) : '' ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Xuất CSV
    </a>
</form>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                    </svg>
                    ID
                </th>
                <th>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Họ tên
                </th>
                <th>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    Số điện thoại
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
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #9ca3af;">
                        <?= $q ? 'Không tìm thấy người chơi nào phù hợp với từ khóa "' . htmlspecialchars($q) . '"' : 'Chưa có người chơi nào đăng ký' ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td><?= htmlspecialchars($user['phone']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= $q ? '&q=' . urlencode($q) : '' ?>">« Trước</a>
        <?php else: ?>
            <span class="disabled">« Trước</span>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <?php if ($i == $page): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?><?= $q ? '&q=' . urlencode($q) : '' ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?><?= $q ? '&q=' . urlencode($q) : '' ?>">Sau »</a>
        <?php else: ?>
            <span class="disabled">Sau »</span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php renderAdminFooter(); ?>
