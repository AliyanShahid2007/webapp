<?php
require_once 'config/database.php';

try {
    $pdo = getPDOConnection();

    // Add the column if it doesn't exist
    $sql = "ALTER TABLE gigs ADD COLUMN deactivated_by_admin BOOLEAN DEFAULT FALSE";
    $pdo->exec($sql);

    echo "Column 'deactivated_by_admin' added successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
