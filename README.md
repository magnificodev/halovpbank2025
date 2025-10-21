# VPBank Solution Day Game

🎮 **Interactive QR Code Game for VPBank Solution Day Event**

A web-based game where participants scan QR codes at different stations to complete challenges and win rewards.

## 🚀 Features

-   **📱 Mobile-First Design**: Responsive design optimized for mobile devices
-   **📷 QR Code Scanning**: Camera-based QR scanning with external app support
-   **🔄 Session Persistence**: Maintains user progress across sessions
-   **🎯 Station Tracking**: Track completion of 5 different stations
-   **🎁 Reward System**: Random gift code generation after completing 3+ stations
-   **🔒 Security**: Verification hashes prevent fake QR scanning

## 🎮 Game Flow

1. **Registration** → User enters name, phone, email
2. **Station Scanning** → Scan QR codes at 5 different stations
3. **Progress Tracking** → Visual progress with checkmarks
4. **Reward Claiming** → Spin for random gift code after 3+ completions
5. **Code Display** → Show unique gift code for redemption

## 🛠 Tech Stack

-   **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
-   **Backend**: PHP 7.4+
-   **Database**: MySQL
-   **QR Scanner**: html5-qrcode library
-   **Hosting**: DirectAdmin compatible

## 📁 Project Structure

```
/
├── index.php              # Registration page
├── game.php               # Main game page
├── reward.php             # Reward display page
├── config.php             # Database configuration
├── database_schema.sql    # Database setup
├── generate_qr_codes.php  # QR code generator
├── api/                   # Backend APIs
│   ├── register.php       # User registration
│   ├── get-progress.php   # Progress tracking
│   ├── check-station.php  # Station completion
│   └── claim-reward.php   # Reward claiming
└── assets/                # Frontend assets
    ├── css/style.css      # Responsive styles
    ├── js/main.js         # Game logic
    ├── js/qr-scanner.js   # QR scanning
    └── images/            # Assets & QR codes
```

## 🚀 Quick Start

### 1. Database Setup

```sql
-- Run database_schema.sql to create tables
mysql -u username -p < database_schema.sql
```

### 2. Configuration

```php
// Update config.php with your database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'vpbank_game');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

### 3. Generate QR Codes

```bash
# Update domain in generate_qr_codes.php
php generate_qr_codes.php
```

### 4. Test Locally

-   Start local PHP server: `php -S localhost:8000`
-   Open `http://localhost:8000`
-   Test registration → QR scanning → reward flow

## 📱 Mobile Testing

-   **Camera Access**: Requires HTTPS in production
-   **QR Apps**: Compatible with Zalo, Google Lens, etc.
-   **Responsive**: Optimized for 320px - 1024px screens

## 🔧 Development

### Adding New Stations

1. Update `STATIONS` array in `config.php`
2. Add station description in `main.js`
3. Generate new QR code with `generate_qr_codes.php`

### Customizing UI

-   Edit `assets/css/style.css` for styling
-   Replace placeholder images in `assets/images/`
-   Update station descriptions in JavaScript

## 📋 TODO

-   [ ] Extract assets from PSD file
-   [ ] Add admin panel for monitoring
-   [ ] Implement analytics tracking
-   [ ] Add more gift codes
-   [ ] Optimize for production deployment

## 🤝 Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature/new-feature`
3. Commit changes: `git commit -am 'Add new feature'`
4. Push to branch: `git push origin feature/new-feature`
5. Submit pull request

## 📄 License

This project is proprietary software for VPBank Solution Day event.

---

**Built with ❤️ for VPBank Solution Day 2025**
