<?php
require_once __DIR__ . '/../includes/header.php';

// Check admin access
requireRole('admin');

$action = $_GET['action'] ?? '';
$gig_id = (int)($_GET['id'] ?? 0);

if (!$gig_id || !in_array($action, ['activate', 'deactivate', 'delete'])) {
    redirectWithMessage('/admin/gigs.php', 'Invalid action', 'danger');
}

try {
    $pdo = getPDOConnection();

    // Get gig data
    $stmt = $pdo->prepare("SELECT * FROM gigs WHERE id = ?");
    $stmt->execute([$gig_id]);
    $gig = $stmt->fetch();

    if (!$gig) {
        redirectWithMessage('/admin/gigs.php', 'Gig not found', 'danger');
    }

    switch ($action) {
        case 'activate':
            $stmt = $pdo->prepare("UPDATE gigs SET status = 'active' WHERE id = ?");
            $stmt->execute([$gig_id]);
            // Reset deactivated_by_admin flag separately
            $stmt = $pdo->prepare("UPDATE gigs SET deactivated_by_admin = FALSE WHERE id = ?");
            $stmt->execute([$gig_id]);
            redirectWithMessage('/admin/gigs.php', 'Gig activated successfully', 'success');
            break;

        case 'deactivate':
            $stmt = $pdo->prepare("UPDATE gigs SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$gig_id]);
            // Set deactivated_by_admin flag separately
            $stmt = $pdo->prepare("UPDATE gigs SET deactivated_by_admin = TRUE WHERE id = ?");
            $stmt->execute([$gig_id]);
            redirectWithMessage('/admin/gigs.php', 'Gig deactivated successfully', 'success');
            break;

        case 'delete':
            $stmt = $pdo->prepare("UPDATE gigs SET status = 'deleted' WHERE id = ?");
            $stmt->execute([$gig_id]);
            redirectWithMessage('/admin/gigs.php', 'Gig deleted successfully', 'success');
            break;
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    redirectWithMessage('/admin/gigs.php', 'An error occurred while processing the action: ' . $e->getMessage(), 'danger');
}
