<?php
// login.php
require_once __DIR__ . '/../classes/user.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = new User();
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $remember = isset($_POST['remember']);

  if (empty($username) || empty($password)) {
    $error = "Username and password are required.";
  } else {
    $result = $user->login($username, $password);
    if ($result) {
      $_SESSION['user_id'] = $result['id'];
      $_SESSION['username'] = $result['username'];

      // Remember me functionality
      if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), "/");
        // Store token in database (you'd need to add this column)
      }

      header("Location: ../index.php");
      exit;
    } else {
      $error = "Invalid username or password.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Blotter System</title>
  <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    <!-- Logo/Title Card -->
    <div class="text-center mb-8">
      <h1 class="text-4xl font-bold text-white mb-2">Barangay Blotter System</h1>
      <p class="text-gray-300">Secure Access Portal</p>
    </div>

    <!-- Login Card -->
    <div class="bg-gray-800 rounded-2xl shadow-2xl p-8 border border-gray-700">
      <h2 class="text-2xl font-bold mb-6 text-white text-center">Welcome Back</h2>
      
      <?php if (isset($error)): ?>
        <div class="mb-4 p-3 rounded-lg bg-red-900/30 text-red-400 border border-red-700 text-sm">
          <span class="font-semibold">Error: </span> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" class="space-y-5">
        <div>
          <label for="username" class="block text-sm font-semibold text-gray-300 mb-2">Username</label>
          <input 
            type="text" 
            name="username" 
            id="username" 
            required 
            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition text-white placeholder-gray-400"
            placeholder="Enter your username"
          >
        </div>
        
        <div>
          <label for="password" class="block text-sm font-semibold text-gray-300 mb-2">Password</label>
          <input 
            type="password" 
            name="password" 
            id="password" 
            required 
            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition text-white placeholder-gray-400"
            placeholder="Enter your password"
          >
        </div>
        
        <div class="flex items-center justify-between text-sm">
          <label class="flex items-center cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 text-purple-600 bg-gray-700 border-gray-600 rounded focus:ring-purple-500">
            <span class="ml-2 text-gray-300">Remember me</span>
          </label>
          <a href="register.php" class="text-purple-400 hover:text-purple-300 font-medium">Create account</a>
        </div>
        
        <button 
          type="submit" 
          class="w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold py-3 rounded-lg shadow-lg hover:shadow-xl transition duration-200"
        >
          Sign In
        </button>
      </form>
    </div>
    
    <!-- Footer -->
    <p class="text-center text-gray-400 text-sm mt-6">
      © 2025 Blotter Management System
    </p>
  </div>
</body>
</html>
