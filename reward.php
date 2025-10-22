<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPBank Solution Day - Số Điện Thoại</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/pages.css">
    <link rel="stylesheet" href="assets/css/reward.css">
</head>
<body class="reward-page">
    <div class="desktop-container">
        <div class="mobile-frame">
            <div class="container">
                <!-- Phone Number Display -->
                <div class="phone-container">
                    <div id="phoneDisplay" class="phone-display">
                        <!-- Phone number will be loaded here -->
                    </div>
                    <p class="instruction">
                        Ghé khu vực Hallo Shop thực hiện trải nghiệm thú vị để nhận được vòng quay may mắn!
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Load user's phone number
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');

            if (token) {
                // Fetch user data from API
                fetch(`api/get-user.php?token=${token}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.user) {
                            document.getElementById('phoneDisplay').textContent = data.user.phone;
                        } else {
                            document.getElementById('phoneDisplay').textContent = 'Không tìm thấy thông tin';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('phoneDisplay').textContent = 'Lỗi tải dữ liệu';
                    });
            } else {
                document.getElementById('phoneDisplay').textContent = 'Thiếu thông tin xác thực';
            }
        });
    </script>
</body>
</html>
