<?php
// editBlotter.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../classes/resident.php";
require_once "../classes/blotter.php";
require_once "../auth/session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

function s($v) {
    return trim(htmlspecialchars($v, ENT_QUOTES, 'UTF-8'));
}

// Get the ID from the URL
$blotter_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($blotter_id === 0) {
    header("Location: viewBlotter.php");
    exit;
}

$blotterModel = new Blotter();
$residentModel = new Resident();

// Fetch the existing blotter data
$existing_blotter = $blotterModel->viewBlotterById($blotter_id);
if (!$existing_blotter) {
    header("Location: viewBlotter.php?error=notfound");
    exit;
}

// Define statuses from your DB diagram
$statuses = ['Pending', 'Scheduled', 'Resolved', 'Endorsed to Police'];

// Initialize arrays
$errors = [];
// Pre-fill 'old' with existing data
$old = [
    'incident_date' => $existing_blotter['incident_date'],
    'incident_time' => $existing_blotter['incident_time'],
    'incident_location' => $existing_blotter['incident_location'],
    'complainant_ids' => $existing_blotter['complainant_ids'], // This is now an array
    'respondent_ids' => $existing_blotter['respondent_ids'],  // This is now an array
    'incident_type' => $existing_blotter['incident_type'],
    'details' => $existing_blotter['details'],
    'remarks' => $existing_blotter['remarks'],
    'status' => $existing_blotter['status']
];

// Load residents for Tom Select
$residents = $residentModel->viewResidents();
$residentOptions = array_map(function($r) {
    return [
        'value' => $r['id'],
        'text' => htmlspecialchars($r['last_name'] . ', ' . $r['first_name'])
    ];
}, $residents);


// Handle POST for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- Collect & Sanitize ---
    $old['incident_date'] = s($_POST['incident_date'] ?? '');
    $old['incident_time'] = s($_POST['incident_time'] ?? '');
    $old['incident_location'] = s($_POST['incident_location'] ?? '');
    $old['incident_type'] = s($_POST['incident_type'] ?? '');
    $old['details'] = s($_POST['details'] ?? '');
    $old['remarks'] = s($_POST['remarks'] ?? '');
    $old['status'] = s($_POST['status'] ?? ''); // Get status

    $complainant_ids = $_POST['complainant_ids'] ?? [];
    $respondent_ids = $_POST['respondent_ids'] ?? [];
    $old['complainant_ids'] = array_map('intval', $complainant_ids);
    $old['respondent_ids'] = array_map('intval', $respondent_ids);


    // --- Validate ---
    if (empty($old['incident_date'])) $errors['incident_date'] = "Incident date is required.";
    if (empty($old['incident_location'])) $errors['incident_location'] = "Incident location is required.";
    if (empty($old['status']) || !in_array($old['status'], $statuses)) $errors['status'] = "A valid status is required.";
    if (empty($complainant_ids)) $errors['complainants'] = "At least one complainant is required.";
    if (empty($respondent_ids)) $errors['respondents'] = "At least one respondent is required.";
    if (!empty(array_intersect($complainant_ids, $respondent_ids))) $errors['respondents'] = "A person cannot be both.";
    if (empty($old['incident_type'])) $errors['incident_type'] = "Incident type is required.";
    if (empty($old['details']) || strlen($old['details']) < 10) $errors['details'] = "Details must be at least 10 characters.";

    // --- Process ---
    if (empty($errors)) {
        // We set the properties on the *same model* we used to fetch
        $blotterModel->id = $blotter_id; // CRITICAL: Set the ID for the update
        $blotterModel->incident_date = $old['incident_date'];
        $blotterModel->incident_time = !empty($old['incident_time']) ? $old['incident_time'] : null;
        $blotterModel->incident_location = $old['incident_location'];
        $blotterModel->incident_type = $old['incident_type'];
        $blotterModel->details = $old['details'];
        $blotterModel->status = $old['status'];
        $blotterModel->remarks = $old['remarks'];
        $blotterModel->complainant_ids = $old['complainant_ids'];
        $blotterModel->respondent_ids = $old['respondent_ids'];

        // Call the fixed updateBlotter() method
        $ok = $blotterModel->updateBlotter(); 

        if ($ok) {
            header("Location: viewBlotter.php?success=2"); // success=2 means updated
            exit;
        } else {
            $errors['general'] = "Failed to update blotter. A database error occurred.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Blotter</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../assets/css/tailwind.css">
  <link rel="stylesheet" href="../assets/css/tom-select.min.css">
</head>
<body class="bg-gray-900 min-h-screen">
  <div class="flex">
    
    <aside class="w-64 bg-gray-950 text-white min-h-screen p-6">
      <div class="mb-8">
        <h1 class="text-2xl font-bold">Blotter System</h1>
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

    <main class="flex-1 p-8">
      <div class="max-w-3xl mx-auto">
        <header class="mb-6">
          <h1 class="text-2xl font-semibold text-white">Edit Blotter (Case: <?= htmlspecialchars($existing_blotter['case_no']) ?>)</h1>
          <p class="text-sm text-gray-500">Update the details for this blotter record.</p>
        </header>

        <?php if (!empty($errors['general'])): ?>
          <div class="mb-4 p-4 rounded bg-red-50 border border-red-200 text-red-800">
            <?= htmlspecialchars($errors['general']) ?>
          </div>
        <?php endif; ?>

        <form method="POST" novalidate class="bg-gray-800 shadow-lg rounded-lg p-6 border border-gray-700" id="blotterForm">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
              <label class="block text-sm font-medium text-gray-300">Incident Date <span class="text-red-500">*</span></label>
              <input type="date" name="incident_date" value="<?= htmlspecialchars($old['incident_date']) ?>" class="mt-1 block w-full rounded border-gray-600 bg-gray-700 text-white shadow-sm" required>
              <?php if (!empty($errors['incident_date'])): ?>
                <p class="mt-1 text-sm text-red-400"><?= htmlspecialchars($errors['incident_date']) ?></p>
              <?php endif; ?>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300">Incident Time (optional)</label>
              <input type="time" name="incident_time" value="<?= htmlspecialchars($old['incident_time']) ?>" class="mt-1 block w-full rounded border-gray-600 bg-gray-700 text-white shadow-sm">
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300">Incident Location <span class="text-red-500">*</span></label>
              <input type="text" name="incident_location" value="<?= htmlspecialchars($old['incident_location']) ?>" class="mt-1 block w-full rounded border-gray-600 bg-gray-700 text-white shadow-sm" required>
              <?php if (!empty($errors['incident_location'])): ?>
                <p class="mt-1 text-sm text-red-400"><?= htmlspecialchars($errors['incident_location']) ?></p>
              <?php endif; ?>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300">Complainant(s) <span class="text-red-500">*</span></label>
              <select id="select-complainants" name="complainant_ids[]" multiple required>
                <?php foreach ($residentOptions as $option): ?>
                  <option value="<?= $option['value'] ?>" <?= in_array($option['value'], $old['complainant_ids']) ? 'selected' : '' ?>>
                    <?= $option['text'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (!empty($errors['complainants'])): ?>
                <p class="mt-1 text-sm text-red-400"><?= htmlspecialchars($errors['complainants']) ?></p>
              <?php endif; ?>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300">Respondent(s) <span class="text-red-500">*</span></label>
              <select id="select-respondents" name="respondent_ids[]" multiple required>
                 <?php foreach ($residentOptions as $option): ?>
                  <option value="<?= $option['value'] ?>" <?= in_array($option['value'], $old['respondent_ids']) ? 'selected' : '' ?>>
                    <?= $option['text'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (!empty($errors['respondents'])): ?>
                <p class="mt-1 text-sm text-red-400"><?= htmlspecialchars($errors['respondents']) ?></p>
              <?php endif; ?>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300">Incident Type <span class="text-red-500">*</span></label>
              <input type="text" name="incident_type" maxlength="100" value="<?= htmlspecialchars($old['incident_type']) ?>" class="mt-1 block w-full rounded border-gray-600 bg-gray-700 text-white shadow-sm" required>
               <?php if (!empty($errors['incident_type'])): ?>
                <p class="mt-1 text-sm text-red-400"><?= htmlspecialchars($errors['incident_type']) ?></p>
              <?php endif; ?>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300">Status <span class="text-red-500">*</span></label>
              <select name="status" class="mt-1 block w-full rounded border-gray-600 bg-gray-700 text-white shadow-sm" required>
                <?php foreach ($statuses as $s): ?>
                  <option value="<?= $s ?>" <?= ($old['status'] === $s) ? 'selected' : '' ?>>
                    <?= $s ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (!empty($errors['status'])): ?>
                <p class="mt-1 text-sm text-red-400"><?= htmlspecialchars($errors['status']) ?></p>
              <?php endif; ?>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300">Details <span class="text-red-500">*</span></label>
              <textarea name="details" rows="5" class="mt-1 block w-full rounded border-gray-600 bg-gray-700 text-white shadow-sm" required><?= htmlspecialchars($old['details']) ?></textarea>
              <?php if (!empty($errors['details'])): ?>
                <p class="mt-1 text-sm text-red-400"><?= htmlspecialchars($errors['details']) ?></p>
              <?php endif; ?>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300">Remarks (optional)</label>
              <textarea name="remarks" rows="2" class="mt-1 block w-full rounded border-gray-600 bg-gray-700 text-white shadow-sm"><?= htmlspecialchars($old['remarks']) ?></textarea>
            </div>
          </div>

          <div class="mt-6 flex items-center justify-between">
            <a href="viewBlotter.php" class="text-indigo-600 hover:underline text-sm">← Cancel</a>
            <div>
              <button type="submit" id="submitBtn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded shadow-lg text-white bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 focus:outline-none transition">
                Update Blotter
              </button>
            </div>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script src="../assets/js/tom-select.min.js"></script>
  <script>
    const residentOptions = <?= json_encode($residentOptions) ?>;
    const tsConfig = {
      plugins: ['remove_button'],
      options: residentOptions,
      valueField: 'value',
      labelField: 'text',
      searchField: 'text',
      create: false,
      placeholder: 'Type to search for a resident...',
      controlClass: 'tom-select-control mt-1 block w-full rounded border-gray-600 bg-gray-700 text-white shadow-sm',
      itemClass: 'tom-select-item bg-indigo-100 text-indigo-700 rounded',
      buttonClass: 'tom-select-button text-indigo-700'
    };
    new TomSelect('#select-complainants', tsConfig);
    new TomSelect('#select-respondents', tsConfig);

    document.getElementById('blotterForm').addEventListener('submit', function(e) {
      if (!this.checkValidity()) {
        return;
      }
      var submitBtn = document.getElementById('submitBtn');
      submitBtn.setAttribute('disabled', 'disabled');
      submitBtn.innerText = 'Updating...';
    });
  </script>
</body>
</html>