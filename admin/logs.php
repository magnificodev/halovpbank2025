<?php
require_once '_auth.php';
require_once '../api/db.php';
require_once '_template.php';

$db = new Database();

$export = $_GET['export'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5;

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

// Get total count for pagination
$totalCount = $db->fetch("SELECT COUNT(*) as count FROM scan_logs sl JOIN users u ON u.id=sl.user_id")['count'];
$logs = $db->fetchAll("SELECT sl.*, u.full_name FROM scan_logs sl JOIN users u ON u.id=sl.user_id ORDER BY sl.id DESC LIMIT ? OFFSET ?", [$perPage, ($page - 1) * $perPage]);
$totalPages = ceil($totalCount / $perPage);

renderAdminHeader('logs');
?>

<style>
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
    
    /* Dark mode overrides */
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
    
    @media (max-width: 768px) {
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            TỔNG LƯỢT QUÉT
        </h3>
        <div class="number"><?= $totalCount ?></div>
        <div class="label">QR SCANS</div>
    </div>
</div>

<div style="margin-bottom: 20px;">
    <a href="?export=csv" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Xuất CSV
    </a>
</div>

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
                    Người dùng
                </th>
                <th>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Trạm
                </th>
                <th>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    IP
                </th>
                <th>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Thời gian
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #9ca3af;">
                        Chưa có log quét nào
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= $log['id'] ?></td>
                        <td><?= htmlspecialchars($log['full_name']) ?></td>
                        <td><?= htmlspecialchars($log['station_id']) ?></td>
                        <td><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>">« Trước</a>
        <?php else: ?>
            <span class="disabled">« Trước</span>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <?php if ($i == $page): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>">Sau »</a>
        <?php else: ?>
            <span class="disabled">Sau »</span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php renderAdminFooter(); ?>
