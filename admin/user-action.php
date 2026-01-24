<?php
require_once __DIR__ . '/../includes/header.php';

// Check admin access
requireRole('admin');

$action = $_GET['action'] ?? '';
$user_id = (int)($_GET['id'] ?? 0);

if (!$user_id || !in_array($action, ['approve', 'reject', 'suspend_7days', 'suspend_permanent', 'activate'])) {
    redirectWithMessage('/admin/dashboard.php', 'Invalid action', 'danger');
}

try {
    $pdo = getPDOConnection();
    
    // Get user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        redirectWithMessage('/admin/dashboard.php', 'User not found', 'danger');
    }
    
    // Don't allow action on admin users
    if ($user['role'] === 'admin') {
        redirectWithMessage('/admin/dashboard.php', 'Cannot perform action on admin users', 'danger');
    }
    
    switch ($action) {
        case 'approve':
            $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            $stmt->execute([$user_id]);
            redirectWithMessage('/admin/dashboard.php', 'User approved successfully', 'success');
            break;
            
        case 'reject':
            // Delete user and related data
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            redirectWithMessage('/admin/dashboard.php', 'User rejected and removed', 'success');
            break;
            
        case 'suspend_7days':
            $suspended_until = date('Y-m-d H:i:s', strtotime('+7 days'));
            $stmt = $pdo->prepare("UPDATE users SET status = 'suspended_7days', suspended_until = ? WHERE id = ?");
            $stmt->execute([$suspended_until, $user_id]);
            redirectWithMessage('/admin/users.php', 'User suspended for 7 days', 'success');
            break;
            
        case 'suspend_permanent':
            $stmt = $pdo->prepare("UPDATE users SET status = 'suspended_permanent', suspended_until = NULL WHERE id = ?");
            $stmt->execute([$user_id]);
            redirectWithMessage('/admin/users.php', 'User permanently suspended', 'success');
            break;
            
        case 'activate':
            $stmt = $pdo->prepare("UPDATE users SET status = 'active', suspended_until = NULL WHERE id = ?");
            $stmt->execute([$user_id]);
            redirectWithMessage('/admin/users.php', 'User activated successfully', 'success');
            break;
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    redirectWithMessage('/admin/dashboard.php', 'An error occurred', 'danger');
}
