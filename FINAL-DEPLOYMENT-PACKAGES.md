# 📦 VPBank Game - Final Deployment Packages

## 🎯 **RECOMMENDED: `halovpbank2025-with-fonts.zip` (8.41 MB)**

### ✅ **Bao gồm:**

-   **Core PHP files** (index.php, game.php, reward.php)
-   **Admin panel** (admin/)
-   **API endpoints** (api/)
-   **Essential CSS/JS** (assets/css/, assets/js/)
-   **All required images** (backgrounds, buttons, icons)
-   **Gilroy fonts** (Bold, Regular, SemiBold) ⭐
-   **Database config** (config.php)
-   **Database schema** (database-setup-directadmin.sql)

### ❌ **Loại bỏ:**

-   PSD design files (không cần thiết)
-   Documentation files (có thể thêm sau)
-   Database folder (chỉ cần SQL file)

---

## 📊 **So sánh các packages:**

| Package            | Size        | Fonts | Images | PSD | Status             |
| ------------------ | ----------- | ----- | ------ | --- | ------------------ |
| **with-fonts.zip** | **8.41 MB** | ✅    | ✅     | ❌  | ⭐ **RECOMMENDED** |
| minimal.zip        | 8.26 MB     | ❌    | ✅     | ❌  | Alternative        |
| optimized.zip      | 8.26 MB     | ❌    | ✅     | ❌  | Alternative        |
| directadmin.zip    | 146.17 MB   | ✅    | ✅     | ✅  | ❌ Too large       |

---

## 🚀 **Hướng dẫn deploy:**

### **Bước 1: Upload package**

1. **Sử dụng**: `halovpbank2025-with-fonts.zip` (8.41 MB)
2. **Upload** lên DirectAdmin File Manager
3. **Extract** file ZIP
4. **Xóa** file ZIP sau khi extract

### **Bước 2: Database setup**

1. Tạo database: `halovpbank` (becomes `vpbankgame_halovpbank`)
2. Tạo user: `user` (becomes `vpbankgame_user`)
3. Set password: `VpBank2025!@#`
4. Import `database-setup-directadmin.sql`

### **Bước 3: Test**

1. Test homepage: `https://halovpbank.beetech.one`
2. Test admin: `https://halovpbank.beetech.one/admin`
3. Test QR scanner trên mobile
4. Test fonts hiển thị đúng

---

## 🎨 **Fonts included:**

### **Gilroy Font Family:**

-   **SVN-GILROY BOLD.OTF** - Cho titles và headings
-   **SVN-GILROY REGULAR.OTF** - Cho body text
-   **SVN-GILROY SEMIBOLD.OTF** - Cho descriptions

### **Usage trong CSS:**

```css
font-family: 'Gilroy', sans-serif;
font-weight: 700; /* Bold */
font-weight: 500; /* SemiBold */
font-weight: 400; /* Regular */
```

---

## ✅ **Tại sao chọn with-fonts.zip:**

### **1. Kích thước hợp lý:**

-   ✅ **8.41 MB** - phù hợp với hầu hết quota limits
-   ✅ **Nhỏ hơn 18x** so với package gốc (146MB)
-   ✅ **Chỉ lớn hơn 150KB** so với minimal (có fonts)

### **2. Đầy đủ chức năng:**

-   ✅ **Fonts Gilroy** - design đúng như yêu cầu
-   ✅ **Tất cả images** - UI hoàn chỉnh
-   ✅ **Core functionality** - game hoạt động đầy đủ

### **3. Dễ deploy:**

-   ✅ **Một file duy nhất** - upload đơn giản
-   ✅ **Không cần thêm fonts sau** - tiết kiệm thời gian
-   ✅ **Ít khả năng lỗi** - ít files để quản lý

---

## 🎯 **Final Recommendation:**

### **Sử dụng `halovpbank2025-with-fonts.zip` (8.41 MB)**

**Lý do:**

-   ✅ **Kích thước nhỏ** - giải quyết lỗi quota
-   ✅ **Có fonts** - design đúng như yêu cầu
-   ✅ **Đầy đủ chức năng** - game hoạt động hoàn hảo
-   ✅ **Dễ deploy** - một file duy nhất

**Package này sẽ giải quyết được lỗi quota và vẫn giữ nguyên design với fonts Gilroy!** 🎉✨
