<?php
/**
 * NotificationMailer Class
 * 
 * Handles email notifications using PHPMailer
 * Requires PHPMailer to be installed via Composer
 * 
 * Installation: composer require phpmailer/phpmailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class NotificationMailer {
    private $mailer;
    private $config;
    private $enabled;

    public function __construct() {
        // Load email configuration
        $this->config = require __DIR__ . '/../config/email.php';
        $this->enabled = $this->config['settings']['enable_notifications'] ?? true;
        
        if (!$this->enabled) {
            return;
        }

        // Check if PHPMailer is installed
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            error_log('PHPMailer is not installed. Run: composer require phpmailer/phpmailer');
            $this->enabled = false;
            return;
        }

        // Initialize PHPMailer
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
    }

    /**
     * Configure SMTP settings
     */
    private function configureSMTP() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp']['host'];
            $this->mailer->SMTPAuth = $this->config['smtp']['auth'];
            $this->mailer->Username = $this->config['smtp']['username'];
            $this->mailer->Password = $this->config['smtp']['password'];
            $this->mailer->SMTPSecure = $this->config['smtp']['encryption'];
            $this->mailer->Port = $this->config['smtp']['port'];
            
            // Debug mode
            if ($this->config['settings']['debug']) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }

            // Character set
            $this->mailer->CharSet = $this->config['settings']['charset'];
            
            // Default sender
            $this->mailer->setFrom(
                $this->config['from']['email'],
                $this->config['from']['name']
            );
        } catch (Exception $e) {
            error_log('SMTP Configuration Error: ' . $e->getMessage());
            $this->enabled = false;
        }
    }

    /**
     * Send notification for new blotter creation
     * 
     * @param array $blotterData Blotter information
     * @param string $recipientEmail Email address
     * @param string $recipientName Recipient name
     * @return bool Success status
     */
    public function sendBlotterCreatedNotification($blotterData, $recipientEmail, $recipientName) {
        if (!$this->enabled || !$this->config['templates']['blotter_created']['enabled']) {
            return false;
        }

        $subject = $this->config['templates']['blotter_created']['subject'];
        $body = $this->getBlotterCreatedTemplate($blotterData);
        
        return $this->sendEmail($recipientEmail, $recipientName, $subject, $body);
    }

    /**
     * Send notification for blotter status update
     * 
     * @param array $blotterData Blotter information
     * @param string $recipientEmail Email address
     * @param string $recipientName Recipient name
     * @param string $oldStatus Previous status
     * @param string $newStatus New status
     * @return bool Success status
     */
    public function sendBlotterStatusUpdateNotification($blotterData, $recipientEmail, $recipientName, $oldStatus, $newStatus) {
        if (!$this->enabled || !$this->config['templates']['blotter_updated']['enabled']) {
            return false;
        }

        $subject = $this->config['templates']['blotter_updated']['subject'];
        $body = $this->getBlotterStatusUpdateTemplate($blotterData, $oldStatus, $newStatus);
        
        return $this->sendEmail($recipientEmail, $recipientName, $subject, $body);
    }

    /**
     * Send notification when blotter is resolved
     * 
     * @param array $blotterData Blotter information
     * @param string $recipientEmail Email address
     * @param string $recipientName Recipient name
     * @return bool Success status
     */
    public function sendBlotterResolvedNotification($blotterData, $recipientEmail, $recipientName) {
        if (!$this->enabled || !$this->config['templates']['blotter_resolved']['enabled']) {
            return false;
        }

        $subject = $this->config['templates']['blotter_resolved']['subject'];
        $body = $this->getBlotterResolvedTemplate($blotterData);
        
        return $this->sendEmail($recipientEmail, $recipientName, $subject, $body);
    }

    /**
     * Send email to admin for new case notifications
     * 
     * @param array $blotterData Blotter information
     * @return bool Success status
     */
    public function sendAdminNewCaseNotification($blotterData) {
        if (!$this->enabled) {
            return false;
        }

        $subject = 'New Blotter Case Requires Attention - #' . $blotterData['id'];
        $body = $this->getAdminNewCaseTemplate($blotterData);
        
        return $this->sendEmail(
            $this->config['admin']['email'],
            $this->config['admin']['name'],
            $subject,
            $body
        );
    }

    /**
     * Core email sending function
     * 
     * @param string $email Recipient email
     * @param string $name Recipient name
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @return bool Success status
     */
    private function sendEmail($email, $name, $subject, $body) {
        if (!$this->enabled) {
            return false;
        }

        try {
            // Recipients
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $name);

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    /**
     * Email Template: Blotter Created
     */
    private function getBlotterCreatedTemplate($data) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .info-box { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #667eea; }
                .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }
                .btn { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>New Blotter Record Created</h2>
                </div>
                <div class='content'>
                    <p>Dear <strong>{$data['complainant_name']}</strong>,</p>
                    <p>Your blotter report has been successfully filed and recorded in our system.</p>
                    
                    <div class='info-box'>
                        <strong>Blotter ID:</strong> {$data['id']}<br>
                        <strong>Incident Type:</strong> {$data['incident_type']}<br>
                        <strong>Date Filed:</strong> " . date('F d, Y', strtotime($data['incident_date'])) . "<br>
                        <strong>Status:</strong> <span style='color: #f59e0b;'>{$data['status']}</span>
                    </div>
                    
                    <p>We will keep you informed of any updates regarding this case. You can check the status anytime by visiting the barangay office.</p>
                    
                    <p><strong>Important:</strong> Please keep your Blotter ID for reference.</p>
                </div>
                <div class='footer'>
                    <p>Barangay Blotter System | © 2025 All rights reserved</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Email Template: Status Update
     */
    private function getBlotterStatusUpdateTemplate($data, $oldStatus, $newStatus) {
        $statusColor = match($newStatus) {
            'Resolved' => '#10b981',
            'Under Investigation' => '#3b82f6',
            'For Mediation', 'For Hearing' => '#8b5cf6',
            'Dismissed' => '#6b7280',
            'Escalated' => '#ef4444',
            default => '#f59e0b'
        };

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .status-change { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center; }
                .status-badge { display: inline-block; padding: 8px 16px; border-radius: 20px; color: white; font-weight: bold; margin: 0 10px; }
                .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Blotter Status Updated</h2>
                </div>
                <div class='content'>
                    <p>Dear <strong>{$data['complainant_name']}</strong>,</p>
                    <p>The status of your blotter case (ID: <strong>{$data['id']}</strong>) has been updated.</p>
                    
                    <div class='status-change'>
                        <p><strong>Status Change:</strong></p>
                        <span class='status-badge' style='background: #6b7280;'>{$oldStatus}</span>
                        <span style='font-size: 20px;'>→</span>
                        <span class='status-badge' style='background: {$statusColor};'>{$newStatus}</span>
                    </div>
                    
                    <p><strong>Incident Type:</strong> {$data['incident_type']}</p>
                    <p><strong>Date Filed:</strong> " . date('F d, Y', strtotime($data['incident_date'])) . "</p>
                    
                    <p>For more information or questions about your case, please visit the barangay office during office hours.</p>
                </div>
                <div class='footer'>
                    <p>Barangay Blotter System | © 2025 All rights reserved</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Email Template: Case Resolved
     */
    private function getBlotterResolvedTemplate($data) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .success-box { background: #d1fae5; border: 2px solid #10b981; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center; }
                .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>✓ Case Resolved</h2>
                </div>
                <div class='content'>
                    <p>Dear <strong>{$data['complainant_name']}</strong>,</p>
                    
                    <div class='success-box'>
                        <h3 style='color: #059669; margin: 0;'>Your case has been successfully resolved!</h3>
                    </div>
                    
                    <p><strong>Blotter ID:</strong> {$data['id']}</p>
                    <p><strong>Incident Type:</strong> {$data['incident_type']}</p>
                    <p><strong>Date Filed:</strong> " . date('F d, Y', strtotime($data['incident_date'])) . "</p>
                    <p><strong>Resolution Date:</strong> " . date('F d, Y') . "</p>
                    
                    <p>Thank you for your patience throughout this process. If you have any further questions or concerns, please don't hesitate to visit the barangay office.</p>
                    
                    <p>This case is now closed in our records.</p>
                </div>
                <div class='footer'>
                    <p>Barangay Blotter System | © 2025 All rights reserved</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Email Template: Admin New Case Notification
     */
    private function getAdminNewCaseTemplate($data) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #ef4444; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .urgent { background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 15px 0; }
                table { width: 100%; background: white; margin: 15px 0; border-collapse: collapse; }
                td { padding: 10px; border: 1px solid #ddd; }
                .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>⚠ New Blotter Case Requires Attention</h2>
                </div>
                <div class='content'>
                    <div class='urgent'>
                        <strong>A new blotter case has been filed and requires administrative review.</strong>
                    </div>
                    
                    <table>
                        <tr><td><strong>Blotter ID</strong></td><td>{$data['id']}</td></tr>
                        <tr><td><strong>Incident Type</strong></td><td>{$data['incident_type']}</td></tr>
                        <tr><td><strong>Date Filed</strong></td><td>" . date('F d, Y h:i A', strtotime($data['incident_date'])) . "</td></tr>
                        <tr><td><strong>Location</strong></td><td>{$data['incident_location']}</td></tr>
                        <tr><td><strong>Complainant</strong></td><td>{$data['complainant_name']}</td></tr>
                        <tr><td><strong>Status</strong></td><td><span style='color: #f59e0b;'>{$data['status']}</span></td></tr>
                    </table>
                    
                    <p><strong>Narrative:</strong><br>{$data['narrative']}</p>
                    
                    <p style='margin-top: 30px;'>Please log in to the system to review and take appropriate action.</p>
                </div>
                <div class='footer'>
                    <p>Barangay Blotter System - Admin Notification</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Test email configuration
     * 
     * @param string $testEmail Test recipient email
     * @return bool Success status
     */
    public function testEmailConfiguration($testEmail = null) {
        if (!$this->enabled) {
            return false;
        }

        $email = $testEmail ?? $this->config['admin']['email'];
        $subject = 'Test Email - Barangay Blotter System';
        $body = '
        <html>
        <body style="font-family: Arial, sans-serif; padding: 20px;">
            <h2>Email Configuration Test</h2>
            <p>This is a test email from your Barangay Blotter System.</p>
            <p>If you received this email, your email configuration is working correctly!</p>
            <p><strong>Timestamp:</strong> ' . date('F d, Y h:i:s A') . '</p>
        </body>
        </html>
        ';

        return $this->sendEmail($email, 'Test Recipient', $subject, $body);
    }
}
