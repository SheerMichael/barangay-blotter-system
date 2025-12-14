<?php
// deleteBlotter.php

require_once "../classes/blotter.php";
require_once "../auth/session.php";

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get the ID from the URL
$blotter_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($blotter_id > 0) {
    $blotter = new Blotter();
    $blotter->deleteBlotter($blotter_id);
}

// Redirect back to the view page with a success message
header("Location: viewBlotter.php?success=3"); // success=3 means deleted
exit;