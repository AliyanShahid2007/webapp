<?php
/**
 * Update Admin Credentials Script
 * This script updates the admin username and password in the database
 */

require_once 'config/database.php';

$newUsername = 'aliyanshahid2007';
$newPassword = 'aliyan@123';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $pdo = getPDOConnection();

    // Update admin user
    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE role = 'admin'");
    $stmt->execute([$newUsername, $hashedPassword]);

    if ($stmt->rowCount() > 0) {
        echo "Admin credentials updated successfully!\n";
        echo "New Username: " . $newUsername . "\n";
        echo "New Password: " . $newPassword . "\n";
    } else {
        echo "No admin user found to update.\n";
    }

} catch (Exception $e) {
    echo "Error updating admin credentials: " . $e->getMessage() . "\n";
}
?>
