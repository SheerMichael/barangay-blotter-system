<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

require_once "../classes/blotter.php";
require_once "../auth/session.php";
// ** We don't need resident.php here anymore **
$blotter = new Blotter();

// Get filter values
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Use the new getBlotters() method
$blotters = $blotter->getBlotters($searchTerm, $statusFilter);

// Define statuses from your DB diagram
$statuses = ['Pending', 'Scheduled', 'Resolved', 'Endorsed to Police'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Blotter Dashboard</title>
  <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-900 min-h-screen">
  <div class="flex">
    
    <!-- Sidebar (Unchanged) -->
    <aside class="w-64 bg-gray-950 text-white min-h-screen p-6">
      <div class="mb-8">
        <h1 class="text-2xl font-bold">Blotter System</h1>
        <p class="text-sm text-gray-400 mt-1">Welcome!</p>
      </div>
      <nav class="space-y-2">
        <div class="mb-4">
          <a href="../index.php" class="block py-2 px-4 rounded hover:bg-gradient-to-r from-purple-600 to-blue-600 transition">Dashboard</a>
        </div>
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Residents</h3>
          <a href="viewResident.php" class="block py-2 px-4 rounded hover:bg-gradient-to-r from-purple-600 to-blue-600 transition">View Residents</a>
          <a href="addResident.php" class="block py-2 px-4 rounded hover:bg-gradient-to-r from-purple-600 to-blue-600 transition">Add Resident</a>
        </div>
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Blotters</h3>
          <a href="viewBlotter.php" class="block py-2 px-4 rounded bg-gradient-to-r from-purple-600 to-blue-600">View Blotters</a>
          <a href="addBlotter.php" class="block py-2 px-4 rounded hover:bg-gradient-to-r from-purple-600 to-blue-600 transition">Add Blotter</a>
        </div>
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Reports</h3>
          <a href="../reports/index.php" class="block py-2 px-4 rounded hover:bg-gradient-to-r from-purple-600 to-blue-600 transition">Generate Reports</a>
        </div>
        <div class="pt-4 border-t border-gray-800">
          <a href="../auth/logout.php" class="block py-2 px-4 rounded bg-red-600/90 hover:bg-red-600 transition text-center font-medium">Logout</a>
        </div>
      </nav>
    </aside>

    <main class="flex-1 p-8 bg-gray-900">
      <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-white">Blotters Dashboard</h1>
          <div class="flex gap-2">
            <?php
              $printUrl = 'printBlotterList.php';
              $params = [];
              if (!empty($searchTerm)) $params[] = 'search=' . urlencode($searchTerm);
              if (!empty($statusFilter)) $params[] = 'status=' . urlencode($statusFilter);
              if (!empty($params)) $printUrl .= '?' . implode('&', $params);
            ?>
            <a 
              href="<?= $printUrl ?>" 
              target="_blank"
              class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2 rounded-lg transition no-underline shadow-lg"
              title="Print Blotters List"
            >
              🖨️ Print List
            </a>
            <a 
              href="addBlotter.php" 
              class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-5 py-2 rounded-lg transition no-underline shadow-lg"
            >
              Add New Blotter
            </a>
          </div>
        </div>

        <!-- Filter Form (UPDATED) -->
        <form method="GET" class="mb-6 flex justify-center items-center gap-2">
          <!-- ** CHANGE HERE: Added onchange event to auto-submit the form ** -->
          <select 
            name="status" 
            class="p-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none"
            onchange="this.form.submit()"
          >
            <option value="">All Statuses</option>
            <?php foreach ($statuses as $s): ?>
              <option value="<?= $s ?>" <?= ($statusFilter === $s) ? 'selected' : '' ?>>
                <?= $s ?>
              </option>
            <?php endforeach; ?>
          </select>
          
          <input 
            type="text" 
            name="search" 
            value="<?= htmlspecialchars($searchTerm) ?>" 
            placeholder="Search case no., complainant..." 
            class="w-1/2 p-2 bg-gray-800 border border-gray-700 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none"
          >
          
          <!-- ** CHANGE HERE: Updated button text ** -->
          <button 
            type="submit" 
            class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-4 py-2 rounded-lg shadow-lg"
          >
            Search
          </button>
        </form>
        
        <!-- Success Alert (Unchanged) -->
        <?php if (isset($_GET['success'])): ?>
          <?php
            $message = match ($_GET['success']) {
                '1' => 'Blotter added successfully!',
                '2' => 'Blotter updated successfully!',
                '3' => 'Blotter deleted successfully!',
                default => 'Action successful!'
            };
          ?>
          <div class="mb-4 p-3 rounded-lg bg-green-900/30 text-green-400 border border-green-700 text-center" id="success-alert">
            <?= $message ?>
          </div>
        <?php endif; ?>

        <!-- Blotter Table (Unchanged) -->
        <div class="overflow-x-auto shadow-lg rounded-lg bg-gray-800 border border-gray-700">
          <table class="min-w-full border-collapse">
            <thead class="bg-gray-900 text-gray-300 border-b border-gray-700">
              <tr>
                <th class="p-3 text-left">Case No.</th>
                <th class="p-3 text-left">Complainant(s)</th>
                <th class="p-3 text-left">Respondent(s)</th>
                <th class="p-3 text-left">Incident Type</th>
                <th class="p-3 text-left">Status</th>
                <th class="p-3 text-left">Date Filed</th>
                <th class="p-3 text-left">Actions</th> </tr>
            </thead>
            <tbody>
              <?php if (!empty($blotters)): ?>
                <?php foreach ($blotters as $b): ?>
                  <?php 
                    $status = strtolower($b['status']);
                    $color = match($status) {
                      'pending' => 'bg-yellow-200 text-yellow-800',
                      'resolved' => 'bg-green-200 text-green-800',
                      'endorsed to police' => 'bg-blue-200 text-blue-800',
                      'scheduled' => 'bg-indigo-200 text-indigo-800',
                      default => 'bg-gray-200 text-gray-800'
                    };
                    
                    // --- HOVER TOOLTIP ---
                    // Create a simple text snippet for the tooltip
                    $details_snippet = $b['details'] ? (strlen($b['details']) > 150 ? substr($b['details'], 0, 150) . '...' : $b['details']) : 'No details provided.';
                  ?>
                  
                  <!-- 
                    ** HOVER FEATURE ADDED ** The title attribute creates a native browser tooltip on hover.
                  -->
                  <tr class="border-b border-gray-700 hover:bg-gray-700 transition cursor-pointer text-gray-300" onclick="window.location='viewBlotterDetail.php?id=<?= $b['id'] ?>'" title="Click to view details">
                    
                    <td class="p-3 font-semibold align-top">
                      <?= htmlspecialchars($b['case_no'] ?? '-') ?>
                    </td>
                    <td class="p-3 align-top"><?= htmlspecialchars($b['complainant_name'] ?? 'N/A') ?></td>
                    <td class="p-3 align-top"><?= htmlspecialchars($b['respondent_name'] ?? 'N/A') ?></td>
                    <td class="p-3 align-top"><?= htmlspecialchars($b['incident_type']) ?></td>
                    <td class="p-3 align-top">
                      <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $color ?>">
                        <?= htmlspecialchars(ucfirst($b['status'])) ?>
                      </span>
                    </td>
                    <td class="p-3 align-top">
                      <?= htmlspecialchars(date('M d, Y', strtotime($b['created_at'] ?? 'now'))) ?>
                    </td>
                    <td class="p-3 align-top whitespace-nowrap" onclick="event.stopPropagation()">
                      <a href="editBlotter.php?id=<?= $b['id'] ?>" class="text-blue-400 hover:text-blue-300 hover:underline mr-2">Edit</a>
                      <a href="deleteBlotter.php?id=<?= $b['id'] ?>" 
                         class="text-red-400 hover:text-red-300 hover:underline"
                         onclick="return confirm('Are you sure you want to delete Case <?= htmlspecialchars($b['case_no']) ?>? This action cannot be undone.')">
                         Delete
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="7" class="text-center py-6 text-gray-400">No blotters found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <!-- Javascript -->
  <script>
    const alert = document.getElementById('success-alert');
    if (alert) {
      setTimeout(() => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
      }, 3000);
    }
  </script>
</body>
</html>

