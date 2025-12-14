<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../classes/resident.php";
require_once "../auth/session.php"; // Added session check

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$residentObj = new Resident();   

$resident = ["first_name"=>"", "last_name"=>"", "age"=>"", "gender"=>"", "house_address"=>"","contact_number"=>"", "email"=>""];
$error = ["first_name"=>"", "last_name"=>"", "age"=>"", "gender"=>"", "house_address"=>"","contact_number"=>"", "email"=>""];
$formError = ""; // For general errors like duplicates

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $resident["first_name"] = trim(htmlspecialchars($_POST["first_name"]));
    $resident["last_name"] = trim(htmlspecialchars($_POST["last_name"]));
    $resident["age"] = trim(htmlspecialchars($_POST["age"]));
    $resident["gender"] = trim(htmlspecialchars($_POST["gender"]));
    $resident["house_address"] = trim(htmlspecialchars($_POST["house_address"]));
    $resident["contact_number"] = trim(htmlspecialchars($_POST["contact_number"]));
    $resident["email"] = trim(htmlspecialchars($_POST["email"]));
    

    if (empty($resident["first_name"])) $error["first_name"] = "First name is required.";
    if (empty($resident["last_name"])) $error["last_name"] = "Last name is required.";
    if (empty($resident["age"])) $error["age"] = "Age is required.";
    if (empty($resident["gender"])) {
        $error["gender"] = "Gender is required.";
    }
    if (empty($resident["house_address"])) $error["house_address"] = "House address is required.";
    if (empty($resident["contact_number"])) {
        $error["contact_number"] = "Contact number is required.";
    } elseif (!is_numeric($resident["contact_number"])) {
        $error["contact_number"] = "Please enter a valid contact number.";
    }
    
    // Email validation (optional field)
    if (!empty($resident["email"]) && !filter_var($resident["email"], FILTER_VALIDATE_EMAIL)) {
        $error["email"] = "Please enter a valid email address.";
    }

    if (empty(array_filter($error))) {
        $residentObj->first_name = $resident["first_name"];
        $residentObj->last_name = $resident["last_name"];
        $residentObj->age = $resident["age"]; 
        $residentObj->gender = $resident["gender"];
        $residentObj->house_address = $resident["house_address"];
        $residentObj->contact_number = $resident["contact_number"];
        $residentObj->email = !empty($resident["email"]) ? $resident["email"] : null;


        if ($residentObj->addResident()) {
            // Redirect to viewResident.php with a success message
            header("Location: viewResident.php?success=1");
            exit;
        } else {
            // Show a general form error if the resident already exists
            $formError = "A resident with this first and last name already exists.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Corrected CSS path -->
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <title>Add Resident</title>
</head>
<body class="bg-gray-900 min-h-screen">
  <div class="flex">
    
    <!-- Sidebar -->
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
          <!-- Current page is highlighted -->
          <a href="addResident.php" class="block py-2 px-4 rounded bg-gradient-to-r from-purple-600 to-blue-600">Add Resident</a>
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

    <!-- Main Content -->
    <main class="flex-1 p-8 bg-gray-900">
      <div class="container mx-auto max-w-2xl">
        <h1 class="text-3xl font-bold mb-6 text-white">Add New Resident</h1>

        <!-- Form Container -->
        <div class="bg-gray-800 shadow-lg rounded-lg p-6 border border-gray-700">
          <form method="POST" action="addResident.php">
            
            <!-- Show general form error -->
            <?php if (!empty($formError)): ?>
              <div class="mb-4 p-3 rounded-lg bg-red-900/30 text-red-400 border border-red-700 text-center">
                <?= $formError ?>
              </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- First Name -->
              <div>
                <label for="first_name" class="block text-sm font-medium text-gray-300 mb-1">First Name:</label>
                <input 
                  type="text" 
                  id="first_name" 
                  name="first_name" 
                  value="<?= htmlspecialchars($resident['first_name']) ?>"
                  class="w-full p-2 bg-gray-700 border text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none <?php echo !empty($error['first_name']) ? 'border-red-500' : 'border-gray-600'; ?>"
                >
                <span class="text-red-400 text-sm"><?= $error['first_name'] ?></span>
              </div>

              <!-- Last Name -->
              <div>
                <label for="last_name" class="block text-sm font-medium text-gray-300 mb-1">Last Name:</label>
                <input 
                  type="text" 
                  id="last_name" 
                  name="last_name" 
                  value="<?= htmlspecialchars($resident['last_name']) ?>"
                  class="w-full p-2 bg-gray-700 border text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none <?php echo !empty($error['last_name']) ? 'border-red-500' : 'border-gray-600'; ?>"
                >
                <span class="text-red-400 text-sm"><?= $error['last_name'] ?></span>
              </div>

              <!-- Age -->
              <div>
                <label for="age" class="block text-sm font-medium text-gray-300 mb-1">Age:</label>
                <input 
                  type="number" 
                  id="age" 
                  name="age" 
                  value="<?= htmlspecialchars($resident['age']) ?>"
                  class="w-full p-2 bg-gray-700 border text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none <?php echo !empty($error['age']) ? 'border-red-500' : 'border-gray-600'; ?>"
                >
                <span class="text-red-400 text-sm"><?= $error['age'] ?></span>
              </div>

              <!-- Gender -->
              <div>
                <label for="gender" class="block text-sm font-medium text-gray-300 mb-1">Gender:</label>
                <select 
                  id="gender" 
                  name="gender" 
                  class="w-full p-2 bg-gray-700 border text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none <?php echo !empty($error['gender']) ? 'border-red-500' : 'border-gray-600'; ?>"
                >
                  <option value="">Select Gender</option>
                  <option value="Male" <?= ($resident['gender'] === "Male") ? "selected" : "" ?>>Male</option>
                  <option value="Female" <?= ($resident['gender'] === "Female") ? "selected" : "" ?>>Female</option>
                  <!-- ** FIX: Corrected check for 'Other' ** -->
                  <option value="Other" <?= ($resident['gender'] === "Other") ? "selected" : "" ?>>Other</option>
                </select>
                <span class="text-red-400 text-sm"><?= $error['gender'] ?></span>
              </div>
            </div>

            <!-- House Address -->
            <div class="mt-6">
              <label for="house_address" class="block text-sm font-medium text-gray-300 mb-1">House Address:</label>
              <input 
                type="text" 
                id="house_address" 
                name="house_address" 
                value="<?= htmlspecialchars($resident['house_address']) ?>"
                class="w-full p-2 bg-gray-700 border text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none <?php echo !empty($error['house_address']) ? 'border-red-500' : 'border-gray-600'; ?>"
              >
              <span class="text-red-400 text-sm"><?= $error['house_address'] ?></span>
            </div>

            <!-- Contact Number -->
            <div class="mt-6">
              <label for="contact_number" class="block text-sm font-medium text-gray-300 mb-1">Contact Number:</label>
              <input 
                type="text" 
                id="contact_number" 
                name="contact_number" 
                placeholder="e.g., 09123456789"
                value="<?= htmlspecialchars($resident['contact_number']) ?>"
                class="w-full p-2 bg-gray-700 border text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none <?php echo !empty($error['contact_number']) ? 'border-red-500' : 'border-gray-600'; ?>"
              >
              <span class="text-red-400 text-sm"><?= $error['contact_number'] ?></span>
            </div>

            <!-- Email -->
            <div class="mt-6">
              <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email: <span class="text-gray-500 text-xs">(Optional)</span></label>
              <input 
                type="email" 
                id="email" 
                name="email" 
                placeholder="e.g., resident@example.com"
                value="<?= htmlspecialchars($resident['email']) ?>"
                class="w-full p-2 bg-gray-700 border text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none <?php echo !empty($error['email']) ? 'border-red-500' : 'border-gray-600'; ?>"
              >
              <span class="text-red-400 text-sm"><?= $error['email'] ?></span>
              <p class="text-xs text-gray-400 mt-1">Email address will be used for blotter status notifications</p>
            </div>

            <!-- Buttons -->
            <div class="mt-8 flex justify-end gap-3">
              <a 
                href="viewResident.php" 
                class="bg-gray-600 hover:bg-gray-500 text-white px-5 py-2 rounded-lg transition no-underline"
              >
                Cancel
              </a>
              <button 
                type="submit" 
                class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-5 py-2 rounded-lg transition shadow-lg"
              >
                Add Resident
              </button>
            </div>

          </form>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
