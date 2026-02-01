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
    $conn = getDBConnection();

    // Verify ownership for edit/delete actions
    if (in_array($action, ['edit', 'delete', 'activate', 'deactivate']) && $gig_id) {
        $stmt = $conn->prepare("SELECT * FROM gigs WHERE id = ? AND freelancer_id = ?");
        $stmt->bind_param("ii", $gig_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $gig = $result->fetch_assoc();
        $stmt->close();

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

                $stmt = $conn->prepare("
                    INSERT INTO gigs (freelancer_id, title, description, category, budget, delivery_time, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $status = 'active';
                $stmt->bind_param("isssdis", $user_id, $title, $description, $category, $budget, $delivery_time, $status);
                $stmt->execute();
                $stmt->close();

                redirectWithMessage('/freelancer/gigs.php', 'Gig created successfully!', 'success');
            }
            break;

        case 'activate':
            // Check if gig was deactivated by admin
            if ($gig['deactivated_by_admin']) {
                redirectWithMessage('/freelancer/gigs.php', 'This gig was deactivated by an admin and cannot be reactivated', 'danger');
            }
            $stmt = $conn->prepare("UPDATE gigs SET status = 'active' WHERE id = ?");
            $stmt->bind_param("i", $gig_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/freelancer/gigs.php', 'Gig activated successfully', 'success');
            break;

        case 'deactivate':
            $stmt = $conn->prepare("UPDATE gigs SET status = 'inactive' WHERE id = ?");
            $stmt->bind_param("i", $gig_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/freelancer/gigs.php', 'Gig deactivated successfully', 'success');
            break;

        case 'delete':
            $stmt = $conn->prepare("UPDATE gigs SET status = 'deleted' WHERE id = ?");
            $stmt->bind_param("i", $gig_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/freelancer/gigs.php', 'Gig deleted successfully', 'success');
            break;
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    redirectWithMessage('/freelancer/gigs.php', 'An error occurred', 'danger');
}
