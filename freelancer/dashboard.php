<?php
$page_title = 'Freelancer Dashboard';
require_once __DIR__ . '/../includes/header.php';

// Check freelancer access
requireRole('freelancer');

try {
    $conn = getDBConnection();

    // Get freelancer profile
    $stmt = $conn->prepare("SELECT * FROM freelancer_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
    $stmt->close();

    // If profile doesn't exist, initialize with defaults
    if (!$profile) {
        $profile = [
            'bio' => '',
            'category' => '',
            'skills' => '',
            'profile_pic' => null,
            'rating' => 0.0,
            'total_reviews' => 0
        ];
    }

    // Get statistics
    $stats = [];

    // Total gigs
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM gigs WHERE freelancer_id = ? AND status = 'active'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['total_gigs'] = $row['count'];
    $stmt->close();

    // Total orders
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE freelancer_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['total_orders'] = $row['count'];
    $stmt->close();

    // Pending orders
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE freelancer_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['pending_orders'] = $row['count'];
    $stmt->close();

    // Completed orders
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE freelancer_id = ? AND status = 'completed'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['completed_orders'] = $row['count'];
    $stmt->close();

    // Total earnings (sum of completed orders)
    $stmt = $conn->prepare("SELECT COALESCE(SUM(budget), 0) as total FROM orders WHERE freelancer_id = ? AND status = 'completed'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['total_earnings'] = $row['total'];
    $stmt->close();

    // Recent orders
    $stmt = $conn->prepare("
        SELECT o.*,
               g.title as gig_title,
               u.name as client_name
        FROM orders o
        JOIN gigs g ON o.gig_id = g.id
        JOIN users u ON o.client_id = u.id
        WHERE o.freelancer_id = ?
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $recent_orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Profile completeness
    $completeness = 0;
    if ($profile['bio']) $completeness += 20;
    if ($profile['category']) $completeness += 20;
    if ($profile['skills']) $completeness += 20;
    if ($profile['profile_pic']) $completeness += 20;
    if ($stats['total_gigs'] > 0) $completeness += 20;

    // Update profile completeness
    $stmt = $conn->prepare("UPDATE freelancer_profiles SET profile_completeness = ? WHERE user_id = ?");
    $stmt->bind_param("ii", $completeness, $user_id);
    $stmt->execute();
    $stmt->close();

} catch (Exception $e) {
    error_log($e->getMessage());
    die('Error loading dashboard data');
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="profile-container">
                        <?php if ($profile['profile_pic']): ?>
                            <img src="<?php echo BASE_PATH; ?>/uploads/profiles/<?php echo htmlspecialchars($profile['profile_pic']); ?>"
                                 alt="<?php echo htmlspecialchars($user_data['name']); ?>"
                                 class="profile-image profile-image-lg">
                        <?php else: ?>
                            <div class="profile-image profile-image-lg" style="background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: 600;">
                                <?php echo strtoupper(substr($user_data['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h5 style="color: var(--text-primary); margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($user_data['name']); ?>
                    </h5>
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                        @<?php echo htmlspecialchars($user_data['username']); ?>
                    </p>
                    
                    <div class="rating justify-content-center mb-3">
                        <i class="fas fa-star star"></i>
                        <span style="color: var(--text-primary); font-weight: 600; font-size: 1.1rem;">
                            <?php echo number_format($profile['rating'], 1); ?>
                        </span>
                        <span style="color: var(--text-secondary);">
                            (<?php echo $profile['total_reviews']; ?> reviews)
                        </span>
                    </div>
                    
                    <a href="<?php echo BASE_PATH; ?>/freelancer/profile.php" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                    <a href="<?php echo BASE_PATH; ?>/freelancer-profile.php?id=<?php echo $user_id; ?>" class="btn btn-outline btn-block" target="_blank">
                        <i class="fas fa-eye"></i> View Public Profile
                    </a>
                </div>
            </div>
            
            <!-- Profile Completeness -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 style="color: var(--text-primary); margin-bottom: 1rem;">
                        <i class="fas fa-tasks"></i> Profile Completeness
                    </h6>
                    <div class="progress" style="height: 10px; background: var(--bg-tertiary); border-radius: 10px;">
                        <div class="progress-bar" style="width: <?php echo $completeness; ?>%; background: var(--primary-color); border-radius: 10px;"></div>
                    </div>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">
                        <?php echo $completeness; ?>% complete
                    </p>
                    
                    <?php if ($completeness < 100): ?>
                        <div style="margin-top: 1rem;">
                            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.5rem;">
                                <strong>To improve:</strong>
                            </p>
                            <ul style="font-size: 0.85rem; color: var(--text-secondary); padding-left: 1.2rem;">
                                <?php if (!$profile['bio']): ?>
                                    <li>Add bio</li>
                                <?php endif; ?>
                                <?php if (!$profile['category']): ?>
                                    <li>Add category</li>
                                <?php endif; ?>
                                <?php if (!$profile['skills']): ?>
                                    <li>Add skills</li>
                                <?php endif; ?>
                                <?php if (!$profile['profile_pic']): ?>
                                    <li>Upload profile picture</li>
                                <?php endif; ?>
                                <?php if ($stats['total_gigs'] == 0): ?>
                                    <li>Create your first gig</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 style="color: var(--text-primary); margin-bottom: 1rem;">
                        <i class="fas fa-link"></i> Quick Links
                    </h6>
                    <div class="d-flex flex-column gap-2">
                        <a href="<?php echo BASE_PATH; ?>/freelancer/gigs.php" class="btn btn-sm" style="background: var(--bg-tertiary); color: var(--text-primary); text-align: left;">
                            <i class="fas fa-briefcase"></i> My Gigs
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/freelancer/orders.php" class="btn btn-sm" style="background: var(--bg-tertiary); color: var(--text-primary); text-align: left;">
                            <i class="fas fa-shopping-cart"></i> Orders
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/browse-gigs.php" class="btn btn-sm" style="background: var(--bg-tertiary); color: var(--text-primary); text-align: left;">
                            <i class="fas fa-search"></i> Browse Gigs
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="dashboard-header mb-4">
                <div class="container">
                    <h1 style="color: white; margin-bottom: 0.5rem;">
                        <i class="fas fa-tachometer-alt"></i> Freelancer Dashboard
                    </h1>
                    <p style="color: rgba(255,255,255,0.9); margin-bottom: 0;">
                        Welcome back, <?php echo htmlspecialchars($user_data['name']); ?>!
                    </p>
                </div>
            </div>
            
            <!-- Statistics Cards -->
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
                            <i class="fas fa-clock"></i> Pending Orders
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card stats-card">
                        <div class="stats-number" style="color: var(--success-color);">
                            $<?php echo number_format($stats['total_earnings'], 2); ?>
                        </div>
                        <div class="stats-label">
                            <i class="fas fa-dollar-sign"></i> Total Earnings
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
                        <a href="<?php echo BASE_PATH; ?>/freelancer/gigs.php?action=create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create New Gig
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/freelancer/profile.php" class="btn btn-secondary">
                            <i class="fas fa-user-edit"></i> Update Profile
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/freelancer/orders.php?status=pending" class="btn btn-warning">
                            <i class="fas fa-clock"></i> View Pending Orders
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <?php if (count($recent_orders) > 0): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title" style="margin-bottom: 0;">
                        <i class="fas fa-history"></i> Recent Orders
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Gig</th>
                                    <th>Client</th>
                                    <th>Budget</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars(substr($order['gig_title'], 0, 30)); ?>...</td>
                                    <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                                    <td><strong>$<?php echo number_format($order['budget'], 2); ?></strong></td>
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
                                            <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo timeAgo($order['created_at']); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_PATH; ?>/freelancer/order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?php echo BASE_PATH?>/freelancer/orders.php" class="btn btn-primary">
                        View All Orders
                    </a>
                </div>
            </div>
            <?php else: ?>
            <div class="card text-center" style="padding: 3rem;">
                <i class="fas fa-shopping-cart" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                <h4 style="color: var(--text-primary);">No Orders Yet</h4>
                <p style="color: var(--text-secondary);">
                    Start creating gigs to receive orders from clients
                </p>
               <a href="<?php echo BASE_PATH; ?>/freelancer/gigs.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Your First Gig
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
