# VPBank Solution Day - DirectAdmin Deployment Guide

## 🚀 Quick Deploy Instructions

### 1. Database Setup (with vpbankgame\_ prefix)

1. **Login to DirectAdmin** → MySQL Databases
2. **Create Database**: `halovpbank` (will become `vpbankgame_halovpbank`)
3. **Create User**: `halovpbank` (will become `vpbankgame_halovpbank`) with password `VpBank2025!@#`
4. **Grant all privileges** to user on database
5. **Import SQL file**: `database-setup-directadmin.sql` via phpMyAdmin

### 2. File Upload

1. **Upload ZIP file** to public_html
2. **Extract ZIP** (should work now with Unix paths)
3. **Rename config-directadmin.php** to `config.php`

### 3. SSL Configuration

1. **Enable SSL** in DirectAdmin
2. **Force HTTPS redirect** (required for QR scanner)

### 4. Test URLs

-   **Registration**: https://halovpbank.beetech.one/
-   **Game**: https://halovpbank.beetech.one/game.php?token=YOUR_TOKEN
-   **Reward**: https://halovpbank.beetech.one/reward.php?token=YOUR_TOKEN
-   **Admin**: https://halovpbank.beetech.one/admin/ (admin/Admin@2025)

## 📋 Database Credentials (with vpbankgame\_ prefix)

-   **Host**: localhost
-   **Database**: vpbankgame_halovpbank
-   **Username**: vpbankgame_halovpbank
-   **Password**: VpBank2025!@#

## 🔧 Admin Access

-   **Username**: admin
-   **Password**: Admin@2025

## ⚠️ Important Notes

-   **HTTPS Required**: QR scanner only works on HTTPS
-   **Camera Permissions**: Users must allow camera access
-   **Mobile Testing**: Test on actual mobile devices for camera functionality

## 🐛 Troubleshooting

-   **Database Error**: Check credentials in config.php
-   **QR Scanner Not Working**: Ensure HTTPS is enabled
-   **Admin Login Failed**: Check password hash in config.php
-   **File Permissions**: Set 755 for folders, 644 for files
