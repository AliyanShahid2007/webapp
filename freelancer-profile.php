<?php
$page_title = 'Freelancer Profile';
require_once 'includes/header.php';

// Get database connection
$pdo = getPDOConnection();

// Get freelancer ID from URL
$freelancer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($freelancer_id == 0) {
    setFlashMessage('Invalid freelancer ID', 'error');
    header('Location: ' . $base_path . '/browse-gigs.php');
    exit;
}

// Get freelancer user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'freelancer'");
$stmt->execute([$freelancer_id]);
$freelancer = $stmt->fetch();

if (!$freelancer) {
    setFlashMessage('Freelancer not found', 'error');
    header('Location: /browse-gigs.php');
    exit;
}

// Get freelancer profile
$stmt = $pdo->prepare("SELECT * FROM freelancer_profiles WHERE user_id = ?");
$stmt->execute([$freelancer_id]);
$profile = $stmt->fetch();

// Get freelancer gigs
$stmt = $pdo->prepare("SELECT * FROM gigs WHERE freelancer_id = ? AND status = 'active' ORDER BY created_at DESC");
$stmt->execute([$freelancer_id]);
$gigs = $stmt->fetchAll();

// Get freelancer stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE freelancer_id = ? AND status = 'completed'");
$stmt->execute([$freelancer_id]);
$stats = $stmt->fetch();

// Calculate average rating
$rating = $profile['rating'] ?? 0;
$total_orders = $stats['total_orders'] ?? 0;
?>

<div class="container py-5">
    <div class="row">
        <!-- Profile Sidebar -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm animate-on-scroll">
                <div class="card-body text-center">
                    <!-- Profile Picture -->
                    <div class="profile-picture-large mb-3 d-flex justify-content-center">
                        <?php if ($profile && $profile['profile_pic']): ?>
                            <img src="<?php echo $base_path; ?>/uploads/profiles/<?php echo htmlspecialchars($profile['profile_pic']); ?>"
                                 alt="<?php echo htmlspecialchars($freelancer['name']); ?>"
                                 class="rounded-circle img-fluid"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 150px; height: 150px; font-size: 3rem;">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Name and Rating -->
                    <h3 class="mb-2"><?php echo htmlspecialchars($freelancer['name']); ?></h3>
                    <div class="mb-3">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $rating ? 'text-warning' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                        <span class="text-muted ms-2">(<?php echo number_format($rating, 1); ?>)</span>
                    </div>

                    <!-- Stats -->
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="stat-box p-3 border rounded">
                                <h4 class="mb-0 text-primary"><?php echo $total_orders; ?></h4>
                                <small class="text-muted">Completed Orders</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box p-3 border rounded">
                                <h4 class="mb-0 text-success"><?php echo count($gigs); ?></h4>
                                <small class="text-muted">Active Gigs</small>
                            </div>
                        </div>
                    </div>

                    <!-- Category -->
                    <?php if ($profile && $profile['category']): ?>
                    <div class="mb-3">
                        <span class="badge bg-primary"><?php echo htmlspecialchars($profile['category']); ?></span>
                    </div>
                    <?php endif; ?>

                    <!-- Contact Button (Only for logged-in clients) -->
                    <?php if ($is_logged_in && $user_role == 'client'): ?>
                    <a href="#" class="btn btn-primary btn-hover-lift w-100">
                        <i class="fas fa-envelope me-2"></i>Contact Freelancer
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Skills Card -->
            <?php if ($profile && $profile['skills']): ?>
            <div class="card shadow-sm mt-4 animate-on-scroll">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Skills</h5>
                </div>
                <div class="card-body">
                    <?php 
                    $skills = explode(',', $profile['skills']);
                    foreach ($skills as $skill): 
                    ?>
                        <span class="badge bg-light text-dark border mb-2 me-1"><?php echo htmlspecialchars(trim($skill)); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Bio Section -->
            <?php if ($profile && $profile['bio']): ?>
            <div class="card shadow-sm mb-4 animate-on-scroll">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>About</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Portfolio Section -->
            <?php if ($profile && $profile['portfolio_images']): ?>
            <div class="card shadow-sm mb-4 animate-on-scroll">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-images me-2"></i>Portfolio</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php 
                        $portfolio = json_decode($profile['portfolio_images'], true);
                        if (is_array($portfolio)):
                            foreach ($portfolio as $image): 
                        ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="portfolio-item">
                                    <img src="/uploads/portfolio/<?php echo htmlspecialchars($image); ?>" 
                                         alt="Portfolio" 
                                         class="img-fluid rounded shadow-sm hover-zoom"
                                         style="height: 200px; width: 100%; object-fit: cover; cursor: pointer;">
                                </div>
                            </div>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Active Gigs Section -->
            <div class="card shadow-sm animate-on-scroll">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Active Gigs (<?php echo count($gigs); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($gigs)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No active gigs available</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($gigs as $gig): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border card-hover">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <a href="<?php echo $base_path; ?>/gig-details.php?id=<?php echo $gig['id']; ?>" class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($gig['title']); ?>
                                            </a>
                                        </h6>
                                        <p class="card-text text-muted small mb-2">
                                            <?php echo substr(htmlspecialchars($gig['description']), 0, 100); ?>...
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($gig['category']); ?></span>
                                            <span class="text-success fw-bold">$<?php echo number_format($gig['budget'], 2); ?></span>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="far fa-clock me-1"></i>
                                                <?php echo $gig['delivery_time']; ?> days delivery
                                            </small>
                                        </div>
                                        <a href="<?php echo $base_path; ?>/gig-details.php?id=<?php echo $gig['id']; ?>" class="btn btn-sm btn-outline-primary mt-2 w-100">
                                            View Details
                                        

                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-picture-large img,
.profile-picture-large div {
    border: 4px solid var(--border-color);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.stat-box {
    transition: all 0.3s ease;
}

.stat-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.portfolio-item {
    overflow: hidden;
    border-radius: 8px;
}

.hover-zoom {
    transition: transform 0.3s ease;
}

.hover-zoom:hover {
    transform: scale(1.05);
}

.card-hover {
    transition: all 0.3s ease;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

/* Dark mode text visibility fixes */
html[data-theme="dark"] .card,
html[data-theme="dark"] .card h3,
html[data-theme="dark"] .card p,
html[data-theme="dark"] .card small,
html[data-theme="dark"] .card span,
html[data-theme="dark"] .badge {
    color: #ffffff !important;
}

html[data-theme="dark"] .card h5,
html[data-theme="dark"] .card-header h5 {
    color: #000000 !important;
}

html[data-theme="dark"] .badge.bg-light {
    background-color: #374151 !important;
    color: #ffffff !important;
    border-color: #4b5563 !important;
}

html[data-theme="dark"] .text-muted {
    color: #cccccc !important;
}
</style>

<?php require_once 'includes/footer.php'; ?>
