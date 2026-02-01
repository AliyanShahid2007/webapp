<?php
$page_title = 'Gigs Management';
require_once '../includes/header.php';

$base_path = '/webapp';

if (!isLoggedIn() || getCurrentUserRole() != 'admin') {
    header('Location: ' . $base_path . 'login.php');
    exit;
}

// Get gigs
try {
    $conn = getDBConnection();
    $result = $conn->query("
        SELECT g.*, u.name as freelancer_name
        FROM gigs g
        JOIN users u ON g.freelancer_id = u.id
        ORDER BY g.created_at DESC
    ");
    $gigs = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $gigs = [];
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mt-5">
        <h1>Gigs Management</h1>
        <a href="<?php echo BASE_PATH; ?>/admin/dashboard.php" class="btn btn-outline">Back to Dashboard</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Freelancer</th>
                            <th>Budget</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gigs as $gig): ?>
                            <tr>
                                <td><?php echo $gig['id']; ?></td>
                                <td><?php echo htmlspecialchars($gig['title']); ?></td>
                                <td><?php echo htmlspecialchars($gig['freelancer_name']); ?></td>
                                <td>$<?php echo number_format($gig['budget'], 2); ?></td>
                                <td><?php echo htmlspecialchars($gig['status']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($gig['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo BASE_PATH; ?>/gig-details.php?id=<?php echo $gig['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    <?php if ($gig['status'] == 'active'): ?>
                                        <a href="<?php echo BASE_PATH; ?>/admin/gig-action.php?action=deactivate&id=<?php echo $gig['id']; ?>" class="btn btn-sm btn-warning">Deactivate</a>
                                    <?php elseif ($gig['status'] == 'inactive'): ?>
                                        <a href="<?php echo BASE_PATH; ?>/admin/gig-action.php?action=activate&id=<?php echo $gig['id']; ?>" class="btn btn-sm btn-success">Activate</a>
                                    <?php endif; ?>
                                    <a href="<?php echo BASE_PATH; ?>/admin/gig-action.php?action=delete&id=<?php echo $gig['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this gig?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
