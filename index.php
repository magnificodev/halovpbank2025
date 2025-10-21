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
                <!-- Registration Form -->
                <div class="form-container">
                    <form id="registrationForm" class="registration-form">
                        <div class="form-group">
                            <label for="full_name"></label>
                            <input type="text" id="full_name" name="full_name" placeholder="HỌ TÊN" required>
                        </div>
                        <div class="form-group">
                            <label for="phone"></label>
                            <input type="tel" id="phone" name="phone" placeholder="SỐ ĐIỆN THOẠI" required>
                        </div>
                        <div class="form-group">
                            <label for="email"></label>
                            <input type="email" id="email" name="email" placeholder="EMAIL" required>
                        </div>
                        <button type="submit" class="join-button">
                            <img src="assets/images/join-button.png" alt="Tham gia">
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
