# 🚀 Hướng dẫn Deploy VPBank Game lên DirectAdmin

## 📋 Chuẩn bị trước khi deploy

### ✅ Đã chuẩn bị sẵn:

-   ✅ **File ZIP**: `halovpbank2025-directadmin.zip` (146.17 MB)
-   ✅ **Database config**: `config-directadmin.php` → `config.php`
-   ✅ **Database schema**: `database-setup-directadmin.sql`
-   ✅ **Deployment guide**: `DEPLOYMENT-GUIDE.md`

### 🔧 Thông tin cần thiết:

-   **Domain**: `https://halovpbank.beetech.one`
-   **Database name**: `vpbankgame_halovpbank`
-   **Database user**: `vpbankgame_user`
-   **Database password**: `VpBank2025!@#`

---

## 📤 Bước 1: Upload file lên DirectAdmin

### 1.1 Đăng nhập DirectAdmin

-   Truy cập: `https://halovpbank.beetech.one:2222`
-   Đăng nhập với tài khoản DirectAdmin

### 1.2 Mở File Manager

-   Click vào **"File Manager"** trong menu chính
-   Navigate đến thư mục `public_html`

### 1.3 Upload file ZIP

-   Click **"Upload"** button
-   Chọn file `halovpbank2025-directadmin.zip`
-   Đợi upload hoàn tất (có thể mất vài phút do file 146MB)

### 1.4 Extract file ZIP

-   Right-click vào file `halovpbank2025-directadmin.zip`
-   Chọn **"Extract"**
-   Chọn **"Extract to current directory"**
-   Đợi extract hoàn tất

### 1.5 Xóa file ZIP (tùy chọn)

-   Sau khi extract thành công, có thể xóa file ZIP để tiết kiệm dung lượng

---

## 🗄️ Bước 2: Tạo Database

### 2.1 Mở MySQL Database

-   Click vào **"MySQL Databases"** trong menu chính

### 2.2 Tạo Database

-   Trong phần **"Create New Database"**:
    -   Database name: `halovpbank`
    -   Click **"Create Database"**
    -   **Lưu ý**: DirectAdmin sẽ tự động thêm prefix `vpbankgame_` → tên thực tế: `vpbankgame_halovpbank`

### 2.3 Tạo Database User

-   Trong phần **"Create New User"**:
    -   Username: `user`
    -   Password: `VpBank2025!@#`
    -   Click **"Create User"**
    -   **Lưu ý**: DirectAdmin sẽ tự động thêm prefix `vpbankgame_` → tên thực tế: `vpbankgame_user`

### 2.4 Gán quyền cho User

-   Trong phần **"Add User To Database"**:
    -   User: `vpbankgame_user`
    -   Database: `vpbankgame_halovpbank`
    -   Check **"ALL PRIVILEGES"**
    -   Click **"Add User To Database"**

---

## 📊 Bước 3: Import Database Schema

### 3.1 Mở phpMyAdmin

-   Click vào **"phpMyAdmin"** trong menu chính
-   Hoặc truy cập: `https://halovpbank.beetech.one/phpmyadmin`

### 3.2 Chọn Database

-   Click vào database `vpbankgame_halovpbank` trong sidebar trái

### 3.3 Import Schema

-   Click tab **"Import"**
-   Click **"Choose File"**
-   Chọn file `database-setup-directadmin.sql` từ thư mục `public_html`
-   Click **"Go"** để import
-   Đợi import hoàn tất

---

## ⚙️ Bước 4: Kiểm tra Configuration

### 4.1 Kiểm tra file config.php

-   Mở file `public_html/config.php`
-   Đảm bảo các thông số đúng:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'vpbankgame_halovpbank');
define('DB_USER', 'vpbankgame_user');
define('DB_PASS', 'VpBank2025!@#');
```

### 4.2 Kiểm tra file permissions

-   Đảm bảo các file có quyền đọc (644)
-   Đảm bảo các thư mục có quyền đọc/execute (755)

---

## 🧪 Bước 5: Test Application

### 5.1 Test trang chủ

-   Truy cập: `https://halovpbank.beetech.one`
-   Kiểm tra trang đăng ký hiển thị đúng

### 5.2 Test admin panel

-   Truy cập: `https://halovpbank.beetech.one/admin`
-   Đăng nhập với tài khoản admin
-   Kiểm tra các chức năng admin

### 5.3 Test QR scanner

-   Truy cập: `https://halovpbank.beetech.one/game.php`
-   Test chức năng quét QR code
-   Kiểm tra camera hoạt động trên mobile

---

## 🔧 Troubleshooting

### Lỗi thường gặp:

#### 1. **Database connection error**

-   Kiểm tra thông tin database trong `config.php`
-   Đảm bảo user có quyền truy cập database

#### 2. **File not found errors**

-   Kiểm tra file permissions
-   Đảm bảo tất cả files đã được upload đúng

#### 3. **QR scanner không hoạt động**

-   Đảm bảo sử dụng HTTPS
-   Kiểm tra camera permissions trên mobile

#### 4. **CSS/JS không load**

-   Kiểm tra đường dẫn assets
-   Clear browser cache

---

## 📱 Test trên Mobile

### iOS Safari:

-   Truy cập: `https://halovpbank.beetech.one`
-   Test QR scanner với camera sau
-   Kiểm tra responsive design

### Android Chrome:

-   Truy cập: `https://halovpbank.beetech.one`
-   Test QR scanner
-   Kiểm tra camera permissions

---

## 🎯 Final Checklist

-   [ ] File ZIP đã upload và extract thành công
-   [ ] Database `vpbankgame_halovpbank` đã tạo
-   [ ] User `vpbankgame_user` đã tạo và có quyền
-   [ ] Database schema đã import thành công
-   [ ] File `config.php` có thông tin đúng
-   [ ] Trang chủ load được: `https://halovpbank.beetech.one`
-   [ ] Admin panel hoạt động: `https://halovpbank.beetech.one/admin`
-   [ ] QR scanner hoạt động trên mobile
-   [ ] Test đăng ký user mới
-   [ ] Test quét QR code và hoàn thành nhiệm vụ

---

## 🆘 Support

Nếu gặp vấn đề, hãy kiểm tra:

1. **Error logs** trong DirectAdmin
2. **Browser console** để xem JavaScript errors
3. **Network tab** để xem API calls
4. **Database** để xem data có được lưu đúng không

**Chúc bạn deploy thành công!** 🎉
