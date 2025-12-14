<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../auth/session.php"; 
require_once "../classes/blotter.php";
require_once "../classes/resident.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: viewBlotter.php?msg=invalid_id");
    exit();
}

$blotter_id = (int)$_GET['id'];
$blotterModel = new Blotter();
$residentModel = new Resident();

$blotter = $blotterModel->viewBlotterById($blotter_id);

// Check if blotter exists
if (!$blotter) {
    header("Location: viewBlotter.php?msg=not_found");
    exit();
}

// Fetch Complainant Details
$complainants = [];
foreach ($blotter['complainant_ids'] as $resident_id) {
    $resident = $residentModel->viewResidentById((int)$resident_id);
    if ($resident) {
        $complainants[] = $resident;
    }
}

// Fetch Respondent Details
$respondents = [];
foreach ($blotter['respondent_ids'] as $resident_id) {
    $resident = $residentModel->viewResidentById((int)$resident_id);
    if ($resident) {
        $respondents[] = $resident;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print: Blotter Case - <?= htmlspecialchars($blotter['case_no']) ?></title>
    <style>
        @media print {
            @page {
                size: letter;
                margin: 0.5in;
            }
            .no-print {
                display: none !important;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #fff;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
            color: #666;
        }
        
        .case-number {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #6b46c1;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-resolved {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-scheduled {
            background: #ddd6fe;
            color: #4c1d95;
        }
        
        .status-endorsed {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            padding: 10px;
            background: #f8f9fa;
            border-left: 3px solid #6b46c1;
        }
        
        .info-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .info-value {
            font-size: 13px;
            color: #333;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .detail-box {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #6b46c1;
            white-space: pre-wrap;
            font-size: 12px;
            line-height: 1.8;
        }
        
        .parties-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .party-card {
            border: 1px solid #ddd;
            padding: 15px;
            background: #f9f9f9;
            page-break-inside: avoid;
        }
        
        .party-card h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #6b46c1;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .party-name {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        
        .party-info {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .party-label {
            font-weight: bold;
            display: inline-block;
            width: 80px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #6b46c1;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background: #553c9a;
        }
        
        .empty-message {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">🖨️ Print</button>
    
    <div class="header">
        <h1>BARANGAY BLOTTER SYSTEM</h1>
        <p>Blotter Case Report</p>
    </div>
    
    <div style="text-align: center;">
        <div class="case-number"><?= htmlspecialchars($blotter['case_no']) ?></div>
        <?php
            $status = strtolower($blotter['status']);
            $statusClass = match($status) {
                'pending' => 'status-pending',
                'resolved' => 'status-resolved',
                'scheduled' => 'status-scheduled',
                'endorsed to police' => 'status-endorsed',
                default => 'status-pending'
            };
        ?>
        <span class="status-badge <?= $statusClass ?>">
            <?= htmlspecialchars(ucfirst($blotter['status'])) ?>
        </span>
    </div>
    
    <div class="section">
        <div class="section-title">Incident Information</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Incident Type</div>
                <div class="info-value"><?= htmlspecialchars($blotter['incident_type']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Incident Location</div>
                <div class="info-value"><?= htmlspecialchars($blotter['incident_location']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Incident Date</div>
                <div class="info-value"><?= htmlspecialchars(date('F d, Y', strtotime($blotter['incident_date']))) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Incident Time</div>
                <div class="info-value">
                    <?= $blotter['incident_time'] ? htmlspecialchars(date('h:i A', strtotime($blotter['incident_time']))) : 'Not specified' ?>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Date Filed</div>
                <div class="info-value"><?= htmlspecialchars(date('F d, Y', strtotime($blotter['created_at']))) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Case Status</div>
                <div class="info-value"><?= htmlspecialchars(ucfirst($blotter['status'])) ?></div>
            </div>
        </div>
        
        <div style="margin-top: 15px;">
            <div class="info-label" style="margin-bottom: 8px;">Details of Incident:</div>
            <div class="detail-box"><?= htmlspecialchars($blotter['details']) ?></div>
        </div>
        
        <?php if (!empty($blotter['remarks'])): ?>
            <div style="margin-top: 15px;">
                <div class="info-label" style="margin-bottom: 8px;">Remarks:</div>
                <div class="detail-box"><?= htmlspecialchars($blotter['remarks']) ?></div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <div class="section-title">Parties Involved</div>
        <div class="parties-grid">
            <!-- Complainants -->
            <div class="party-card">
                <h3>Complainant(s)</h3>
                <?php if (empty($complainants)): ?>
                    <div class="empty-message">No complainants listed</div>
                <?php else: ?>
                    <?php foreach ($complainants as $c): ?>
                        <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed #ddd;">
                            <div class="party-name"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></div>
                            <div class="party-info">
                                <span class="party-label">Address:</span>
                                <?= htmlspecialchars($c['house_address']) ?>
                            </div>
                            <div class="party-info">
                                <span class="party-label">Contact:</span>
                                <?= htmlspecialchars($c['contact_number']) ?>
                            </div>
                            <div class="party-info">
                                <span class="party-label">Age/Gender:</span>
                                <?= htmlspecialchars($c['age']) ?> / <?= htmlspecialchars($c['gender']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Respondents -->
            <div class="party-card">
                <h3>Respondent(s)</h3>
                <?php if (empty($respondents)): ?>
                    <div class="empty-message">No respondents listed</div>
                <?php else: ?>
                    <?php foreach ($respondents as $r): ?>
                        <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed #ddd;">
                            <div class="party-name"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></div>
                            <div class="party-info">
                                <span class="party-label">Address:</span>
                                <?= htmlspecialchars($r['house_address']) ?>
                            </div>
                            <div class="party-info">
                                <span class="party-label">Contact:</span>
                                <?= htmlspecialchars($r['contact_number']) ?>
                            </div>
                            <div class="party-info">
                                <span class="party-label">Age/Gender:</span>
                                <?= htmlspecialchars($r['age']) ?> / <?= htmlspecialchars($r['gender']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>Generated on <?= date('F d, Y h:i A') ?></p>
        <p>Barangay Blotter Management System</p>
    </div>
</body>
</html>
