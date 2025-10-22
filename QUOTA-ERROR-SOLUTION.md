# 🚨 Giải quyết lỗi Quota trên DirectAdmin

## ❌ Lỗi gặp phải:

```
Warning: Saved filesize is less than uploaded filesize. Check quotas.
```

## 🔍 Nguyên nhân:

-   **File ZIP quá lớn**: 146.17 MB vượt quá quota được cấp phát
-   **DirectAdmin quota limit**: Thường giới hạn ở 50-100MB cho shared hosting
-   **Disk space**: Server không đủ dung lượng

---

## ✅ Giải pháp đã chuẩn bị:

### 📦 **3 Package options:**

#### 1. **halovpbank2025-minimal.zip** (8.26 MB) ⭐ **RECOMMENDED**

-   ✅ **Kích thước nhỏ nhất**
-   ✅ **Chỉ chứa files cần thiết**
-   ✅ **Không có fonts, PSD files**
-   ✅ **Phù hợp với quota thấp**

#### 2. **halovpbank2025-optimized.zip** (8.26 MB)

-   ✅ **Tương tự minimal**
-   ✅ **Loại bỏ files lớn**
-   ✅ **Giữ nguyên chức năng**

#### 3. **halovpbank2025-directadmin.zip** (146.17 MB)

-   ❌ **Quá lớn cho quota thấp**
-   ✅ **Chứa đầy đủ files**

---

## 🚀 Hướng dẫn upload:

### **Bước 1: Upload package nhỏ**

1. **Sử dụng**: `halovpbank2025-minimal.zip` (8.26 MB)
2. **Upload** lên DirectAdmin File Manager
3. **Extract** file ZIP
4. **Xóa** file ZIP sau khi extract

### **Bước 2: Kiểm tra quota**

1. Vào **"Account Information"** trong DirectAdmin
2. Kiểm tra **"Disk Usage"** và **"Quota"**
3. Nếu vẫn còn lỗi, liên hệ hosting provider

### **Bước 3: Thêm fonts sau (nếu cần)**

1. Upload fonts riêng lẻ sau khi deploy
2. Hoặc sử dụng Google Fonts thay thế

---

## 📋 Checklist sau khi upload:

### ✅ **Core Files**

-   [ ] `index.php` - Trang chủ
-   [ ] `game.php` - Game interface
-   [ ] `reward.php` - Reward page
-   [ ] `config.php` - Database config
-   [ ] `admin/` - Admin panel
-   [ ] `api/` - API endpoints

### ✅ **Assets**

-   [ ] `assets/css/` - Stylesheets
-   [ ] `assets/js/` - JavaScript files
-   [ ] `assets/images/` - Essential images
-   [ ] `database-setup-directadmin.sql` - Database schema

### ✅ **Missing (có thể thêm sau)**

-   [ ] `assets/fonts/` - Font files (OTF)
-   [ ] `assets/designs/` - PSD files
-   [ ] Documentation files

---

## 🔧 Alternative Solutions:

### **Option 1: Upload từng thư mục**

1. Upload `admin/` folder trước
2. Upload `api/` folder
3. Upload `assets/` folder
4. Upload các file PHP riêng lẻ

### **Option 2: Sử dụng FTP**

1. Kết nối FTP đến server
2. Upload trực tiếp không qua DirectAdmin
3. Bypass quota limit của web interface

### **Option 3: Liên hệ hosting**

1. Yêu cầu tăng quota
2. Hoặc upgrade hosting plan
3. Hoặc xóa files không cần thiết

---

## 🎯 **Recommended Action:**

### **Sử dụng `halovpbank2025-minimal.zip` (8.26 MB)**

**Lý do:**

-   ✅ **Kích thước nhỏ** - phù hợp quota thấp
-   ✅ **Đầy đủ chức năng** - game hoạt động bình thường
-   ✅ **Không cần fonts** - có thể dùng system fonts
-   ✅ **Dễ upload** - ít khả năng lỗi quota

**Sau khi deploy thành công:**

1. Test tất cả chức năng
2. Thêm fonts nếu cần thiết
3. Upload thêm files nếu có quota

---

## 📞 **Nếu vẫn gặp lỗi:**

### **Contact hosting provider:**

-   Yêu cầu tăng quota
-   Hoặc upgrade hosting plan
-   Hoặc xóa files cũ không dùng

### **Alternative hosting:**

-   Sử dụng VPS thay vì shared hosting
-   Hoặc hosting có quota cao hơn

---

## ✅ **Kết luận:**

**Sử dụng `halovpbank2025-minimal.zip` (8.26 MB) để giải quyết lỗi quota!**

Package này chứa đầy đủ chức năng cần thiết và có kích thước phù hợp với hầu hết quota limits.
