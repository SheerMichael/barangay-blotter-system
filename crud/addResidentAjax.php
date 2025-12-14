<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once "../database/database.php";
require_once "../classes/resident.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Sanitize inputs
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $house_address = trim($_POST['house_address'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validation
    if (empty($first_name) || empty($last_name) || $age <= 0 || empty($gender) || empty($house_address)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }
    
    // Validate email format if provided
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }

    // Create resident object
    $residentObj = new Resident();
    $residentObj->first_name = $first_name;
    $residentObj->last_name = $last_name;
    $residentObj->age = $age;
    $residentObj->gender = $gender;
    $residentObj->house_address = $house_address;
    $residentObj->contact_number = $contact_number;
    $residentObj->email = !empty($email) ? $email : null;

    // Attempt to add resident
    $result = $residentObj->addResident();
    
    if ($result) {
        // Get the newly created resident's ID
        $db = new Database();
        $conn = $db->connect();
        $resident_id = $conn->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Resident added successfully.',
            'resident_id' => $resident_id,
            'resident_name' => "{$first_name} {$last_name}"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'A resident with this name already exists.']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
