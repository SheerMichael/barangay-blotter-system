# Barangay Blotter System

A comprehensive web-based system for managing barangay blotter records and resident information.

## Features

- 👥 **Resident Management** - Add, view, edit, and delete resident records
- 📋 **Blotter Records** - Manage incident reports with complainants and respondents
- 📊 **Dashboard** - View statistics and summaries
- 📧 **Email Notifications** - Automated email alerts for new records and status updates
- 🖨️ **Print Reports** - Generate printable reports for residents and blotter records
- 🔐 **User Authentication** - Secure login system
- 📱 **Responsive Design** - Mobile-friendly interface

## Technology Stack

- **Backend:** PHP 8.x
- **Database:** MySQL
- **Frontend:** HTML, CSS (Tailwind), JavaScript
- **Email:** PHPMailer
- **Libraries:** Chart.js, Tom Select

## Local Development Setup

### Prerequisites
- XAMPP/LAMPP with PHP 8.x
- MySQL
- Composer (for PHPMailer)

### Installation

1. Clone the repository:
```bash
cd /opt/lampp/htdocs
git clone <repository-url> WebdevBlotter
cd WebdevBlotter
```

2. Install dependencies:
```bash
composer install
```

3. Configure environment:
```bash
cp .env.example .env
# Edit .env with your local database and email credentials
```

4. Import database:
```bash
mysql -u root -p < database/schema.sql
```

5. Start XAMPP/LAMPP and access:
```
http://localhost/WebdevBlotter
```

## 🚀 Deployment to InfinityFree

### Quick Deployment Guide

1. **Prepare Files**
   - Export database: `mysqldump -u root Blotter_System > database/schema.sql`
   - Ensure all files are ready

2. **Set Up InfinityFree**
   - Create account at [InfinityFree](https://infinityfree.net)
   - Create MySQL database
   - Note database credentials

3. **Upload Files**
   - Connect via FTP (ftpupload.net)
   - Upload all files to `htdocs/` (except `.env`, `node_modules/`)
   - Create new `.env` with production settings

4. **Import Database**
   - Use phpMyAdmin in Control Panel
   - Import `database/schema.sql`

5. **Configure & Test**
   - Set file permissions
   - Enable SSL
   - Test all features

### 📚 Deployment Documentation

- **Quick Start:** [QUICK_START.md](QUICK_START.md) - Fast track deployment
- **Full Guide:** [DEPLOYMENT.md](DEPLOYMENT.md) - Comprehensive instructions
- **Checklist:** [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) - Step-by-step tasks

## Configuration

### Environment Variables (.env)

```env
# Application
APP_ENV=development
APP_DEBUG=true

# Database
DB_HOST=127.0.0.1
DB_NAME=Blotter_System
DB_USERNAME=root
DB_PASSWORD=

# Email (Gmail)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_gmail_app_password
```

### Gmail Setup for Email Notifications

1. Enable 2-Factor Authentication
2. Generate App Password: Google Account → Security → App passwords
3. Use App Password in `.env` file

## Project Structure

```
WebdevBlotter/
├── auth/               # Authentication (login, register, session)
├── classes/            # PHP classes (Blotter, Resident, User)
├── config/             # Configuration files
│   ├── config.php      # Environment loader
│   ├── email.php       # Email configuration
│   └── error_handler.php # Error handling
├── crud/               # CRUD operations
├── database/           # Database files
│   ├── database.php    # Database connection
│   └── schema.sql      # Database export
├── error/              # Custom error pages
├── vendor/             # Composer dependencies (PHPMailer)
├── assets/             # CSS, JS files
├── .env                # Environment configuration (not in Git)
├── .env.example        # Environment template
├── .htaccess           # Apache configuration
└── index.php           # Dashboard
```

## Features TODO

1. Individual blotters should be viewed with full details (onclick)
2. Filter viewBlotters by status
3. Add update and delete to resident and blotter entries
4. When adding new blotter entries status shouldn't default to pending
5. Optional time field
6. Multi-form for blotter and resident
7. Multi-respondent and multi-complainant support

## Security

- ✅ Environment variables for sensitive data
- ✅ Password hashing
- ✅ SQL injection prevention (PDO)
- ✅ XSS protection headers
- ✅ CSRF protection
- ✅ File permission restrictions
- ✅ Production error handling

## Troubleshooting

### Database Connection Issues
- Verify `.env` database credentials
- Check database exists and is imported
- Ensure MySQL service is running

### Email Not Sending
- Use Gmail App Password (not regular password)
- Enable 2FA on Gmail
- Check SMTP port (587 for TLS)

### 500 Error
- Check `logs/error.log`
- Verify `.env` file exists
- Review `.htaccess` configuration

## Support

For deployment issues, see:
- [DEPLOYMENT.md](DEPLOYMENT.md) - Full troubleshooting guide
- [InfinityFree Forum](https://forum.infinityfree.net)

## Repository

**GitHub:** [https://github.com/SheerMichael/barangay-blotter-system](https://github.com/SheerMichael/barangay-blotter-system)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contributors

- **Sheer Michael C. Librero** - *Initial work* - [SheerMichael](https://github.com/SheerMichael)

## Acknowledgments

- PHPMailer for email functionality
- Tailwind CSS for styling
- Chart.js for data visualization
- Tom Select for enhanced select inputs