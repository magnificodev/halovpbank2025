# VPBank Game - Project Structure

## 📁 Core Files
- `index.php` - Registration page
- `game.php` - Game interface with QR scanner
- `reward.php` - Reward page
- `config.php` - Database configuration

## 📁 Admin Panel (`admin/`)
- `index.php` - Dashboard
- `users.php` - User management
- `gifts.php` - Gift management
- `logs.php` - Activity logs
- `qr-codes.php` - QR code management
- `_auth.php` - Authentication
- `_template.php` - Common template

## 📁 API (`api/`)
- `register.php` - User registration
- `get-progress.php` - Get user progress
- `check-station.php` - Verify station completion
- `claim-reward.php` - Claim reward
- `get-user.php` - Get user info
- `endroid-qr-generator.php` - QR code generation
- `qr-png.php` - PNG QR code output
- `db.php` - Database connection

## 📁 Assets (`assets/`)
- `css/` - Stylesheets (base, forms, game, admin, modal, reward, pages)
- `fonts/` - Gilroy font family
- `images/` - Game assets (backgrounds, buttons, icons)
- `js/` - JavaScript files (main, QR scanner, libraries)
- `qr-codes/` - Generated QR codes storage

## 📁 Source (`src/`)
- `QRCodeService.php` - QR code generation service

## 📁 Database (`database/`)
- `schema.sql` - Main database schema
- `schema-simple.sql` - Simplified schema
- `qr-management.sql` - QR management tables

## 📁 Vendor (`vendor/`)
- Composer dependencies (Endroid QR-Code, DomPDF, etc.)

## 🗑️ Cleaned Up
- Removed test files
- Removed duplicate QR generators
- Removed unused API endpoints
- Cleaned up QR code storage directories
