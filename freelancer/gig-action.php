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

    // Handle edit action
    if ($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $category = sanitize($_POST['category'] ?? '');
        $budget = (float)($_POST['budget'] ?? 0);
        $delivery_time = (int)($_POST['delivery_time'] ?? 0);

        if (empty($title) || empty($description) || empty($category) || $budget <= 0 || $delivery_time <= 0) {
            redirectWithMessage('/freelancer/gigs.php?action=edit&id=' . $gig_id, 'All fields are required', 'danger');
        }

        // Handle image upload for edit
        $image_path = $gig['image']; // Keep existing image by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/gigs/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_extension, $allowed_extensions)) {
                redirectWithMessage('/freelancer/gigs.php?action=edit&id=' . $gig_id, 'Invalid image format. Only JPG, PNG, and GIF are allowed.', 'danger');
            }

            if ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB limit
                redirectWithMessage('/freelancer/gigs.php?action=edit&id=' . $gig_id, 'Image size too large. Maximum 5MB allowed.', 'danger');
            }

            $filename = uniqid('gig_') . '.' . $file_extension;
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Delete old image if exists
                if ($gig['image'] && file_exists(__DIR__ . '/../' . $gig['image'])) {
                    unlink(__DIR__ . '/../' . $gig['image']);
                }
                $image_path = 'uploads/gigs/' . $filename;
            } else {
                redirectWithMessage('/freelancer/gigs.php?action=edit&id=' . $gig_id, 'Failed to upload image.', 'danger');
            }
        }

        $stmt = $conn->prepare("
            UPDATE gigs SET title = ?, description = ?, category = ?, budget = ?, delivery_time = ?, image = ?
            WHERE id = ? AND freelancer_id = ?
        ");
        $stmt->bind_param("sssdissi", $title, $description, $category, $budget, $delivery_time, $image_path, $gig_id, $user_id);
        $stmt->execute();
        $stmt->close();

        redirectWithMessage('/freelancer/gigs.php', 'Gig updated successfully!', 'success');
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

                // Handle image upload for create
                $image_path = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/../uploads/gigs/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                    if (!in_array($file_extension, $allowed_extensions)) {
                        redirectWithMessage('/freelancer/gigs.php?action=create', 'Invalid image format. Only JPG, PNG, and GIF are allowed.', 'danger');
                    }

                    if ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB limit
                        redirectWithMessage('/freelancer/gigs.php?action=create', 'Image size too large. Maximum 5MB allowed.', 'danger');
                    }

                    $filename = uniqid('gig_') . '.' . $file_extension;
                    $target_path = $upload_dir . $filename;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                        $image_path = 'uploads/gigs/' . $filename;
                    } else {
                        redirectWithMessage('/freelancer/gigs.php?action=create', 'Failed to upload image.', 'danger');
                    }
                }

                $stmt = $conn->prepare("
                    INSERT INTO gigs (freelancer_id, title, description, category, budget, delivery_time, status, image)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $status = 'active';
                $stmt->bind_param("isssdiss", $user_id, $title, $description, $category, $budget, $delivery_time, $status, $image_path);
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
