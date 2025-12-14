<?php
// populate_data.php - Script to populate sample data for testing

require_once "database/database.php";

$db = new Database();
$conn = $db->connect();

echo "Starting data population...\n\n";

try {
    // Start transaction
    $conn->beginTransaction();

    // Sample residents data
    $residents = [
        ['Juan', 'Dela Cruz', 35, 'Male', 'Purok 1, Barangay Centro', '09171234567'],
        ['Maria', 'Santos', 28, 'Female', 'Purok 2, Barangay Centro', '09181234568'],
        ['Pedro', 'Reyes', 42, 'Male', 'Purok 3, Barangay Centro', '09191234569'],
        ['Ana', 'Garcia', 31, 'Female', 'Purok 1, Barangay Centro', '09201234570'],
        ['Jose', 'Martinez', 45, 'Male', 'Purok 4, Barangay Centro', '09211234571'],
        ['Carmen', 'Lopez', 38, 'Female', 'Purok 2, Barangay Centro', '09221234572'],
        ['Ricardo', 'Fernandez', 29, 'Male', 'Purok 5, Barangay Centro', '09231234573'],
        ['Rosa', 'Gonzalez', 33, 'Female', 'Purok 3, Barangay Centro', '09241234574'],
        ['Miguel', 'Rodriguez', 41, 'Male', 'Purok 6, Barangay Centro', '09251234575'],
        ['Sofia', 'Hernandez', 27, 'Female', 'Purok 4, Barangay Centro', '09261234576'],
        ['Antonio', 'Diaz', 36, 'Male', 'Purok 7, Barangay Centro', '09271234577'],
        ['Elena', 'Torres', 30, 'Female', 'Purok 5, Barangay Centro', '09281234578'],
        ['Carlos', 'Ramirez', 39, 'Male', 'Purok 8, Barangay Centro', '09291234579'],
        ['Isabel', 'Flores', 26, 'Female', 'Purok 6, Barangay Centro', '09301234580'],
        ['Fernando', 'Cruz', 44, 'Male', 'Purok 1, Barangay Centro', '09311234581']
    ];

    echo "Inserting residents...\n";
    $residentIds = [];
    $sqlResident = "INSERT INTO residents (first_name, last_name, age, gender, house_address, contact_number) 
                    VALUES (:first_name, :last_name, :age, :gender, :house_address, :contact_number)";
    $stmtResident = $conn->prepare($sqlResident);

    foreach ($residents as $resident) {
        $stmtResident->execute([
            ':first_name' => $resident[0],
            ':last_name' => $resident[1],
            ':age' => $resident[2],
            ':gender' => $resident[3],
            ':house_address' => $resident[4],
            ':contact_number' => $resident[5]
        ]);
        $residentIds[] = $conn->lastInsertId();
    }
    echo "✓ Inserted " . count($residents) . " residents\n\n";

    // Sample blotters data with variety across different months
    $incidentTypes = ['Noise Complaint', 'Property Dispute', 'Theft', 'Assault', 'Harassment', 'Vandalism', 'Trespassing'];
    $statuses = ['Pending', 'Scheduled', 'Resolved', 'Endorsed to Police'];
    $locations = ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5', 'Purok 6', 'Basketball Court', 'Barangay Hall', 'Main Road'];

    echo "Inserting blotters...\n";
    
    // Generate blotters for the last 12 months
    $blottersData = [];
    for ($monthsAgo = 11; $monthsAgo >= 0; $monthsAgo--) {
        $numBlotters = rand(2, 6); // Random number of blotters per month
        
        for ($i = 0; $i < $numBlotters; $i++) {
            $date = date('Y-m-d', strtotime("-{$monthsAgo} months -" . rand(0, 28) . " days"));
            $time = sprintf("%02d:%02d:00", rand(6, 22), rand(0, 59));
            
            $blottersData[] = [
                'date' => $date,
                'time' => $time,
                'location' => $locations[array_rand($locations)],
                'type' => $incidentTypes[array_rand($incidentTypes)],
                'status' => $statuses[array_rand($statuses)],
                'details' => "Sample incident details for testing purposes. This is a simulated case filed on {$date}.",
                'remarks' => rand(0, 1) ? "Additional remarks for this case." : "",
                'complainant' => $residentIds[array_rand($residentIds)],
                'respondent' => $residentIds[array_rand($residentIds)]
            ];
        }
    }

    $sqlBlotter = "INSERT INTO blotters (incident_date, incident_time, incident_location, incident_type, details, status, remarks, created_at) 
                   VALUES (:incident_date, :incident_time, :incident_location, :incident_type, :details, :status, :remarks, :created_at)";
    $stmtBlotter = $conn->prepare($sqlBlotter);

    $sqlComplainant = "INSERT INTO blotter_complainants (blotter_id, resident_id) VALUES (:blotter_id, :resident_id)";
    $stmtComplainant = $conn->prepare($sqlComplainant);

    $sqlRespondent = "INSERT INTO blotter_respondents (blotter_id, resident_id) VALUES (:blotter_id, :resident_id)";
    $stmtRespondent = $conn->prepare($sqlRespondent);

    foreach ($blottersData as $blotter) {
        // Insert blotter
        $stmtBlotter->execute([
            ':incident_date' => $blotter['date'],
            ':incident_time' => $blotter['time'],
            ':incident_location' => $blotter['location'],
            ':incident_type' => $blotter['type'],
            ':details' => $blotter['details'],
            ':status' => $blotter['status'],
            ':remarks' => $blotter['remarks'],
            ':created_at' => $blotter['date'] . ' ' . $blotter['time']
        ]);
        
        $blotterId = $conn->lastInsertId();
        
        // Generate case number
        $year = date("Y", strtotime($blotter['date']));
        $caseNo = "CASE-{$year}-" . str_pad($blotterId, 5, "0", STR_PAD_LEFT);
        $conn->prepare("UPDATE blotters SET case_no = ? WHERE id = ?")->execute([$caseNo, $blotterId]);
        
        // Insert complainant
        $stmtComplainant->execute([
            ':blotter_id' => $blotterId,
            ':resident_id' => $blotter['complainant']
        ]);
        
        // Insert respondent
        $stmtRespondent->execute([
            ':blotter_id' => $blotterId,
            ':resident_id' => $blotter['respondent']
        ]);
    }
    
    echo "✓ Inserted " . count($blottersData) . " blotters\n\n";

    // Commit transaction
    $conn->commit();
    
    echo "========================================\n";
    echo "✓ Data population completed successfully!\n";
    echo "========================================\n\n";
    
    // Display summary
    echo "Summary:\n";
    echo "- Total Residents: " . count($residents) . "\n";
    echo "- Total Blotters: " . count($blottersData) . "\n";
    echo "- Date Range: " . $blottersData[0]['date'] . " to " . end($blottersData)['date'] . "\n\n";
    
    echo "You can now view the dashboard to see the charts populated with data!\n";
    echo "Navigate to: http://localhost/WebdevBlotter/\n";

} catch (Exception $e) {
    $conn->rollBack();
    echo "✗ Error: " . $e->getMessage() . "\n";
}
