<?php
require_once 'config/database.php';

try {
    $pdo = getPDOConnection();

    // Check current database
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $db = $stmt->fetch();
    echo "Current database: " . $db['db_name'] . "\n";

    // Check if gigs table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'gigs'");
    $table_exists = $stmt->fetch();
    if (!$table_exists) {
        echo "Gigs table does not exist!\n";
        exit;
    }
    echo "Gigs table exists.\n";

    // Check columns in gigs table
    $stmt = $pdo->query("DESCRIBE gigs");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Columns in gigs table:\n";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }

    // Check if deactivated_by_admin column exists
    $column_exists = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'deactivated_by_admin') {
            $column_exists = true;
            break;
        }
    }

    if (!$column_exists) {
        echo "Adding deactivated_by_admin column...\n";
        $pdo->exec("ALTER TABLE gigs ADD COLUMN deactivated_by_admin BOOLEAN DEFAULT FALSE");
        echo "Column added successfully!\n";
    } else {
        echo "Column deactivated_by_admin already exists.\n";
    }

    // Test a simple update
    echo "Testing update query...\n";
    $stmt = $pdo->prepare("UPDATE gigs SET deactivated_by_admin = TRUE WHERE id = 1");
    $result = $stmt->execute();
    echo "Update test: " . ($result ? "SUCCESS" : "FAILED") . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
