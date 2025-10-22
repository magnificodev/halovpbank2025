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

// Get statistics
$claimedCount = $db->fetch("SELECT COUNT(*) as count FROM gift_codes WHERE user_id IS NOT NULL")['count'];
$unclaimedCount = $totalCount - $claimedCount;

renderAdminHeader('gifts');
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
    
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    .status-claimed {
        background: #dcfce7;
        color: #166534;
    }
    
    .status-unclaimed {
        background: #fef3c7;
        color: #92400e;
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
    
    body.dark .status-claimed {
        background: #064e3b;
        color: #a7f3d0;
    }
    
    body.dark .status-unclaimed {
        background: #78350f;
        color: #fbbf24;
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
            </svg>
            TỔNG MÃ QUÀ
        </h3>
        <div class="number"><?= $totalCount ?></div>
        <div class="label">GIFT CODES</div>
    </div>
    <div class="stat-card">
        <h3>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            ĐÃ PHÁT
        </h3>
        <div class="number"><?= $claimedCount ?></div>
        <div class="label">CLAIMED</div>
    </div>
    <div class="stat-card">
        <h3>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            CHƯA PHÁT
        </h3>
        <div class="number"><?= $unclaimedCount ?></div>
        <div class="label">UNCLAIMED</div>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
                    Mã quà
                </th>
                <th>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Trạng thái
                </th>
                <th>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Người nhận
                </th>
                <th>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Thời gian claim
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($gifts)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #9ca3af;">
                        Chưa có mã quà nào
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($gifts as $gift): ?>
                    <tr>
                        <td><?= $gift['id'] ?></td>
                        <td><code><?= htmlspecialchars($gift['code']) ?></code></td>
                        <td>
                            <?php if (!empty($gift['user_id'])): ?>
                                <span class="status-badge status-claimed">Đã phát</span>
                            <?php else: ?>
                                <span class="status-badge status-unclaimed">Chưa phát</span>
                            <?php endif; ?>
                        </td>
                        <td><?= !empty($gift['user_id']) ? 'User #' . $gift['user_id'] : '-' ?></td>
                        <td><?= !empty($gift['claimed_at']) ? date('d/m/Y H:i', strtotime($gift['claimed_at'])) : '-' ?></td>
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
