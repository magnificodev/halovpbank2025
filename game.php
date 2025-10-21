<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPBank Solution Day - Khám Phá</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="logo">
                <img src="assets/images/vpbank-logo.png" alt="VPBank" class="logo-img">
            </div>
            <h1 class="main-title">KHÁM PHÁ GIẢI PHÁP THANH TOÁN</h1>
        </header>

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

        <!-- Footer -->
        <footer class="footer">
            <div class="solution-day-text">SOLUTION DAY</div>
        </footer>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/qr-scanner.js"></script>
</body>
</html>
