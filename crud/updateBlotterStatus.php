<?php
// updateBlotterStatus.php - Quick status update endpoint with email notifications
require_once "../auth/session.php";
require_once "../classes/blotter.php";
require_once "../classes/resident.php";

// Load email notification system
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../classes/NotificationMailer.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $newStatus = $_POST['status'] ?? null;
    
    if (!$id || !$newStatus) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        exit();
    }
    
    $blotterObj = new Blotter();
    
    // Get current blotter details BEFORE updating (to get old status)
    $blotterData = $blotterObj->getBlotterById($id);
    if (!$blotterData) {
        echo json_encode(['success' => false, 'message' => 'Blotter not found']);
        exit();
    }
    
    $oldStatus = $blotterData['status'];
    
    // Update the status
    $result = $blotterObj->updateStatus($id, $newStatus);
    
    if ($result) {
        // Status updated successfully - now send email notifications
        try {
            $mailer = new NotificationMailer();
            $residentObj = new Resident();
            
            // Get updated blotter data with new status
            $blotterData['status'] = $newStatus;
            
            // Send notification to all complainants
            if (!empty($blotterData['complainants'])) {
                foreach ($blotterData['complainants'] as $complainant) {
                    // Only send if complainant has email
                    if (!empty($complainant['email'])) {
                        $complainantName = $complainant['first_name'] . ' ' . $complainant['last_name'];
                        
                        // Prepare blotter data for email
                        $emailData = [
                            'id' => $blotterData['case_no'],
                            'incident_type' => $blotterData['incident_type'],
                            'incident_date' => $blotterData['incident_date'],
                            'incident_location' => $blotterData['incident_location'],
                            'status' => $newStatus,
                            'complainant_name' => $complainantName,
                            'narrative' => $blotterData['details']
                        ];
                        
                        // Send appropriate notification based on new status
                        if ($newStatus === 'Resolved') {
                            $mailer->sendBlotterResolvedNotification(
                                $emailData,
                                $complainant['email'],
                                $complainantName
                            );
                        } else {
                            $mailer->sendBlotterStatusUpdateNotification(
                                $emailData,
                                $complainant['email'],
                                $complainantName,
                                $oldStatus,
                                $newStatus
                            );
                        }
                        
                        error_log("Email notification sent to {$complainant['email']} for blotter #{$blotterData['case_no']}");
                    }
                }
            }
        } catch (Exception $e) {
            // Don't fail the status update if email fails - just log it
            error_log("Email notification error: " . $e->getMessage());
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Status updated successfully and notifications sent'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
