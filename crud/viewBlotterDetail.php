<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// This assumes your session.php is in the auth folder
require_once "../auth/session.php"; 
require_once "../classes/blotter.php";
require_once "../classes/resident.php";
require_once "../auth/session.php";

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

// Helper for status color
$status = strtolower($blotter['status']);
$statusColor = match($status) {
    'pending' => 'bg-yellow-200 text-yellow-800',
    'resolved' => 'bg-green-200 text-green-800',
    'endorsed to police' => 'bg-blue-200 text-blue-800',
    'scheduled' => 'bg-indigo-200 text-indigo-800',
    default => 'bg-gray-200 text-white'
};

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Blotter Case: <?= htmlspecialchars($blotter['case_no']) ?></title>
  <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-900 min-h-screen">
  <div class="flex">
    
    <!-- Sidebar -->
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

    <!-- Main Content -->
    <main class="flex-1 p-8">
      <div class="container mx-auto max-w-4xl">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-3xl text-white font-bold">Blotter Case Report</h1>
            <p class="text-xl font-semibold text-gray-600"><?= htmlspecialchars($blotter['case_no']) ?></p>
          </div>
          <span class="px-4 py-2 rounded-full text-lg font-semibold <?= $statusColor ?>">
            <?= htmlspecialchars(ucfirst($blotter['status'])) ?>
          </span>
        </div>

        <!-- Update Status Section -->
        <div class="mb-6 bg-gray-800 border border-gray-700 shadow rounded-lg p-4">
          <div class="flex items-center gap-4">
            <label for="statusUpdate" class="text-sm font-semibold text-gray-300">Update Status:</label>
            <select 
              id="statusUpdate" 
              class="flex-1 px-4 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none"
            >
              <option value="">-- Select New Status --</option>
              <option value="Pending">Pending</option>
              <option value="Scheduled">Scheduled</option>
              <option value="Resolved">Resolved</option>
              <option value="Endorsed to Police">Endorsed to Police</option>
            </select>
            <button 
              onclick="updateStatus()" 
              class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-6 py-2 rounded-lg transition font-medium shadow-lg"
            >
              Update Status
            </button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mb-6 flex gap-3">
          <a href="viewBlotter.php" class="bg-gray-600 hover:bg-gray-500 text-white px-5 py-2 rounded-lg transition no-underline">
            &larr; Back to Dashboard
          </a>
          <a 
            href="printBlotterDetail.php?id=<?= $blotter['id'] ?>" 
            target="_blank"
            class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2 rounded-lg transition no-underline"
            title="Print Case Report"
          >
            🖨️ Print
          </a>
          <a href="editBlotter.php?id=<?= $blotter['id'] ?>" class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 shadow-lg text-white px-5 py-2 rounded-lg transition no-underline">
            Edit This Case
          </a>
          <a href="deleteBlotter.php?id=<?= $blotter['id'] ?>" 
             class="bg-red-600/90 hover:bg-red-600 text-white px-5 py-2 rounded-lg transition no-underline"
             onclick="return confirm('Are you sure you want to delete Case <?= htmlspecialchars($blotter['case_no']) ?>? This action cannot be undone.')">
             Delete This Case
          </a>
        </div>

        <!-- Case Details Card -->
        <div class="bg-gray-800 border border-gray-700 shadow-lg rounded-lg p-6 mb-6">
          <h2 class="text-2xl font-semibold mb-4 border-b border-gray-600 pb-2 text-white">Case Details</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <strong class="text-gray-400 text-sm uppercase tracking-wide">Incident Type:</strong>
              <p class="text-lg text-white mt-1"><?= htmlspecialchars($blotter['incident_type']) ?></p>
            </div>
            <div>
              <strong class="text-gray-400 text-sm uppercase tracking-wide">Incident Location:</strong>
              <p class="text-lg text-white mt-1"><?= htmlspecialchars($blotter['incident_location']) ?></p>
            </div>
            <div>
              <strong class="text-gray-400 text-sm uppercase tracking-wide">Incident Date:</strong>
              <p class="text-lg text-white mt-1"><?= htmlspecialchars(date('F d, Y', strtotime($blotter['incident_date']))) ?></p>
            </div>
            <div>
              <strong class="text-gray-400 text-sm uppercase tracking-wide">Incident Time:</strong>
              <p class="text-lg text-white mt-1"><?= $blotter['incident_time'] ? htmlspecialchars(date('h:i A', strtotime($blotter['incident_time']))) : 'N/A' ?></p>
            </div>
          </div>
          <div class="mt-6">
            <strong class="text-gray-400 text-sm uppercase tracking-wide">Details of Incident:</strong>
            <p class="text-base whitespace-pre-wrap bg-gray-700 text-gray-200 p-4 rounded-md mt-2 leading-relaxed">
              <?= htmlspecialchars($blotter['details']) ?>
            </p>
          </div>
          <div class="mt-6">
            <strong class="text-gray-400 text-sm uppercase tracking-wide">Remarks:</strong>
            <p class="text-base whitespace-pre-wrap bg-gray-700 text-gray-200 p-4 rounded-md mt-2 leading-relaxed">
              <?= htmlspecialchars($blotter['remarks'] ?? 'No remarks.') ?>
            </p>
          </div>
        </div>

        <!-- Involved Parties -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Complainants Card -->
          <div class="bg-gray-800 border border-gray-700 shadow-lg rounded-lg p-6">
            <h2 class="text-2xl font-semibold mb-4 border-b border-gray-600 pb-2 text-white">Complainant(s)</h2>
            <div class="space-y-3">
              <?php if (empty($complainants)): ?>
                <p class="text-gray-400">No complainants listed.</p>
              <?php else: ?>
                <?php foreach ($complainants as $c): ?>
                  <div class="p-4 bg-gray-700 rounded-lg border border-gray-600 space-y-2">
                    <p class="text-lg font-semibold text-purple-400"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></p>
                    <p class="text-sm text-gray-300">
                      <strong class="font-medium text-gray-400">Address:</strong>
                      <?= htmlspecialchars($c['house_address']) ?>
                    </p>
                    <p class="text-sm text-gray-300">
                      <strong class="font-medium text-gray-400">Contact:</strong>
                      <?= htmlspecialchars($c['contact_number']) ?>
                    </p>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <!-- Respondents Card -->
          <div class="bg-gray-800 border border-gray-700 shadow-lg rounded-lg p-6">
            <h2 class="text-2xl font-semibold mb-4 border-b border-gray-600 pb-2 text-white">Respondent(s)</h2>
            <div class="space-y-3">
              <?php if (empty($respondents)): ?>
                <p class="text-gray-400">No respondents listed.</p>
              <?php else: ?>
                <?php foreach ($respondents as $r): ?>
                  <div class="p-4 bg-gray-700 rounded-lg border border-gray-600 space-y-2">
                    <p class="text-lg font-semibold text-red-400"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></p>
                     <p class="text-sm text-gray-300">
                      <strong class="font-medium text-gray-400">Address:</strong>
                      <?= htmlspecialchars($r['house_address']) ?>
                    </p>
                    <p class="text-sm text-gray-300">
                      <strong class="font-medium text-gray-400">Contact:</strong>
                      <?= htmlspecialchars($r['contact_number']) ?>
                    </p>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>

  <script>
    function updateStatus() {
      const selectElement = document.getElementById('statusUpdate');
      const newStatus = selectElement.value;
      const blotterId = <?= $blotter['id'] ?>;
      const caseNo = '<?= htmlspecialchars($blotter['case_no']) ?>';

      if (!newStatus) {
        alert('Please select a status to update.');
        return;
      }

      if (confirm(`Are you sure you want to mark ${caseNo} as "${newStatus}"?`)) {
        // Send AJAX request to update status
        fetch('updateBlotterStatus.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `id=${blotterId}&status=${encodeURIComponent(newStatus)}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Status updated successfully!');
            window.location.reload();
          } else {
            alert('Failed to update status: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while updating the status.');
        });
      }
    }
  </script>
</body>
</html>

