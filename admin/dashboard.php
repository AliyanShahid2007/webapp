<?php
$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';

// Check admin access
requireRole('admin');

try {
    $conn = getDBConnection();

    // Get statistics
    $stats = [];

    // Total users
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role != 'admin'");
    $stats['total_users'] = $result->fetch_assoc()['count'];

    // Pending approvals
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'pending'");
    $stats['pending_approvals'] = $result->fetch_assoc()['count'];

    // Active freelancers
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'freelancer' AND status = 'active'");
    $stats['active_freelancers'] = $result->fetch_assoc()['count'];

    // Active clients
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'client' AND status = 'active'");
    $stats['active_clients'] = $result->fetch_assoc()['count'];

    // Total gigs
    $result = $conn->query("SELECT COUNT(*) as count FROM gigs WHERE status = 'active'");
    $stats['total_gigs'] = $result->fetch_assoc()['count'];

    // Total orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $result->fetch_assoc()['count'];

    // Pending orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    $stats['pending_orders'] = $result->fetch_assoc()['count'];

    // Completed orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'");
    $stats['completed_orders'] = $result->fetch_assoc()['count'];

    // Recent users (pending approval)
    $result = $conn->query("SELECT * FROM users WHERE status = 'pending' ORDER BY created_at DESC LIMIT 10");
    $pending_users = $result->fetch_all(MYSQLI_ASSOC);

    // Recent orders
    $result = $conn->query("
        SELECT o.*,
               g.title as gig_title,
               c.name as client_name,
               f.name as freelancer_name
        FROM orders o
        JOIN gigs g ON o.gig_id = g.id
        JOIN users c ON o.client_id = c.id
        JOIN users f ON o.freelancer_id = f.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $recent_orders = $result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log($e->getMessage());
    die('Error loading dashboard data');
}
?>

<div class="container mt-4">
    <div class="dashboard-header">
        <div class="container">
            <h1 style="color: white; margin-bottom: 0.5rem;">
                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
            </h1>
            <p style="color: rgba(255,255,255,0.9); margin-bottom: 0;">
                Welcome back, <?php echo htmlspecialchars($user_data['name']); ?>
            </p>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="stats-number"><?php echo $stats['total_users']; ?></div>
                <div class="stats-label">
                    <i class="fas fa-users"></i> Total Users
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="stats-number" style="color: var(--warning-color);">
                    <?php echo $stats['pending_approvals']; ?>
                </div>
                <div class="stats-label">
                    <i class="fas fa-clock"></i> Pending Approvals
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="stats-number" style="color: var(--success-color);">
                    <?php echo $stats['active_freelancers']; ?>
                </div>
                <div class="stats-label">
                    <i class="fas fa-laptop-code"></i> Active Freelancers
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="stats-number" style="color: var(--info-color);">
                    <?php echo $stats['active_clients']; ?>
                </div>
                <div class="stats-label">
                    <i class="fas fa-user-tie"></i> Active Clients
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="stats-number"><?php echo $stats['total_gigs']; ?></div>
                <div class="stats-label">
                    <i class="fas fa-briefcase"></i> Active Gigs
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="stats-number"><?php echo $stats['total_orders']; ?></div>
                <div class="stats-label">
                    <i class="fas fa-shopping-cart"></i> Total Orders
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="stats-number" style="color: var(--warning-color);">
                    <?php echo $stats['pending_orders']; ?>
                </div>
                <div class="stats-label">
                    <i class="fas fa-hourglass-half"></i> Pending Orders
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card stats-card">
                <div class="stats-number" style="color: var(--success-color);">
                    <?php echo $stats['completed_orders']; ?>
                </div>
                <div class="stats-label">
                    <i class="fas fa-check-circle"></i> Completed Orders
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Approvals -->
    <?php if (count($pending_users) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title">
                <i class="fas fa-user-clock"></i> Pending Approvals
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $user['role'] == 'freelancer' ? 'primary' : 'secondary'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo timeAgo($user['created_at']); ?></td>
                            <td>
                                <a href="<?php echo $base_path; ?>/admin/user-action.php?action=approve&id=<?php echo $user['id']; ?>"
                                   class="btn btn-success btn-sm" data-confirm="Approve this user?">
                                    <i class="fas fa-check"></i> Approve
                                </a>
                                <a href="<?php echo $base_path; ?>/admin/user-action.php?action=reject&id=<?php echo $user['id']; ?>"
                                   class="btn btn-danger btn-sm" data-confirm="Reject this user?">
                                    <i class="fas fa-times"></i> Reject
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?php echo $base_path; ?>/admin/users.php?status=pending" class="btn btn-primary">
                View All Pending Users
            </a>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Recent Orders -->
    <?php if (count($recent_orders) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title">
                <i class="fas fa-shopping-cart"></i> Recent Orders
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Gig</th>
                            <th>Client</th>
                            <th>Freelancer</th>
                            <th>Budget</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars(substr($order['gig_title'], 0, 40)); ?>...</td>
                            <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['freelancer_name']); ?></td>
                            <td>$<?php echo number_format($order['budget'], 2); ?></td>
                            <td>
                                <?php
                                $badge_class = 'secondary';
                                switch($order['status']) {
                                    case 'pending': $badge_class = 'warning'; break;
                                    case 'accepted': $badge_class = 'info'; break;
                                    case 'in_progress': $badge_class = 'primary'; break;
                                    case 'completed': $badge_class = 'success'; break;
                                    case 'canceled': $badge_class = 'danger'; break;
                                }
                                ?>
                                <span class="badge badge-<?php echo $badge_class; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo timeAgo($order['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?php echo $base_path; ?>/admin/orders.php" class="btn btn-primary">
                View All Orders
            </a>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                <i class="fas fa-bolt"></i> Quick Actions
            </h4>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="<?php echo $base_path; ?>/admin/users.php" class="btn btn-primary">
                    <i class="fas fa-users"></i> Manage Users
                </a>
                <a href="<?php echo $base_path; ?>/admin/gigs.php" class="btn btn-primary">
                    <i class="fas fa-briefcase"></i> Manage Gigs
                </a>
                <a href="<?php echo $base_path; ?>/admin/orders.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Manage Orders
                </a>
                <a href="<?php echo $base_path; ?>/admin/categories.php" class="btn btn-primary">
                    <i class="fas fa-tags"></i> Manage Categories
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
