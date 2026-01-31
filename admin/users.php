<?php
$page_title = 'Users Management';
require_once '../includes/header.php';

if (!isLoggedIn() || getCurrentUserRole() != 'admin') {
    header('Location: ' . BASE_PATH . '/login.php');
    exit;
}

// Get users
try {
    $pdo = getPDOConnection();
    $stmt = $pdo->query("
        SELECT * FROM users
        ORDER BY created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    error_log($e->getMessage());
    $users = [];
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mt-5">
        <h1>Users Management</h1>
        <a href="<?php echo BASE_PATH; ?>/admin/dashboard.php" class="btn btn-outline">Back to Dashboard</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($user['role']); ?></span></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : ($user['status'] == 'pending' ? 'warning' : 'danger'); ?>">
                                        <?php echo htmlspecialchars($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo BASE_PATH; ?>/admin/user-action.php?action=manage&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Manage</a>
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
