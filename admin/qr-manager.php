<?php
require_once '_auth.php';
require_once '../api/db.php';

$db = new Database();
$message = '';
$error = '';

// Handle QR generation
if ($_POST['action'] ?? '' === 'generate_qr') {
    $stationId = $_POST['station_id'] ?? '';
    
    if ($stationId && array_key_exists($stationId, STATIONS)) {
        try {
            $verifyHash = generateVerifyHash($stationId);
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $baseUrl = $protocol . '://' . $host . dirname($_SERVER['REQUEST_URI'], 2);
            $qrUrl = $baseUrl . '/game.php?station=' . urlencode($stationId) . '&verify=' . $verifyHash;
            
            $message = "QR Code generated successfully for " . STATIONS[$stationId];
        } catch (Exception $e) {
            $error = "Error generating QR: " . $e->getMessage();
        }
    } else {
        $error = "Invalid station selected";
    }
}

// Get scan statistics
$scanStats = [];
try {
    $scanStats = $db->fetchAll("
        SELECT 
            station_id,
            COUNT(*) as scan_count,
            COUNT(DISTINCT user_id) as unique_users
        FROM scan_logs 
        GROUP BY station_id
        ORDER BY scan_count DESC
    ");
} catch (Exception $e) {
    // ignore stats errors
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Manager - VPBank Admin</title>
    <script src="https://unpkg.com/heroicons@2.0.18/24/outline/index.js" type="module"></script>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <style>
        :root{--accent:#10b981;--accent-600:#059669;--header-height:74px}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f6f7f9;color:#111827;min-height:100vh;line-height:1.5}
        header{display:flex;justify-content:space-between;align-items:center;padding:16px 24px;background:#ffffff;border-bottom:1px solid #e5e7eb;height:var(--header-height)}
        .logo{font-size:18px;font-weight:700;color:#111827}
        .user-info{display:flex;align-items:center;gap:12px}
        a{color:var(--accent-600);text-decoration:none}
        a:hover{color:var(--accent)}
        .toggle{padding:8px;border-radius:12px;border:1px solid #e5e7eb;background:#eef2f7;color:#111827;cursor:pointer;display:flex;align-items:center;justify-content:center;width:36px;height:36px;transition:all 0.2s ease}
        .logout-btn{padding:10px 20px;border-radius:10px;background:var(--accent);border:1px solid var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;gap:6px;text-decoration:none;font-size:14px;font-weight:600;cursor:pointer;border:none;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;line-height:1.5;transition:all 0.2s ease}
        .logout-btn:hover{background:var(--accent-600);transform:translateY(-1px);box-shadow:0 4px 8px rgba(16,185,129,0.3)}
        /* Logout Dialog */
        .logout-dialog{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center}
        .logout-dialog.show{display:flex}
        .dialog-content{background:#fff;border-radius:12px;padding:24px;max-width:400px;width:90%;box-shadow:0 10px 25px rgba(0,0,0,0.2)}
        .dialog-header{display:flex;align-items:center;gap:12px;margin-bottom:16px}
        .dialog-icon{width:24px;height:24px;color:#ef4444}
        .dialog-title{font-size:18px;font-weight:600;color:#111827}
        .dialog-message{color:#6b7280;margin-bottom:24px;line-height:1.5}
        .dialog-actions{display:flex;gap:12px;justify-content:flex-end}
        .btn{padding:8px 16px;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;border:none;transition:all 0.2s}
        .btn-secondary{background:#f3f4f6;color:#374151;border:1px solid #d1d5db}
        .btn-secondary:hover{background:#e5e7eb}
        .btn-danger{background:#ef4444;color:#fff}
        .btn-danger:hover{background:#dc2626}
        /* Layout with sidebar */
        .layout{display:flex;min-height:calc(100vh - var(--header-height))}
        .sidebar{width:280px;background:#fff;border-right:1px solid #e5e7eb;padding:24px 0;overflow-y:auto}
        .sidebar.collapsed{width:80px}
        .nav-group{margin-bottom:32px}
        .nav-group:last-child{margin-bottom:0}
        .nav-title{font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;padding:0 24px}
        .nav-link{display:flex;align-items:center;gap:12px;padding:12px 24px;color:#6b7280;text-decoration:none;transition:all 0.2s;border-right:3px solid transparent}
        .nav-link:hover{background:#f8fafc;color:#111827}
        .nav-link.active{background:#f0fdf4;color:var(--accent-600);border-right-color:var(--accent-600);font-weight:600}
        .nav-link svg{width:20px;height:20px;flex-shrink:0}
        .main-content{flex:1;padding:32px;overflow-y:auto}
        .main-content.collapsed{margin-left:-200px}
        /* Cards */
        .card{background:#fff;border-radius:12px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,0.1);border:1px solid #e5e7eb;margin-bottom:24px}
        .card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
        .card-title{font-size:18px;font-weight:600;color:#111827}
        .card-subtitle{color:#6b7280;font-size:14px;margin-top:4px}
        /* Forms */
        .form-group{margin-bottom:20px}
        .form-group:last-child{margin-bottom:0}
        .form-label{display:block;font-size:14px;font-weight:500;color:#374151;margin-bottom:6px}
        .form-input{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;transition:border-color 0.2s}
        .form-input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(16,185,129,0.1)}
        .form-select{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;background:#fff;cursor:pointer}
        .form-select:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(16,185,129,0.1)}
        .btn-primary{background:var(--accent);color:#fff;padding:10px 20px;border-radius:8px;border:none;font-size:14px;font-weight:500;cursor:pointer;transition:all 0.2s}
        .btn-primary:hover{background:var(--accent-600);transform:translateY(-1px)}
        .btn-secondary{background:#f3f4f6;color:#374151;padding:10px 20px;border-radius:8px;border:1px solid #d1d5db;font-size:14px;font-weight:500;cursor:pointer;transition:all 0.2s}
        .btn-secondary:hover{background:#e5e7eb}
        /* Alerts */
        .alert{padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:14px}
        .alert-success{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}
        .alert-error{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
        /* Tables */
        .table-responsive{overflow-x:auto;margin-top:20px}
        .table{width:100%;border-collapse:collapse;font-size:14px}
        .table th{background:#f8fafc;color:#374151;font-weight:600;text-align:left;padding:12px;border-bottom:1px solid #e5e7eb}
        .table td{padding:12px;border-bottom:1px solid #f3f4f6;color:#6b7280}
        .table tbody tr:hover{background:#f8fafc}
        /* Responsive */
        @media (max-width: 768px) {
            .layout{flex-direction:column}
            .sidebar{width:100%;border-right:none;border-bottom:1px solid #e5e7eb}
            .main-content{padding:20px}
            .card{padding:16px}
        }
        /* QR specific styles */
        .qr-display{display:flex;gap:20px;align-items:flex-start;margin-top:20px}
        .qr-info{flex:1}
        .qr-code{flex-shrink:0}
        .qr-code canvas{border:1px solid #ddd;border-radius:8px}
        @media (max-width: 768px) {
            .qr-display{flex-direction:column}
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">VPBank Admin</div>
        <div class="user-info">
            <span>Admin</span>
            <button class="logout-btn" onclick="showLogoutDialog()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Đăng xuất
            </button>
        </div>
    </header>

    <div class="layout">
        <aside class="sidebar">
            <div class="nav-group">
                <a class="nav-link" href="index.php">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                    </svg>
                    Dashboard
                </a>
                <a class="nav-link" href="users.php">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Người chơi
                </a>
                <a class="nav-link" href="gifts.php">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                    </svg>
                    Mã quà
                </a>
                <a class="nav-link active" href="qr-manager.php">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                    QR Manager
                </a>
                <a class="nav-link" href="logs.php">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Log quét
                </a>
                <a class="nav-link" href="#" onclick="showLogoutDialog(); return false;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Đăng xuất
                </a>
            </div>
        </aside>

        <main class="main-content">
            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Generate QR Code</h2>
                        <p class="card-subtitle">Tạo mã QR cho các trạm để người chơi quét</p>
                    </div>
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label for="station_id" class="form-label">Select Station:</label>
                        <select name="station_id" id="station_id" class="form-select" required>
                            <option value="">Choose a station...</option>
                            <?php foreach (STATIONS as $id => $name): ?>
                                <option value="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="hidden" name="action" value="generate_qr">
                    <button type="submit" class="btn btn-primary">Generate QR Code</button>
                </form>
            </div>

            <?php if (isset($qrUrl)): ?>
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Generated QR Code</h2>
                            <p class="card-subtitle">Mã QR đã được tạo thành công</p>
                        </div>
                    </div>
                    <div class="qr-display">
                        <div class="qr-info">
                            <p><strong>Station:</strong> <?= htmlspecialchars(STATIONS[$stationId]) ?></p>
                            <p><strong>URL:</strong> <code><?= htmlspecialchars($qrUrl) ?></code></p>
                            <button onclick="copyToClipboard('<?= htmlspecialchars($qrUrl) ?>')" class="btn btn-secondary">Copy URL</button>
                        </div>
                        <div class="qr-code">
                            <canvas id="qrcode"></canvas>
                        </div>
                    </div>
                </div>

                <script>
                    // Generate QR code
                    QRCode.toCanvas(document.getElementById('qrcode'), '<?= htmlspecialchars($qrUrl) ?>', {
                        width: 200,
                        margin: 2,
                        color: {
                            dark: '#000000',
                            light: '#FFFFFF'
                        }
                    }, function (error) {
                        if (error) console.error(error);
                    });

                    function copyToClipboard(text) {
                        navigator.clipboard.writeText(text).then(function() {
                            alert('URL copied to clipboard!');
                        });
                    }
                </script>
            <?php endif; ?>

            <?php if (!empty($scanStats)): ?>
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Scan Statistics</h2>
                            <p class="card-subtitle">Thống kê quét QR theo từng trạm</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Station</th>
                                    <th>Total Scans</th>
                                    <th>Unique Users</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scanStats as $stat): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(STATIONS[$stat['station_id']] ?? $stat['station_id']) ?></td>
                                        <td><?= $stat['scan_count'] ?></td>
                                        <td><?= $stat['unique_users'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Logout Dialog -->
    <div id="logoutDialog" class="logout-dialog">
        <div class="dialog-content">
            <div class="dialog-header">
                <svg class="dialog-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="dialog-title">Xác nhận đăng xuất</h3>
            </div>
            <p class="dialog-message">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?</p>
            <div class="dialog-actions">
                <button class="btn btn-secondary" onclick="hideLogoutDialog()">Hủy</button>
                <button class="btn btn-danger" onclick="confirmLogout()">Đăng xuất</button>
            </div>
        </div>
    </div>

    <script>
        function showLogoutDialog() {
            document.getElementById('logoutDialog').classList.add('show');
        }

        function hideLogoutDialog() {
            document.getElementById('logoutDialog').classList.remove('show');
        }

        function confirmLogout() {
            window.location.href = 'logout.php';
        }

        // Close dialog when clicking outside
        document.getElementById('logoutDialog').addEventListener('click', function(e) {
            if (e.target === this) {
                hideLogoutDialog();
            }
        });
    </script>
</body>
</html>
