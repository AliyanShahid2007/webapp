<?php
$page_title = 'My Orders';
require_once __DIR__ . '/../includes/header.php';

// Check client access
requireRole('client');

$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

try {
    $conn = getDBConnection();

    // Build query
    $where_clause = "o.client_id = ?";
    $params = [$user_id];

    if ($status_filter && in_array($status_filter, ['pending', 'accepted', 'in_progress', 'completed', 'canceled'])) {
        $where_clause .= " AND o.status = ?";
        $params[] = $status_filter;
    }

    // Get orders
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
        WHERE $where_clause
        ORDER BY o.created_at DESC
    ");
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get counts for filters
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM orders WHERE client_id = ? GROUP BY status");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $status_counts = [];
    while ($row = $result->fetch_assoc()) {
        $status_counts[$row['status']] = $row['count'];
    }
    $stmt->close();

} catch (Exception $e) {
    error_log($e->getMessage());
    $orders = [];
    $status_counts = [];
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="color: var(--text-primary);">
            <i class="fas fa-shopping-cart"></i> My Orders
        </h2>
        <a href="<?php echo $base_path ?>/browse-gigs.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Order New Gig
        </a>
    </div>
    
    <!-- Status Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <a href="<?php echo BASE_PATH; ?>/client/orders.php" 
                   class="btn <?php echo !$status_filter ? 'btn-primary' : 'btn-outline'; ?>">
                    <i class="fas fa-list"></i> All Orders
                    <span class="badge" style="background: var(--bg-tertiary); color: var(--text-primary); margin-left: 0.5rem;">
                        <?php echo array_sum($status_counts); ?>
                    </span>
                </a>
                <a href="<?php echo BASE_PATH; ?>/client/orders.php?status=pending" 
                   class="btn <?php echo $status_filter == 'pending' ? 'btn-warning' : 'btn-outline'; ?>">
                    <i class="fas fa-clock"></i> Pending
                    <?php if (isset($status_counts['pending'])): ?>
                        <span class="badge" style="background: var(--warning-color); color: white; margin-left: 0.5rem;">
                            <?php echo $status_counts['pending']; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo BASE_PATH; ?>/client/orders.php?status=in_progress" 
                   class="btn <?php echo $status_filter == 'in_progress' ? 'btn-primary' : 'btn-outline'; ?>">
                    <i class="fas fa-spinner"></i> In Progress
                    <?php if (isset($status_counts['in_progress'])): ?>
                        <span class="badge" style="background: var(--primary-color); color: white; margin-left: 0.5rem;">
                            <?php echo $status_counts['in_progress']; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo BASE_PATH; ?>/client/orders.php?status=completed" 
                   class="btn <?php echo $status_filter == 'completed' ? 'btn-success' : 'btn-outline'; ?>">
                    <i class="fas fa-check-circle"></i> Completed
                    <?php if (isset($status_counts['completed'])): ?>
                        <span class="badge" style="background: var(--success-color); color: white; margin-left: 0.5rem;">
                            <?php echo $status_counts['completed']; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Orders List -->
    <?php if (count($orders) > 0): ?>
        <div class="row">
            <?php foreach ($orders as $order): ?>
                <div class="col-md-12 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-1">
                                    <div style="text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">
                                            #<?php echo $order['id']; ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                                            ORDER ID
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-5">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
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
                                                <?php echo htmlspecialchars($order['gig_title']); ?>
                                            </h6>
                                            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0;">
                                                by <?php echo htmlspecialchars($order['freelancer_name']); ?>
                                                <span class="rating" style="margin-left: 0.5rem;">
                                                    <i class="fas fa-star star"></i>
                                                    <?php echo number_format($order['rating'], 1); ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-2 text-center">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">
                                        $<?php echo number_format($order['budget'], 2); ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                                        <i class="fas fa-clock"></i> <?php echo $order['delivery_time']; ?> days
                                    </div>
                                </div>
                                
                                <div class="col-md-2 text-center">
                                    <?php
                                    $badge_class = 'secondary';
                                    $icon = 'fa-circle';
                                    switch($order['status']) {
                                        case 'pending': 
                                            $badge_class = 'warning'; 
                                            $icon = 'fa-clock';
                                            break;
                                        case 'accepted': 
                                            $badge_class = 'info'; 
                                            $icon = 'fa-handshake';
                                            break;
                                        case 'in_progress': 
                                            $badge_class = 'primary'; 
                                            $icon = 'fa-spinner';
                                            break;
                                        case 'completed': 
                                            $badge_class = 'success'; 
                                            $icon = 'fa-check-circle';
                                            break;
                                        case 'canceled': 
                                            $badge_class = 'danger'; 
                                            $icon = 'fa-times-circle';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $badge_class; ?>" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                        <i class="fas <?php echo $icon; ?>"></i>
                                        <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                    </span>
                                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">
                                        <?php echo timeAgo($order['created_at']); ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-2 text-end">
                                    <a href="<?php echo $base_path; ?>/client/order-details.php?id=<?php echo $order['id']; ?>" 
                                       class="btn btn-primary btn-sm mb-1">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <?php if ($order['status'] == 'completed'): ?>
                                        <br>
                                        <a href="<?php echo $base_path; ?>/client/review.php?order_id=<?php echo $order['id']; ?>" 
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-star"></i> Leave Review
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card text-center" style="padding: 3rem;">
            <i class="fas fa-shopping-cart" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h4 style="color: var(--text-primary);">No Orders Found</h4>
            <p style="color: var(--text-secondary);">
                <?php if ($status_filter): ?>
                    No <?php echo $status_filter; ?> orders at the moment.
                <?php else: ?>
                    You haven't placed any orders yet. Start by browsing our gigs!
                <?php endif; ?>
            </p>
            <?php if (!$status_filter): ?>
                <a href="<?php echo $base_path ?>/browse-gigs.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Browse Gigs
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
