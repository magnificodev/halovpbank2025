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

// Get total count for pagination
$totalCount = $db->fetch("SELECT COUNT(*) as count FROM gift_codes")['count'];
$gifts = $db->fetchAll("SELECT id, code, user_id, claimed_at FROM gift_codes ORDER BY id DESC LIMIT ? OFFSET ?", [$perPage, ($page - 1) * $perPage]);
$totalPages = ceil($totalCount / $perPage);

renderAdminHeader('gifts');
?>

<style>
    .toolbar{display:flex;gap:10px;align-items:center;justify-content:flex-start;margin:0 0 12px}
    .csv-btn{padding:10px 16px;background:#10b981;border:1px solid #10b981;border-radius:10px;color:#ffffff;font-weight:600;display:inline-flex;align-items:center;justify-content:center;text-align:center;width:160px;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;font-size:14px;letter-spacing:0;line-height:1;transition:all 0.2s ease}
    .csv-btn:hover{background:#059669;transform:translateY(-1px);box-shadow:0 4px 8px rgba(16,185,129,0.3)}
    table{width:100%;border-collapse:separate;background:#ffffff;border-radius:12px;overflow:hidden}
    th{background:#f9fafb;color:#374151;padding:12px;text-align:left;font-weight:600;text-transform:uppercase;font-size:12px;letter-spacing:.4px}
    th svg{width:14px;height:14px;color:#6b7280;margin-right:6px;vertical-align:middle}
    td{padding:12px;transition:background .2s ease;vertical-align:middle}
    tr:hover td{background:#f9fafb}
    .badge{padding:4px 8px;border-radius:8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;display:inline-flex;align-items:center;gap:3px;white-space:nowrap}
    .badge svg{width:10px;height:10px;flex-shrink:0}
    .ok{background:#10b981;color:#fff}
    .pending{background:#ef4444;color:#fff}
    .gift-code{font-family:monospace;font-size:14px;font-weight:700;color:#059669;letter-spacing:2px}
    .user-id{color:#059669;font-weight:600}
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
    body.dark .user-id,body.dark .gift-code{color:#a7f3d0}
    body.dark .date{color:#94a3b8}

    @media (max-width: 768px){
        table{font-size:14px}
        th,td{padding:10px}
    }
</style>

<div class="stats">
    <div class="stat-card">
        <div class="stat-number"><?php echo number_format((int)$totalCount); ?></div>
        <div class="stat-label">Tổng mã quà</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php
            $issuedRow = $db->fetch("SELECT COUNT(*) AS c FROM gift_codes WHERE user_id IS NOT NULL");
            $issued = $issuedRow ? (int)$issuedRow['c'] : 0;
            echo number_format($issued);
        ?></div>
        <div class="stat-label">Đã phát</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo number_format((int)$totalCount - $issued); ?></div>
        <div class="stat-label">Chưa phát</div>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                    </svg>
                    Mã quà
                </div>
            </th>
            <th>
                <div style="display: inline-flex; align-items: center;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 6px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Trạng thái
                </div>
            </th>
            <th>
                <div style="display: inline-flex; align-items: center;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 6px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Người nhận
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
    <?php foreach ($gifts as $g): $claimed = !empty($g['user_id']); ?>
        <tr>
            <td><span class="user-id">#<?php echo (int)$g['id']; ?></span></td>
            <td><span class="gift-code"><?php echo htmlspecialchars($g['code']); ?></span></td>
            <td>
                <?php if ($claimed): ?>
                    <span class="badge ok">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        ĐÃ PHÁT
                    </span>
                <?php else: ?>
                    <span class="badge pending">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        CHƯA PHÁT
                    </span>
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
