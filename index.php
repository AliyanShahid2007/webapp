<?php
$page_title = 'Home';
require_once 'includes/header.php';

// Get categories
try {
    $pdo = getPDOConnection();
    $stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $categories = $stmt->fetchAll();
    
    // Get top freelancers
    $stmt = $pdo->query("
        SELECT u.id, u.name, u.username, fp.profile_pic, fp.category, fp.rating, fp.total_reviews 
        FROM users u 
        JOIN freelancer_profiles fp ON u.id = fp.user_id 
        WHERE u.status = 'active' 
        ORDER BY fp.rating DESC, fp.total_reviews DESC 
        LIMIT 6
    ");
    $top_freelancers = $stmt->fetchAll();
    
    // Get recent gigs
    $stmt = $pdo->query("
        SELECT g.*, u.name as freelancer_name, u.username, fp.profile_pic, fp.rating 
        FROM gigs g 
        JOIN users u ON g.freelancer_id = u.id 
        JOIN freelancer_profiles fp ON u.id = fp.user_id 
        WHERE g.status = 'active' AND u.status = 'active' 
        ORDER BY g.created_at DESC 
        LIMIT 6
    ");
    $recent_gigs = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $categories = [];
    $top_freelancers = [];
    $recent_gigs = [];
}
?>

<!-- Hero Section -->
<section style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: white; padding: 4rem 0; margin-bottom: 3rem;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 style="font-size: 3rem; font-weight: 700; margin-bottom: 1rem;">
                    Find the Perfect <span style="color: #fbbf24;">Freelance</span> Services
                </h1>
                <p style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.9;">
                    Connect with talented freelancers and get your projects done with quality and efficiency
                </p>
                <div style="display: flex; gap: 1rem;">
                    <?php if (!isLoggedIn()): ?>
                        <a href="/register.php" class="btn btn-lg" style="background: white; color: var(--primary-color);">
                            <i class="fas fa-user-plus"></i> Get Started
                        </a>
                        <a href="/browse-gigs.php" class="btn btn-lg btn-outline" style="border-color: white; color: white;">
                            <i class="fas fa-search"></i> Browse Gigs
                        </a>
                    <?php else: ?>
                        <a href="/browse-gigs.php" class="btn btn-lg" style="background: white; color: var(--primary-color);">
                            <i class="fas fa-search"></i> Browse Gigs
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-5 text-center">
                <i class="fas fa-laptop-code" style="font-size: 15rem; opacity: 0.2;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="container mb-5">
    <div class="text-center mb-4">
        <h2 style="font-weight: 700; color: var(--text-primary);">Popular Categories</h2>
        <p style="color: var(--text-secondary);">Explore services by category</p>
    </div>
    
    <div class="row">
        <?php foreach ($categories as $category): ?>
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="/browse-gigs.php?category=<?php echo urlencode($category['name']); ?>" style="text-decoration: none;">
                    <div class="card text-center" style="height: 100%; transition: var(--transition);">
                        <div class="card-body">
                            <i class="fas <?php echo htmlspecialchars($category['icon']); ?>" 
                               style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                            <h5 style="color: var(--text-primary);">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h5>
                            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                <?php echo htmlspecialchars($category['description']); ?>
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Recent Gigs Section -->
<?php if (count($recent_gigs) > 0): ?>
<section class="container mb-5">
    <div class="text-center mb-4">
        <h2 style="font-weight: 700; color: var(--text-primary);">Recent Gigs</h2>
        <p style="color: var(--text-secondary);">Latest services from talented freelancers</p>
    </div>
    
    <div class="row">
        <?php foreach ($recent_gigs as $gig): ?>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card gig-card">
                    <div class="card-body">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                            <?php if ($gig['profile_pic']): ?>
                                <img src="/uploads/profiles/<?php echo htmlspecialchars($gig['profile_pic']); ?>" 
                                     alt="<?php echo htmlspecialchars($gig['freelancer_name']); ?>" 
                                     class="profile-image">
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                    <?php echo strtoupper(substr($gig['freelancer_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div style="font-weight: 600; color: var(--text-primary);">
                                    <?php echo htmlspecialchars($gig['freelancer_name']); ?>
                                </div>
                                <div class="rating">
                                    <i class="fas fa-star star"></i>
                                    <span style="color: var(--text-secondary);">
                                        <?php echo number_format($gig['rating'], 1); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="gig-title">
                            <?php echo htmlspecialchars($gig['title']); ?>
                        </h5>
                        
                        <p style="color: var(--text-secondary); font-size: 0.95rem;">
                            <?php echo htmlspecialchars(substr($gig['description'], 0, 100)); ?>...
                        </p>
                        
                        <div class="gig-meta">
                            <div class="gig-price">
                                $<?php echo number_format($gig['budget'], 2); ?>
                            </div>
                            <a href="/gig-details.php?id=<?php echo $gig['id']; ?>" class="btn btn-primary btn-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-4">
        <a href="/browse-gigs.php" class="btn btn-primary btn-lg">
            <i class="fas fa-search"></i> Browse All Gigs
        </a>
    </div>
</section>
<?php endif; ?>

<!-- Top Freelancers Section -->
<?php if (count($top_freelancers) > 0): ?>
<section class="container mb-5">
    <div class="text-center mb-4">
        <h2 style="font-weight: 700; color: var(--text-primary);">Top Rated Freelancers</h2>
        <p style="color: var(--text-secondary);">Work with the best professionals</p>
    </div>
    
    <div class="row">
        <?php foreach ($top_freelancers as $freelancer): ?>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <?php if ($freelancer['profile_pic']): ?>
                            <img src="/uploads/profiles/<?php echo htmlspecialchars($freelancer['profile_pic']); ?>" 
                                 alt="<?php echo htmlspecialchars($freelancer['name']); ?>" 
                                 class="profile-image profile-image-lg" 
                                 style="margin-bottom: 1rem;">
                        <?php else: ?>
                            <div style="width: 120px; height: 120px; border-radius: 50%; background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: 600; margin: 0 auto 1rem;">
                                <?php echo strtoupper(substr($freelancer['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <h5 style="color: var(--text-primary); font-weight: 600;">
                            <?php echo htmlspecialchars($freelancer['name']); ?>
                        </h5>
                        
                        <p style="color: var(--text-secondary);">
                            <?php echo htmlspecialchars($freelancer['category'] ?? 'Freelancer'); ?>
                        </p>
                        
                        <div class="rating" style="justify-content: center; margin-bottom: 1rem;">
                            <i class="fas fa-star star"></i>
                            <span style="color: var(--text-primary); font-weight: 600;">
                                <?php echo number_format($freelancer['rating'], 1); ?>
                            </span>
                            <span style="color: var(--text-secondary);">
                                (<?php echo $freelancer['total_reviews']; ?> reviews)
                            </span>
                        </div>
                        
                        <a href="/freelancer-profile.php?id=<?php echo $freelancer['id']; ?>" class="btn btn-outline btn-sm">
                            View Profile
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section style="background: var(--card-bg); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 3rem 0; margin-bottom: 0;">
    <div class="container text-center">
        <h2 style="font-weight: 700; color: var(--text-primary); margin-bottom: 1rem;">
            Ready to Get Started?
        </h2>
        <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 2rem;">
            Join thousands of freelancers and clients on FreelanceHub
        </p>
        <?php if (!isLoggedIn()): ?>
            <a href="/register.php" class="btn btn-primary btn-lg">
                <i class="fas fa-user-plus"></i> Create Free Account
            </a>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
