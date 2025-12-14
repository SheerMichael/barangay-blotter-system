# Deployment Guide for InfinityFree Hosting

This guide will walk you through deploying the Barangay Blotter System to InfinityFree hosting.

## Table of Contents
- [Prerequisites](#prerequisites)
- [Pre-Deployment Checklist](#pre-deployment-checklist)
- [Step-by-Step Deployment](#step-by-step-deployment)
- [Post-Deployment Configuration](#post-deployment-configuration)
- [Troubleshooting](#troubleshooting)
- [InfinityFree Limitations](#infinityfree-limitations)

---

## Prerequisites

Before deploying, ensure you have:
- ✅ An InfinityFree account ([Sign up here](https://infinityfree.net))
- ✅ FTP client installed (FileZilla recommended)
- ✅ Your database exported from local environment
- ✅ Gmail App Password for email notifications (if using Gmail)

---

## Pre-Deployment Checklist

### 1. Update `.env` Configuration
Do NOT upload the current `.env` file. Instead, prepare production settings:

```env
# Production Environment
APP_ENV=production
APP_DEBUG=false

# InfinityFree Database (Get from cPanel)
DB_HOST=sql123.infinityfree.com
DB_NAME=if0_12345678_blotter_system
DB_USERNAME=if0_12345678
DB_PASSWORD=your_database_password

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_gmail_app_password
SMTP_FROM_EMAIL=your_email@gmail.com
SMTP_FROM_NAME=Barangay Blotter System
ADMIN_EMAIL=admin@yourdomain.com
ADMIN_NAME=Barangay Admin

# Email Settings
EMAIL_NOTIFICATIONS_ENABLED=true
EMAIL_DEBUG=0

# Application Settings
APP_NAME=Barangay Blotter System
APP_URL=https://yourdomain.infinityfreeapp.com
```

### 2. Files to Upload
Upload ALL files EXCEPT:
- ❌ `.env` (create new one on server)
- ❌ `node_modules/` (not needed on server)
- ❌ `.git/` (if present)
- ❌ Local development files

### 3. Files to Upload:
- ✅ All PHP files
- ✅ `vendor/` directory (PHPMailer)
- ✅ `assets/` directory
- ✅ `config/` directory
- ✅ `.htaccess` file
- ✅ `.env.example` (for reference)

---

## Step-by-Step Deployment

### Step 1: Create InfinityFree Account
1. Go to [InfinityFree](https://infinityfree.net)
2. Sign up for free hosting
3. Create a new account/website
4. Choose subdomain (e.g., `barangay-blotter.infinityfreeapp.com`)

### Step 2: Set Up Database

#### A. Create MySQL Database
1. Login to InfinityFree Control Panel
2. Go to **MySQL Databases**
3. Click **Create Database**
4. Note down the following (you'll need these):
   - Database Host: `sqlXXX.infinityfree.com`
   - Database Name: `if0_XXXXXXXX_blotter_system`
   - Database Username: `if0_XXXXXXXX`
   - Database Password: (set your own)

#### B. Import Database
1. Go to **MySQL Databases** → **phpMyAdmin**
2. Select your database
3. Click **Import** tab
4. Upload your SQL file (`database/schema.sql`)
5. Click **Go** to import

### Step 3: Upload Files via FTP

#### A. Get FTP Credentials
1. In Control Panel, go to **FTP Accounts**
2. Note down:
   - FTP Host: `ftpupload.net`
   - FTP Username: Your account username
   - FTP Password: Your account password
   - Port: `21`

#### B. Connect with FileZilla
1. Open FileZilla
2. Enter FTP credentials:
   - Host: `ftpupload.net`
   - Username: Your username
   - Password: Your password
   - Port: `21`
3. Click **Quickconnect**

#### C. Upload Files
1. Navigate to `htdocs` folder on the server (right pane)
2. Upload all project files to `htdocs` directory
3. Upload structure should look like:
   ```
   htdocs/
   ├── .htaccess
   ├── .env (create this later)
   ├── index.php
   ├── auth/
   ├── classes/
   ├── config/
   ├── crud/
   ├── database/
   ├── vendor/
   └── ...
   ```

### Step 4: Create `.env` File on Server

1. In FileZilla, right-click in `htdocs` → **Create file**
2. Name it `.env`
3. Right-click `.env` → **Edit**
4. Copy your PRODUCTION environment settings (see Pre-Deployment Checklist)
5. Replace with actual InfinityFree database credentials
6. Save and upload

### Step 5: Set File Permissions

Set proper permissions via FileZilla:
1. Right-click on files/folders → **File permissions**
2. Set permissions:
   - PHP files: `644` (rw-r--r--)
   - Directories: `755` (rwxr-xr-x)
   - `.htaccess`: `644`
   - `config/` directory: `755`
   - `.env` file: `600` (rw-------)

### Step 6: Configure PHP Settings

InfinityFree uses PHP 8.x by default. To check or change:
1. Go to Control Panel → **PHP Version**
2. Select PHP 8.0 or higher
3. Enable required extensions:
   - ✅ mysqli
   - ✅ pdo_mysql
   - ✅ openssl (for email)

### Step 7: Test Your Application

1. Visit your domain: `https://yourdomain.infinityfreeapp.com`
2. Test login functionality
3. Test database operations
4. Test email notifications (if configured)

---

## Post-Deployment Configuration

### Enable HTTPS (SSL)
1. InfinityFree provides free SSL via Cloudflare
2. Go to Control Panel → **SSL Certificates**
3. Install free SSL certificate
4. Once activated, uncomment HTTPS redirect in `.htaccess`:
   ```apache
   # Uncomment these lines:
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

### Configure Email Notifications

#### Using Gmail:
1. Enable 2-Factor Authentication on Gmail
2. Generate App Password:
   - Google Account → Security → 2-Step Verification
   - App passwords → Select app: Mail, Device: Other
   - Copy the 16-character password
3. Update `.env` with app password

#### Important Notes:
- InfinityFree blocks port 25 (standard SMTP)
- Use port 587 (TLS) or 465 (SSL)
- Gmail's port 587 works reliably

### Set Up Error Logging
1. Create `logs` directory via FTP if not exists
2. Set permissions to `755`
3. Check `logs/error.log` for any errors

### Create Admin Account
1. Visit your site
2. Go to registration page
3. Create admin account
4. Optionally, modify database to set admin privileges

---

## Troubleshooting

### Common Issues and Solutions

#### 1. **500 Internal Server Error**
**Causes:**
- Incorrect `.htaccess` file
- PHP syntax errors
- Missing `.env` file

**Solutions:**
- Check `.htaccess` syntax
- Review PHP error logs
- Ensure `.env` exists with correct values
- Check file permissions

#### 2. **Database Connection Failed**
**Causes:**
- Wrong database credentials in `.env`
- Database not imported
- InfinityFree database host incorrect

**Solutions:**
- Verify database credentials in Control Panel
- Double-check `.env` DB settings
- Ensure database is imported via phpMyAdmin
- Use full database host: `sqlXXX.infinityfree.com`

#### 3. **Email Not Sending**
**Causes:**
- Wrong SMTP credentials
- Port blocked
- Gmail blocking less secure apps

**Solutions:**
- Use Gmail App Password, not regular password
- Use port 587 (TLS)
- Enable 2FA on Gmail and create app password
- Check InfinityFree SMTP restrictions

#### 4. **White Screen / Blank Page**
**Causes:**
- PHP fatal error
- Missing required files
- Incorrect file paths

**Solutions:**
- Enable error display temporarily: Set `APP_DEBUG=true` in `.env`
- Check `logs/error.log`
- Verify all files uploaded correctly
- Check PHP version compatibility

#### 5. **File Upload Issues**
**Causes:**
- File permissions incorrect
- Upload directory doesn't exist

**Solutions:**
- Set upload directories to `755`
- Create necessary directories
- Check InfinityFree upload limits

#### 6. **CSS/JS Not Loading**
**Causes:**
- Incorrect paths
- `.htaccess` blocking files

**Solutions:**
- Check file paths are relative or absolute
- Verify `.htaccess` allows static files
- Check file permissions `644`

### Checking Error Logs

#### Via Control Panel:
1. Go to Control Panel → **Error Logs**
2. View recent PHP errors

#### Via FTP:
1. Check `logs/error.log` file
2. Download and review

---

## InfinityFree Limitations

### What Works:
✅ PHP 8.x support  
✅ MySQL databases  
✅ PHPMailer (SMTP email)  
✅ SSL certificates (free via Cloudflare)  
✅ .htaccess and mod_rewrite  
✅ Composer packages (upload via FTP)  

### Limitations:
❌ No SSH access  
❌ No Composer commands directly  
❌ No Cron jobs (use alternative services)  
❌ Port 25 blocked (use 587/465 for email)  
❌ Limited CPU/memory resources  
❌ File upload size limits (check current limits)  
❌ Some PHP functions disabled for security  

### Performance Tips:
- Optimize images before upload
- Minimize database queries
- Use browser caching (configured in `.htaccess`)
- Enable output compression (configured in `.htaccess`)
- Keep file structure lean

---

## Production Maintenance

### Regular Tasks:
1. **Backup Database** - Export via phpMyAdmin weekly
2. **Monitor Error Logs** - Check for recurring issues
3. **Update Dependencies** - Keep PHPMailer updated
4. **Review Security** - Check for vulnerabilities
5. **Test Functionality** - Regular testing of key features

### Updating the Application:
1. Test changes locally first
2. Backup production database
3. Upload changed files via FTP
4. Test immediately after upload
5. Monitor error logs

---

## Additional Resources

- [InfinityFree Knowledge Base](https://forum.infinityfree.net/docs)
- [InfinityFree Support Forum](https://forum.infinityfree.net)
- [PHPMailer Documentation](https://github.com/PHPMailer/PHPMailer)
- [FileZilla Documentation](https://wiki.filezilla-project.org)

---

## Support

If you encounter issues:
1. Check this troubleshooting guide first
2. Review `logs/error.log` for specific errors
3. Consult InfinityFree forum for hosting-specific issues
4. Contact your system administrator

---

**Good luck with your deployment! 🚀**
