<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPBank Solution Day - Số Điện Thoại</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/game.css">
    <style>
        body {
            background-image: url('assets/images/background-3.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .desktop-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .mobile-frame {
            width: 360px;
            min-height: 100vh;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .container {
            padding: 40px 30px;
            text-align: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .header {
            margin-bottom: 40px;
        }
        
        .logo {
            margin-bottom: 20px;
        }
        
        .logo-img {
            height: 60px;
            width: auto;
        }
        
        .main-title {
            font-family: 'Gilroy', sans-serif;
            font-weight: 800;
            font-size: 24px;
            color: #0b66cf;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .phone-container {
            margin: 30px 0;
        }
        
        .phone-display {
            background: linear-gradient(135deg, #0b66cf, #02d15e);
            color: white;
            padding: 20px;
            border-radius: 15px;
            font-family: 'Gilroy', sans-serif;
            font-weight: 700;
            font-size: 28px;
            letter-spacing: 2px;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(11, 102, 207, 0.3);
            border: 3px solid transparent;
            background-clip: padding-box;
        }
        
        .instruction {
            font-family: 'Gilroy', sans-serif;
            font-weight: 500;
            font-size: 16px;
            color: #333;
            line-height: 1.6;
            margin: 0;
        }
        
        .footer {
            margin-top: auto;
            padding-top: 30px;
        }
        
        .solution-day-text {
            font-family: 'Gilroy', sans-serif;
            font-weight: 800;
            font-size: 18px;
            color: #0b66cf;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        @media (max-width: 768px) {
            .desktop-container {
                padding: 0;
            }
            
            .mobile-frame {
                width: 100%;
                min-height: 100vh;
                border-radius: 0;
            }
            
            .container {
                padding: 30px 20px;
            }
            
            .main-title {
                font-size: 20px;
            }
            
            .phone-display {
                font-size: 24px;
                padding: 15px;
            }
            
            .instruction {
                font-size: 14px;
            }
        }
    </style>
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
                    <h1 class="main-title">SỐ ĐIỆN THOẠI CỦA BẠN</h1>
                </header>

                <!-- Phone Number Display -->
                <div class="phone-container">
                    <div id="phoneDisplay" class="phone-display">
                        <!-- Phone number will be loaded here -->
                    </div>
                    <p class="instruction">
                        Cảm ơn bạn đã tham gia VPBank Solution Day!<br>
                        Hẹn gặp lại bạn trong những sự kiện tiếp theo!
                    </p>
                </div>

                <!-- Footer -->
                <footer class="footer">
                    <div class="solution-day-text">SOLUTION DAY</div>
                </footer>
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
