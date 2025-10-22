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
    .toolbar{display:flex;gap:10px;align-items:center;justify-content:flex-start;margin:0 0 12px}
    .csv-btn{padding:10px 16px;background:#10b981;border:1px solid #10b981;border-radius:10px;color:#ffffff;font-weight:600;display:inline-flex;align-items:center;justify-content:center;text-align:center;width:160px;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;font-size:14px;letter-spacing:0;line-height:1;transition:all 0.2s ease}
    .csv-btn:hover{background:#059669;transform:translateY(-1px);box-shadow:0 4px 8px rgba(16,185,129,0.3)}
    table{width:100%;border-collapse:separate;background:#ffffff;border-radius:12px;overflow:hidden}
    th{background:#f9fafb;color:#374151;padding:12px;text-align:left;font-weight:600;text-transform:uppercase;font-size:12px;letter-spacing:.4px}
    th svg{width:14px;height:14px;color:#6b7280;margin-right:6px;vertical-align:middle}
    td{padding:12px;transition:background .2s ease}
    tr:hover td{background:#f9fafb}
    .log-id{color:#059669;font-weight:600}
    .user-name{font-weight:600;color:#111827}
    .user-id{color:#059669;font-size:12px}
    .station{background:#ecfdf5;color:#059669;padding:4px 8px;border-radius:10px;font-size:11px;font-weight:700;text-transform:uppercase;border:1px solid #bbf7d0}
    .ip{font-family:monospace;color:#6b7280;font-size:12px}
    .ua{max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#6b7280;font-size:12px}
    .date{color:#6b7280;font-size:12px}
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
    body.dark .csv-btn{background:#059669;border-color:#059669;color:#ffffff}
    body.dark .csv-btn:hover{background:#10b981;color:#ffffff}
    body.dark tr:hover td{background:#1f2937}
    body.dark .log-id{color:#a7f3d0}
    body.dark .user-name{color:#e5e7eb}
    body.dark .user-id{color:#a7f3d0}
    body.dark .station{background:#0b1220;color:#a7f3d0;border-color:#1f2937}
    body.dark .ip,body.dark .ua,body.dark .date{color:#94a3b8}
    body.dark .stat-card{background:#111827;border-color:#1f2937}
    body.dark .stat-number{color:#e5e7eb}
    body.dark .stat-label{color:#94a3b8}
    body.dark .pagination a{color:#e5e7eb;background:#111827;border-color:#1f2937}
    body.dark .pagination a:hover{background:#1f2937;border-color:#059669;color:#a7f3d0}
    body.dark .pagination .current{background:#059669;color:#ffffff;border-color:#059669}
    body.dark .pagination .disabled{color:#6b7280;background:#0f172a}
    body.dark .pagination .disabled:hover{background:#0f172a;border-color:#1f2937;color:#6b7280}
    body.dark .pagination-info{color:#94a3b8}

    @media (max-width: 768px){
        table{font-size:14px}
        th,td{padding:10px}
        .ua{max-width:200px}
    }
</style>

<div class="stats">
    <div class="stat-card">
        <div class="stat-number"><?php echo number_format((int)$totalCount); ?></div>
        <div class="stat-label">Tổng lượt quét</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php
            $distinctUsersRow = $db->fetch("SELECT COUNT(DISTINCT user_id) AS c FROM scan_logs");
            echo number_format($distinctUsersRow ? (int)$distinctUsersRow['c'] : 0);
        ?></div>
        <div class="stat-label">Người dùng quét</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php
            $distinctStationsRow = $db->fetch("SELECT COUNT(DISTINCT station_id) AS c FROM scan_logs");
            echo number_format($distinctStationsRow ? (int)$distinctStationsRow['c'] : 0);
        ?></div>
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
                <div style="display: inline-flex; align-items: center;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 6px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Người dùng
                </div>
            </th>
            <th>
                <div style="display: inline-flex; align-items: center;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 6px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Trạm
                </div>
            </th>
            <th>
                <div style="display: inline-flex; align-items: center;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 6px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                    </svg>
                    IP
                </div>
            </th>
            <th>
                <div style="display: inline-flex; align-items: center;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 6px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    User-Agent
                </div>
            </th>
            <th>
                <div style="display: inline-flex; align-items: center;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 6px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Thời gian
                </div>
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

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=1">«</a>
        <a href="?page=<?php echo $page - 1; ?>">‹</a>
    <?php else: ?>
        <span class="disabled">«</span>
        <span class="disabled">‹</span>
    <?php endif; ?>

    <?php
    $startPage = max(1, $page - 2);
    $endPage = min($totalPages, $page + 2);

    if ($startPage > 1): ?>
        <a href="?page=1">1</a>
        <?php if ($startPage > 2): ?>
            <span class="disabled">...</span>
        <?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <?php if ($i == $page): ?>
            <span class="current"><?php echo $i; ?></span>
        <?php else: ?>
            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($endPage < $totalPages): ?>
        <?php if ($endPage < $totalPages - 1): ?>
            <span class="disabled">...</span>
        <?php endif; ?>
        <a href="?page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
    <?php endif; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page + 1; ?>">›</a>
        <a href="?page=<?php echo $totalPages; ?>">»</a>
    <?php else: ?>
        <span class="disabled">›</span>
        <span class="disabled">»</span>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php renderAdminFooter(); ?>
