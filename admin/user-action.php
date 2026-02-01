<?php
require_once __DIR__ . '/../includes/header.php';

// Check admin access
requireRole('admin');

$action = $_GET['action'] ?? '';
$user_id = (int)($_GET['id'] ?? 0);

if (!$user_id || !in_array($action, ['approve', 'reject', 'suspend_7days', 'suspend_permanent', 'activate', 'manage', 'update'])) {
    redirectWithMessage('/admin/dashboard.php', 'Invalid action', 'danger');
}

try {
    $conn = getDBConnection();

    // Get user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
    redirectWithMessage('/admin/dashboard.php', 'User not found', 'danger');
    }
    
    // Don't allow action on admin users
    if ($user['role'] === 'admin') {
        redirectWithMessage('/admin/dashboard.php', 'Cannot perform action on admin users', 'danger');
    }
    
    switch ($action) {
        case 'approve':
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/admin/users.php', 'User approved successfully', 'success');
            break;
            
        case 'reject':
            // Delete user and related data
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/admin/users.php', 'User rejected and removed', 'success');
            break;
            
        case 'suspend_7days':
            $suspended_until = date('Y-m-d H:i:s', strtotime('+7 days'));
            $stmt = $conn->prepare("UPDATE users SET status = 'suspended_7days', suspended_until = ? WHERE id = ?");
            $stmt->bind_param("si", $suspended_until, $user_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/admin/users.php', 'User suspended for 7 days', 'success');
            break;
            
        case 'suspend_permanent':
            $stmt = $conn->prepare("UPDATE users SET status = 'suspended_permanent', suspended_until = NULL WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/admin/users.php', 'User permanently suspended', 'success');
            break;
            
        case 'activate':
            $stmt = $conn->prepare("UPDATE users SET status = 'active', suspended_until = NULL WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/admin/users.php', 'User activated successfully', 'success');
            break;

        case 'manage':
            // Redirect to manage user page
            header('Location: ' . BASE_PATH . '/admin/manage-user.php?id=' . $user_id);
            exit;

        case 'update':
            // Handle POST data for updating user
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? '';
            $status = $_POST['status'] ?? '';

            if (!$name || !$email || !in_array($role, ['client', 'freelancer']) || !in_array($status, ['active', 'pending', 'suspended_7days', 'suspended_permanent'])) {
                redirectWithMessage(BASE_PATH . '/admin/manage-user.php?id=' . $user_id, 'Invalid input data', 'danger');
            }

            // Check if email is already taken by another user
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->fetch_assoc()) {
                $stmt->close();
                redirectWithMessage(BASE_PATH . '/admin/manage-user.php?id=' . $user_id, 'Email already in use', 'danger');
            }
            $stmt->close();

            // Update user
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $name, $email, $role, $status, $user_id);
            $stmt->execute();
            $stmt->close();

            redirectWithMessage('/admin/users.php', 'User updated successfully', 'success');
            break;
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    redirectWithMessage('/admin/users.php', 'An error occurred while processing the action', 'danger');
}
