<?php
$page_title = 'Orders Management';
require_once '../includes/header.php';

$base_path = '/webapp';

if (!isLoggedIn() || getCurrentUserRole() != 'admin') {
    header('Location: ' . $base_path . 'login.php');
    exit;
}

// Get orders
try {
    $conn = getDBConnection();
    $result = $conn->query("
        SELECT o.*, u1.name as client_name, u2.name as freelancer_name, g.title as gig_title
        FROM orders o
        JOIN users u1 ON o.client_id = u1.id
        JOIN users u2 ON o.freelancer_id = u2.id
        JOIN gigs g ON o.gig_id = g.id
        ORDER BY o.created_at DESC
    ");
    $orders = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $orders = [];
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mt-5">
        <h1>Orders Management</h1>
        <a href="<?php echo $base_path;?>/admin/dashboard.php" class="btn btn-outline">Back to Dashboard</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gig</th>
                            <th>Client</th>
                            <th>Freelancer</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['gig_title']); ?></td>
                                <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['freelancer_name']); ?></td>
                                <td>$<?php echo number_format($order['budget'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $order['status'] == 'completed' ? 'success' : 
                                             ($order['status'] == 'pending' ? 'warning' : 
                                              ($order['status'] == 'cancelled' ? 'danger' : 'info'));
                                    ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo BASE_PATH; ?>/gig-details.php?id=<?php echo $order['gig_id']; ?>" class="btn btn-sm btn-primary">View</a>
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
