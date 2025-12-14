<?php
/**
 * Database Migration Script
 * Adds email column to users table if it doesn't exist
 * 
 * Run this file once to update your database schema
 */

require_once __DIR__ . "/database/database.php";

$db = new Database();
$conn = $db->connect();

echo "Starting database migration...\n\n";

try {
    // Check if email column exists in users table
    $checkSql = "SHOW COLUMNS FROM users LIKE 'email'";
    $checkQuery = $conn->query($checkSql);
    
    if ($checkQuery->rowCount() === 0) {
        // Add email column
        echo "Adding 'email' column to 'users' table...\n";
        $alterSql = "ALTER TABLE users ADD COLUMN email VARCHAR(255) NULL AFTER password, ADD UNIQUE KEY unique_email (email)";
        $conn->exec($alterSql);
        echo "✓ Successfully added 'email' column to 'users' table\n\n";
    } else {
        echo "✓ 'email' column already exists in 'users' table\n\n";
    }
    
    echo "Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
