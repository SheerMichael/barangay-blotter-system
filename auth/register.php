<?php
// register.php
require_once __DIR__ . '/../classes/user.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = new User();
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $confirm_password = trim($_POST['confirm_password'] ?? '');
  $email = trim($_POST['email'] ?? '');

  // Validation
  if (empty($username) || empty($password) || empty($confirm_password)) {
    $error = "Username, password, and confirm password are required.";
  } elseif ($password !== $confirm_password) {
    $error = "Passwords do not match.";
  } elseif (strlen($password) < 6) {
    $error = "Password must be at least 6 characters long.";
  } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email address format.";
  } else {
    $result = $user->register($username, $password, $email);
    if ($result === true) {
      $success = "Registration successful! You can now login.";
    } elseif ($result === 'email_exists') {
      $error = "Email address is already registered.";
    } else {
      $error = "Username already exists or registration failed.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Blotter System</title>
  <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    <!-- Logo/Title Card -->
    <div class="text-center mb-8">
      <h1 class="text-4xl font-bold text-white mb-2">Blotter System</h1>
      <p class="text-gray-300">Create Your Account</p>
    </div>

    <!-- Register Card -->
    <div class="bg-gray-800 rounded-2xl shadow-2xl p-8 border border-gray-700">
      <h2 class="text-2xl font-bold mb-6 text-white text-center">Sign Up</h2>
      
      <?php if (isset($error)): ?>
        <div class="mb-4 p-3 rounded-lg bg-red-900/30 text-red-400 border border-red-700 text-sm">
          <span class="font-semibold">Error:</span> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($success)): ?>
        <div class="mb-4 p-3 rounded-lg bg-green-900/30 text-green-400 border border-green-700 text-sm">
          <span class="font-semibold">Success:</span> <?= htmlspecialchars($success) ?>
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
            placeholder="Choose a username"
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
          >
        </div>
        
        <div>
          <label for="email" class="block text-sm font-semibold text-gray-300 mb-2">
            Email Address <span class="text-gray-500 font-normal">(Optional)</span>
          </label>
          <input 
            type="email" 
            name="email" 
            id="email" 
            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition text-white placeholder-gray-400"
            placeholder="your.email@example.com"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          >
        </div>
        
        <div>
          <label for="password" class="block text-sm font-semibold text-gray-300 mb-2">Password</label>
          <input 
            type="password" 
            name="password" 
            id="password" 
            required 
            minlength="6"
            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition text-white placeholder-gray-400"
            placeholder="Create a password (min 6 characters)"
          >
        </div>
        
        <div>
          <label for="confirm_password" class="block text-sm font-semibold text-gray-300 mb-2">Confirm Password</label>
          <input 
            type="password" 
            name="confirm_password" 
            id="confirm_password" 
            required 
            minlength="6"
            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition text-white placeholder-gray-400"
            placeholder="Re-enter your password"
          >
        </div>
        
        <button 
          type="submit" 
          class="w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold py-3 rounded-lg shadow-lg hover:shadow-xl transition duration-200"
        >
          Create Account
        </button>
      </form>
      
      <div class="mt-6 text-center">
        <a href="login.php" class="text-purple-400 hover:text-purple-300 font-medium text-sm">
          Already have an account? Sign in
        </a>
      </div>
    </div>
    
    <!-- Footer -->
    <p class="text-center text-gray-400 text-sm mt-6">
      © 2025 Blotter Management System
    </p>
  </div>
</body>
</html>
