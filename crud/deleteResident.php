<?php
// In crud/deleteResident.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../classes/resident.php";
require_once "../auth/session.php";

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// 2. Check if an ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // No ID, redirect back with an error (though we don't have an error message setup)
    header("Location: viewResident.php");
    exit();
}

$id = (int)$_GET['id'];
$residentObj = new Resident();

// 3. Attempt to delete the resident
if ($residentObj->deleteResident($id)) {
    // 4. Success: Redirect back to the view page with success code 3
    header("Location: viewResident.php?success=3");
    exit();
} else {
    // 5. Failure: Redirect back (maybe with an error in the future)
    // For now, just redirect
    header("Location: viewResident.php?error=1"); // Example error
    exit();
}