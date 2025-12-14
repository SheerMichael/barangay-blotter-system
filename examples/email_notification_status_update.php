<?php
/**
 * Example: Integration in crud/updateBlotterStatus.php
 * 
 * This file demonstrates how to add email notifications
 * when a blotter status is updated.
 */

// Include at the top of updateBlotterStatus.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../classes/NotificationMailer.php';
require_once __DIR__ . '/../classes/resident.php';

// After successfully updating the status in database:
if ($updateSuccess) {
    try {
        // Initialize mailer
        $mailer = new NotificationMailer();
        
        // Get full blotter details (you already have this in updateBlotterStatus.php)
        // Assuming $blotterData contains the blotter information
        
        // Get complainant information
        $residentObj = new Resident();
        $complainant = $residentObj->getResidentById($blotterData['complainant_id']);
        
        // Only send if complainant has email
        if ($complainant && !empty($complainant['email'])) {
            if ($newStatus === 'Resolved') {
                // Special notification for resolved cases
                $emailSent = $mailer->sendBlotterResolvedNotification(
                    $blotterData,
                    $complainant['email'],
                    $complainant['name']
                );
            } else {
                // Regular status update notification
                $emailSent = $mailer->sendBlotterStatusUpdateNotification(
                    $blotterData,
                    $complainant['email'],
                    $complainant['name'],
                    $oldStatus,
                    $newStatus
                );
            }
            
            if ($emailSent) {
                error_log("Email notification sent to {$complainant['email']} for blotter #{$blotterData['id']}");
            } else {
                error_log("Failed to send email notification for blotter #{$blotterData['id']}");
            }
        }
    } catch (Exception $e) {
        // Don't fail the status update if email fails
        error_log("Email notification error: " . $e->getMessage());
    }
}
