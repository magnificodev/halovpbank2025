<?php
require_once '_auth.php';
require_once '../api/db.php';
require_once '_template.php';

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

renderAdminHeader('qr-manager');
?>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<style>
    .card {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        margin-bottom: 24px;
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
    }
    
    .card-subtitle {
        color: #6b7280;
        font-size: 14px;
        margin-top: 4px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group:last-child {
        margin-bottom: 0;
    }
    
    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }
    
    .form-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        background: #fff;
        cursor: pointer;
    }
    
    .form-select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
    }
    
    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
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
    
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    
    .alert-success {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    
    .alert-error {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    
    .table-responsive {
        overflow-x: auto;
        margin-top: 20px;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    
    .table th {
        background: #f8fafc;
        color: #374151;
        font-weight: 600;
        text-align: left;
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .table td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        color: #6b7280;
    }
    
    .table tbody tr:hover {
        background: #f8fafc;
    }
    
    .qr-display {
        display: flex;
        gap: 20px;
        align-items: flex-start;
        margin-top: 20px;
    }
    
    .qr-info {
        flex: 1;
    }
    
    .qr-code {
        flex-shrink: 0;
    }
    
    .qr-code canvas {
        border: 1px solid #ddd;
        border-radius: 8px;
    }
    
    @media (max-width: 768px) {
        .qr-display {
            flex-direction: column;
        }
    }
    
    /* Dark mode overrides */
    body.dark .card {
        background: #111827;
        border-color: #1f2937;
    }
    
    body.dark .card-title {
        color: #e5e7eb;
    }
    
    body.dark .card-subtitle {
        color: #94a3b8;
    }
    
    body.dark .form-label {
        color: #e5e7eb;
    }
    
    body.dark .form-select {
        background: #1f2937;
        border-color: #374151;
        color: #e5e7eb;
    }
    
    body.dark .form-select:focus {
        border-color: var(--accent);
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
    
    body.dark .qr-code canvas {
        border-color: #374151;
    }
</style>
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
<?php renderAdminFooter(); ?>
