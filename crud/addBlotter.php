<?php
// addBlotter.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// FIX: Use __DIR__ for robust paths
require_once __DIR__ . "/../classes/resident.php";
require_once __DIR__ . "/../classes/blotter.php";
require_once __DIR__ . "/../auth/session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

function s($v) {
    return trim(htmlspecialchars($v, ENT_QUOTES, 'UTF-8'));
}

// Define statuses
$statuses = ['Pending', 'Scheduled', 'Resolved', 'Endorsed to Police'];

$errors = [];
$old = [
    'incident_date' => '',
    'incident_time' => '',
    'incident_location' => '',
    'complainant_ids' => [],
    'respondent_ids' => [],
    'incident_type' => '',
    'status' => '', // ADDED: Status field
    'details' => '',
    'remarks' => ''
];

$residentModel = new Resident();
$residents = $residentModel->viewResidents();

// Format residents for Tom Select (value, text)
$residentOptions = array_map(function($r) {
    return [
        'value' => $r['id'],
        'text' => htmlspecialchars($r['last_name'] . ', ' . $r['first_name'])
    ];
}, $residents);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- Collect & Sanitize ---
    $old['incident_date'] = s($_POST['incident_date'] ?? '');
    $old['incident_time'] = s($_POST['incident_time'] ?? '');
    $old['incident_location'] = s($_POST['incident_location'] ?? '');
    $old['incident_type'] = s($_POST['incident_type'] ?? '');
    $old['status'] = s($_POST['status'] ?? ''); // ADDED: Collect status
    $old['details'] = s($_POST['details'] ?? '');
    $old['remarks'] = s($_POST['remarks'] ?? '');

    // Collect arrays of IDs
    $complainant_ids = $_POST['complainant_ids'] ?? [];
    $respondent_ids = $_POST['respondent_ids'] ?? [];

    // Sanitize and store for "old" data
    $old['complainant_ids'] = array_map('intval', $complainant_ids);
    $old['respondent_ids'] = array_map('intval', $respondent_ids);


    // --- Validate ---
    if (empty($old['incident_date'])) {
        $errors['incident_date'] = "Incident date is required.";
    } else {
        $today = date('Y-m-d');
        if ($old['incident_date'] > $today) {
            $errors['incident_date'] = "Incident date cannot be in the future.";
        }
    }

    if (empty($old['incident_location'])) {
        $errors['incident_location'] = "Incident location is required.";
    }

    // Validate Complainants
    // FIX: Use the sanitized $old[] array for validation
    if (empty($old['complainant_ids'])) {
        $errors['complainants'] = "At least one complainant is required.";
    } else {
        // FIX: Use the sanitized $old[] array for validation
        foreach ($old['complainant_ids'] as $id) {
            if (!$residentModel->isResidentExistById((int)$id)) {
                $errors['complainants'] = "An invalid complainant was selected.";
                break;
            }
        }
    }

    // Validate Respondents
    // FIX: Use the sanitized $old[] array for validation
    if (empty($old['respondent_ids'])) {
        $errors['respondents'] = "At least one respondent is required.";
    } else {
        // FIX: Use the sanitized $old[] array for validation
        foreach ($old['respondent_ids'] as $id) {
            if (!$residentModel->isResidentExistById((int)$id)) {
                $errors['respondents'] = "An invalid respondent was selected.";
                break;
            }
        }
    }

    // Prevent overlap
    if (empty($errors['complainants']) && empty($errors['respondents'])) {
        // FIX: Use the sanitized $old[] arrays for the overlap check
        $overlap = array_intersect($old['complainant_ids'], $old['respondent_ids']);
        if (!empty($overlap)) {
            $errors['respondents'] = "A person cannot be both a complainant and a respondent.";
        }
    }

    if (empty($old['incident_type'])) {
        $errors['incident_type'] = "Incident type is required.";
    }
    
    // ADDED: Validate Status
    if (empty($old['status'])) {
        $errors['status'] = "Status is required.";
    } elseif (!in_array($old['status'], $statuses)) {
        $errors['status'] = "Invalid status selected.";
    }

    if (empty($old['details']) || strlen($old['details']) < 10) {
        $errors['details'] = "Details must be at least 10 characters.";
    }

    // --- Process ---
    if (empty($errors)) {
        $blotter = new Blotter();
        
        $blotter->incident_date = $old['incident_date'];
        $blotter->incident_time = !empty($old['incident_time']) ? $old['incident_time'] : null;
        $blotter->incident_location = $old['incident_location'];
        $blotter->incident_type = $old['incident_type'];
        $blotter->details = $old['details'];
        $blotter->status = $old['status']; // UPDATED: Use selected status
        $blotter->remarks = $old['remarks'];
        
        // Pass the arrays of IDs to the class
        $blotter->complainant_ids = $old['complainant_ids'];
        $blotter->respondent_ids = $old['respondent_ids'];

        $ok = $blotter->addBlotter();

        if ($ok) {
            header("Location: viewBlotter.php?success=1");
            exit;
        } else {
            $errors['general'] = "Failed to save blotter. A database error occurred.";
        }
    }
}

$success = isset($_GET['success']) && $_GET['success'] == '1';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-g">
  <title>Add Blotter</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../assets/css/tailwind.css">
  <link rel="stylesheet" href="../assets/css/tom-select.min.css">
</head>
<body class="bg-gray-900 min-h-screen">
  <div class="flex">
    
    <aside class="w-64 bg-gray-950 text-white min-h-screen p-6 border-r border-gray-800">
      <div class="mb-8">
        <h1 class="text-2xl font-bold bg-gradient-to-r from-purple-400 to-blue-400 bg-clip-text text-transparent">Blotter System</h1>
        <p class="text-sm text-gray-400 mt-1">Welcome!</p>
      </div>
      <nav class="space-y-2">
        <div class="mb-4">
          <a href="../index.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">Dashboard</a>
        </div>
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Residents</h3>
          <a href="viewResident.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">View Residents</a>
          <a href="addResident.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">Add Resident</a>
        </div>
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Blotters</h3>
          <a href="viewBlotter.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">View Blotters</a>
          <a href="addBlotter.php" class="block py-2 px-4 rounded bg-gradient-to-r from-purple-600 to-blue-600">Add Blotter</a>
        </div>
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Reports</h3>
          <a href="../reports/index.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">Generate Reports</a>
        </div>
        <div class="pt-4 border-t border-gray-800">
          <a href="../auth/logout.php" class="block py-2 px-4 rounded bg-red-600/90 hover:bg-red-600 transition text-center font-medium">Logout</a>
        </div>
      </nav>
    </aside>

    <main class="flex-1 p-8 bg-gray-900">
      <div class="max-w-3xl mx-auto">
        <header class="mb-6">
          <h1 class="text-2xl font-semibold text-white">Add Blotter</h1>
          <p class="text-sm text-gray-400">Fill out the form to create a new blotter record.</p>
        </header>

        <?php if ($success): ?>
          <div class="mb-4 p-4 rounded bg-green-900/30 border border-green-700 text-green-400">
            Blotter added successfully.
          </div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
          <div class="mb-4 p-4 rounded bg-red-900/30 border border-red-700 text-red-400">
            <?= htmlspecialchars($errors['general']) ?>
          </div>
        <?php endif; ?>

        <form method="POST" novalidate class="bg-gray-800 shadow-lg rounded-lg p-6 border border-gray-700" id="blotterForm">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
              <label class="block text-sm font-medium text-gray-300">Incident Date <span class="text-red-500">*</span></label>
              <input type="date" name="incident_date" value="<?= htmlspecialchars($old['incident_date']) ?>" max="<?= date('Y-m-d') ?>" class="mt-1 block w-full rounded bg-gray-700 border-gray-600 text-white shadow-sm focus:ring-purple-500 focus:border-purple-500" required>
              <?php if (!empty($errors['incident_date'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['incident_date']) ?></p>
              <?php endif; ?>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300">Incident Time (optional)</label>
              <input type="time" name="incident_time" value="<?= htmlspecialchars($old['incident_time']) ?>" class="mt-1 block w-full rounded bg-gray-700 border-gray-600 text-white shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300">Incident Location <span class="text-red-500">*</span></label>
              <input type="text" name="incident_location" value="<?= htmlspecialchars($old['incident_location']) ?>" class="mt-1 block w-full rounded bg-gray-700 border-gray-600 text-white shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
              <?php if (!empty($errors['incident_location'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['incident_location']) ?></p>
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
                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['complainants']) ?></p>
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
                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['respondents']) ?></p>
              <?php endif; ?>
            </div>

            <!-- Add New Resident Toggle -->
            <div class="md:col-span-2">
              <button type="button" onclick="toggleAddResidentForm()" class="text-sm text-purple-400 hover:text-purple-300 font-medium">
                + Add New Resident
              </button>
            </div>

            <!-- Inline Add Resident Form (Hidden by Default) -->
            <div id="addResidentForm" class="md:col-span-2 hidden border-2 border-purple-600 rounded-lg p-5 bg-gray-700">
              <h4 class="text-lg font-semibold text-white mb-4 border-b border-gray-600 pb-2">Quick Add Resident</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-1">First Name <span class="text-red-500">*</span></label>
                  <input type="text" id="new_first_name" class="w-full px-3 py-2 text-sm bg-gray-800 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent placeholder-gray-400" placeholder="Enter first name">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-1">Last Name <span class="text-red-500">*</span></label>
                  <input type="text" id="new_last_name" class="w-full px-3 py-2 text-sm bg-gray-800 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent placeholder-gray-400" placeholder="Enter last name">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-1">Age <span class="text-red-500">*</span></label>
                  <input type="number" id="new_age" min="1" max="150" class="w-full px-3 py-2 text-sm bg-gray-800 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent placeholder-gray-400" placeholder="Enter age">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-1">Gender <span class="text-red-500">*</span></label>
                  <select id="new_gender" class="w-full px-3 py-2 text-sm bg-gray-800 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                  </select>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-300 mb-1">Address <span class="text-red-500">*</span></label>
                  <input type="text" id="new_address" class="w-full px-3 py-2 text-sm bg-gray-800 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent placeholder-gray-400" placeholder="Enter house address">
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-300 mb-1">Contact Number</label>
                  <input type="text" id="new_contact" class="w-full px-3 py-2 text-sm bg-gray-800 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent placeholder-gray-400" placeholder="Enter contact number (optional)">
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-300 mb-1">Email <span class="text-gray-500 text-xs">(Optional)</span></label>
                  <input type="email" id="new_email" class="w-full px-3 py-2 text-sm bg-gray-800 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent placeholder-gray-400" placeholder="Enter email (optional for notifications)">
                </div>
              </div>
              <div id="residentFormMessage" class="mt-3 text-sm font-medium"></div>
              <div class="mt-4 flex gap-3 border-t border-gray-600 pt-4">
                <button type="button" onclick="saveNewResident()" class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-2.5 rounded-lg font-medium shadow-lg transition">
                  Save Resident
                </button>
                <button type="button" onclick="toggleAddResidentForm()" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-2.5 rounded-lg font-medium shadow-lg transition">
                  Cancel
                </button>
              </div>
            </div>

            <div class="md:col-span-1">
              <label class="block text-sm font-medium text-gray-300">Incident Type <span class="text-red-500">*</span></label>
              <input type="text" name="incident_type" maxlength="100" value="<?= htmlspecialchars($old['incident_type']) ?>" class="mt-1 block w-full rounded bg-gray-700 border-gray-600 text-white shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required placeholder="e.g., Theft, Noise Complaint">
               <?php if (!empty($errors['incident_type'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['incident_type']) ?></p>
              <?php endif; ?>
            </div>
            
            <!-- ADDED: Status Dropdown -->
            <div class="md:col-span-1">
              <label class="block text-sm font-medium text-gray-300">Status <span class="text-red-500">*</span></label>
              <select name="status" class="mt-1 block w-full rounded bg-gray-700 border-gray-600 text-white shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required>
                <option value="">Select a status</option>
                <?php foreach ($statuses as $s): ?>
                  <option value="<?= htmlspecialchars($s) ?>" <?= ($old['status'] === $s) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (!empty($errors['status'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['status']) ?></p>
              <?php endif; ?>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300">Details <span class="text-red-500">*</span></label>
              <textarea name="details" rows="5" class="mt-1 block w-full rounded bg-gray-700 border-gray-600 text-white shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" required><?= htmlspecialchars($old['details']) ?></textarea>
              <?php if (!empty($errors['details'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['details']) ?></p>
              <?php endif; ?>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300">Remarks (optional)</label>
              <textarea name="remarks" rows="2" class="mt-1 block w-full rounded bg-gray-700 border-gray-600 text-white shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"><?= htmlspecialchars($old['remarks']) ?></textarea>
            </div>
          </div>

          <div class="mt-6 flex items-center justify-end">
            <div>
              
                            <button type="submit" id="submitBtn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded shadow-lg text-white bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 focus:outline-none transition">
                Submit Blotter
              </button>
            </div>
          </div>
        </form>
        
 
    </main>
  </div>

  <script src="../assets/js/tom-select.min.js"></script>

  <script>
    // 3. Initialize Tom Select
    // Pass the PHP residents array to JS
    const residentOptions = <?= json_encode($residentOptions) ?>;
    
    const tsConfig = {
      plugins: ['remove_button'],
      options: residentOptions,
      valueField: 'value',
      labelField: 'text',
      searchField: 'text',
      create: false,
      placeholder: 'Type to search for a resident...',
      // Sync with Tailwind form styles
      controlClass: 'tom-select-control mt-1 block w-full rounded border-gray-600 shadow-sm',
      itemClass: 'tom-select-item bg-indigo-100 text-indigo-700 rounded',
      buttonClass: 'tom-select-button text-indigo-700'
    };
    
    // Initialize both selects
    new TomSelect('#select-complainants', tsConfig);
    new TomSelect('#select-respondents', tsConfig);


    // --- Form Submit Logic ---
    document.getElementById('blotterForm').addEventListener('submit', function(e) {
      if (!this.checkValidity()) {
        return;
      }
      var submitBtn = document.getElementById('submitBtn');
      submitBtn.setAttribute('disabled', 'disabled');
      submitBtn.innerText = 'Saving...';
    });

    // Toggle inline add resident form
    function toggleAddResidentForm() {
      const form = document.getElementById('addResidentForm');
      form.classList.toggle('hidden');
      
      // Clear form if hiding
      if (form.classList.contains('hidden')) {
        document.getElementById('new_first_name').value = '';
        document.getElementById('new_last_name').value = '';
        document.getElementById('new_age').value = '';
        document.getElementById('new_gender').value = '';
        document.getElementById('new_address').value = '';
        document.getElementById('new_contact').value = '';
        document.getElementById('new_email').value = '';
        document.getElementById('residentFormMessage').innerHTML = '';
      }
    }

    // Save new resident via AJAX
    function saveNewResident() {
      const firstName = document.getElementById('new_first_name').value.trim();
      const lastName = document.getElementById('new_last_name').value.trim();
      const age = document.getElementById('new_age').value;
      const gender = document.getElementById('new_gender').value;
      const address = document.getElementById('new_address').value.trim();
      const contact = document.getElementById('new_contact').value.trim();
      const email = document.getElementById('new_email').value.trim();
      const messageDiv = document.getElementById('residentFormMessage');

      // Validation
      if (!firstName || !lastName || !age || !gender || !address) {
        messageDiv.innerHTML = '<span class="text-red-600">Please fill in all required fields.</span>';
        return;
      }

      // Send AJAX request
      fetch('addResidentAjax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `first_name=${encodeURIComponent(firstName)}&last_name=${encodeURIComponent(lastName)}&age=${age}&gender=${encodeURIComponent(gender)}&house_address=${encodeURIComponent(address)}&contact_number=${encodeURIComponent(contact)}&email=${encodeURIComponent(email)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          messageDiv.innerHTML = '<span class="text-green-600">Resident added successfully!</span>';
          
          // Add new resident to both Tom Select dropdowns
          const newOption = {
            value: data.resident_id,
            text: `${firstName} ${lastName} (ID: ${data.resident_id})`
          };
          
          // Get Tom Select instances
          const complainantSelect = document.getElementById('select-complainants').tomselect;
          const respondentSelect = document.getElementById('select-respondents').tomselect;
          
          // Add option to both
          complainantSelect.addOption(newOption);
          respondentSelect.addOption(newOption);
          
          // Clear form after short delay
          setTimeout(() => {
            toggleAddResidentForm();
          }, 1500);
        } else {
          messageDiv.innerHTML = `<span class="text-red-600">${data.message || 'Failed to add resident.'}</span>`;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        messageDiv.innerHTML = '<span class="text-red-600">An error occurred. Please try again.</span>';
      });
    }
  </script>
</body>
</html>

