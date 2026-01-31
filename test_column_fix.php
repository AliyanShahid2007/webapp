<?php
/**
 * Test Script to Verify Column Fix
 * Checks if deactivated_by_admin column exists and can be updated
 */

require_once 'config/database.php';

try {
    // Use mysqli connection
    $conn = getDBConnection();

    // Check if column exists
    $result = $conn->query("SHOW COLUMNS FROM gigs LIKE 'deactivated_by_admin'");
    $exists = $result->fetch_assoc();

    if ($exists) {
        echo "✓ Column 'deactivated_by_admin' exists in gigs table.\n";

        // Test updating the column
        $test_id = 1; // Assuming there's at least one gig
        $update_result = $conn->query("UPDATE gigs SET deactivated_by_admin = TRUE WHERE id = $test_id");

        if ($update_result) {
            echo "✓ Column update test successful.\n";

            // Reset it back
            $conn->query("UPDATE gigs SET deactivated_by_admin = FALSE WHERE id = $test_id");
            echo "✓ Column reset successful.\n";
        } else {
            echo "✗ Column update test failed.\n";
        }
    } else {
        echo "✗ Column 'deactivated_by_admin' does not exist.\n";
    }

    $conn->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
