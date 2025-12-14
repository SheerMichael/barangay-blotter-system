<?php
/**
 * Example: Integration in crud/addBlotter.php
 * 
 * This file demonstrates how to add email notifications
 * when a new blotter is created.
 */

// Include at the top of addBlotter.php (after other includes)
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../classes/NotificationMailer.php';

// After successfully creating the blotter:
if ($blotterId) { // Assuming $blotterId is the newly created blotter ID
    try {
        // Initialize mailer
        $mailer = new NotificationMailer();
        
        // Prepare blotter data for email
        $blotterData = [
            'id' => $blotterId,
            'incident_type' => $_POST['incident_type'],
            'incident_date' => $_POST['incident_date'],
            'incident_location' => $_POST['incident_location'],
            'complainant_name' => $_POST['complainant_name'] ?? 'N/A',
            'status' => $_POST['status'] ?? 'Pending',
            'narrative' => $_POST['narrative'] ?? ''
        ];
        
        // Option 1: Send to complainant if email is provided
        if (!empty($_POST['complainant_email'])) {
            $emailSent = $mailer->sendBlotterCreatedNotification(
                $blotterData,
                $_POST['complainant_email'],
                $_POST['complainant_name']
            );
            
            if ($emailSent) {
                error_log("Confirmation email sent to complainant for blotter #{$blotterId}");
            }
        }
        
        // Option 2: Get email from residents table
        // Assuming complainant_id is stored
        if (!empty($_POST['complainant_id'])) {
            $residentObj = new Resident();
            $complainant = $residentObj->getResidentById($_POST['complainant_id']);
            
            if ($complainant && !empty($complainant['email'])) {
                $mailer->sendBlotterCreatedNotification(
                    $blotterData,
                    $complainant['email'],
                    $complainant['name']
                );
            }
        }
        
        // Always send admin notification for new cases
        $adminEmailSent = $mailer->sendAdminNewCaseNotification($blotterData);
        
        if ($adminEmailSent) {
            error_log("Admin notification sent for new blotter #{$blotterId}");
        }
        
    } catch (Exception $e) {
        // Don't fail the blotter creation if email fails
        error_log("Email notification error: " . $e->getMessage());
    }
}
