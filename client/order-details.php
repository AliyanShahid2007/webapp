<?php
$page_title = 'Order Details';
require_once __DIR__ . '/../includes/header.php';

// Check client access
requireRole('client');

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    header('Location: ' . BASE_PATH . '/client/orders.php');
    exit;
}

try {
    $pdo = getPDOConnection();

    // Get order details with client ownership check
    $stmt = $pdo->prepare("
        SELECT o.*,
               g.title as gig_title,
               g.description as gig_description,
               g.category,
               u.name as freelancer_name,
               u.email as freelancer_email,
               fp.profile_pic,
               fp.rating,
               fp.bio,
               fp.skills
        FROM orders o
        JOIN gigs g ON o.gig_id = g.id
        JOIN users u ON o.freelancer_id = u.id
        JOIN freelancer_profiles fp ON u.id = fp.user_id
        WHERE o.id = ? AND o.client_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        // Order not found or doesn't belong to user
        header('Location: ' . BASE_PATH . '/client/orders.php');
        exit;
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    header('Location: ' . BASE_PATH . '/client/orders.php');
    exit;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="color: var(--text-primary);">
            <i class="fas fa-shopping-cart"></i> Order Details
        </h2>
        <a href="<?php echo BASE_PATH; ?>/client/orders.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>

    <div class="row">
        <!-- Order Summary -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header" style="background: var(--bg-secondary); color: var(--text-primary);">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Order #<?php echo $order['id']; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 style="color: var(--text-primary);">Gig Details</h6>
                            <p style="color: var(--text-secondary);">
                                <strong><?php echo htmlspecialchars($order['gig_title']); ?></strong>
                            </p>
                            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                <?php echo htmlspecialchars(substr($order['gig_description'], 0, 200)); ?>...
                            </p>
                            <p style="color: var(--text-secondary);">
                                <i class="fas fa-tag"></i> Category: <?php echo htmlspecialchars($order['category']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 style="color: var(--text-primary);">Order Information</h6>
                            <p style="color: var(--text-secondary);">
                                <i class="fas fa-calendar"></i> Ordered: <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                            </p>
                            <p style="color: var(--text-secondary);">
                                <i class="fas fa-clock"></i> Delivery Time: <?php echo $order['delivery_time']; ?> days
                            </p>
                            <p style="color: var(--text-secondary);">
                                <i class="fas fa-dollar-sign"></i> Budget: $<?php echo number_format($order['budget'], 2); ?>
                            </p>
                            <p>
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
                                <span class="badge badge-<?php echo $badge_class; ?>" style="font-size: 0.9rem;">
                                    <i class="fas <?php echo $icon; ?>"></i>
                                    <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <?php if ($order['requirements']): ?>
                        <div class="mt-3">
                            <h6 style="color: var(--text-primary);">Requirements</h6>
                            <p style="color: var(--text-secondary);">
                                <?php echo nl2br(htmlspecialchars($order['requirements'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Freelancer Info -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header" style="background: var(--bg-secondary); color: var(--text-primary);">
                    <h5 class="mb-0">
                        <i class="fas fa-user"></i> Freelancer
                    </h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($order['profile_pic']): ?>
                        <img src="<?php echo $base_path; ?>/uploads/profiles/<?php echo htmlspecialchars($order['profile_pic']); ?>"
                             alt="<?php echo htmlspecialchars($order['freelancer_name']); ?>"
                             class="profile-image mb-3" style="width: 80px; height: 80px;">
                    <?php else: ?>
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--primary-color); display: inline-flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 2rem; margin-bottom: 1rem;">
                            <?php echo strtoupper(substr($order['freelancer_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>

                    <h6 style="color: var(--text-primary);">
                        <?php echo htmlspecialchars($order['freelancer_name']); ?>
                    </h6>

                    <div class="rating mb-2">
                        <i class="fas fa-star star"></i>
                        <?php echo number_format($order['rating'], 1); ?>
                    </div>

                    <?php if ($order['bio']): ?>
                        <p style="color: var(--text-secondary); font-size: 0.9rem;">
                            <?php echo htmlspecialchars(substr($order['bio'], 0, 150)); ?>...
                        </p>
                    <?php endif; ?>

                    <?php if ($order['skills']): ?>
                        <div class="mt-2">
                            <small style="color: var(--text-muted);">Skills:</small>
                            <p style="color: var(--text-secondary); font-size: 0.85rem;">
                                <?php echo htmlspecialchars($order['skills']); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <a href="mailto:<?php echo htmlspecialchars($order['freelancer_email']); ?>" class="btn btn-primary btn-sm mt-2">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header" style="background: var(--bg-secondary); color: var(--text-primary);">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs"></i> Actions
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($order['status'] == 'completed'): ?>
                        <a href="<?php echo $base_path; ?>/client/review.php?order_id=<?php echo $order['id']; ?>" class="btn btn-success btn-sm w-100 mb-2">
                            <i class="fas fa-star"></i> Leave Review
                        </a>
                    <?php endif; ?>

                    <a href="<?php echo BASE_PATH; ?>/client/orders.php" class="btn btn-secondary btn-sm w-100">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
