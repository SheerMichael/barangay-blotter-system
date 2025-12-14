<?php
/**
 * Email Configuration File
 * 
 * Configure your SMTP settings here
 * For Gmail: Enable 2FA and use App Password
 * For other services: Use their SMTP settings
 * 
 * Credentials are loaded from .env file for security
 */

// Load configuration
require_once __DIR__ . '/config.php';
Config::load();

return [
    // SMTP Configuration
    'smtp' => [
        'host' => Config::get('SMTP_HOST', 'smtp.gmail.com'),
        'port' => Config::getInt('SMTP_PORT', 587),
        'encryption' => Config::get('SMTP_ENCRYPTION', 'tls'),
        'auth' => true,
        'username' => Config::get('SMTP_USERNAME'),
        'password' => Config::get('SMTP_PASSWORD'),
    ],

    // Sender Information
    'from' => [
        'email' => Config::get('SMTP_FROM_EMAIL'),
        'name' => Config::get('SMTP_FROM_NAME', 'Barangay Blotter System')
    ],

    // Default Recipients (for admin notifications)
    'admin' => [
        'email' => Config::get('ADMIN_EMAIL'),
        'name' => Config::get('ADMIN_NAME', 'Barangay Admin')
    ],

    // Email Settings
    'settings' => [
        'enable_notifications' => Config::getBool('EMAIL_NOTIFICATIONS_ENABLED', true),
        'debug' => Config::getInt('EMAIL_DEBUG', 0),
        'charset' => 'UTF-8',
    ],

    // Notification Templates
    'templates' => [
        'blotter_created' => [
            'subject' => 'New Blotter Record Created',
            'enabled' => true
        ],
        'blotter_updated' => [
            'subject' => 'Blotter Status Updated',
            'enabled' => true
        ],
        'blotter_resolved' => [
            'subject' => 'Your Blotter Case Has Been Resolved',
            'enabled' => true
        ],
        'resident_registered' => [
            'subject' => 'Welcome to Barangay System',
            'enabled' => false
        ]
    ]
];
