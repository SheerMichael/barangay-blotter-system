<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../classes/resident.php";
require_once "../auth/session.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: viewResident.php");
    exit();
}

$id = (int)$_GET['id'];

// Get resident data and case history
$residentObj = new Resident();
$data = $residentObj->getResidentWithCaseHistory($id);

if (!$data) {
    header("Location: viewResident.php?error=notfound");
    exit();
}

$resident = $data['details'];
$caseHistory = $data['history'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print: Resident Profile - <?= htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']) ?></title>
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
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 18px;
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
            margin-bottom: 20px;
        }
        
        .info-item {
            padding: 10px;
            background: #f8f9fa;
            border-left: 3px solid #6b46c1;
        }
        
        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .info-value {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background: #f1f1f1;
            padding: 10px;
            text-align: left;
            font-size: 12px;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        
        td {
            padding: 10px;
            font-size: 12px;
            border: 1px solid #ddd;
        }
        
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .badge-complainant {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-respondent {
            background: #fee2e2;
            color: #991b1b;
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
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">🖨️ Print</button>
    
    <div class="header">
        <h1>BARANGAY BLOTTER SYSTEM</h1>
        <p>Resident Profile Report</p>
    </div>
    
    <div class="section">
        <div class="section-title">Personal Information</div>
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="font-size: 22px; color: #333;">
                <?= htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']) ?>
            </h2>
            <p style="color: #666; font-size: 14px;">
                <?= htmlspecialchars($resident['house_address']) ?>
            </p>
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Age</div>
                <div class="info-value"><?= htmlspecialchars($resident['age']) ?> years old</div>
            </div>
            <div class="info-item">
                <div class="info-label">Gender</div>
                <div class="info-value"><?= htmlspecialchars($resident['gender']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Contact Number</div>
                <div class="info-value"><?= htmlspecialchars($resident['contact_number']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Email Address</div>
                <div class="info-value">
                    <?= !empty($resident['email']) ? htmlspecialchars($resident['email']) : 'Not provided' ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">Case History</div>
        <?php if (!empty($caseHistory)): ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Case No.</th>
                        <th style="width: 15%;">Involvement</th>
                        <th style="width: 25%;">Incident Type</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%;">Date Filed</th>
                        <th style="width: 15%;">Incident Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($caseHistory as $case): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($case['case_no']) ?></strong></td>
                            <td>
                                <span class="badge <?= $case['involvement_role'] === 'Complainant' ? 'badge-complainant' : 'badge-respondent' ?>">
                                    <?= htmlspecialchars($case['involvement_role']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($case['incident_type']) ?></td>
                            <td><?= htmlspecialchars($case['status']) ?></td>
                            <td><?= htmlspecialchars(date('M d, Y', strtotime($case['created_at']))) ?></td>
                            <td><?= htmlspecialchars(date('M d, Y', strtotime($case['incident_date']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-message">
                This resident has no case history on record.
            </div>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <p>Generated on <?= date('F d, Y h:i A') ?></p>
        <p>Barangay Blotter Management System</p>
    </div>
    
    <script>
        // Auto-print when opened in a new window (optional)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>
