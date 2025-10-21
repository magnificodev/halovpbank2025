<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPBank Solution Day - Khám Phá</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/game.css">
</head>
<body class="game-page">
    <div class="desktop-container">
        <div class="mobile-frame">
            <div class="container">
                <!-- Instructions -->
                <div class="instructions">
                    <p>Hoàn thành 3 trong số các nhiệm vụ để tham gia quay thường trúng quà 100%</p>
                </div>

                <!-- Stations List -->
                <div class="stations-container">
                    <div id="stationsList" class="stations-list">
                        <!-- Stations will be loaded dynamically -->
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button id="scanQRBtn" class="scan-button">QUÉT MÃ QR</button>
                    <button id="claimRewardBtn" class="claim-button" style="display: none;">QUAY SỐ TRÚNG QUÀ</button>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Scanner Modal -->
    <div id="qrScannerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Quét Mã QR</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div id="qr-reader"></div>
                <p class="scan-instruction">Hướng camera về phía mã QR để quét</p>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/qr-scanner.js"></script>
</body>
</html>
