# VPBank Solution Day Game - Local Testing Guide

## 🚀 Quick Local Setup

### 1. Database Setup (MySQL)

```sql
-- Create database
CREATE DATABASE vpbank_game CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use database
USE vpbank_game;

-- Run the schema
SOURCE database_schema.sql;
```

### 2. Configuration

Edit `config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'vpbank_game');
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', 'your_password');  // Your MySQL password
```

### 3. Start Local Server

```bash
# Start PHP built-in server
php -S localhost:8000

# Or use XAMPP/WAMP
# Place files in htdocs/www folder
```

### 4. Test URLs

-   **Registration**: http://localhost:8000
-   **Game**: http://localhost:8000/game.php
-   **Reward**: http://localhost:8000/reward.php

## 🧪 Testing Flow

### Step 1: Registration Test

1. Open http://localhost:8000
2. Fill form with test data:
    - Name: "Nguyễn Văn A"
    - Phone: "0123456789"
    - Email: "test@example.com"
3. Click "THAM GIA"
4. Should redirect to game page

### Step 2: QR Code Generation

```bash
# Edit generate_qr_codes.php
# Change $BASE_URL to 'http://localhost:8000'
php generate_qr_codes.php
```

### Step 3: QR Scanning Test

1. Click "QUÉT MÃ QR" button
2. Allow camera permission
3. Scan generated QR codes from `assets/images/qr-codes/`
4. Should mark stations as completed

### Step 4: External QR Test

1. Use Zalo/Google Lens to scan QR codes
2. Should open game page and mark station complete
3. Test session persistence by refreshing page

### Step 5: Reward System Test

1. Complete 3+ stations
2. Click "QUAY SỐ TRÚNG QUÀ"
3. Should redirect to reward page with gift code

## 🔧 Troubleshooting

### Database Connection Issues

```php
// Check database connection in api/db.php
try {
    $db = new Database();
    echo "Database connected successfully";
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
}
```

### Camera Permission Issues

-   Use HTTPS in production
-   Test on mobile device for better camera support
-   Check browser console for errors

### QR Code Not Working

-   Verify QR codes are generated correctly
-   Check URL format in generated QR codes
-   Test with external QR apps

## 📱 Mobile Testing

### Chrome DevTools

1. Open DevTools (F12)
2. Click device toggle icon
3. Select mobile device (iPhone/Android)
4. Test responsive design

### Real Mobile Testing

1. Find your computer's IP address
2. Access from mobile: http://YOUR_IP:8000
3. Test camera scanning functionality

## 🎨 Design Customization

### Colors

-   Primary: #00ff88 (Green)
-   Secondary: #4a90e2 (Blue)
-   Accent: #ff6b6b (Red)
-   Background: #0a0e27 (Dark Blue)

### Fonts

-   Primary: Arial, sans-serif
-   Sizes: 14px-48px responsive

### Animations

-   Hover effects on buttons
-   Loading spinners
-   Gradient shifts in footer

## 📊 Database Monitoring

### Check User Registrations

```sql
SELECT * FROM users ORDER BY created_at DESC;
```

### Check Progress

```sql
SELECT u.full_name, up.station_id, up.completed_at
FROM users u
JOIN user_progress up ON u.id = up.user_id;
```

### Check Rewards

```sql
SELECT u.full_name, gc.code, gc.claimed_at
FROM users u
JOIN gift_codes gc ON u.id = gc.user_id
WHERE gc.claimed_at IS NOT NULL;
```

## 🚀 Production Deployment Checklist

-   [ ] Update `config.php` with production database
-   [ ] Generate QR codes with production domain
-   [ ] Test HTTPS for camera access
-   [ ] Add more gift codes to database
-   [ ] Setup monitoring/logging
-   [ ] Test on multiple devices/browsers
-   [ ] Backup database regularly

---

**Happy Testing! 🎮**
