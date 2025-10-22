# 🚀 Hướng dẫn Upload Thủ Công lên DirectAdmin

## ❌ **Vấn đề hiện tại:**

-   File ZIP sử dụng backslashes (Windows paths)
-   DirectAdmin yêu cầu forward slashes (Unix paths)
-   Lỗi: "appears to use backslashes as path separators"

## ✅ **Giải pháp: Upload từng thư mục**

### **📋 Các thư mục cần upload:**

#### **1. Core PHP Files (Upload trực tiếp)**

-   `index.php`
-   `game.php`
-   `reward.php`
-   `config-directadmin.php` → rename thành `config.php`

#### **2. Admin Panel**

-   Upload toàn bộ thư mục `admin/`

#### **3. API Endpoints**

-   Upload toàn bộ thư mục `api/`

#### **4. Assets**

-   Upload thư mục `assets/css/`
-   Upload thư mục `assets/js/`
-   Upload thư mục `assets/images/`
-   Upload thư mục `assets/fonts/`

#### **5. Database**

-   Upload file `database-setup-directadmin.sql`

---

## 🔧 **Hướng dẫn chi tiết:**

### **Bước 1: Upload Core Files**

1. Mở DirectAdmin File Manager
2. Navigate đến `public_html`
3. Upload các file PHP:
    - `index.php`
    - `game.php`
    - `reward.php`
    - `config-directadmin.php` (sau đó rename thành `config.php`)

### **Bước 2: Upload Admin Panel**

1. Tạo thư mục `admin` trong `public_html`
2. Upload tất cả files từ thư mục `admin/` local vào `public_html/admin/`

### **Bước 3: Upload API**

1. Tạo thư mục `api` trong `public_html`
2. Upload tất cả files từ thư mục `api/` local vào `public_html/api/`

### **Bước 4: Upload Assets**

1. Tạo thư mục `assets` trong `public_html`
2. Tạo các subfolder: `css`, `js`, `images`, `fonts`
3. Upload files tương ứng vào từng subfolder

### **Bước 5: Upload Database Schema**

1. Upload `database-setup-directadmin.sql` vào `public_html`

---

## 📁 **Cấu trúc thư mục sau khi upload:**

```
public_html/
├── index.php
├── game.php
├── reward.php
├── config.php (renamed from config-directadmin.php)
├── database-setup-directadmin.sql
├── admin/
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── users.php
│   ├── gifts.php
│   ├── logs.php
│   ├── qr-manager.php
│   ├── _auth.php
│   └── _template.php
├── api/
│   ├── check-station.php
│   ├── claim-reward.php
│   ├── db.php
│   ├── generate-qr.php
│   ├── get-progress.php
│   ├── get-user.php
│   └── register.php
└── assets/
    ├── css/
    │   ├── admin.css
    │   ├── common.css
    │   ├── game.css
    │   └── style.css
    ├── js/
    │   ├── html5-qrcode.min.js
    │   ├── main.js
    │   └── qr-scanner.js
    ├── images/
    │   ├── background-1.png
    │   ├── background-2.png
    │   ├── background-3.png
    │   ├── background.png
    │   ├── checked-box.png
    │   ├── no-checked-box.png
    │   ├── scan-button.png
    │   ├── claim-button.png
    │   └── join-button.png
    └── fonts/
        ├── SVN-GILROY BOLD.OTF
        ├── SVN-GILROY REGULAR.OTF
        └── SVN-GILROY SEMIBOLD.OTF
```

---

## ⚡ **Cách nhanh nhất:**

### **Option 1: Upload từng thư mục**

1. **Compress từng thư mục riêng lẻ** trên Windows
2. **Upload từng ZIP nhỏ** lên DirectAdmin
3. **Extract từng thư mục** một

### **Option 2: Sử dụng FTP**

1. **Kết nối FTP** đến server
2. **Upload trực tiếp** không qua DirectAdmin web interface
3. **Bypass** vấn đề path separators

### **Option 3: Sử dụng 7-Zip**

1. **Cài đặt 7-Zip** trên Windows
2. **Tạo ZIP với Unix paths** bằng 7-Zip
3. **Upload file ZIP** đã sửa

---

## 🎯 **Recommended: Upload từng thư mục**

### **Lợi ích:**

-   ✅ **Không bị lỗi path separators**
-   ✅ **Dễ kiểm soát** từng thư mục
-   ✅ **Có thể upload song song** nhiều thư mục
-   ✅ **Dễ debug** nếu có lỗi

### **Thứ tự upload:**

1. **Core files** (index.php, game.php, reward.php, config.php)
2. **API folder** (api/)
3. **Admin folder** (admin/)
4. **Assets folders** (assets/css/, assets/js/, assets/images/, assets/fonts/)
5. **Database schema** (database-setup-directadmin.sql)

---

## 🚀 **Sau khi upload xong:**

### **Bước 1: Database Setup**

1. Tạo database: `halovpbank` (becomes `vpbankgame_halovpbank`)
2. Tạo user: `user` (becomes `vpbankgame_user`)
3. Set password: `VpBank2025!@#`
4. Import `database-setup-directadmin.sql`

### **Bước 2: Test**

1. Test homepage: `https://halovpbank.beetech.one`
2. Test admin: `https://halovpbank.beetech.one/admin`
3. Test QR scanner trên mobile

**Cách này sẽ giải quyết được vấn đề backslashes và deploy thành công!** 🎉
