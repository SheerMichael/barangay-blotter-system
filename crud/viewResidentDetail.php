<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../classes/resident.php";
require_once "../auth/session.php";

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// 2. Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: viewResident.php");
    exit();
}

$id = (int)$_GET['id'];

// 3. Get resident data and case history
$residentObj = new Resident();
$data = $residentObj->getResidentWithCaseHistory($id);

if (!$data) {
    // No resident found, redirect
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
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <title>Resident Profile</title>
</head>
<body class="bg-gray-900 min-h-screen">
  <div class="flex">
    
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
          <a href="viewResident.php" class="block py-2 px-4 rounded bg-gradient-to-r from-purple-600 to-blue-600">View Residents</a>
          <a href="addResident.php" class="block py-2 px-4 rounded hover:bg-gradient-to-r from-purple-600 to-blue-600 transition">Add Resident</a>
        </div>
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Blotters</h3>
          <a href="viewBlotter.php" class="block py-2 px-4 rounded hover:bg-gradient-to-r from-purple-600 to-blue-600 transition">View Blotters</a>
          <a href="addBlotter.php" class="block py-2 px-4 rounded hover:bg-gradient-to-r from-purple-600 to-blue-600 transition">Add Blotter</a>
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

    <main class="flex-1 p-8">
      <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl text-white font-bold">Resident Profile</h1>
          <div class="flex gap-2">
            <a 
              href="printResidentDetail.php?id=<?= $resident['id'] ?>" 
              target="_blank"
              class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2 rounded-lg transition no-underline"
              title="Print Resident Profile"
            >
              🖨️ Print
            </a>
            <a 
              href="viewResident.php" 
              class="bg-gray-600 hover:bg-gray-500 text-white px-5 py-2 rounded-lg transition no-underline"
            >
              ← Back to List
            </a>
          </div>
        </div>

        <div class="bg-gray-800 border border-gray-700 p-6 rounded-lg shadow-lg mb-8">
          <div class="flex justify-between items-start">
            <div>
              <h2 class="text-2xl font-bold text-white">
                <?= htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']) ?>
              </h2>
              <p class="text-gray-300 mt-1"><?= htmlspecialchars($resident['house_address']) ?></p>
            </div>
            <a href="editResident.php?id=<?= $resident['id'] ?>" class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 shadow-lg text-white px-4 py-2 rounded-lg transition">
              Edit Resident
            </a>
          </div>
          <hr class="my-4 border-gray-600">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <span class="text-sm font-medium text-gray-400 uppercase tracking-wide">Age</span>
              <p class="text-lg font-semibold text-white mt-1"><?= htmlspecialchars($resident['age']) ?></p>
            </div>
            <div>
              <span class="text-sm font-medium text-gray-400 uppercase tracking-wide">Gender</span>
              <p class="text-lg font-semibold text-white mt-1"><?= htmlspecialchars($resident['gender']) ?></p>
            </div>
            <div>
              <span class="text-sm font-medium text-gray-400 uppercase tracking-wide">Contact Number</span>
              <p class="text-lg font-semibold text-white mt-1"><?= htmlspecialchars($resident['contact_number']) ?></p>
            </div>
            <div>
              <span class="text-sm font-medium text-gray-400 uppercase tracking-wide">Email</span>
              <p class="text-lg font-semibold text-white mt-1"><?= !empty($resident['email']) ? htmlspecialchars($resident['email']) : '<span class="text-gray-600">Not provided</span>' ?></p>
            </div>
          </div>
        </div>

        <h2 class="text-2xl font-bold mb-4 text-white">Case History</h2>
        <div class="overflow-x-auto shadow-lg rounded-lg bg-gray-800 border border-gray-700">
          <table class="min-w-full border-collapse">
            <thead class="bg-gray-700 text-gray-300">
              <tr>
                <th class="p-3 text-left text-sm font-semibold uppercase tracking-wide">Case No.</th>
                <th class="p-3 text-left text-sm font-semibold uppercase tracking-wide">Involvement</th>
                <th class="p-3 text-left text-sm font-semibold uppercase tracking-wide">Incident Type</th>
                <th class="p-3 text-left text-sm font-semibold uppercase tracking-wide">Status</th>
                <th class="p-3 text-left text-sm font-semibold uppercase tracking-wide">Date Filed</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($caseHistory)): ?>
                <?php foreach ($caseHistory as $case): ?>
                  <?php
                    // Color-code the involvement role
                    $roleClass = $case['involvement_role'] === 'Complainant' ? 'bg-green-600 text-white' : 'bg-red-600 text-white';
                  ?>
                  <tr class="border-b border-gray-700 hover:bg-gray-700 transition text-gray-300">
                    <td class="p-3 font-semibold">
                      <a href="viewBlotterDetail.php?id=<?= $case['id'] ?>" class="text-purple-400 hover:text-purple-300 hover:underline">
                        <?= htmlspecialchars($case['case_no']) ?>
                      </a>
                    </td>
                    <td class="p-3">
                      <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $roleClass ?>">
                        <?= htmlspecialchars($case['involvement_role']) ?>
                      </span>
                    </td>
                    <td class="p-3 text-white"><?= htmlspecialchars($case['incident_type']) ?></td>
                    <td class="p-3 text-white"><?= htmlspecialchars($case['status']) ?></td>
                    <td class="p-3 text-gray-400"><?= htmlspecialchars(date('M d, Y', strtotime($case['created_at']))) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center py-6 text-gray-400">
                    This resident has no case history.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
    </main>
  </div>
</body>
</html>