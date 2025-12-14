<?php
// index.php - Dashboard with sidebar navigation

// FIX: Use __DIR__ to create robust, absolute paths
require_once __DIR__ . "/auth/session.php";

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // FIX: Corrected redirect path for root index.php
    header("Location: auth/login.php"); 
    exit();
}

// 2. Include models to get stats
// FIX: Pre-load database.php from the root path.
// This solves the 500 error caused by nested relative paths in the class files.
require_once __DIR__ . "/database/database.php"; 

// Now include classes. Their internal 'require_once "../database/database.php"' 
// will be skipped, avoiding the path error.
require_once __DIR__ . "/classes/resident.php";
require_once __DIR__ . "/classes/blotter.php";

// 3. Fetch data for stats
$residentModel = new Resident();
$blotterModel = new Blotter();

// FIX: Used viewResidents() which exists and is correct for a simple count
$residents = $residentModel->viewResidents();
$totalResidents = count($residents);

// FIX: Used getBlotters() which is the correct method name
$allBlotters = $blotterModel->getBlotters();
$totalBlotters = count($allBlotters);

// FIX: More efficient way to count statuses
$pendingBlotters = $blotterModel->getBlotters('', 'Pending');
$totalPending = count($pendingBlotters);

$resolvedBlotters = $blotterModel->getBlotters('', 'Resolved');
$totalResolved = count($resolvedBlotters);

// Get chart data
$monthlyData = $blotterModel->getCasesCountByMonth();
$casesByType = $blotterModel->getCasesByType();
$casesByStatus = $blotterModel->getCasesByStatus();
$trendData6Months = $blotterModel->getMonthlyTrend(6);
$trendData12Months = $blotterModel->getMonthlyTrend(12);

// Get username, with a fallback
$username = $_SESSION['username'] ?? 'User';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Barangay Blotter System</title>
  <link rel="stylesheet" href="assets/css/tailwind.css">
  <script src="assets/js/chart.js"></script>
</head>
<body class="bg-gray-900 min-h-screen">
  
  <div class="flex">
    
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-950 text-white min-h-screen p-6 border-r border-gray-800">
      <div class="mb-8">
        <h1 class="text-2xl font-bold bg-gradient-to-r from-purple-400 to-blue-400 bg-clip-text text-transparent">Blotter System</h1>
        <p class="text-sm text-gray-400 mt-1">Welcome, <?= htmlspecialchars($username) ?>!</p>
      </div>

      <nav class="space-y-2">
        <!-- Dashboard Home -->
        <div class="mb-4">
          <!-- Highlighted as current page -->
            <a href="index.php" class="block py-2 px-4 rounded bg-gradient-to-r from-purple-600 to-blue-600">
            Dashboard
          </a>
        </div>

        <!-- Residents -->
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Residents</h3>
          <a href="crud/viewResident.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">
            View Residents
          </a>
          <a href="crud/addResident.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">
            Add Resident
          </a>
        </div>

        <!-- Blotters -->
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Blotters</h3>
          <a href="crud/viewBlotter.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">
            View Blotters
          </a>
          <a href="crud/addBlotter.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">
            Add Blotter
          </a>
        </div>

        <!-- Reports -->
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Reports</h3>
          <a href="reports/index.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">
            Generate Reports
          </a>
        </div>

        <!-- Logout -->
        <div class="pt-4 border-t border-gray-800">
          <a href="auth/logout.php" class="block py-2 px-4 rounded bg-red-600/90 hover:bg-red-600 transition text-center font-medium">
            Logout
          </a>
        </div>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 bg-gray-900">
      <div class="max-w-6xl mx-auto">
        
        <!-- Welcome Header -->
        <div class="bg-gray-800 rounded-lg shadow-lg p-6 mb-6 border border-gray-700">
          <h2 class="text-3xl font-bold text-white mb-2">Welcome to your Dashboard</h2>
          <p class="text-gray-400">Manage residents and blotter records efficiently.</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          
          <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700 hover:border-purple-500 transition">
            <div>
              <p class="text-sm text-gray-400 uppercase font-semibold">Total Residents</p>
              <p class="text-3xl font-bold text-purple-400 mt-2">
                <?= $totalResidents ?>
              </p>
            </div>
          </div>

          <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700 hover:border-blue-500 transition">
            <div>
              <p class="text-sm text-gray-400 uppercase font-semibold">Total Blotters</p>
              <p class="text-3xl font-bold text-blue-400 mt-2">
                <?= $totalBlotters ?>
              </p>
            </div>
          </div>

          <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700 hover:border-yellow-500 transition">
            <div>
              <p class="text-sm text-gray-400 uppercase font-semibold">Pending Cases</p>
              <p class="text-3xl font-bold text-yellow-400 mt-2">
                <?= $totalPending ?>
              </p>
            </div>
          </div>

          <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700 hover:border-green-500 transition">
            <div>
              <p class="text-sm text-gray-400 uppercase font-semibold">Resolved Cases</p>
              <p class="text-3xl font-bold text-green-400 mt-2">
                <?= $totalResolved ?>
              </p>
            </div>
          </div>

        </div>
                <!-- Quick Actions -->
        <div class="bg-gray-800 rounded-lg shadow-lg p-6 mb-6 border border-gray-700">
          <h3 class="text-xl font-semibold text-white mb-4">Quick Actions</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <a href="crud/addResident.php" class="p-4 border-2 border-gray-700 rounded-lg hover:border-purple-500 hover:bg-gray-700 transition">
              <div>
                <p class="font-semibold text-white">Add New Resident</p>
                <p class="text-sm text-gray-400">Register a new resident to the system</p>
              </div>
            </a>
            
            <a href="crud/addBlotter.php" class="p-4 border-2 border-gray-700 rounded-lg hover:border-blue-500 hover:bg-gray-700 transition">
              <div>
                <p class="font-semibold text-white">Create New Blotter</p>
                <p class="text-sm text-gray-400">File a new blotter record</p>
              </div>
            </a>

            <a href="crud/viewResident.php" class="p-4 border-2 border-gray-700 rounded-lg hover:border-green-500 hover:bg-gray-700 transition">
              <div>
                <p class="font-semibold text-white">View All Residents</p>
                <p class="text-sm text-gray-400">Browse and manage residents</p>
              </div>
            </a>

            <a href="crud/viewBlotter.php" class="p-4 border-2 border-gray-700 rounded-lg hover:border-purple-500 hover:bg-gray-700 transition">
              <div>
                <p class="font-semibold text-white">View All Blotters</p>
                <p class="text-sm text-gray-400">Search and manage blotter records</p>
              </div>
            </a>
          </div>
        </div>

        <!-- Analytics & Trends -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          
          <!-- Monthly Comparison -->
          <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700">
            <h3 class="text-xl font-semibold text-white mb-4">Cases This Month vs Last Month</h3>
            <div style="height: 300px;">
              <canvas id="monthlyComparisonChart"></canvas>
            </div>
          </div>

          <!-- Cases by Type -->
          <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700">
            <h3 class="text-xl font-semibold text-white mb-4">Cases by Type</h3>
            <div style="height: 300px;">
              <canvas id="casesByTypeChart"></canvas>
            </div>
          </div>

        </div>

        <!-- Trend Chart with Filters -->
        <div class="bg-gray-800 rounded-lg shadow-lg p-6 mb-6 border border-gray-700">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
            <h3 class="text-xl font-semibold text-white">Case Trends</h3>
            
            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center gap-2">
              <button onclick="updateTrendChart('daily')" id="btn-daily" class="px-3 py-1.5 text-sm rounded-lg border-2 border-gray-700 text-gray-300 hover:border-purple-500 hover:bg-gray-700 transition">
                Daily
              </button>
              <button onclick="updateTrendChart('weekly')" id="btn-weekly" class="px-3 py-1.5 text-sm rounded-lg border-2 border-gray-700 text-gray-300 hover:border-purple-500 hover:bg-gray-700 transition">
                Weekly
              </button>
              <button onclick="updateTrendChart('monthly')" id="btn-monthly" class="px-3 py-1.5 text-sm rounded-lg border-2 border-purple-500 bg-purple-600/20 text-purple-300 font-medium transition">
                Monthly
              </button>
              <button onclick="updateTrendChart('6months')" id="btn-6months" class="px-3 py-1.5 text-sm rounded-lg border-2 border-gray-700 text-gray-300 hover:border-purple-500 hover:bg-gray-700 transition">
                6 Months
              </button>
              <button onclick="updateTrendChart('12months')" id="btn-12months" class="px-3 py-1.5 text-sm rounded-lg border-2 border-gray-700 text-gray-300 hover:border-purple-500 hover:bg-gray-700 transition">
                12 Months
              </button>
              <div class="flex items-center gap-2 ml-2 pl-2 border-l-2 border-gray-700">
                <input type="date" id="startDate" class="px-2 py-1 text-sm bg-gray-700 border border-gray-600 text-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <span class="text-sm text-gray-400">to</span>
                <input type="date" id="endDate" class="px-2 py-1 text-sm bg-gray-700 border border-gray-600 text-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <button onclick="applyCustomRange()" class="px-3 py-1.5 text-sm bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white rounded-lg transition font-medium">
                  Apply
                </button>
              </div>
            </div>
          </div>
          
          <div style="height: 350px;">
            <canvas id="trendChart"></canvas>
          </div>
        </div>

        </div>



      </div>
    </main>

  </div>

  <script>
    let trendChartInstance = null;
    let currentFilter = 'monthly';

    // Monthly Comparison Chart
    const monthlyData = <?= json_encode($monthlyData) ?>;
    const monthlyComparisonCtx = document.getElementById('monthlyComparisonChart').getContext('2d');
    new Chart(monthlyComparisonCtx, {
      type: 'bar',
      data: {
        labels: ['Last Month', 'This Month'],
        datasets: [{
          label: 'Number of Cases',
          data: [monthlyData.last_month, monthlyData.this_month],
          backgroundColor: [
            'rgba(168, 85, 247, 0.8)',
            'rgba(59, 130, 246, 0.8)'
          ],
          borderColor: [
            'rgba(168, 85, 247, 1)',
            'rgba(59, 130, 246, 1)'
          ],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
              color: '#9ca3af'
            },
            grid: {
              color: 'rgba(75, 85, 99, 0.3)'
            }
          },
          x: {
            ticks: {
              color: '#9ca3af'
            },
            grid: {
              color: 'rgba(75, 85, 99, 0.3)'
            }
          }
        }
      }
    });

    // Cases by Type Chart
    const casesByType = <?= json_encode($casesByType) ?>;
    const types = casesByType.map(item => item.incident_type);
    const typeCounts = casesByType.map(item => item.count);
    const casesByTypeCtx = document.getElementById('casesByTypeChart').getContext('2d');
    new Chart(casesByTypeCtx, {
      type: 'doughnut',
      data: {
        labels: types,
        datasets: [{
          data: typeCounts,
          backgroundColor: [
            'rgba(239, 68, 68, 0.7)',
            'rgba(249, 115, 22, 0.7)',
            'rgba(234, 179, 8, 0.7)',
            'rgba(34, 197, 94, 0.7)',
            'rgba(59, 130, 246, 0.7)',
            'rgba(168, 85, 247, 0.7)',
            'rgba(236, 72, 153, 0.7)',
            'rgba(20, 184, 166, 0.7)',
            'rgba(251, 146, 60, 0.7)',
            'rgba(147, 51, 234, 0.7)'
          ],
          borderColor: '#374151',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
            labels: {
              padding: 10,
              font: {
                size: 11
              },
              color: '#9ca3af'
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

    // Initialize Trend Chart with default data (monthly)
    const trendData6 = <?= json_encode($trendData6Months) ?>;
    initializeTrendChart(trendData6.map(item => ({
      label: new Date(item.month + '-01').toLocaleDateString('en-US', { month: 'short', year: 'numeric' }),
      count: parseInt(item.count)
    })));

    // Function to initialize/update trend chart
    function initializeTrendChart(data) {
      const ctx = document.getElementById('trendChart').getContext('2d');
      
      if (trendChartInstance) {
        trendChartInstance.destroy();
      }
      
      trendChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.map(item => item.label),
          datasets: [{
            label: 'Number of Cases',
            data: data.map(item => item.count),
            borderColor: 'rgba(168, 85, 247, 1)',
            backgroundColor: 'rgba(168, 85, 247, 0.15)',
            tension: 0.4,
            fill: true,
            borderWidth: 3,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointBackgroundColor: 'rgba(168, 85, 247, 1)',
            pointBorderColor: '#374151',
            pointBorderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.9)',
              padding: 12,
              titleFont: {
                size: 14,
                weight: 'bold'
              },
              bodyFont: {
                size: 13
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1,
                font: {
                  size: 12
                },
                color: '#9ca3af'
              },
              grid: {
                color: 'rgba(75, 85, 99, 0.3)'
              }
            },
            x: {
              ticks: {
                font: {
                  size: 11
                },
                color: '#9ca3af'
              },
              grid: {
                display: false
              }
            }
          }
        }
      });
    }

    // Update button active state
    function updateButtonState(activeButton) {
      const buttons = ['daily', 'weekly', 'monthly', '6months', '12months'];
      buttons.forEach(btn => {
        const element = document.getElementById(`btn-${btn}`);
        if (btn === activeButton) {
          element.classList.remove('border-gray-700', 'text-gray-300', 'hover:border-purple-500', 'hover:bg-gray-700');
          element.classList.add('border-purple-500', 'bg-purple-600/20', 'text-purple-300', 'font-medium');
        } else {
          element.classList.add('border-gray-700', 'text-gray-300', 'hover:border-purple-500', 'hover:bg-gray-700');
          element.classList.remove('border-purple-500', 'bg-purple-600/20', 'text-purple-300', 'font-medium');
        }
      });
    }

    // Update trend chart based on filter
    function updateTrendChart(filterType) {
      currentFilter = filterType;
      updateButtonState(filterType);
      
      fetch(`crud/getChartData.php?filterType=${filterType}`)
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            initializeTrendChart(result.data);
          } else {
            console.error('Error fetching chart data:', result.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
        });
    }

    // Apply custom date range
    function applyCustomRange() {
      const startDate = document.getElementById('startDate').value;
      const endDate = document.getElementById('endDate').value;
      
      if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
      }
      
      if (new Date(startDate) > new Date(endDate)) {
        alert('Start date must be before end date');
        return;
      }
      
      updateButtonState('custom');
      
      fetch(`crud/getChartData.php?filterType=custom&startDate=${startDate}&endDate=${endDate}`)
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            initializeTrendChart(result.data);
          } else {
            alert(result.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while fetching data');
        });
    }
  </script>

</body>
</html>


