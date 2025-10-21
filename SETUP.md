# VPBank Solution Day Game - Setup Instructions

## Database Setup

1. **Create Database**:

    ```sql
    -- Run the SQL commands in database_schema.sql
    mysql -u your_username -p < database_schema.sql
    ```

2. **Update Configuration**:
    - Edit `config.php` and update database credentials:
    ```php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'vpbank_game');
    define('DB_USER', 'your_db_user');
    define('DB_PASS', 'your_db_password');
    ```

## QR Code Generation

1. **Update Domain**:

    - Edit `generate_qr_codes.php` and update `$BASE_URL` with your actual domain

2. **Generate QR Codes**:

    ```bash
    php generate_qr_codes.php
    ```

3. **QR Codes Location**:
    - Generated QR codes will be saved in `assets/images/qr-codes/`
    - Print these QR codes and place them at respective stations

## File Structure

```
/
├── index.php              # Registration page
├── game.php               # Main game page
├── reward.php             # Reward display page
├── config.php             # Configuration
├── database_schema.sql    # Database setup
├── generate_qr_codes.php   # QR code generator
├── api/
│   ├── db.php             # Database class
│   ├── register.php       # User registration API
│   ├── get-progress.php   # Get user progress API
│   ├── check-station.php  # Station completion API
│   └── claim-reward.php   # Reward claiming API
└── assets/
    ├── css/
    │   └── style.css      # Main styles
    ├── js/
    │   ├── main.js       # Main game logic
    │   └── qr-scanner.js # QR scanning functionality
    └── images/
        ├── vpbank-logo.svg
        ├── background-pattern.svg
        └── qr-codes/     # Generated QR codes
```

## Testing

1. **Registration Flow**:

    - Open `index.php`
    - Fill registration form
    - Should redirect to `game.php`

2. **QR Scanning**:

    - Click "QUÉT MÃ QR" button
    - Allow camera permission
    - Scan generated QR codes
    - Should mark stations as completed

3. **External QR Apps**:

    - Use Zalo, Google Lens, or other QR apps
    - Scan QR codes
    - Should open game page and mark station complete

4. **Reward System**:
    - Complete 3+ stations
    - Click "QUAY SỐ TRÚNG QUÀ"
    - Should redirect to reward page with gift code

## Security Notes

-   QR codes include verification hashes to prevent fake scanning
-   All user inputs are sanitized
-   Database uses prepared statements
-   HTTPS required for camera access

## Deployment

1. Upload all files to your DirectAdmin hosting
2. Create MySQL database and run schema
3. Update `config.php` with production credentials
4. Generate QR codes with production domain
5. Test all functionality

## Assets Integration

When you provide the PSD file:

1. Extract logo → replace `assets/images/vpbank-logo.svg`
2. Extract backgrounds → replace `assets/images/background-pattern.svg`
3. Update CSS if needed for exact styling
