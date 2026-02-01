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
    $conn = getDBConnection();

    // Get gig data
    $stmt = $conn->prepare("SELECT * FROM gigs WHERE id = ?");
    $stmt->bind_param("i", $gig_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $gig = $result->fetch_assoc();
    $stmt->close();

    if (!$gig) {
        redirectWithMessage('/admin/gigs.php', 'Gig not found', 'danger');
    }

    switch ($action) {
        case 'activate':
            $stmt = $conn->prepare("UPDATE gigs SET status = 'active' WHERE id = ?");
            $stmt->bind_param("i", $gig_id);
            $stmt->execute();
            $stmt->close();
            // Reset deactivated_by_admin flag separately
            $stmt = $conn->prepare("UPDATE gigs SET deactivated_by_admin = 0 WHERE id = ?");
            $stmt->bind_param("i", $gig_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/admin/gigs.php', 'Gig activated successfully', 'success');
            break;

        case 'deactivate':
            $stmt = $conn->prepare("UPDATE gigs SET status = 'inactive' WHERE id = ?");
            $stmt->bind_param("i", $gig_id);
            $stmt->execute();
            $stmt->close();
            // Set deactivated_by_admin flag separately
            $stmt = $conn->prepare("UPDATE gigs SET deactivated_by_admin = 1 WHERE id = ?");
            $stmt->bind_param("i", $gig_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/admin/gigs.php', 'Gig deactivated successfully', 'success');
            break;

        case 'delete':
            $stmt = $conn->prepare("UPDATE gigs SET status = 'deleted' WHERE id = ?");
            $stmt->bind_param("i", $gig_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/admin/gigs.php', 'Gig deleted successfully', 'success');
            break;
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    redirectWithMessage('/admin/gigs.php', 'An error occurred while processing the action: ' . $e->getMessage(), 'danger');
}
