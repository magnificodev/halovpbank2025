# VPBank Solution Day Game - XAMPP Setup Guide

## 🚀 XAMPP Installation & Setup

### Step 1: Download & Install XAMPP

1. Go to: https://www.apachefriends.org/download.html
2. Download XAMPP for Windows
3. Run installer as Administrator
4. Install to default location: `C:\xampp\`

### Step 2: Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Start **Apache** (click Start button)
3. Start **MySQL** (click Start button)
4. Both should show green "Running" status

### Step 3: Copy Project Files

1. Copy entire project folder to: `C:\xampp\htdocs\halovpbank2025\`
2. Your project structure should be:
    ```
    C:\xampp\htdocs\halovpbank2025\
    ├── index.php
    ├── game.php
    ├── reward.php
    ├── config.php
    ├── api/
    ├── assets/
    └── database_schema.sql
    ```

### Step 4: Setup Database

1. Open browser: http://localhost/phpmyadmin
2. Click **"New"** to create database
3. Database name: `vpbank_game`
4. Collation: `utf8mb4_unicode_ci`
5. Click **"Create"**
6. Select `vpbank_game` database
7. Click **"Import"** tab
8. Choose file: `database_schema.sql`
9. Click **"Go"**

### Step 5: Test Application

1. Open browser: http://localhost/halovpbank2025
2. Should see registration page
3. Test registration form

## 🔧 Configuration Files

### config.php (Already Updated)

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'vpbank_game');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP default
```

### Generate QR Codes for Local Testing

1. Edit `generate_qr_codes.php`:
    ```php
    $BASE_URL = 'http://localhost/halovpbank2025';
    ```
2. Run: `php generate_qr_codes.php` (if PHP CLI available)
   Or manually create QR codes with URLs like:
    ```
    http://localhost/halovpbank2025/game.php?station=HALLO_GLOW&verify=hash
    ```

## 🧪 Testing URLs

-   **Registration**: http://localhost/halovpbank2025
-   **Game**: http://localhost/halovpbank2025/game.php
-   **Reward**: http://localhost/halovpbank2025/reward.php
-   **phpMyAdmin**: http://localhost/phpmyadmin

## 📱 Mobile Testing

### Option 1: Same Network

1. Find your computer's IP: `ipconfig` (Windows)
2. Access from mobile: http://YOUR_IP/halovpbank2025
3. Example: http://192.168.1.100/halovpbank2025

### Option 2: Chrome DevTools

1. Open Chrome DevTools (F12)
2. Click device toggle icon
3. Select mobile device
4. Test responsive design

## 🔍 Troubleshooting

### Apache Won't Start

-   Check if port 80 is in use
-   Change Apache port in XAMPP config
-   Run as Administrator

### MySQL Won't Start

-   Check if port 3306 is in use
-   Change MySQL port in XAMPP config
-   Check MySQL error logs

### Database Connection Error

-   Verify database exists in phpMyAdmin
-   Check config.php credentials
-   Ensure MySQL service is running

### QR Scanner Not Working

-   Camera requires HTTPS in production
-   Test with external QR apps (Zalo, Google Lens)
-   Check browser console for errors

## 🎯 Quick Test Flow

1. **Registration Test**

    - Fill form with test data
    - Should redirect to game page

2. **Database Test**

    - Check phpMyAdmin for new user record
    - Verify user_token is generated

3. **QR Code Test**

    - Generate test QR codes
    - Test external QR scanning

4. **Reward Test**
    - Complete 3+ stations manually
    - Test reward claiming

## 📊 Database Monitoring

### Check Users

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

---

**Ready to test! 🎮**
