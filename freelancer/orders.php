<?php
$page_title = 'My Orders';
require_once __DIR__ . '/../includes/header.php';

// Check freelancer access
requireRole('freelancer');

$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

try {
    $conn = getDBConnection();

    // Build query
    $where_clause = "o.freelancer_id = ?";
    $params = [$user_id];
    $types = "i";

    if ($status_filter && in_array($status_filter, ['pending', 'accepted', 'in_progress', 'completed', 'canceled'])) {
        $where_clause .= " AND o.status = ?";
        $params[] = $status_filter;
        $types .= "s";
    }

    // Get orders
    $stmt = $conn->prepare("
        SELECT o.*,
               g.title as gig_title,
               u.name as client_name,
               u.email as client_email
        FROM orders o
        JOIN gigs g ON o.gig_id = g.id
        JOIN users u ON o.client_id = u.id
        WHERE $where_clause
        ORDER BY o.created_at DESC
    ");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get counts for filters
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM orders WHERE freelancer_id = ? GROUP BY status");
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
    </div>
    
    <!-- Status Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <a href="<?php echo BASE_PATH; ?>/freelancer/orders.php"
                   class="btn <?php echo !$status_filter ? 'btn-primary' : 'btn-outline'; ?>">
                    <i class="fas fa-list"></i> All Orders
                    <span class="badge" style="background: var(--bg-tertiary); color: var(--text-primary); margin-left: 0.5rem;">
                        <?php echo array_sum($status_counts); ?>
                    </span>
                </a>
                <a href="<?php echo BASE_PATH; ?>/freelancer/orders.php?status=pending"
                   class="btn <?php echo $status_filter == 'pending' ? 'btn-warning' : 'btn-outline'; ?>">
                    <i class="fas fa-clock"></i> Pending
                    <?php if (isset($status_counts['pending'])): ?>
                        <span class="badge" style="background: var(--warning-color); color: white; margin-left: 0.5rem;">
                            <?php echo $status_counts['pending']; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo BASE_PATH; ?>/freelancer/orders.php?status=in_progress"
                   class="btn <?php echo $status_filter == 'in_progress' ? 'btn-primary' : 'btn-outline'; ?>">
                    <i class="fas fa-spinner"></i> In Progress
                    <?php if (isset($status_counts['in_progress'])): ?>
                        <span class="badge" style="background: var(--primary-color); color: white; margin-left: 0.5rem;">
                            <?php echo $status_counts['in_progress']; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo BASE_PATH; ?>/freelancer/orders.php?status=completed"
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
                                
                                <div class="col-md-4">
                                    <h6 style="color: var(--text-primary); margin-bottom: 0.5rem;">
                                        <?php echo htmlspecialchars($order['gig_title']); ?>
                                    </h6>
                                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0;">
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($order['client_name']); ?>
                                    </p>
                                </div>
                                
                                <div class="col-md-2 text-center">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">
                                        $<?php echo number_format($order['budget'], 2); ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                                        Budget
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
                                
                                <div class="col-md-3 text-end">
                                    <?php if ($order['status'] == 'pending'): ?>
                                        <a href="<?php echo BASE_PATH; ?>/freelancer/order-action.php?action=accept&id=<?php echo $order['id']; ?>"
                                           class="btn btn-success btn-sm mb-1"
                                           data-confirm="Accept this order?">
                                            <i class="fas fa-check"></i> Accept
                                        </a>
                                        <a href="<?php echo BASE_PATH; ?>/freelancer/order-action.php?action=reject&id=<?php echo $order['id']; ?>"
                                           class="btn btn-danger btn-sm mb-1"
                                           data-confirm="Reject this order?">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                    <?php elseif ($order['status'] == 'accepted'): ?>
                                        <a href="<?php echo BASE_PATH; ?>/freelancer/order-action.php?action=start&id=<?php echo $order['id']; ?>"
                                           class="btn btn-primary btn-sm mb-1">
                                            <i class="fas fa-play"></i> Start Work
                                        </a>
                                    <?php elseif ($order['status'] == 'in_progress'): ?>
                                        <a href="<?php echo BASE_PATH; ?>/freelancer/order-action.php?action=complete&id=<?php echo $order['id']; ?>" 
                                           class="btn btn-success btn-sm mb-1"
                                           data-confirm="Mark as completed?">
                                            <i class="fas fa-check-circle"></i> Complete
                                        </a>
                                    <?php endif; ?>
                                    <br>
                                    <a href="<?php echo BASE_PATH; ?>/freelancer/order-details.php?id=<?php echo $order['id']; ?>"
                                       class="btn btn-outline btn-sm">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                            
                            <?php if ($order['client_notes']): ?>
                                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                                    <strong style="color: var(--text-primary);">Client Notes:</strong>
                                    <p style="color: var(--text-secondary); margin-bottom: 0; margin-top: 0.5rem;">
                                        <?php echo htmlspecialchars($order['client_notes']); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
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
                    You haven't received any orders yet.
                <?php endif; ?>
            </p>
            <?php if (!$status_filter): ?>
                <a href="<?php echo BASE_PATH; ?>/freelancer/gigs.php" class="btn btn-primary">
                    <i class="fas fa-briefcase"></i> View My Gigs
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
