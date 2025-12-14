<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once "../database/database.php";
require_once "../classes/blotter.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filterType = $_GET['filterType'] ?? 'month';
    $startDate = $_GET['startDate'] ?? '';
    $endDate = $_GET['endDate'] ?? '';
    
    $blotterModel = new Blotter();
    $data = [];
    
    try {
        switch ($filterType) {
            case 'daily':
                // Last 7 days
                $startDate = date('Y-m-d', strtotime('-7 days'));
                $endDate = date('Y-m-d');
                $data = $blotterModel->getTrendData($startDate, $endDate, 'day');
                break;
                
            case 'weekly':
                // Last 8 weeks
                $startDate = date('Y-m-d', strtotime('-8 weeks'));
                $endDate = date('Y-m-d');
                $data = $blotterModel->getTrendData($startDate, $endDate, 'week');
                break;
                
            case 'monthly':
                // Last 6 months
                $data = $blotterModel->getMonthlyTrend(6);
                break;
                
            case '6months':
                // Last 6 months
                $data = $blotterModel->getMonthlyTrend(6);
                break;
                
            case '12months':
                // Last 12 months
                $data = $blotterModel->getMonthlyTrend(12);
                break;
                
            case 'custom':
                if (empty($startDate) || empty($endDate)) {
                    echo json_encode(['success' => false, 'message' => 'Start date and end date are required for custom range']);
                    exit;
                }
                
                // Determine grouping based on date range
                $start = new DateTime($startDate);
                $end = new DateTime($endDate);
                $diff = $start->diff($end)->days;
                
                if ($diff <= 31) {
                    $groupBy = 'day';
                } elseif ($diff <= 90) {
                    $groupBy = 'week';
                } else {
                    $groupBy = 'month';
                }
                
                $data = $blotterModel->getTrendData($startDate, $endDate, $groupBy);
                break;
                
            default:
                $data = $blotterModel->getMonthlyTrend(6);
        }
        
        // Format the data for better display
        $formattedData = [];
        foreach ($data as $row) {
            $period = $row['month'] ?? $row['period'];
            $count = $row['count'];
            
            // Format period label based on filter type
            if ($filterType === 'daily' || ($filterType === 'custom' && isset($row['period']) && strlen($row['period']) === 10)) {
                $label = date('M d', strtotime($period));
            } elseif ($filterType === 'weekly') {
                // Week format
                $label = 'Week ' . substr($period, -2);
            } else {
                // Month format
                $label = date('M Y', strtotime($period . '-01'));
            }
            
            $formattedData[] = [
                'label' => $label,
                'count' => (int)$count
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $formattedData,
            'filterType' => $filterType
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching data: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
