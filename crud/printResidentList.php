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

$residentObj = new Resident();
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$residents = $residentObj->getAllResidents($searchTerm);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print: Residents List</title>
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background: #f1f1f1;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        
        td {
            padding: 10px 8px;
            font-size: 11px;
            border: 1px solid #ddd;
        }
        
        tr:nth-child(even) {
            background: #f9f9f9;
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
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">🖨️ Print</button>
    
    <div class="header">
        <h1>BARANGAY BLOTTER SYSTEM</h1>
        <p>Residents Directory</p>
        <?php if (!empty($searchTerm)): ?>
            <p style="font-size: 12px; color: #999; margin-top: 5px;">
                Filtered by: "<?= htmlspecialchars($searchTerm) ?>"
            </p>
        <?php endif; ?>
    </div>
    
    <div class="summary-info">
        <strong>Total Residents: <?= count($residents) ?></strong>
    </div>
    
    <?php if (!empty($residents)): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 15%;">Full Name</th>
                    <th style="width: 6%;">Age</th>
                    <th style="width: 8%;">Gender</th>
                    <th style="width: 25%;">Address</th>
                    <th style="width: 13%;">Contact Number</th>
                    <th style="width: 18%;">Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($residents as $index => $res): ?>
                    <tr>
                        <td class="row-number"><?= $index + 1 ?></td>
                        <td><strong><?= htmlspecialchars($res['first_name'] . ' ' . $res['last_name']) ?></strong></td>
                        <td><?= htmlspecialchars($res['age']) ?></td>
                        <td><?= htmlspecialchars($res['gender']) ?></td>
                        <td><?= htmlspecialchars($res['house_address']) ?></td>
                        <td><?= htmlspecialchars($res['contact_number']) ?></td>
                        <td style="font-size: 10px;">
                            <?= !empty($res['email']) ? htmlspecialchars($res['email']) : '—' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-message">
            No residents found<?php if (!empty($searchTerm)) echo ' matching "' . htmlspecialchars($searchTerm) . '"'; ?>.
        </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>Generated on <?= date('F d, Y h:i A') ?></p>
        <p>Barangay Blotter Management System</p>
    </div>
</body>
</html>
