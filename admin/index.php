<?php
require_once '_auth.php';
require_once '../api/db.php';
require_once '_template.php';

$db = new Database();

$totalUsers = (int)$db->fetch("SELECT COUNT(*) AS c FROM users")['c'];
$completed3Result = $db->fetch("SELECT COUNT(DISTINCT user_id) AS c FROM user_progress GROUP BY user_id HAVING COUNT(*) >= 3");
$completed3 = $completed3Result ? (int)$completed3Result['c'] : 0;
$giftIssued = (int)$db->fetch("SELECT COUNT(*) AS c FROM gift_codes WHERE user_id IS NOT NULL")['c'];
$scansTodayResult = $db->fetch("SELECT COUNT(*) AS c FROM scan_logs WHERE DATE(created_at)=CURDATE()");
$scansToday = $scansTodayResult ? (int)$scansTodayResult['c'] : 0;

renderAdminHeader('index');
?>

<style>
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px}
    .card{background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;transition:all 0.3s ease}
    .card:hover{box-shadow:0 4px 12px rgba(17,24,39,.12);transform:translateY(-2px)}
    .card h3{margin:0 0 8px;font-size:14px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;gap:6px}
    .card h3 svg{width:16px;height:16px;color:#6b7280;margin-right:0}
    .card .number{font-size:28px;font-weight:700;color:#111827;margin-bottom:4px}
    .card .label{color:#6b7280;font-size:12px;text-transform:uppercase}
    
    /* Dark mode overrides */
    body.dark .card{background:#111827;border-color:#1f2937}
    body.dark .card h3{color:#94a3b8}
    body.dark .card h3 svg{color:#94a3b8}
    body.dark .card .number{color:#e5e7eb}
    body.dark .card .label{color:#94a3b8}
    
    @media (max-width: 768px){
        .grid{grid-template-columns:1fr}
        .card{padding:20px}
    }
</style>

<div class="grid">
    <div class="card">
        <h3>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Tổng người chơi
        </h3>
        <div class="number"><?php echo number_format($totalUsers); ?></div>
        <div class="label">Đã đăng ký</div>
    </div>
    <div class="card">
        <h3>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Đủ điều kiện
        </h3>
        <div class="number"><?php echo number_format($completed3); ?></div>
        <div class="label">Hoàn thành ≥3 trạm</div>
    </div>
    <div class="card">
        <h3>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
            </svg>
            Quà đã phát
        </h3>
        <div class="number"><?php echo number_format($giftIssued); ?></div>
        <div class="label">Mã quà đã claim</div>
    </div>
    <div class="card">
        <h3>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            Quét hôm nay
        </h3>
        <div class="number"><?php echo number_format($scansToday); ?></div>
        <div class="label">Lượt quét QR</div>
    </div>
</div>

<?php renderAdminFooter(); ?>