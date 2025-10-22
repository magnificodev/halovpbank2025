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
                <!-- Page 2 UI cleared intentionally. Add new layout here. -->
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
