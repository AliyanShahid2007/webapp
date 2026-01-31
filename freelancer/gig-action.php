<?php
require_once __DIR__ . '/../includes/header.php';

// Check freelancer access
requireRole('freelancer');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$gig_id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if (!in_array($action, ['create', 'edit', 'delete', 'activate', 'deactivate'])) {
    redirectWithMessage('/freelancer/gigs.php', 'Invalid action', 'danger');
}

try {
    $pdo = getPDOConnection();
    
    // Verify ownership for edit/delete actions
    if (in_array($action, ['edit', 'delete', 'activate', 'deactivate']) && $gig_id) {
        $stmt = $pdo->prepare("SELECT * FROM gigs WHERE id = ? AND freelancer_id = ?");
        $stmt->execute([$gig_id, $user_id]);
        $gig = $stmt->fetch();
        
        if (!$gig) {
            redirectWithMessage('/freelancer/gigs.php', 'Gig not found or access denied', 'danger');
        }
    }
    
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $title = sanitize($_POST['title'] ?? '');
                $description = sanitize($_POST['description'] ?? '');
                $category = sanitize($_POST['category'] ?? '');
                $budget = (float)($_POST['budget'] ?? 0);
                $delivery_time = (int)($_POST['delivery_time'] ?? 0);
                
                if (empty($title) || empty($description) || empty($category) || $budget <= 0 || $delivery_time <= 0) {
                    redirectWithMessage('/freelancer/gigs.php?action=create', 'All fields are required', 'danger');
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO gigs (freelancer_id, title, description, category, budget, delivery_time, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'active')
                ");
                $stmt->execute([$user_id, $title, $description, $category, $budget, $delivery_time]);
                
                redirectWithMessage('/freelancer/gigs.php', 'Gig created successfully!', 'success');
            }
            break;
            
        case 'activate':
            // Check if gig was deactivated by admin
            if ($gig['deactivated_by_admin']) {
                redirectWithMessage('/freelancer/gigs.php', 'This gig was deactivated by an admin and cannot be reactivated', 'danger');
            }
            $stmt = $pdo->prepare("UPDATE gigs SET status = 'active' WHERE id = ?");
            $stmt->execute([$gig_id]);
            redirectWithMessage('/freelancer/gigs.php', 'Gig activated successfully', 'success');
            break;
            
        case 'deactivate':
            $stmt = $pdo->prepare("UPDATE gigs SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$gig_id]);
            redirectWithMessage('/freelancer/gigs.php', 'Gig deactivated successfully', 'success');
            break;
            
        case 'delete':
            $stmt = $pdo->prepare("UPDATE gigs SET status = 'deleted' WHERE id = ?");
            $stmt->execute([$gig_id]);
            redirectWithMessage('/freelancer/gigs.php', 'Gig deleted successfully', 'success');
            break;
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    redirectWithMessage('/freelancer/gigs.php', 'An error occurred', 'danger');
}
