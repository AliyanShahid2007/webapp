<?php
/**
 * Database Import Script
 * This script imports sample data into the freelance marketplace database
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'freelance_marketplace');

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

try {
    $conn = getDBConnection();

    // Read the SQL file
    $sql = file_get_contents('database.sql');

    // Split the SQL file into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE|--)/i', $statement)) {
            try {
                if ($conn->query($statement) === TRUE) {
                    echo "Executed: " . substr($statement, 0, 50) . "...\n";
                } else {
                    echo "Error executing statement: " . $conn->error . "\n";
                    echo "Statement: " . substr($statement, 0, 100) . "...\n";
                }
            } catch (Exception $e) {
                echo "Error executing statement: " . $e->getMessage() . "\n";
                echo "Statement: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }

    echo "\nDatabase import completed successfully!\n";
    $conn->close();

} catch (Exception $e) {
    echo "Database import failed: " . $e->getMessage() . "\n";
}
?>
