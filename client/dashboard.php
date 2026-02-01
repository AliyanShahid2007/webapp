<?php
$page_title = 'Client Dashboard';
require_once __DIR__ . '/../includes/header.php';

// Check client access
requireRole('client');

try {
    $conn = getDBConnection();

    // Get statistics
    $stats = [];

    // Total orders
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE client_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_orders'] = $result->fetch_assoc()['count'];
    $stmt->close();

    // Pending orders
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE client_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['pending_orders'] = $result->fetch_assoc()['count'];
    $stmt->close();

    // In progress orders
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE client_id = ? AND status IN ('accepted', 'in_progress')");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['in_progress'] = $result->fetch_assoc()['count'];
    $stmt->close();

    // Completed orders
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE client_id = ? AND status = 'completed'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['completed_orders'] = $result->fetch_assoc()['count'];
    $stmt->close();

    // Total spent
    $stmt = $conn->prepare("SELECT COALESCE(SUM(budget), 0) as total FROM orders WHERE client_id = ? AND status = 'completed'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_spent'] = $result->fetch_assoc()['total'];
    $stmt->close();

    // Recent orders
    $stmt = $conn->prepare("
        SELECT o.*,
               g.title as gig_title,
               u.name as freelancer_name,
               fp.profile_pic,
               fp.rating
        FROM orders o
        JOIN gigs g ON o.gig_id = g.id
        JOIN users u ON o.freelancer_id = u.id
        JOIN freelancer_profiles fp ON u.id = fp.user_id
        WHERE o.client_id = ?
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $recent_orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Recommended gigs
    $result = $conn->query("
        SELECT g.*,
               u.name as freelancer_name,
               fp.profile_pic,
               fp.rating,
               fp.total_reviews
        FROM gigs g
        JOIN users u ON g.freelancer_id = u.id
        JOIN freelancer_profiles fp ON u.id = fp.user_id
        WHERE g.status = 'active' AND u.status = 'active'
        ORDER BY fp.rating DESC, g.created_at DESC
        LIMIT 4
    ");
    $recommended_gigs = $result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log($e->getMessage());
    die('Error loading dashboard data');
}
?>

<div class="container-fluid mt-4">
    <div class="dashboard-header mb-4">
        <div class="container">
            <h1 style="color: white; margin-bottom: 0.5rem;">
                <i class="fas fa-tachometer-alt"></i> Client Dashboard
            </h1>
            <p style="color: rgba(255,255,255,0.9); margin-bottom: 0;">
                Welcome back, <?php echo htmlspecialchars($user_data['name']); ?>!
            </p>
        </div>
    </div>
    
    <div class="container">
        <!-- Statistics Cards -->
        <div class="row mb-4">
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
                        <i class="fas fa-clock"></i> Pending
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="stats-number" style="color: var(--primary-color);">
                        <?php echo $stats['in_progress']; ?>
                    </div>
                    <div class="stats-label">
                        <i class="fas fa-spinner"></i> In Progress
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="stats-number" style="color: var(--success-color);">
                        $<?php echo number_format($stats['total_spent'], 2); ?>
                    </div>
                    <div class="stats-label">
                        <i class="fas fa-dollar-sign"></i> Total Spent
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title" style="margin-bottom: 0;">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="<?php echo $base_path?>/browse-gigs.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Browse Gigs
                    </a>
                    <a href="<?php echo $base_path?>/client/orders.php" class="btn btn-secondary">
                        <i class="fas fa-shopping-cart"></i> My Orders
                    </a>
                    <a href="<?php echo $base_path?>/browse-gigs.php?sort=rating" class="btn btn-success">
                        <i class="fas fa-star"></i> Top Rated Gigs
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Orders -->
            <div class="col-md-7 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title" style="margin-bottom: 0;">
                            <i class="fas fa-history"></i> Recent Orders
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($recent_orders) > 0): ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                        <?php if ($order['profile_pic']): ?>
                                                <img src="<?php echo $base_path; ?>/uploads/profiles/<?php echo htmlspecialchars($order['profile_pic']); ?>"
                                                     alt="<?php echo htmlspecialchars($order['freelancer_name']); ?>"
                                                     class="profile-image">
                                            <?php else: ?>
                                                <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                    <?php echo strtoupper(substr($order['freelancer_name'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 style="color: var(--text-primary); margin-bottom: 0.25rem;">
                                                    <?php echo htmlspecialchars(substr($order['gig_title'], 0, 40)); ?>...
                                                </h6>
                                                <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                                    by <?php echo htmlspecialchars($order['freelancer_name']); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="display: flex; gap: 1rem; font-size: 0.9rem;">
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
                                                <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                            </span>
                                            <span style="color: var(--text-muted);">
                                                <?php echo timeAgo($order['created_at']); ?>
                                            </span>
                                            <span style="color: var(--success-color); font-weight: 600;">
                                                $<?php echo number_format($order['budget'], 2); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <a href="<?php echo BASE_PATH; ?>/client/order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center" style="padding: 2rem;">
                                <i class="fas fa-shopping-cart" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                                <p style="color: var(--text-secondary);">No orders yet. Start by browsing gigs!</p>
                                <a href="<?php echo BASE_PATH; ?>/browse-gigs.php" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Browse Gigs
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($recent_orders) > 0): ?>
                        <div class="card-footer">
                            <a href="<?php echo BASE_PATH; ?>/client/orders.php" class="btn btn-primary">
                                View All Orders
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recommended Gigs -->
            <div class="col-md-5 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title" style="margin-bottom: 0;">
                            <i class="fas fa-star"></i> Recommended Gigs
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($recommended_gigs as $gig): ?>
                            <div style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <h6 style="color: var(--text-primary); margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars(substr($gig['title'], 0, 50)); ?>...
                                </h6>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                            by <?php echo htmlspecialchars($gig['freelancer_name']); ?>
                                        </div>
                                        <div class="rating">
                                            <i class="fas fa-star star"></i>
                                            <span style="color: var(--text-secondary); font-size: 0.9rem;">
                                                <?php echo number_format($gig['rating'], 1); ?> (<?php echo $gig['total_reviews']; ?>)
                                            </span>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="color: var(--primary-color); font-weight: 700; font-size: 1.1rem;">
                                            $<?php echo number_format($gig['budget'], 2); ?>
                                        </div>
                                        <a href="<?php echo $base_path?>/gig-details.php?id=<?php echo $gig['id']; ?>" class="btn btn-primary btn-sm">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo $base_path?>/browse-gigs.php" class="btn btn-primary">
                            Browse All Gigs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
