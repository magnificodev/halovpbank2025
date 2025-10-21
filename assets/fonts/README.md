# Fonts cho VPBank Solution Day Game

## Thư mục chứa fonts cho game

### Cấu trúc thư mục:
```
assets/fonts/
├── README.md (file này)
├── [font-files sẽ được thêm vào đây]
└── ...
```

### Hướng dẫn sử dụng:

#### 1. Thêm font files:
- Copy các file font (.ttf, .woff, .woff2) vào thư mục này
- Đặt tên file rõ ràng, ví dụ: `vpbank-label-font.ttf`

#### 2. Khai báo font trong CSS:
```css
@font-face {
    font-family: 'VPBankLabel';
    src: url('../fonts/vpbank-label-font.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
}

.form-group label {
    font-family: 'VPBankLabel', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
```

#### 3. Font formats được hỗ trợ:
- **.ttf** - TrueType Font (tương thích tốt)
- **.woff** - Web Open Font Format (nén tốt)
- **.woff2** - Web Open Font Format 2 (nén tốt nhất)

#### 4. Lưu ý:
- Font files nên được tối ưu cho web
- Kiểm tra license của font trước khi sử dụng
- Test trên nhiều trình duyệt khác nhau

### Font hiện tại đang sử dụng:
- **Fallback:** 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
- **Style:** Bold (font-weight: 700)
- **Size:** 14px
- **Transform:** uppercase
