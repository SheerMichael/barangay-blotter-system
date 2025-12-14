<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../database/database.php";
require_once "../classes/blotter.php";

$blotterObj = new Blotter();

// Handle report generation
$reportData = null;
$reportType = null;
$dateRange = null;
$analyticsData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportType = $_POST['report_type'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $status = $_POST['status'] ?? '';
    
    if ($reportType === 'date_range' && $startDate && $endDate) {
        $reportData = $blotterObj->getBlottersForReport($startDate, $endDate);
        $dateRange = ['start' => $startDate, 'end' => $endDate];
        
        // Get analytics data for charts
        $analyticsData = [
            'byStatus' => $blotterObj->getCasesByStatus(),
            'byType' => $blotterObj->getCasesByType(),
            'trend' => $blotterObj->getTrendData($startDate, $endDate, 'day')
        ];
    } elseif ($reportType === 'by_status' && $status) {
        $reportData = $blotterObj->getBlottersForReport('', '', $status);
        $dateRange = ['status' => $status];
        
        // Get incident type breakdown for this status
        $analyticsData = [
            'byType' => $blotterObj->getCasesByType()
        ];
    } elseif ($reportType === 'incident_type') {
        $reportData = $blotterObj->getBlottersForReport();
        $dateRange = ['type' => 'all'];
        
        // Get analytics data
        $analyticsData = [
            'byStatus' => $blotterObj->getCasesByStatus(),
            'byType' => $blotterObj->getCasesByType()
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports | Barangay Blotter System</title>
  <link rel="stylesheet" href="../assets/css/tailwind.css">
  <script src="../assets/js/chart.js"></script>
  <style>
    @media print {
      .no-print {
        display: none !important;
      }
      body {
        background: white;
      }
      .print-section {
        box-shadow: none;
        margin: 0;
        padding: 0;
      }
      .chart-container {
        page-break-inside: avoid;
      }
    }
  </style>
</head>
<body class="bg-gray-900 min-h-screen">

  <div class="flex min-h-screen">
    
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-950 text-white p-6 border-r border-gray-800 no-print">
      <div class="mb-8">
        <h1 class="text-2xl font-bold bg-gradient-to-r from-purple-400 to-blue-400 bg-clip-text text-transparent">Blotter System</h1>
        <p class="text-sm text-gray-400">Barangay Management</p>
      </div>
      <nav>
        <div class="mb-4">
          <a href="../index.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">Dashboard</a>
        </div>
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Residents</h3>
          <a href="../crud/viewResident.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">View Residents</a>
          <a href="../crud/addResident.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">Add Resident</a>
        </div>
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Blotters</h3>
          <a href="../crud/viewBlotter.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">View Blotters</a>
          <a href="../crud/addBlotter.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">Add Blotter</a>
        </div>
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Reports</h3>
          <a href="index.php" class="block py-2 px-4 rounded bg-gradient-to-r from-purple-600 to-blue-600">Generate Reports</a>
        </div>
        <div class="pt-4 border-t border-gray-800">
          <a href="../auth/logout.php" class="block py-2 px-4 rounded bg-red-600/90 hover:bg-red-600 transition text-center font-medium">Logout</a>
        </div>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 bg-gray-900">
      <div class="max-w-6xl mx-auto">
        
        <!-- Header -->
        <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6 mb-6 no-print">
          <h2 class="text-3xl font-bold text-white mb-2">Generate Reports</h2>
          <p class="text-gray-400">Create customized reports and export them for printing or PDF.</p>
        </div>

        <!-- Report Generation Form -->
        <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6 mb-6 no-print">
          <h3 class="text-xl font-semibold text-white mb-4">Report Options</h3>
          
          <form method="POST" action="index.php" class="space-y-4">
            
            <!-- Report Type -->
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Select Report Type</label>
              <select name="report_type" id="reportType" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                <option value="">-- Choose a report type --</option>
                <option value="date_range">Cases by Date Range - View all cases within a specific time period</option>
                <option value="by_status">Cases by Status - Filter cases by their current status (Pending, Resolved, etc.)</option>
                <option value="incident_type">Incident Type Analysis - Breakdown of all cases by incident type</option>
              </select>
              <p class="mt-2 text-sm text-gray-400">
                <span class="font-medium">Tip:</span> Each report includes visualizations and analytics to help you understand the data better.
              </p>
            </div>

            <!-- Date Range (for Date Range Report) -->
            <div id="dateRangeSection" class="hidden">
              <label class="block text-sm font-medium text-gray-300 mb-2">Select Date Range</label>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-gray-400 mb-1">From Date</label>
                  <input type="date" name="start_date" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1">To Date</label>
                  <input type="date" name="end_date" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
              </div>
              <p class="mt-2 text-sm text-gray-400">This will show all cases filed within the selected date range with trend analysis.</p>
            </div>

            <!-- Status Filter (for Status Report) -->
            <div id="statusSection" class="hidden">
              <label class="block text-sm font-medium text-gray-300 mb-2">Filter by Case Status</label>
              <select name="status" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">All Statuses</option>
                <option value="Pending">Pending - Cases awaiting action</option>
                <option value="Under Investigation">Under Investigation - Currently being reviewed</option>
                <option value="For Mediation">For Mediation - Scheduled for mediation</option>
                <option value="For Hearing">For Hearing - Awaiting formal hearing</option>
                <option value="Resolved">Resolved - Successfully closed cases</option>
                <option value="Dismissed">Dismissed - Cases that were dismissed</option>
                <option value="Escalated">Escalated - Cases referred to higher authorities</option>
              </select>
              <p class="mt-2 text-sm text-gray-400">Filter cases by their current processing status to track resolution progress.</p>
            </div>

            <div class="flex gap-4">
              <button type="submit" class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-6 py-2 rounded-lg transition font-medium shadow-lg">
                Generate Report
              </button>
            </div>

          </form>
        </div>

        <!-- Report Display -->
        <?php if ($reportData !== null): ?>
        <div class="bg-white rounded-lg shadow p-8 print-section">
          
          <!-- Report Header -->
          <div class="text-center mb-8 border-b-2 border-gray-300 pb-4">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Barangay Blotter Report</h2>
            <?php if ($reportType === 'date_range'): ?>
              <p class="text-gray-600">Cases by Date Range: <?= date('F d, Y', strtotime($dateRange['start'])) ?> to <?= date('F d, Y', strtotime($dateRange['end'])) ?></p>
            <?php elseif ($reportType === 'by_status'): ?>
              <p class="text-gray-600">Cases by Status: <?= htmlspecialchars($dateRange['status'] ?: 'All Cases') ?></p>
            <?php elseif ($reportType === 'incident_type'): ?>
              <p class="text-gray-600">Incident Type Analysis - All Cases</p>
            <?php endif; ?>
            <p class="text-sm text-gray-500">Generated on: <?= date('F d, Y h:i A') ?></p>
          </div>

          <!-- Summary Statistics -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-4 rounded-lg text-center border border-indigo-200">
              <p class="text-sm text-indigo-600 uppercase font-semibold mb-1">Total Cases</p>
              <p class="text-3xl font-bold text-indigo-700"><?= count($reportData) ?></p>
            </div>
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-lg text-center border border-yellow-200">
              <p class="text-sm text-yellow-600 uppercase font-semibold mb-1">Pending</p>
              <p class="text-3xl font-bold text-yellow-700">
                <?= count(array_filter($reportData, fn($b) => $b['status'] === 'Pending')) ?>
              </p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg text-center border border-green-200">
              <p class="text-sm text-green-600 uppercase font-semibold mb-1">Resolved</p>
              <p class="text-3xl font-bold text-green-700">
                <?= count(array_filter($reportData, fn($b) => $b['status'] === 'Resolved')) ?>
              </p>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg text-center border border-blue-200">
              <p class="text-sm text-blue-600 uppercase font-semibold mb-1">In Progress</p>
              <p class="text-3xl font-bold text-blue-700">
                <?= count(array_filter($reportData, fn($b) => in_array($b['status'], ['Under Investigation', 'For Mediation', 'For Hearing']))) ?>
              </p>
            </div>
          </div>

          <!-- Analytics Charts -->
          <?php if ($analyticsData): ?>
          <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Visual Analytics</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              
              <!-- Status Distribution Chart -->
              <?php if (isset($analyticsData['byStatus']) && !empty($analyticsData['byStatus'])): ?>
              <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 chart-container">
                <h4 class="text-lg font-semibold text-gray-700 mb-4 text-center">Case Status Distribution</h4>
                <canvas id="statusChart" class="max-h-64"></canvas>
              </div>
              <?php endif; ?>

              <!-- Incident Type Chart -->
              <?php if (isset($analyticsData['byType']) && !empty($analyticsData['byType'])): ?>
              <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 chart-container">
                <h4 class="text-lg font-semibold text-gray-700 mb-4 text-center">Incident Type Breakdown</h4>
                <canvas id="incidentTypeChart" class="max-h-64"></canvas>
              </div>
              <?php endif; ?>

            </div>

            <!-- Trend Chart (for date range reports) -->
            <?php if ($reportType === 'date_range' && isset($analyticsData['trend']) && !empty($analyticsData['trend'])): ?>
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 chart-container">
              <h4 class="text-lg font-semibold text-gray-700 mb-4 text-center">Daily Case Trend</h4>
              <canvas id="trendChart" class="max-h-80"></canvas>
            </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <!-- Report Table -->
          <div class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="bg-gray-100 border-b-2 border-gray-300">
                  <th class="p-3 text-left text-sm font-semibold text-gray-700">Blotter ID</th>
                  <th class="p-3 text-left text-sm font-semibold text-gray-700">Date</th>
                  <th class="p-3 text-left text-sm font-semibold text-gray-700">Type</th>
                  <th class="p-3 text-left text-sm font-semibold text-gray-700">Location</th>
                  <th class="p-3 text-left text-sm font-semibold text-gray-700">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($reportData)): ?>
                  <tr>
                    <td colspan="5" class="p-4 text-center text-gray-500">No records found for the selected criteria.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($reportData as $blotter): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                      <td class="p-3 text-sm"><?= htmlspecialchars($blotter['id']) ?></td>
                      <td class="p-3 text-sm"><?= date('M d, Y', strtotime($blotter['incident_date'])) ?></td>
                      <td class="p-3 text-sm"><?= htmlspecialchars($blotter['incident_type']) ?></td>
                      <td class="p-3 text-sm"><?= htmlspecialchars($blotter['incident_location']) ?></td>
                      <td class="p-3 text-sm">
                        <span class="px-2 py-1 rounded text-xs font-medium
                          <?php 
                            echo match($blotter['status']) {
                              'Pending' => 'bg-yellow-100 text-yellow-800',
                              'Resolved' => 'bg-green-100 text-green-800',
                              'Under Investigation' => 'bg-blue-100 text-blue-800',
                              'Dismissed' => 'bg-gray-100 text-gray-800',
                              default => 'bg-indigo-100 text-indigo-800'
                            };
                          ?>
                        ">
                          <?= htmlspecialchars($blotter['status']) ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Action Buttons -->
          <div class="mt-6 flex gap-4 justify-end no-print">
            <button type="button" onclick="console.log('Email button clicked'); openEmailModal();" class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-2 rounded-lg transition font-medium shadow-lg">
              📧 Email Report
            </button>
            <button type="button" onclick="window.print()" class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-6 py-2 rounded-lg transition font-medium shadow-lg">
              🖨️ Print Report
            </button>
          </div>

        </div>
        <?php endif; ?>

      </div>
    </main>

  </div>

  <!-- Email Report Modal -->
  <div id="emailModal" style="display: none; background-color: rgba(0, 0, 0, 0.5);" class="fixed inset-0 z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-gray-800 rounded-lg shadow-xl max-w-md w-full border border-gray-700 relative" onclick="event.stopPropagation();">
      <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white p-6 rounded-t-lg">
        <h3 class="text-xl font-bold">📧 Email Report</h3>
        <p class="text-sm opacity-90 mt-1">Send this report to an email address</p>
      </div>
      
      <form id="emailForm" class="p-6">
        <div class="space-y-4">
          
          <!-- Recipient Name -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Recipient Name (Optional)</label>
            <input 
              type="text" 
              id="recipientName" 
              name="recipient_name"
              placeholder="e.g., Juan Dela Cruz"
              class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
          </div>
          
          <!-- Recipient Email -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Recipient Email <span class="text-red-400">*</span></label>
            <input 
              type="email" 
              id="recipientEmail" 
              name="recipient_email"
              placeholder="e.g., recipient@example.com"
              required
              class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
          </div>
          
          <!-- Optional Message -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Message (Optional)</label>
            <textarea 
              id="emailMessage" 
              name="message"
              rows="3"
              placeholder="Add a personal message to include in the email..."
              class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            ></textarea>
          </div>
          
          <!-- Hidden fields for report parameters -->
          <input type="hidden" name="report_type" value="<?= htmlspecialchars($reportType ?? '') ?>">
          <input type="hidden" name="start_date" value="<?= htmlspecialchars($dateRange['start'] ?? '') ?>">
          <input type="hidden" name="end_date" value="<?= htmlspecialchars($dateRange['end'] ?? '') ?>">
          <input type="hidden" name="status" value="<?= htmlspecialchars($dateRange['status'] ?? '') ?>">
          
          <!-- Status Message -->
          <div id="emailStatus" class="hidden"></div>
          
        </div>
        
        <div class="flex gap-3 mt-6">
          <button 
            type="submit" 
            id="sendEmailBtn"
            class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-2 rounded-lg transition font-medium shadow-lg"
          >
            Send Email
          </button>
          <button 
            type="button" 
            onclick="closeEmailModal()"
            class="flex-1 bg-gray-600 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition font-medium"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
    </div>
  </div>

  <script>
    // Show/hide sections based on report type
    const reportTypeSelect = document.getElementById('reportType');
    const dateRangeSection = document.getElementById('dateRangeSection');
    const statusSection = document.getElementById('statusSection');

    reportTypeSelect.addEventListener('change', function() {
      const selectedType = this.value;
      
      if (selectedType === 'date_range') {
        dateRangeSection.classList.remove('hidden');
        statusSection.classList.add('hidden');
      } else if (selectedType === 'by_status') {
        dateRangeSection.classList.add('hidden');
        statusSection.classList.remove('hidden');
      } else {
        dateRangeSection.classList.add('hidden');
        statusSection.classList.add('hidden');
      }
    });

    // Initialize Charts if analytics data exists
    <?php if ($analyticsData): ?>
    
    // Common chart options for dark theme compatibility
    Chart.defaults.color = '#374151'; // gray-700
    Chart.defaults.borderColor = '#e5e7eb'; // gray-200

    // Status Distribution Chart (Pie Chart)
    <?php if (isset($analyticsData['byStatus']) && !empty($analyticsData['byStatus'])): ?>
    const statusData = <?= json_encode($analyticsData['byStatus']) ?>;
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    
    const statusColors = {
      'Pending': '#eab308',
      'Under Investigation': '#3b82f6',
      'For Mediation': '#8b5cf6',
      'For Hearing': '#f59e0b',
      'Resolved': '#10b981',
      'Dismissed': '#6b7280',
      'Escalated': '#ef4444'
    };
    
    new Chart(statusCtx, {
      type: 'doughnut',
      data: {
        labels: statusData.map(item => item.status),
        datasets: [{
          label: 'Cases',
          data: statusData.map(item => item.count),
          backgroundColor: statusData.map(item => statusColors[item.status] || '#6366f1'),
          borderWidth: 2,
          borderColor: '#ffffff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 15,
              font: { size: 11 }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.parsed || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                return `${label}: ${value} (${percentage}%)`;
              }
            }
          }
        }
      }
    });
    <?php endif; ?>

    // Incident Type Chart (Bar Chart)
    <?php if (isset($analyticsData['byType']) && !empty($analyticsData['byType'])): ?>
    const typeData = <?= json_encode($analyticsData['byType']) ?>;
    const typeCtx = document.getElementById('incidentTypeChart').getContext('2d');
    
    new Chart(typeCtx, {
      type: 'bar',
      data: {
        labels: typeData.map(item => item.incident_type),
        datasets: [{
          label: 'Number of Cases',
          data: typeData.map(item => item.count),
          backgroundColor: 'rgba(147, 51, 234, 0.7)', // purple-600
          borderColor: 'rgb(147, 51, 234)',
          borderWidth: 2,
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                return `Cases: ${context.parsed.y}`;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });
    <?php endif; ?>

    // Trend Chart (Line Chart for date range reports)
    <?php if ($reportType === 'date_range' && isset($analyticsData['trend']) && !empty($analyticsData['trend'])): ?>
    const trendData = <?= json_encode($analyticsData['trend']) ?>;
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    
    new Chart(trendCtx, {
      type: 'line',
      data: {
        labels: trendData.map(item => {
          const date = new Date(item.period);
          return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }),
        datasets: [{
          label: 'Cases Filed',
          data: trendData.map(item => item.count),
          borderColor: 'rgb(59, 130, 246)', // blue-500
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointBackgroundColor: 'rgb(59, 130, 246)',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          },
          tooltip: {
            mode: 'index',
            intersect: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });
    <?php endif; ?>

    <?php endif; ?>

    // Email Modal Functions - Always defined
    window.openEmailModal = function() {
      console.log('openEmailModal called');
      const modal = document.getElementById('emailModal');
      console.log('Modal element:', modal);
      if (modal) {
        console.log('Setting modal display to block');
        modal.style.display = 'block';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.zIndex = '9999';
        console.log('Modal styles after setting:', window.getComputedStyle(modal));
        const form = document.getElementById('emailForm');
        if (form) form.reset();
        const status = document.getElementById('emailStatus');
        if (status) status.style.display = 'none';
      } else {
        console.error('Modal element not found!');
      }
    }

    window.closeEmailModal = function() {
      const modal = document.getElementById('emailModal');
      if (modal) {
        modal.style.display = 'none';
      }
    }

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
      const emailModal = document.getElementById('emailModal');
      const emailForm = document.getElementById('emailForm');

      // Close modal on outside click
      if (emailModal) {
        emailModal.addEventListener('click', function(e) {
          if (e.target === this) {
            closeEmailModal();
          }
        });
      }

      // Handle email form submission
      if (emailForm) {
        emailForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const sendBtn = document.getElementById('sendEmailBtn');
      const statusDiv = document.getElementById('emailStatus');
      const formData = new FormData(this);
      
      // Disable button and show loading
      sendBtn.disabled = true;
      sendBtn.textContent = 'Sending...';
      statusDiv.style.display = 'none';
      
      try {
        const response = await fetch('send_report_email.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          statusDiv.className = 'p-3 rounded-lg bg-green-900/30 text-green-400 border border-green-700';
          statusDiv.textContent = result.message;
          statusDiv.style.display = 'block';
          
          // Close modal after 2 seconds
          setTimeout(() => {
            closeEmailModal();
          }, 2000);
        } else {
          statusDiv.className = 'p-3 rounded-lg bg-red-900/30 text-red-400 border border-red-700';
          statusDiv.textContent = result.message;
          statusDiv.style.display = 'block';
        }
      } catch (error) {
        statusDiv.className = 'p-3 rounded-lg bg-red-900/30 text-red-400 border border-red-700';
        statusDiv.textContent = 'Network error. Please try again.';
        statusDiv.style.display = 'block';
      } finally {
        sendBtn.disabled = false;
        sendBtn.textContent = 'Send Email';
      }
        });
      }
    });
  </script>

</body>
</html>
