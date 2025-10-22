<?php
session_start();
require_once '../config.php';
require_once '../api/db.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

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
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
</head>
<body class="admin-page">
    <div class="admin-container">
        <header class="admin-header">
            <h1>QR Code Manager</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="users.php">Users</a>
                <a href="qr-manager.php" class="active">QR Manager</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <main class="admin-main">
            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="card">
                <h2>Generate QR Code</h2>
                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="station_id">Select Station:</label>
                        <select name="station_id" id="station_id" required>
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
                    <h2>Generated QR Code</h2>
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
                    <h2>Scan Statistics</h2>
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

    <style>
        .qr-display {
            display: flex;
            gap: 20px;
            align-items: flex-start;
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
    </style>
</body>
</html>
