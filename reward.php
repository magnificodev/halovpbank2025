<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPBank Solution Day - Số Điện Thoại</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .desktop-container {
            background-image: url('assets/images/background-3.png');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .mobile-frame {
            width: 360px;
            min-height: 100vh;
            background: transparent;
            border-radius: 20px;
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

        .phone-container {
            position: absolute;
            top: 160px;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 300px;
        }

        .phone-display {
            background: white;
            color: #0b66cf;
            padding: 20px;
            border-radius: 24px;
            font-family: 'Gilroy', sans-serif;
            font-weight: 700;
            font-size: 28px;
            letter-spacing: 2px;
            margin-bottom: 24px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border: 3px solid transparent;
            background-clip: padding-box;
            position: relative;
        }

        .phone-display::before {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            background: linear-gradient(135deg, #0b66cf, #02d15e);
            border-radius: 27px;
            z-index: -1;
        }

        .instruction {
            font-family: 'Gilroy', sans-serif;
            font-weight: 500;
            font-size: 20px;
            color: white;
            line-height: 1.6;
            margin: 0;
            text-align: center;
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
