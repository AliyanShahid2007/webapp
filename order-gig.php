<?php
require_once 'includes/header.php';

// Check client access
requireRole('client');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('/browse-gigs.php', 'Invalid request', 'danger');
}

$gig_id = (int)($_POST['gig_id'] ?? 0);
$client_notes = sanitize($_POST['client_notes'] ?? '');

if (!$gig_id) {
    redirectWithMessage('/browse-gigs.php', 'Invalid gig', 'danger');
}

try {
    $pdo = getPDOConnection();
    
    // Get gig details
    $stmt = $pdo->prepare("
        SELECT g.*, u.status as freelancer_status
        FROM gigs g
        JOIN users u ON g.freelancer_id = u.id
        WHERE g.id = ? AND g.status = 'active' AND u.status = 'active'
    ");
    $stmt->execute([$gig_id]);
    $gig = $stmt->fetch();
    
    if (!$gig) {
        redirectWithMessage('/browse-gigs.php', 'Gig not found or unavailable', 'danger');
    }
    
    // Check if already ordered
    $stmt = $pdo->prepare("
        SELECT id FROM orders 
        WHERE gig_id = ? AND client_id = ? AND status NOT IN ('canceled', 'completed')
    ");
    $stmt->execute([$gig_id, $user_id]);
    $existing_order = $stmt->fetch();
    
    if ($existing_order) {
        redirectWithMessage('/client/orders.php', 'You already have an active order for this gig', 'warning');
    }
    
    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (gig_id, client_id, freelancer_id, status, budget, delivery_time, client_notes)
        VALUES (?, ?, ?, 'pending', ?, ?, ?)
    ");
    $stmt->execute([
        $gig_id,
        $user_id,
        $gig['freelancer_id'],
        $gig['budget'],
        $gig['delivery_time'],
        $client_notes
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    redirectWithMessage(
        '/client/orders.php', 
        'Order placed successfully! The freelancer will review your order shortly. Order ID: #' . $order_id, 
        'success'
    );
    
} catch (Exception $e) {
    error_log($e->getMessage());
    redirectWithMessage('/browse-gigs.php', 'An error occurred while placing your order', 'danger');
}
