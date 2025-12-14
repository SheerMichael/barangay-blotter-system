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

// 2. Get the ID from URL or POST
$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
    header("Location: viewResident.php");
    exit();
}

$id = (int)$id;

// 3. Initialize objects and variables
$resident = new Resident();
$message = '';

// 4. Handle POST request (form submission)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Assign POST data to the resident object
    $resident->id = $id;
    $resident->first_name = $_POST['first_name'] ?? '';
    $resident->last_name = $_POST['last_name'] ?? '';
    $resident->age = $_POST['age'] ?? null;
    $resident->gender = $_POST['gender'] ?? '';
    $resident->house_address = $_POST['house_address'] ?? '';
    $resident->contact_number = $_POST['contact_number'] ?? '';
    $resident->email = !empty($_POST['email']) ? $_POST['email'] : null;

    // Attempt to update
    if ($resident->updateResident()) {
        // Success
        header("Location: viewResident.php?success=2");
        exit();
    } else {
        // Failure
        $message = "An error occurred while updating the resident. Please try again.";
        // Repopulate $formData with submitted data so the user doesn't lose changes
        $formData = $_POST;
    }

} else {
    // 5. Handle GET request (page load)
    $formData = $resident->viewResidentById($id);
    
    if (!$formData) {
        // No resident found with this ID
        header("Location: viewResident.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <title>Edit Resident</title>
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
          <a href="../reports/index.php" class="block py-2 px-4 rounded hover:bg-gradient-to-r from-purple-600 to-blue-600 transition">Generate Reports</a>
        </div>
        <div class="pt-4 border-t border-gray-800">
          <a href="../auth/logout.php" class="block py-2 px-4 rounded bg-red-600/90 hover:bg-red-600 transition text-center font-medium">Logout</a>
        </div>
      </nav>
    </aside>

    <main class="flex-1 p-8">
      <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl  text-white font-bold">Edit Resident</h1>
          <a 
            href="viewResident.php" 
            class="bg-gray-600 hover:bg-gray-500 text-white px-5 py-2 rounded-lg transition no-underline"
          >
            ← Back to List
          </a>
        </div>

        <div class="bg-gray-800 border border-gray-700 p-6 rounded-lg shadow-lg max-w-2xl mx-auto">
          <form action="editResident.php" method="POST">
            <input type="hidden" name="id" value="<?= $id ?>">

            <?php if (!empty($message)): ?>
              <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-300 text-center">
                <?= $message ?>
              </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
              <div>
                <label for="first_name" class="block text-sm font-medium text-gray-300 mb-1">First Name</label>
                <input type="text" name="first_name" id="first_name" required
                       value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>"
                       class="w-full p-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
              </div>
              <div>
                <label for="last_name" class="block text-sm font-medium text-gray-300 mb-1">Last Name</label>
                <input type="text" name="last_name" id="last_name" required
                       value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>"
                       class="w-full p-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
              <div>
                <label for="age" class="block text-sm font-medium text-gray-300 mb-1">Age</label>
                <input type="number" name="age" id="age" min="0" max="150"
                       value="<?= htmlspecialchars($formData['age'] ?? '') ?>"
                       class="w-full p-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
              </div>
              <div>
                <label for="gender" class="block text-sm font-medium text-gray-300 mb-1">Gender</label>
                <select name="gender" id="gender"
                        class="w-full p-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                  <option value="" <?= !isset($formData['gender']) ? 'selected' : '' ?>>Select...</option>
                  <option value="Male" <?= ($formData['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                  <option value="Female" <?= ($formData['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                  <option value="Other" <?= ($formData['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
              </div>
            </div>

            <div class="mb-4">
              <label for="house_address" class="block text-sm font-medium text-gray-300 mb-1">House Address</label>
              <input type="text" name="house_address" id="house_address"
                     value="<?= htmlspecialchars($formData['house_address'] ?? '') ?>"
                     placeholder="e.g., 123 Main St., Purok 1"
                     class="w-full p-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
            </div>

            <div class="mb-4">
              <label for="contact_number" class="block text-sm font-medium text-gray-300 mb-1">Contact Number</label>
              <input type="text" name="contact_number" id="contact_number"
                     value="<?= htmlspecialchars($formData['contact_number'] ?? '') ?>"
                     placeholder="e.g., 09123456789"
                     class="w-full p-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
            </div>

            <div class="mb-4">
              <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email <span class="text-gray-500 text-xs">(Optional)</span></label>
              <input type="email" name="email" id="email"
                     value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                     placeholder="e.g., resident@example.com"
                     class="w-full p-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
              <p class="text-xs text-gray-400 mt-1">Email address will be used for blotter status notifications</p>
            </div>

            <div class="mt-6 text-right">
              <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition font-medium">
                Update Resident
              </button>
            </div>

          </form>
        </div>
      </div>
    </main>
  </div>
</body>
</html>