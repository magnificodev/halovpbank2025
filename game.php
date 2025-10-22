<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPBank Solution Day - Khám Phá</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="game-page">
    <div class="desktop-container">
        <div class="mobile-frame">
            <div class="container">
                <div class="instructions">
                    <p><strong>Hoàn thành 2</strong> trong số các nhiệm vụ và <strong>thực hiện trải nghiệm đặc biệt tại booth Hallo Shop</strong> để nhận quà từ chương trình</p>
                </div>

                <!-- Stations list (5 items) -->
                <ul class="station-checklist">
                    <li class="station-checklist-item" data-station="HALLO_GLOW">
                        <img class="status-icon" src="assets/images/no-checked-box.png" alt="Chưa hoàn thành" />
                        <div class="station-text">
                            <h4 class="station-title">HALLO GLOW</h4>
                            <p class="station-desc">Chụp hình check in</p>
                        </div>
                    </li>
                    <li class="station-checklist-item" data-station="HALLO_SOLUTION">
                        <img class="status-icon" src="assets/images/no-checked-box.png" alt="Chưa hoàn thành" />
                        <div class="station-text">
                            <h4 class="station-title">HALLO SOLUTION</h4>
                            <p class="station-desc">Thử thách game công nghệ<br>“không chạm”</p>
                        </div>
                    </li>
                    <li class="station-checklist-item" data-station="HALLO_SUPER_SINH_LOI">
                        <img class="status-icon" src="assets/images/no-checked-box.png" alt="Chưa hoàn thành" />
                        <div class="station-text">
                            <h4 class="station-title">HALLO SUPER SINH LỜI PREMIER</h4>
                            <p class="station-desc">Thử thách hứng tiền lời</p>
                        </div>
                    </li>
                    <li class="station-checklist-item" data-station="HALLO_WIN">
                        <img class="status-icon" src="assets/images/no-checked-box.png" alt="Chưa hoàn thành" />
                        <div class="station-text">
                            <h4 class="station-title">HALLO WIN</h4>
                            <p class="station-desc">Trò chơi đuổi hình bắt chữ vui nhộn</p>
                        </div>
                    </li>
                    <li class="station-checklist-item" data-station="HALLO_SHOP">
                        <img class="status-icon" src="assets/images/no-checked-box.png" alt="Chưa hoàn thành" />
                        <div class="station-text">
                            <h4 class="station-title">HALLO SHOP</h4>
                            <p class="station-desc">Trải nghiệm các giải pháp<br>thanh toán từ VPBank</p>
                        </div>
                    </li>
                </ul>

                <!-- Action Buttons Row (image-based) -->
                <div class="action-image-buttons">
                    <img id="scanQRBtn" class="action-image-btn" src="assets/images/scan-button.png" alt="QUÉT MÃ QR" role="button" />
                    <img id="claimRewardBtn" class="action-image-btn" src="assets/images/claim-button.png" alt="QUAY SỐ TRÚNG QUÀ" role="button" />
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

    <script src="assets/js/html5-qrcode.min.js" type="text/javascript"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/qr-scanner.js"></script>
</body>
</html>
