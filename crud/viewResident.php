<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../classes/resident.php";
require_once "../auth/session.php"; // Correct path from crud/

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$residentObj = new Resident();

// Get search term
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Use the getAllResidents() method
$residents = $residentObj->getAllResidents($searchTerm);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <title>View Residents</title>
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
          <a href="viewResident.php" class="block py-2 px-4 rounded bg-gradient-to-r from-purple-600 to-blue-600">View Residents</a>
          <a href="addResident.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">Add Resident</a>
        </div>
        <div class="mb-4">
          <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Blotters</h3>
          <a href="viewBlotter.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">View Blotters</a>
          <a href="addBlotter.php" class="block py-2 px-4 rounded hover:bg-gray-800 transition">Add Blotter</a>
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
      <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-white">Residents List</h1>
          <div class="flex gap-2">
            <a 
              href="printResidentList.php<?= !empty($searchTerm) ? '?search=' . urlencode($searchTerm) : '' ?>" 
              target="_blank"
              class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2 rounded-lg transition no-underline"
              title="Print Residents List"
            >
              🖨️ Print List
            </a>
            <a 
              href="addResident.php" 
              class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-5 py-2 rounded-lg transition no-underline"
            >
              Add New Resident
            </a>
          </div>
        </div>

        <form method="GET" class="mb-6 flex gap-2">
          <input 
            type="text" 
            name="search" 
            value="<?= htmlspecialchars($searchTerm) ?>" 
            placeholder="Search by name, address, contact..." 
            class="w-full p-2 bg-gray-800 border border-gray-700 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none"
          >
          <button 
            type="submit" 
            class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-4 py-2 rounded-lg"
          >
            Search
          </button>
        </form>

        <?php if (isset($_GET['success'])): ?>
          <?php
            $message = match ($_GET['success']) {
                '1' => 'Resident added successfully!',
                '2' => 'Resident updated successfully!',
                '3' => 'Resident deleted successfully!',
                default => 'Action successful!'
            };
          ?>
          <div class="mb-4 p-3 rounded-lg bg-green-900/30 text-green-400 border border-green-700 text-center" id="success-alert">
            <?= $message ?>
          </div>
        <?php endif; ?>

        <div class="overflow-x-auto shadow-lg rounded-lg bg-gray-800 border border-gray-700">
          <table class="min-w-full border-collapse">
            <thead class="bg-gray-900 text-gray-300 border-b border-gray-700">
              <tr>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">First Name</th>
                <th class="p-3 text-left">Last Name</th>
                <th class="p-3 text-left">Age</th>
                <th class="p-3 text-left">Gender</th>
                <th class="p-3 text-left">House Address</th>
                <th class="p-3 text-left">Contact Number</th>
                <th class="p-3 text-left">Email</th>
                <th class="p-3 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($residents)): ?>
                <?php foreach ($residents as $res): ?>
                  <tr class="border-b border-gray-700 hover:bg-gray-700 transition cursor-pointer text-gray-300" onclick="window.location='viewResidentDetail.php?id=<?= $res['id'] ?>'" title="Click to view details">
                    <td class="p-3 align-top"><?= htmlspecialchars($res['id']) ?></td>
                    <td class="p-3 align-top font-medium">
                      <?= htmlspecialchars($res['first_name']) ?>
                    </td>
                    <td class="p-3 align-top font-medium">
                      <?= htmlspecialchars($res['last_name']) ?>
                    </td>
                    <td class="p-3 align-top"><?= htmlspecialchars($res['age']) ?></td>
                    <td class="p-3 align-top"><?= htmlspecialchars($res['gender']) ?></td>
                    <td class="p-3 align-top"><?= htmlspecialchars($res['house_address']) ?></td>
                    <td class="p-3 align-top"><?= htmlspecialchars($res['contact_number']) ?></td>
                    <td class="p-3 align-top text-gray-400"><?= !empty($res['email']) ? htmlspecialchars($res['email']) : '<span class="text-gray-600">—</span>' ?></td>
                    
                    <td class="p-3 align-top whitespace-nowrap" onclick="event.stopPropagation()">
                      <a href="editResident.php?id=<?= $res['id'] ?>" class="text-blue-400 hover:text-blue-300 hover:underline mr-2">Edit</a>
                      <a href="deleteResident.php?id=<?= $res['id'] ?>" 
                         class="text-red-400 hover:text-red-300 hover:underline"
                         onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($res['first_name'] . ' ' . $res['last_name']) ?>? \n\nNote: Deleting this resident will remove them from any blotters they are in, but will not delete the blotter case itself.')">
                         Delete
                      </a>
                    </td> 
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="9" class="text-center py-6 text-gray-500">
                    No residents found<?php if (!empty($searchTerm)) echo ' matching "' . htmlspecialchars($searchTerm) . '"'; ?>.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
    </main>
  </div>

  <script>
    const alert = document.getElementById('success-alert');
    if (alert) {
      // **FIXED HERE:** Removed the extra dot after '=>'
      setTimeout(() => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
      }, 3000);
    }
  </script>
</body>
</html>