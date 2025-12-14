# Deployment Checklist for InfinityFree

## Pre-Deployment Checklist

### Local Environment
- [ ] All features tested locally
- [ ] Database exported (`database/schema.sql`)
- [ ] All dependencies installed (`vendor/` directory present)
- [ ] Error handling tested
- [ ] Email notifications tested (if using)

### Configuration Files
- [ ] `.env.example` updated with all required variables
- [ ] Production `.env` prepared (DO NOT UPLOAD local .env)
- [ ] Database credentials ready from InfinityFree
- [ ] Email credentials ready (Gmail App Password)
- [ ] `.htaccess` file ready

### Code Review
- [ ] No hardcoded passwords in code
- [ ] No debug statements (`var_dump`, `print_r`, etc.)
- [ ] Error reporting set to production mode
- [ ] All file paths use `__DIR__` for portability
- [ ] No localhost references in code

---

## InfinityFree Setup

### Account Creation
- [ ] InfinityFree account created
- [ ] Hosting account activated
- [ ] Domain/subdomain configured
- [ ] Control Panel access verified

### Database Setup
- [ ] MySQL database created in Control Panel
- [ ] Database credentials saved:
  - Database Host: `sql___.infinityfree.com`
  - Database Name: `if0_________`
  - Username: `if0_________`
  - Password: `___________`
- [ ] phpMyAdmin accessible
- [ ] Database imported successfully
- [ ] Tables created and populated

### FTP Setup
- [ ] FTP credentials obtained from Control Panel
- [ ] FileZilla (or FTP client) installed
- [ ] FTP connection tested successfully

---

## File Upload

### Upload via FTP
- [ ] Connected to FTP (ftpupload.net)
- [ ] Navigated to `htdocs` directory
- [ ] Uploaded all files EXCEPT:
  - ❌ `.env` (create new on server)
  - ❌ `node_modules/`
  - ❌ `.git/`
  - ❌ Local backup files

### Files to Upload
- [ ] `index.php` and all PHP files
- [ ] `auth/` directory
- [ ] `classes/` directory
- [ ] `config/` directory (without local .env)
- [ ] `crud/` directory
- [ ] `database/` directory
- [ ] `vendor/` directory (PHPMailer)
- [ ] `assets/` directory (CSS, JS)
- [ ] `error/` directory (error pages)
- [ ] `.htaccess` file
- [ ] `.env.example` (for reference)
- [ ] `composer.json`
- [ ] `README.md`
- [ ] `DEPLOYMENT.md`

---

## Server Configuration

### Create Production .env File
- [ ] Created `.env` file in `htdocs` directory
- [ ] Added all environment variables
- [ ] Updated `APP_ENV=production`
- [ ] Updated `APP_DEBUG=false`
- [ ] Added InfinityFree database credentials
- [ ] Added email credentials
- [ ] Set correct `APP_URL`

### File Permissions
Set via FileZilla (Right-click → File Permissions):
- [ ] PHP files: `644`
- [ ] Directories: `755`
- [ ] `.htaccess`: `644`
- [ ] `.env`: `600` (most restrictive)
- [ ] `logs/` directory: `755` (if exists)
- [ ] `config/` directory: `755`

### PHP Configuration
- [ ] PHP version set (8.0 or higher recommended)
- [ ] Required extensions enabled:
  - [ ] mysqli
  - [ ] pdo_mysql
  - [ ] openssl
  - [ ] curl

---

## Testing

### Basic Functionality
- [ ] Website loads at your domain
- [ ] Homepage displays correctly
- [ ] CSS and JavaScript loading properly
- [ ] Images displaying correctly

### Database Connection
- [ ] Application connects to database
- [ ] No database connection errors
- [ ] Data displays correctly from database

### Authentication
- [ ] Login page loads
- [ ] Can login with existing credentials
- [ ] Registration works (if applicable)
- [ ] Session management works
- [ ] Logout works

### Core Features
- [ ] View residents list
- [ ] Add new resident
- [ ] View resident details
- [ ] Edit resident
- [ ] Delete resident
- [ ] View blotter records
- [ ] Add new blotter entry
- [ ] View blotter details
- [ ] Edit blotter
- [ ] Update blotter status
- [ ] Dashboard statistics display

### Email Notifications (if configured)
- [ ] Test email sending
- [ ] New blotter notification works
- [ ] Status update notification works
- [ ] Emails received successfully

### Error Handling
- [ ] 404 error page displays
- [ ] 500 error page displays (if error occurs)
- [ ] Errors logged to `logs/error.log`
- [ ] No sensitive information exposed in errors

---

## Security

### SSL/HTTPS
- [ ] SSL certificate installed (via Control Panel)
- [ ] HTTPS redirect enabled in `.htaccess`
- [ ] All pages load via HTTPS
- [ ] No mixed content warnings

### File Protection
- [ ] `.env` file not accessible via browser
- [ ] `config/` directory protected
- [ ] `database/` directory protected
- [ ] No directory listing enabled
- [ ] Sensitive files return 403 error

### Application Security
- [ ] Session security configured
- [ ] Password hashing working
- [ ] SQL injection prevention active (PDO)
- [ ] XSS protection headers set
- [ ] CSRF protection implemented (if applicable)

---

## Post-Deployment

### Monitoring
- [ ] Error logs reviewed (`logs/error.log`)
- [ ] Control Panel error logs checked
- [ ] Performance tested
- [ ] Load time acceptable

### Backup
- [ ] Database backup created
- [ ] Files backed up locally
- [ ] Backup schedule planned

### Documentation
- [ ] Admin credentials documented (securely)
- [ ] Database credentials saved (securely)
- [ ] FTP credentials saved (securely)
- [ ] Deployment notes documented

### User Accounts
- [ ] Admin account created
- [ ] Test user account created (optional)
- [ ] Default passwords changed
- [ ] User roles verified

---

## Troubleshooting Checklist

If something doesn't work:

### General Issues
- [ ] Checked `logs/error.log`
- [ ] Checked InfinityFree error logs
- [ ] Verified `.env` file exists and is correct
- [ ] Verified file permissions
- [ ] Cleared browser cache
- [ ] Tested in different browser

### Database Issues
- [ ] Database imported correctly
- [ ] Database credentials correct in `.env`
- [ ] Database host correct (sqlXXX.infinityfree.com)
- [ ] Tables exist and have data
- [ ] User has proper database permissions

### Email Issues
- [ ] Using Gmail App Password (not regular password)
- [ ] Port 587 specified
- [ ] SMTP credentials correct
- [ ] Gmail 2FA enabled
- [ ] Test email sent from examples

---

## Performance Optimization

- [ ] Images optimized
- [ ] CSS/JS minified (if applicable)
- [ ] Browser caching enabled (via `.htaccess`)
- [ ] GZIP compression enabled (via `.htaccess`)
- [ ] Unnecessary files removed

---

## Final Verification

- [ ] All features working
- [ ] No console errors
- [ ] No PHP errors
- [ ] Mobile responsive
- [ ] Cross-browser compatible
- [ ] Performance acceptable
- [ ] Security measures in place
- [ ] Backups completed
- [ ] Documentation complete

---

## Launch

- [ ] Final smoke test completed
- [ ] Users notified (if applicable)
- [ ] Support contact information ready
- [ ] Monitoring in place
- [ ] Celebration! 🎉

---

## Maintenance Schedule

### Daily
- Monitor error logs
- Check website availability

### Weekly
- Review error logs
- Test critical features
- Backup database

### Monthly
- Update dependencies (if needed)
- Security review
- Performance review
- Full backup

---

**Deployment Date:** _______________

**Deployed By:** _______________

**Domain:** _______________

**Notes:**
_______________________________________________________________________________
_______________________________________________________________________________
_______________________________________________________________________________
