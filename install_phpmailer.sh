#!/bin/bash
# Quick Setup Script for PHPMailer Integration
# Run this script to quickly set up PHPMailer in your system

echo "=========================================="
echo "PHPMailer Quick Setup Script"
echo "=========================================="
echo ""

# Check if composer is installed
echo "1. Checking for Composer..."
if ! command -v composer &> /dev/null; then
    echo "   ❌ Composer not found"
    echo "   Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    echo "   ✓ Composer installed"
else
    echo "   ✓ Composer already installed"
fi

echo ""
echo "2. Installing PHPMailer..."
cd /opt/lampp/htdocs/WebdevBlotter
composer require phpmailer/phpmailer

echo ""
echo "3. Checking installation..."
if [ -d "vendor/phpmailer" ]; then
    echo "   ✓ PHPMailer installed successfully"
else
    echo "   ❌ PHPMailer installation failed"
    exit 1
fi

echo ""
echo "=========================================="
echo "Installation Complete!"
echo "=========================================="
echo ""
echo "Next Steps:"
echo "1. Edit config/email.php with your SMTP details"
echo "2. Run: php test_email.php"
echo "3. See PHPMAILER_SETUP.md for integration examples"
echo ""
echo "For Gmail:"
echo "- Enable 2FA"
echo "- Generate App Password at: https://myaccount.google.com/apppasswords"
echo "- Use App Password in config/email.php"
echo ""
