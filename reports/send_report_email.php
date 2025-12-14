<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/../classes/blotter.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load email configuration
$EMAIL_CONFIG = require __DIR__ . '/../config/email.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $recipientEmail = trim($_POST['recipient_email'] ?? '');
    $recipientName = trim($_POST['recipient_name'] ?? '');
    $reportType = $_POST['report_type'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $status = $_POST['status'] ?? '';
    $message = trim($_POST['message'] ?? '');
    
    // Validate email
    if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
        exit;
    }
    
    // Get report data
    $blotterObj = new Blotter();
    $reportData = null;
    $reportTitle = '';
    $reportDescription = '';
    
    if ($reportType === 'date_range' && $startDate && $endDate) {
        $reportData = $blotterObj->getBlottersForReport($startDate, $endDate);
        $reportTitle = 'Cases by Date Range';
        $reportDescription = date('F d, Y', strtotime($startDate)) . ' to ' . date('F d, Y', strtotime($endDate));
    } elseif ($reportType === 'by_status' && $status) {
        $reportData = $blotterObj->getBlottersForReport('', '', $status);
        $reportTitle = 'Cases by Status';
        $reportDescription = htmlspecialchars($status ?: 'All Cases');
    } elseif ($reportType === 'incident_type') {
        $reportData = $blotterObj->getBlottersForReport();
        $reportTitle = 'Incident Type Analysis';
        $reportDescription = 'All Cases';
    }
    
    if ($reportData === null) {
        echo json_encode(['success' => false, 'message' => 'Invalid report parameters.']);
        exit;
    }
    
    // Calculate statistics
    $totalCases = count($reportData);
    $pendingCases = count(array_filter($reportData, fn($b) => $b['status'] === 'Pending'));
    $resolvedCases = count(array_filter($reportData, fn($b) => $b['status'] === 'Resolved'));
    $inProgressCases = count(array_filter($reportData, fn($b) => in_array($b['status'], ['Under Investigation', 'For Mediation', 'For Hearing'])));
    
    // Create HTML email body
    $htmlBody = getReportEmailTemplate(
        $recipientName,
        $reportTitle,
        $reportDescription,
        $totalCases,
        $pendingCases,
        $resolvedCases,
        $inProgressCases,
        $reportData,
        $message
    );
    
    // Send email using PHPMailer
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $EMAIL_CONFIG['smtp']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $EMAIL_CONFIG['smtp']['username'];
        $mail->Password = $EMAIL_CONFIG['smtp']['password'];
        $mail->SMTPSecure = $EMAIL_CONFIG['smtp']['encryption'];
        $mail->Port = $EMAIL_CONFIG['smtp']['port'];
        
        // Recipients
        $mail->setFrom($EMAIL_CONFIG['from']['email'], $EMAIL_CONFIG['from']['name']);
        $mail->addAddress($recipientEmail, $recipientName);
        $mail->addReplyTo($EMAIL_CONFIG['from']['email'], $EMAIL_CONFIG['from']['name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Barangay Blotter Report - {$reportTitle}";
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);
        
        $mail->send();
        
        echo json_encode([
            'success' => true, 
            'message' => "Report successfully sent to {$recipientEmail}"
        ]);
        
    } catch (Exception $e) {
        error_log("Report email failed: " . $mail->ErrorInfo);
        echo json_encode([
            'success' => false, 
            'message' => "Failed to send email. Please try again later."
        ]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

/**
 * Generate HTML email template for report
 */
function getReportEmailTemplate($recipientName, $reportTitle, $reportDescription, $totalCases, $pendingCases, $resolvedCases, $inProgressCases, $reportData, $customMessage) {
    $generatedDate = date('F d, Y h:i A');
    
    // Build table rows
    $tableRows = '';
    if (empty($reportData)) {
        $tableRows = '<tr><td colspan="5" style="padding: 16px; text-align: center; color: #6b7280;">No records found for the selected criteria.</td></tr>';
    } else {
        foreach ($reportData as $blotter) {
            $statusColor = match($blotter['status']) {
                'Pending' => 'background: #fef3c7; color: #92400e;',
                'Resolved' => 'background: #d1fae5; color: #065f46;',
                'Under Investigation' => 'background: #dbeafe; color: #1e40af;',
                'Dismissed' => 'background: #f3f4f6; color: #374151;',
                default => 'background: #e0e7ff; color: #3730a3;'
            };
            
            $tableRows .= '<tr style="border-bottom: 1px solid #e5e7eb;">
                <td style="padding: 12px; font-size: 14px;">' . htmlspecialchars($blotter['id']) . '</td>
                <td style="padding: 12px; font-size: 14px;">' . date('M d, Y', strtotime($blotter['incident_date'])) . '</td>
                <td style="padding: 12px; font-size: 14px;">' . htmlspecialchars($blotter['incident_type']) . '</td>
                <td style="padding: 12px; font-size: 14px;">' . htmlspecialchars($blotter['incident_location']) . '</td>
                <td style="padding: 12px; font-size: 14px;">
                    <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; ' . $statusColor . '">' 
                        . htmlspecialchars($blotter['status']) . 
                    '</span>
                </td>
            </tr>';
        }
    }
    
    $customMessageHtml = '';
    if (!empty($customMessage)) {
        $customMessageHtml = '
        <div style="background: #f3f4f6; border-left: 4px solid #6366f1; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
            <p style="margin: 0; font-size: 14px; color: #374151;"><strong>Message from sender:</strong></p>
            <p style="margin: 8px 0 0 0; font-size: 14px; color: #4b5563;">' . nl2br(htmlspecialchars($customMessage)) . '</p>
        </div>';
    }
    
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Barangay Blotter Report</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f9fafb;">
        <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
            
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 32px; text-align: center; border-radius: 8px 8px 0 0;">
                <h1 style="margin: 0 0 8px 0; font-size: 28px; font-weight: bold;">Barangay Blotter Report</h1>
                <p style="margin: 0; font-size: 16px; opacity: 0.9;">' . htmlspecialchars($reportTitle) . '</p>
            </div>
            
            <!-- Main Content -->
            <div style="background: white; padding: 32px; border-radius: 0 0 8px 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                
                <!-- Greeting -->
                <p style="margin: 0 0 16px 0; font-size: 16px; color: #374151;">
                    ' . (!empty($recipientName) ? 'Dear ' . htmlspecialchars($recipientName) . ',' : 'Hello,') . '
                </p>
                
                <p style="margin: 0 0 24px 0; font-size: 14px; color: #6b7280;">
                    Please find attached your requested Barangay Blotter Report generated on ' . $generatedDate . '.
                </p>
                
                ' . $customMessageHtml . '
                
                <!-- Report Info -->
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; padding: 16px; margin-bottom: 24px; border-radius: 8px;">
                    <p style="margin: 0 0 8px 0; font-size: 14px; color: #6b7280;"><strong>Report Type:</strong> ' . htmlspecialchars($reportTitle) . '</p>
                    <p style="margin: 0; font-size: 14px; color: #6b7280;"><strong>Report Period/Filter:</strong> ' . htmlspecialchars($reportDescription) . '</p>
                </div>
                
                <!-- Summary Statistics -->
                <div style="margin-bottom: 32px;">
                    <h3 style="margin: 0 0 16px 0; font-size: 18px; color: #111827; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px;">Summary Statistics</h3>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                        <div style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); padding: 16px; border-radius: 8px; text-align: center; border: 1px solid #c7d2fe;">
                            <p style="margin: 0 0 4px 0; font-size: 12px; color: #4338ca; text-transform: uppercase; font-weight: 600;">Total Cases</p>
                            <p style="margin: 0; font-size: 28px; font-weight: bold; color: #4338ca;">' . $totalCases . '</p>
                        </div>
                        <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 16px; border-radius: 8px; text-align: center; border: 1px solid #fde047;">
                            <p style="margin: 0 0 4px 0; font-size: 12px; color: #92400e; text-transform: uppercase; font-weight: 600;">Pending</p>
                            <p style="margin: 0; font-size: 28px; font-weight: bold; color: #92400e;">' . $pendingCases . '</p>
                        </div>
                        <div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); padding: 16px; border-radius: 8px; text-align: center; border: 1px solid #6ee7b7;">
                            <p style="margin: 0 0 4px 0; font-size: 12px; color: #065f46; text-transform: uppercase; font-weight: 600;">Resolved</p>
                            <p style="margin: 0; font-size: 28px; font-weight: bold; color: #065f46;">' . $resolvedCases . '</p>
                        </div>
                        <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); padding: 16px; border-radius: 8px; text-align: center; border: 1px solid #93c5fd;">
                            <p style="margin: 0 0 4px 0; font-size: 12px; color: #1e40af; text-transform: uppercase; font-weight: 600;">In Progress</p>
                            <p style="margin: 0; font-size: 28px; font-weight: bold; color: #1e40af;">' . $inProgressCases . '</p>
                        </div>
                    </div>
                </div>
                
                <!-- Report Data Table -->
                <div style="margin-bottom: 24px;">
                    <h3 style="margin: 0 0 16px 0; font-size: 18px; color: #111827; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px;">Case Details</h3>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb;">
                            <thead>
                                <tr style="background: #f3f4f6; border-bottom: 2px solid #d1d5db;">
                                    <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase;">ID</th>
                                    <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase;">Date</th>
                                    <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase;">Type</th>
                                    <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase;">Location</th>
                                    <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                ' . $tableRows . '
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Footer Note -->
                <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 16px; border-radius: 8px; margin-top: 24px;">
                    <p style="margin: 0; font-size: 13px; color: #92400e;">
                        <strong>Note:</strong> This is an automated report generated from the Barangay Blotter Management System. 
                        For more detailed information or inquiries, please contact the barangay office directly.
                    </p>
                </div>
                
            </div>
            
            <!-- Email Footer -->
            <div style="text-align: center; padding: 24px 0; color: #6b7280; font-size: 12px;">
                <p style="margin: 0 0 8px 0;">This email was sent from the Barangay Blotter Management System</p>
                <p style="margin: 0;">Generated on ' . $generatedDate . '</p>
            </div>
            
        </div>
    </body>
    </html>
    ';
}
