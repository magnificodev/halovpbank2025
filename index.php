<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPBank Solution Day - Đăng Ký</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="desktop-container">
        <div class="mobile-frame">
            <div class="container">
                <!-- Header -->
                <header class="header">
                    <div class="logo">
                        <img src="assets/images/vpbank-logo.svg" alt="VPBank" class="logo-img">
                    </div>
                    <h1 class="main-title">SIÊU GIẢI PHÁP TOÀN DIỆN</h1>
                    <p class="subtitle">WIN MỌI TRẢI NGHIỆM</p>
                </header>

                <!-- Registration Form -->
                <div class="form-container">
                    <form id="registrationForm" class="registration-form">
                        <div class="form-group">
                            <input type="text" id="fullName" name="full_name" placeholder="HỌ TÊN" required>
                        </div>
                        <div class="form-group">
                            <input type="tel" id="phone" name="phone" placeholder="SỐ ĐIỆN THOẠI" required>
                        </div>
                        <div class="form-group">
                            <input type="email" id="email" name="email" placeholder="EMAIL" required>
                        </div>
                        <img src="assets/images/join-button.png" alt="Tham gia" class="join-button" onclick="document.getElementById('registrationForm').submit()">
                    </form>
                </div>

                <!-- Footer -->
                <footer class="footer">
                    <div class="solution-day-text">SOLUTION DAY</div>
                </footer>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
