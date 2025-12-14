# Quick Start Guide - InfinityFree Deployment

This is a condensed version of the deployment process. For detailed instructions, see [DEPLOYMENT.md](DEPLOYMENT.md).

## 🚀 Quick Steps

### 1. Prepare Locally
```bash
# Export database
mysqldump -u root Blotter_System > database/schema.sql
```

### 2. Set Up InfinityFree
1. Create account at [InfinityFree](https://infinityfree.net)
2. Create new hosting account
3. Create MySQL database and note credentials

### 3. Configure Production `.env`
Create `.env` file on server with:
```env
APP_ENV=production
APP_DEBUG=false

DB_HOST=sql123.infinityfree.com
DB_NAME=if0_12345678_blotter_system
DB_USERNAME=if0_12345678
DB_PASSWORD=your_password

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_gmail_app_password
```

### 4. Upload Files
Via FTP (ftpupload.net):
- Upload ALL files to `htdocs/`
- **EXCEPT:** `.env`, `node_modules/`, `.git/`
- Create new `.env` on server with production values

### 5. Import Database
1. Go to phpMyAdmin in Control Panel
2. Import `database/schema.sql`

### 6. Set Permissions
- PHP files: `644`
- Directories: `755`
- `.env`: `600`

### 7. Enable HTTPS
1. Install free SSL in Control Panel
2. Uncomment HTTPS redirect in `.htaccess`

### 8. Test
- Visit your domain
- Test login
- Test all features
- Check error logs

## 📋 Files Created for Deployment

- ✅ `.env` - Environment configuration
- ✅ `.env.example` - Template for production
- ✅ `config/config.php` - Config loader
- ✅ `config/error_handler.php` - Error handling
- ✅ `.htaccess` - Server configuration
- ✅ `database/schema.sql` - Database export
- ✅ `error/` - Custom error pages
- ✅ `DEPLOYMENT.md` - Full deployment guide
- ✅ `DEPLOYMENT_CHECKLIST.md` - Step-by-step checklist

## 🔒 Security Notes

- **NEVER** commit `.env` to Git
- Use Gmail App Password (not regular password)
- Set `APP_DEBUG=false` in production
- Verify file permissions after upload

## 📚 Documentation

- **Full Guide:** [DEPLOYMENT.md](DEPLOYMENT.md)
- **Checklist:** [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)
- **InfinityFree Help:** [forum.infinityfree.net](https://forum.infinityfree.net)

## ⚠️ InfinityFree Limitations

- No SSH access
- Port 25 blocked (use 587 for SMTP)
- No cron jobs
- Upload vendor/ folder via FTP

## 🆘 Troubleshooting

### Database Connection Failed
✓ Check `.env` database credentials  
✓ Verify database imported  
✓ Use correct host: `sqlXXX.infinityfree.com`

### Email Not Sending
✓ Use Gmail App Password  
✓ Set port to 587  
✓ Enable 2FA on Gmail

### 500 Error
✓ Check `.htaccess` syntax  
✓ Review `logs/error.log`  
✓ Verify `.env` exists

---

**Need help?** Check [DEPLOYMENT.md](DEPLOYMENT.md) for detailed troubleshooting.
