<!-- dae86456-2344-488a-a049-6fbd43a7b642 95be3a57-861b-429a-8279-ba605337e79a -->
# VPBank Solution Day - Game Web Application

## Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla JS hoặc lightweight framework)
- **QR Scanner**: html5-qrcode library (hỗ trợ camera scanning)
- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Hosting**: DirectAdmin compatible (PHP + MySQL)

## Database Schema

### Table: `users`

- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `full_name` (VARCHAR 255)
- `phone` (VARCHAR 20, UNIQUE)
- `email` (VARCHAR 255)
- `user_token` (VARCHAR 64, UNIQUE) - để tracking qua URL
- `created_at` (TIMESTAMP)

### Table: `user_progress`

- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `user_id` (INT, FOREIGN KEY)
- `station_id` (VARCHAR 50) - HALLO_GLOW, HALLO_SOLUTION, etc.
- `completed_at` (TIMESTAMP)
- UNIQUE constraint trên (user_id, station_id)

### Table: `gift_codes`

- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `code` (VARCHAR 20, UNIQUE)
- `user_id` (INT, NULL) - NULL nếu chưa được claim
- `claimed_at` (TIMESTAMP, NULL)

## File Structure

```
/
├── index.php (landing page - Giao diện 1: Form đăng ký)
├── game.php (Giao diện 2 & 3: Quét QR và danh sách trạm)
├── reward.php (Giao diện 4: Hiển thị mã quà tặng)
├── api/
│   ├── register.php (Xử lý đăng ký user)
│   ├── check-station.php (Verify QR code của station)
│   ├── get-progress.php (Lấy tiến độ user)
│   ├── claim-reward.php (Generate mã quà tặng unique)
│   └── db.php (Database connection)
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   ├── main.js
│   │   ├── qr-scanner.js
│   │   └── html5-qrcode.min.js
│   └── images/ (extracted từ PSD)
├── config.php (Database config)
└── .htaccess (URL rewriting nếu cần)
```

## Implementation Flow

### 1. Màn hình đăng ký (index.php)

- Form validation: họ tên (không rỗng), email (regex), số điện thoại (10-11 số)
- Submit → API tạo user + generate unique `user_token`
- Lưu `user_token` vào localStorage
- Redirect sang `game.php?token={user_token}`

### 2. Màn hình game (game.php)

- Kiểm tra `user_token` từ URL hoặc localStorage
- Load tiến độ user từ database (stations đã hoàn thành)
- Hiển thị 5 stations với trạng thái (✓ hoặc ✗)
- Button "QUÉT MÃ QR":
  - Request camera permission
  - Sử dụng html5-qrcode library
  - Scan QR code chứa format: `https://domain.com/station?id=HALLO_GLOW&verify={hash}`
  - Gửi lên API để verify và mark complete
- Khi quét bằng app external (Zalo, etc):
  - URL sẽ mở: `game.php?station=HALLO_GLOW&verify={hash}`
  - Auto-detect station parameter → call API mark complete → redirect về game.php với token

### 3. Logic "QUAY SỐ TRÚNG QUÀ"

- Khi user complete ≥3 stations → hiện button "QUAY SỐ TRÚNG QUÀ"
- Click button → animation quay số
- Call API `claim-reward.php` để:
  - Random chọn 1 gift_code chưa được claim
  - Assign cho user_id
  - Return mã code
- Redirect sang `reward.php?token={user_token}`

### 4. Màn hình mã quà tặng (reward.php)

- Hiển thị code đã claim của user
- Nếu chưa claim → redirect về game.php

## Session Persistence Strategy

1. **localStorage**: Lưu `user_token` khi đăng ký/vào game
2. **URL Parameter**: Mỗi QR code chứa `?station=xxx&verify=yyy`
3. **Auto-recovery**: 

   - Khi user vào bất kỳ page nào, check localStorage có token không
   - Nếu URL có `station` param mà không có token → redirect về index.php để đăng ký
   - Nếu có token → sync progress từ DB

## QR Code Generation

Tạo 5 QR codes cho 5 stations với format:

```
https://yourdomain.com/game.php?station=HALLO_GLOW&verify={SHA256_hash}
https://yourdomain.com/game.php?station=HALLO_SOLUTION&verify={SHA256_hash}
https://yourdomain.com/game.php?station=HALLO_SUPER_SINH_LOI&verify={SHA256_hash}
https://yourdomain.com/game.php?station=HALLO_SHOP&verify={SHA256_hash}
https://yourdomain.com/game.php?station=HALLO_WIN&verify={SHA256_hash}
```

## Security Considerations

- Validate `verify` hash trong QR code để prevent fake scanning
- Rate limiting cho API endpoints
- Sanitize user inputs (SQL injection prevention)
- HTTPS required cho camera access

## Responsive Design

- Mobile-first approach
- Breakpoints: 320px, 375px, 768px, 1024px
- Touch-friendly buttons (minimum 44x44px)
- Camera viewport responsive

### To-dos

- [ ] Tạo cấu trúc thư mục và files cơ bản (index.php, game.php, reward.php, API endpoints, assets folders)
- [ ] Tạo file SQL schema và PHP database connection (users, user_progress, gift_codes tables)
- [ ] Đợi user cung cấp file PSD, sau đó extract images, logo, backgrounds
- [ ] Implement màn hình đăng ký (Giao diện 1) với form validation và API register.php
- [ ] Implement UI màn hình game (Giao diện 2 & 3) với danh sách 5 stations và buttons
- [ ] Tích hợp html5-qrcode library và xử lý camera scanning + external QR app links
- [ ] Tạo API endpoints: check-station.php, get-progress.php để track tiến độ user
- [ ] Implement logic quay số trúng quà và API claim-reward.php với unique code generation
- [ ] Implement màn hình hiển thị mã quà tặng (Giao diện 4)
- [ ] Implement localStorage + URL token recovery để giữ phiên user khi out/in web
- [ ] Tạo 5 QR codes cho 5 stations với verify hash và export file images
- [ ] Hoàn thiện CSS responsive mobile-first cho tất cả màn hình
- [ ] Test toàn bộ flow: đăng ký → quét QR → track progress → quay thưởng → hiển thị mã