# 📥 FreelanceHub Installation Guide

Complete step-by-step installation instructions for deploying FreelanceHub on your server.

## 📋 System Requirements

### Minimum Requirements:
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Apache/Nginx**: Latest stable version
- **Disk Space**: 100 MB minimum
- **RAM**: 512 MB minimum

### Required PHP Extensions:
- mysqli or PDO_MySQL
- mbstring
- json
- fileinfo
- session
- GD (for image processing)

### Recommended:
- PHP 8.0+
- MySQL 8.0+
- SSL Certificate (for HTTPS)
- mod_rewrite (Apache) or URL rewriting enabled

## 🚀 Installation Methods

### Method 1: XAMPP (Windows/Mac/Linux)

#### Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP to default location (e.g., C:\xampp)
3. Start Apache and MySQL from XAMPP Control Panel

#### Step 2: Extract Project
1. Extract the project ZIP file
2. Copy the `webapp` folder to `C:\xampp\htdocs\`
3. Rename folder to `freelancehub` (optional)
4. Final path: `C:\xampp\htdocs\freelancehub\`

#### Step 3: Create Database
1. Open browser and go to `http://localhost/phpmyadmin`
2. Click "New" to create a database
3. Database name: `freelance_marketplace`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

#### Step 4: Import Database
1. Select the `freelance_marketplace` database
2. Click on "Import" tab
3. Click "Choose File" and select `database.sql` from project folder
4. Click "Go" to import
5. Wait for success message

#### Step 5: Configure Database Connection
1. Navigate to `C:\xampp\htdocs\freelancehub\config\`
2. Copy `database.example.php` and rename to `database.php`
3. Open `database.php` in text editor
4. Update credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Empty for XAMPP
   define('DB_NAME', 'freelance_marketplace');
   ```
5. Save the file

#### Step 6: Set Permissions (Important)
1. Right-click on `uploads` folder
2. Properties → Security → Edit
3. Give "Full Control" to Users/Everyone
4. Apply to all subfolders

#### Step 7: Access Application
1. Open browser
2. Go to: `http://localhost/freelancehub/`
3. You should see the homepage

#### Step 8: Login as Admin
1. Go to: `http://localhost/freelancehub/login.php`
2. Username: `admin`
3. Password: `admin123`
4. Click Login

---

### Method 2: WAMP (Windows)

#### Step 1: Install WAMP
1. Download WAMP from https://www.wampserver.com/
2. Install to default location (e.g., C:\wamp64)
3. Start all services (Apache, MySQL)

#### Step 2-8: Same as XAMPP
Follow Steps 2-8 from XAMPP method above.
- Path will be: `C:\wamp64\www\freelancehub\`
- Access: `http://localhost/freelancehub/`

---

### Method 3: LAMP (Linux - Ubuntu/Debian)

#### Step 1: Install LAMP Stack
```bash
# Update package list
sudo apt update

# Install Apache
sudo apt install apache2 -y

# Install MySQL
sudo apt install mysql-server -y

# Install PHP and required extensions
sudo apt install php libapache2-mod-php php-mysql php-mbstring php-json php-gd -y

# Restart Apache
sudo systemctl restart apache2
```

#### Step 2: Extract Project
```bash
# Navigate to web root
cd /var/www/html

# Extract project (or upload via FTP/SFTP)
sudo unzip freelancehub.zip

# Rename folder
sudo mv webapp freelancehub

# Set ownership
sudo chown -R www-data:www-data freelancehub
```

#### Step 3: Create Database
```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE freelance_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'freelance_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON freelance_marketplace.* TO 'freelance_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import database
sudo mysql -u root -p freelance_marketplace < /var/www/html/freelancehub/database.sql
```

#### Step 4: Configure Database
```bash
# Copy example config
cd /var/www/html/freelancehub/config
sudo cp database.example.php database.php

# Edit database config
sudo nano database.php
```

Update with your MySQL credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'freelance_user');
define('DB_PASS', 'your_strong_password');
define('DB_NAME', 'freelance_marketplace');
```

#### Step 5: Set Permissions
```bash
# Set folder permissions
sudo chmod -R 755 /var/www/html/freelancehub
sudo chmod -R 777 /var/www/html/freelancehub/uploads
```

#### Step 6: Configure Apache (Optional - for clean URLs)
```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

#### Step 7: Access Application
Open browser and go to:
- `http://your-server-ip/freelancehub/`
- Or: `http://localhost/freelancehub/` (if on local machine)

---

### Method 4: cPanel (Shared Hosting)

#### Step 1: Upload Files
1. Login to cPanel
2. Go to File Manager
3. Navigate to `public_html`
4. Upload project ZIP file
5. Extract ZIP file
6. Rename folder to `freelancehub` (or your choice)

#### Step 2: Create MySQL Database
1. In cPanel, go to "MySQL Databases"
2. Create new database: `freelance_marketplace`
3. Create new user with strong password
4. Add user to database with ALL PRIVILEGES

#### Step 3: Import Database
1. In cPanel, go to phpMyAdmin
2. Select your database
3. Click "Import"
4. Choose `database.sql` file
5. Click "Go"

#### Step 4: Configure Database
1. In File Manager, navigate to `/public_html/freelancehub/config/`
2. Edit `database.php` (or copy from example)
3. Update with your cPanel database credentials:
   ```php
   define('DB_HOST', 'localhost');  // Usually localhost
   define('DB_USER', 'cpanel_username_dbuser');
   define('DB_PASS', 'your_database_password');
   define('DB_NAME', 'cpanel_username_freelance_marketplace');
   ```

#### Step 5: Set Permissions
1. Right-click on `uploads` folder
2. Change Permissions
3. Set to `777` or `755` (depends on server)
4. Apply recursively to all subfolders

#### Step 6: Access Application
- `https://yourdomain.com/freelancehub/`
- Or if installed in root: `https://yourdomain.com/`

---

## 🔑 Default Credentials

### Admin Account:
- **Username**: `admin`
- **Email**: `admin@freelancehub.com`
- **Password**: `admin123`

⚠️ **IMPORTANT**: Change admin password immediately after first login!

---

## ✅ Post-Installation Checklist

- [ ] Database imported successfully
- [ ] Database connection configured
- [ ] Application homepage loads
- [ ] Admin login works
- [ ] File upload directories are writable
- [ ] Register new test user
- [ ] Approve test user from admin panel
- [ ] Test user login
- [ ] Change default admin password

---

## 🔧 Configuration Options

### 1. Change Site Name
Edit `includes/header.php`:
```php
<a href="/" class="navbar-brand">
    <i class="fas fa-briefcase"></i>
    YourSiteName  <!-- Change this -->
</a>
```

### 2. Update Logo
- Add your logo to `assets/images/logo.png`
- Update header.php to use image instead of text

### 3. Configure Email (Future Feature)
Edit a config file (to be created) for SMTP settings.

### 4. Set Upload Limits
Edit `.htaccess` (create if not exists):
```apache
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value max_input_time 300
```

### 5. Security Hardening
- Change database table prefix (if desired)
- Use strong passwords for all accounts
- Enable HTTPS (SSL certificate)
- Disable directory listing
- Keep PHP and MySQL updated

---

## 🐛 Troubleshooting

### Issue: Database Connection Error
**Solution**: 
- Check database credentials in `config/database.php`
- Verify MySQL service is running
- Check database name is correct

### Issue: Upload Not Working
**Solution**:
- Check `uploads/` folder permissions (777 or 755)
- Verify PHP upload settings in php.ini
- Check disk space

### Issue: Blank Page / White Screen
**Solution**:
- Enable PHP error display
- Check Apache/Nginx error logs
- Verify all PHP extensions are installed

### Issue: 404 Not Found
**Solution**:
- Check Apache mod_rewrite is enabled
- Verify .htaccess file exists
- Check file paths are correct

### Issue: Session Not Working
**Solution**:
- Check PHP session configuration
- Verify session save path is writable
- Clear browser cookies

### Issue: Images Not Displaying
**Solution**:
- Check file paths in code
- Verify uploads folder exists
- Check file permissions

---

## 📞 Support

If you encounter any issues:
1. Check the troubleshooting section
2. Review error logs (Apache/PHP)
3. Verify all installation steps were followed
4. Contact your hosting provider for server-specific issues

---

## 🔄 Updating

To update FreelanceHub:
1. Backup your database
2. Backup your files (especially `config/` and `uploads/`)
3. Extract new version
4. Restore `config/database.php` and `uploads/` folder
5. Run any database migration scripts (if provided)

---

## 📄 License & Credits

FreelanceHub - Freelance Marketplace Platform  
Version: 1.0.0  
Last Updated: January 24, 2026

---

**🎉 Congratulations! Your FreelanceHub installation is complete!**

Next Steps:
1. Login as admin
2. Change default password
3. Customize site settings
4. Add categories (already populated)
5. Register test freelancer/client accounts
6. Start exploring features!
