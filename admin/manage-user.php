<?php
$page_title = 'Manage User';
require_once '../includes/header.php';

// Check admin access
requireRole('admin');

$user_id = (int)($_GET['id'] ?? 0);

if (!$user_id) {
    redirectWithMessage('/admin/dashboard.php', 'Invalid user ID', 'danger');
}

try {
    $conn = getDBConnection();

    // Get user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        redirectWithMessage('/admin/dashboard.php', 'User not found', 'danger');
    }

    // Don't allow editing admin users
    if ($user['role'] === 'admin') {
        redirectWithMessage('/admin/dashboard.php', 'Cannot edit admin users', 'danger');
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    redirectWithMessage('/admin/dashboard.php', 'Database error', 'danger');
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mt-5">
        <h1>Manage User: <?php echo htmlspecialchars($user['name']); ?></h1>
        <a href="<?php echo BASE_PATH; ?>/admin/users.php" class="btn btn-outline">Back to Users</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?php echo BASE_PATH; ?>/admin/user-action.php?action=update&id=<?php echo $user['id']; ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>Client</option>
                        <option value="freelancer" <?php echo $user['role'] === 'freelancer' ? 'selected' : ''; ?>>Freelancer</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="pending" <?php echo $user['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="suspended_7days" <?php echo $user['status'] === 'suspended_7days' ? 'selected' : ''; ?>>Suspended 7 Days</option>
                        <option value="suspended_permanent" <?php echo $user['status'] === 'suspended_permanent' ? 'selected' : ''; ?>>Suspended Permanent</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update User</button>
            </form>

            <hr>

            <h5>Quick Actions</h5>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?php echo BASE_PATH; ?>/admin/user-action.php?action=activate&id=<?php echo $user['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to activate this user?')">Activate</a>
                <a href="<?php echo BASE_PATH; ?>/admin/user-action.php?action=suspend_7days&id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm" onclick="return confirm('Are you sure you want to suspend this user for 7 days?')">Suspend 7 Days</a>
                <a href="<?php echo BASE_PATH; ?>/admin/user-action.php?action=suspend_permanent&id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to permanently suspend this user?')">Suspend Permanent</a>
                <a href="<?php echo BASE_PATH; ?>/admin/user-action.php?action=reject&id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject and delete this user?')">Reject & Delete</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
