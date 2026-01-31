<?php
require_once 'config/database.php';

try {
    $pdo = getPDOConnection();

    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM gigs LIKE 'deactivated_by_admin'");
    $exists = $stmt->fetch();

    if (!$exists) {
        // Add the column
        $pdo->exec("ALTER TABLE gigs ADD COLUMN deactivated_by_admin BOOLEAN DEFAULT FALSE");
        echo "Column 'deactivated_by_admin' added successfully to gigs table.\n";
    } else {
        echo "Column 'deactivated_by_admin' already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
