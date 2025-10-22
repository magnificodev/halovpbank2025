<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPBank Solution Day - Test Reward Page</title>
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
            margin-top: 0;
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
            background: white;
            color: #0b66cf;
            padding: 20px;
            border-radius: 15px;
            font-family: 'Gilroy', sans-serif;
            font-weight: 700;
            font-size: 28px;
            letter-spacing: 2px;
            margin-bottom: 20px;
            position: relative;
            background-clip: padding-box;
        }

        .phone-display::before {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            background: linear-gradient(135deg, #0b66cf, #02d15e);
            border-radius: 18px;
            z-index: -1;
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
                <!-- Phone Number Display -->
                <div class="phone-container">
                    <div class="phone-display">
                        0901234567
                    </div>
                    <p class="instruction">
                        Cảm ơn bạn đã tham gia VPBank Solution Day!<br>
                        Hẹn gặp lại bạn trong những sự kiện tiếp theo!
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
