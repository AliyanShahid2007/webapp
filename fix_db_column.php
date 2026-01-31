<?php
/**
 * Fix Database Column Script
 * Adds the deactivated_by_admin column to gigs table if it doesn't exist
 */

require_once 'config/database.php';

try {
    // Use mysqli connection instead of PDO since PDO driver might not be available
    $conn = getDBConnection();

    // Check if column exists
    $result = $conn->query("SHOW COLUMNS FROM gigs LIKE 'deactivated_by_admin'");
    $exists = $result->fetch_assoc();

    if (!$exists) {
        // Add the column
        $conn->query("ALTER TABLE gigs ADD COLUMN deactivated_by_admin BOOLEAN DEFAULT FALSE");
        echo "Column 'deactivated_by_admin' added successfully to gigs table.\n";
    } else {
        echo "Column 'deactivated_by_admin' already exists.\n";
    }

    $conn->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
