<?php
require_once __DIR__ . '/../includes/header.php';

// Check freelancer access
requireRole('freelancer');

$action = $_GET['action'] ?? '';
$order_id = (int)($_GET['id'] ?? 0);

if (!in_array($action, ['accept', 'reject', 'start', 'complete']) || !$order_id) {
    redirectWithMessage('/freelancer/orders.php', 'Invalid action', 'danger');
}

try {
    $pdo = getPDOConnection();
    
    // Verify ownership
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND freelancer_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        redirectWithMessage('/freelancer/orders.php', 'Order not found or access denied', 'danger');
    }
    
    switch ($action) {
        case 'accept':
            if ($order['status'] != 'pending') {
                redirectWithMessage('/freelancer/orders.php', 'Order cannot be accepted', 'danger');
            }
            $stmt = $pdo->prepare("UPDATE orders SET status = 'accepted' WHERE id = ?");
            $stmt->execute([$order_id]);
            redirectWithMessage('/freelancer/orders.php', 'Order accepted successfully!', 'success');
            break;
            
        case 'reject':
            if ($order['status'] != 'pending') {
                redirectWithMessage('/freelancer/orders.php', 'Order cannot be rejected', 'danger');
            }
            $stmt = $pdo->prepare("UPDATE orders SET status = 'canceled' WHERE id = ?");
            $stmt->execute([$order_id]);
            redirectWithMessage('/freelancer/orders.php', 'Order rejected', 'info');
            break;
            
        case 'start':
            if ($order['status'] != 'accepted') {
                redirectWithMessage('/freelancer/orders.php', 'Order cannot be started', 'danger');
            }
            $stmt = $pdo->prepare("UPDATE orders SET status = 'in_progress' WHERE id = ?");
            $stmt->execute([$order_id]);
            redirectWithMessage('/freelancer/orders.php', 'Order marked as in progress', 'success');
            break;
            
        case 'complete':
            if ($order['status'] != 'in_progress') {
                redirectWithMessage('/freelancer/orders.php', 'Order cannot be completed', 'danger');
            }
            $stmt = $pdo->prepare("UPDATE orders SET status = 'completed', completed_at = NOW() WHERE id = ?");
            $stmt->execute([$order_id]);
            redirectWithMessage('/freelancer/orders.php', 'Order completed! Client can now leave a review.', 'success');
            break;
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    redirectWithMessage('/freelancer/orders.php', 'An error occurred', 'danger');
}
