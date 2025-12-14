<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../classes/blotter.php";
require_once "../auth/session.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$blotter = new Blotter();

// Get filter values
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Use the getBlotters() method
$blotters = $blotter->getBlotters($searchTerm, $statusFilter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print: Blotters List</title>
    <style>
        @media print {
            @page {
                size: letter landscape;
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
        
        .summary-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-left: 4px solid #6b46c1;
        }
        
        .summary-info strong {
            color: #6b46c1;
            font-size: 16px;
        }
        
        .filter-info {
            font-size: 11px;
            color: #999;
            margin-top: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background: #f1f1f1;
            padding: 10px 6px;
            text-align: left;
            font-size: 10px;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        
        td {
            padding: 10px 6px;
            font-size: 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-resolved {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-scheduled {
            background: #ddd6fe;
            color: #4c1d95;
        }
        
        .badge-endorsed {
            background: #dbeafe;
            color: #1e40af;
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
            padding: 40px;
            color: #999;
            font-style: italic;
            font-size: 14px;
        }
        
        .row-number {
            font-weight: bold;
            color: #666;
        }
        
        .case-no {
            font-weight: bold;
            color: #6b46c1;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">🖨️ Print</button>
    
    <div class="header">
        <h1>BARANGAY BLOTTER SYSTEM</h1>
        <p>Blotter Cases Summary</p>
        <?php if (!empty($searchTerm) || !empty($statusFilter)): ?>
            <p class="filter-info">
                <?php if (!empty($statusFilter)): ?>
                    Status: <?= htmlspecialchars($statusFilter) ?>
                <?php endif; ?>
                <?php if (!empty($searchTerm)): ?>
                    <?= !empty($statusFilter) ? ' | ' : '' ?>
                    Search: "<?= htmlspecialchars($searchTerm) ?>"
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
    
    <div class="summary-info">
        <strong>Total Cases: <?= count($blotters) ?></strong>
        <div class="filter-info">Generated on <?= date('F d, Y h:i A') ?></div>
    </div>
    
    <?php if (!empty($blotters)): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 12%;">Case No.</th>
                    <th style="width: 18%;">Complainant(s)</th>
                    <th style="width: 18%;">Respondent(s)</th>
                    <th style="width: 15%;">Incident Type</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 11%;">Incident Date</th>
                    <th style="width: 12%;">Date Filed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blotters as $index => $b): ?>
                    <?php 
                        $status = strtolower($b['status']);
                        $badgeClass = match($status) {
                            'pending' => 'badge-pending',
                            'resolved' => 'badge-resolved',
                            'scheduled' => 'badge-scheduled',
                            'endorsed to police' => 'badge-endorsed',
                            default => 'badge-pending'
                        };
                    ?>
                    <tr>
                        <td class="row-number"><?= $index + 1 ?></td>
                        <td class="case-no"><?= htmlspecialchars($b['case_no'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($b['complainant_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($b['respondent_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($b['incident_type']) ?></td>
                        <td>
                            <span class="badge <?= $badgeClass ?>">
                                <?= htmlspecialchars(ucfirst($b['status'])) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars(date('M d, Y', strtotime($b['incident_date']))) ?></td>
                        <td><?= htmlspecialchars(date('M d, Y', strtotime($b['created_at'] ?? 'now'))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-message">
            No blotter cases found.
        </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>Generated on <?= date('F d, Y h:i A') ?></p>
        <p>Barangay Blotter Management System</p>
    </div>
</body>
</html>
