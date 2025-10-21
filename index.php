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
                            <label for="full_name">HỌ TÊN</label>
                            <div class="input-shell">
                                <input class="input" type="text" id="full_name" name="full_name" placeholder=" " autocomplete="name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="phone">SỐ ĐIỆN THOẠI</label>
                            <div class="input-shell">
                                <input class="input" type="tel" id="phone" name="phone" placeholder=" " autocomplete="tel" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">EMAIL</label>
                            <div class="input-shell">
                                <input class="input" type="email" id="email" name="email" placeholder=" " autocomplete="email" required>
                            </div>
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
