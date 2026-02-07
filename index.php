<?php
$page_title = 'Home';
require_once 'includes/header.php';

// Get categories
try {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $categories = $result->fetch_all(MYSQLI_ASSOC);

    // Get top freelancers
    $result = $conn->query("
        SELECT u.id, u.name, u.username, fp.profile_pic, fp.category, fp.rating, fp.total_reviews
        FROM users u
        JOIN freelancer_profiles fp ON u.id = fp.user_id
        WHERE u.status = 'active'
        ORDER BY fp.rating DESC, fp.total_reviews DESC
        LIMIT 6
    ");
    $top_freelancers = $result->fetch_all(MYSQLI_ASSOC);

    // Get recent gigs
    $result = $conn->query("
        SELECT g.*, u.name as freelancer_name, u.username, fp.profile_pic, fp.rating
        FROM gigs g
        JOIN users u ON g.freelancer_id = u.id
        JOIN freelancer_profiles fp ON u.id = fp.user_id
        WHERE g.status = 'active' AND u.status = 'active'
        ORDER BY g.created_at DESC
        LIMIT 6
    ");
    $recent_gigs = $result->fetch_all(MYSQLI_ASSOC);

    // Get platform statistics
    $result = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'freelancer' AND status = 'active'");
    $total_freelancers = $result->fetch_row()[0];

    $result = $conn->query("SELECT COUNT(*) FROM gigs WHERE status = 'active'");
    $total_gigs = $result->fetch_row()[0];

    $result = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'");
    $completed_orders = $result->fetch_row()[0];

    $result = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'client' AND status = 'active'");
    $total_clients = $result->fetch_row()[0];

    $conn->close();
} catch (Exception $e) {
    error_log($e->getMessage());
    $categories = [];
    $top_freelancers = [];
    $recent_gigs = [];
    $total_freelancers = 0;
    $total_gigs = 0;
    $completed_orders = 0;
    $total_clients = 0;
}
?>

<!-- Hero Section -->
<section class="hero-section animate-fade-in" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: white; padding: 5rem 0 4rem; margin-bottom: 3rem; position: relative; overflow: hidden;">
    <!-- Background Animation -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1;">
        <div class="floating-shape" style="position: absolute; top: 10%; left: 10%; width: 100px; height: 100px; background: white; border-radius: 50%; animation: float 6s infinite;"></div>
        <div class="floating-shape" style="position: absolute; bottom: 20%; right: 15%; width: 80px; height: 80px; background: white; border-radius: 50%; animation: float 8s infinite;"></div>
        <div class="floating-shape" style="position: absolute; top: 50%; left: 50%; width: 60px; height: 60px; background: white; border-radius: 50%; animation: float 10s infinite;"></div>
    </div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="animate-slide-up" style="font-size: 3.5rem; font-weight: 800; margin-bottom: 1.5rem; line-height: 1.2;">
                    Find the Perfect <span style="color: #fbbf24; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Freelance</span> Services
                </h1>
                <p class="animate-slide-up" style="font-size: 1.3rem; margin-bottom: 2.5rem; opacity: 0.95; line-height: 1.6;">
                    Connect with talented freelancers worldwide and transform your ideas into reality with quality and efficiency
                </p>
                
                <!-- Search Bar -->
                <form method="GET" action="<?php echo $base_path; ?>/browse-gigs.php" class="search-box animate-slide-up mb-4" style="background: rgba(255,255,255,0.95); border-radius: 50px; padding: 0.5rem; display: flex; align-items: center; max-width: 600px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                    <input type="text" name="search" placeholder="Search for services..."
                           style="flex: 1; border: none; padding: 0.75rem 1.5rem; background: transparent; color: var(--text-primary); font-size: 1rem; outline: none;">
                    <button type="submit" class="btn btn-primary" style="border-radius: 50px; padding: 0.75rem 2rem;">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
                
                <div class="animate-slide-up" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <?php if (!isLoggedIn()): ?>
                        <a href="<?php echo $base_path; ?>/register.php" class="btn btn-lg btn-hover-lift" style="background: white; color: var(--primary-color); box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                            <i class="fas fa-user-plus"></i> Get Started Free
                        </a>
                        <a href="<?php echo $base_path; ?>/browse-gigs.php" class="btn btn-lg btn-outline btn-hover-lift" style="border: 2px solid white; color: white; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                            <i class="fas fa-search"></i> Browse Gigs
                        </a>
                    <?php else: ?>
                        <a href="/browse-gigs.php" class="btn btn-lg btn-hover-lift" style="background: white; color: var(--primary-color); box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                            <i class="fas fa-search"></i> Browse Gigs
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-5 text-center">
                <div class="hero-illustration animate-float">
                    <i class="fas fa-laptop-code" style="font-size: 18rem; opacity: 0.2; text-shadow: 0 10px 30px rgba(0,0,0,0.3);"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="container mb-5">
    <div class="row text-center">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-card card shadow-sm p-4 h-100 animate-on-scroll card-hover">
                <div class="stat-icon mb-3">
                    <i class="fas fa-users fa-3x" style="color: var(--primary-color);"></i>
                </div>
                <h3 class="count-up mb-2" data-target="<?php echo $total_freelancers; ?>" data-stat="total_freelancers" style="font-weight: 700; color: var(--text-primary);">0</h3>
                <p class="text-muted">Active Freelancers</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-card card shadow-sm p-4 h-100 animate-on-scroll card-hover">
                <div class="stat-icon mb-3">
                    <i class="fas fa-briefcase fa-3x" style="color: var(--success-color);"></i>
                </div>
                <h3 class="count-up mb-2" data-target="<?php echo $total_gigs; ?>" data-stat="total_gigs" style="font-weight: 700; color: var(--text-primary);">0</h3>
                <p class="text-muted">Available Gigs</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-card card shadow-sm p-4 h-100 animate-on-scroll card-hover">
                <div class="stat-icon mb-3">
                    <i class="fas fa-check-circle fa-3x" style="color: var(--info-color);"></i>
                </div>
                <h3 class="count-up mb-2" data-target="<?php echo $completed_orders; ?>" data-stat="completed_orders" style="font-weight: 700; color: var(--text-primary);">0</h3>
                <p class="text-muted">Completed Orders</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-card card shadow-sm p-4 h-100 animate-on-scroll card-hover">
                <div class="stat-icon mb-3">
                    <i class="fas fa-handshake fa-3x" style="color: var(--warning-color);"></i>
                </div>
                <h3 class="count-up mb-2" data-target="<?php echo $total_clients; ?>" data-stat="total_clients" style="font-weight: 700; color: var(--text-primary);">0</h3>
                <p class="text-muted">Happy Clients</p>
            </div>
        </div>
    </div>
</section>
<!-- Recent Gigs Section -->
<?php if (count($recent_gigs) > 0): ?>
<section class="container mb-5">
    <div class="text-center mb-4 animate-on-scroll">
        <h2 style="font-weight: 700; color: var(--text-primary);">
            <i class="fas fa-fire-alt text-danger"></i> Trending Gigs
        </h2>
        <p style="color: var(--text-secondary);">Latest services from talented freelancers</p>
    </div>
    
    <div class="row">
        <?php foreach ($recent_gigs as $gig): ?>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card gig-card animate-on-scroll">
                    <?php if ($gig['image']): ?>
                        <img src="<?php echo $base_path; ?>/<?php echo htmlspecialchars($gig['image']); ?>"
                             class="card-img-top" alt="Gig Image"
                             style="height: 200px; object-fit: cover; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
                    <?php endif; ?>
                    <div class="card-body">
                        <a href="<?php echo $base_path; ?>/freelancer-profile.php?id=<?php echo $gig['freelancer_id']; ?>" class="text-decoration-none" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                            <?php if ($gig['profile_pic']): ?>
                                <img src="<?php echo $base_path; ?>/uploads/profiles/<?php echo htmlspecialchars($gig['profile_pic']); ?>"
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
                        </a>
                        
                        <h5 class="gig-title">
                            <a href="<?php echo $base_path; ?>/gig-details.php?id=<?php echo $gig['id']; ?>" class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($gig['title']); ?>
                            </a>
                        </h5>
                        
                        <p style="color: var(--text-secondary); font-size: 0.95rem;">
                            <?php echo htmlspecialchars(substr($gig['description'], 0, 100)); ?>...
                        </p>
                        
                        <div class="gig-meta">
                            <div class="gig-price">
                                $<?php echo number_format($gig['budget'], 2); ?>
                            </div>
                            <a href="<?php echo $base_path; ?>/gig-details.php?id=<?php echo $gig['id']; ?>" class="btn btn-primary btn-sm btn-hover-lift">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-4 animate-on-scroll">
        <a href="<?php echo $base_path; ?>/browse-gigs.php" class="btn btn-primary btn-lg btn-hover-lift">
            <i class="fas fa-search"></i> Browse All Gigs
        </a>
    </div>
</section>
<?php endif; ?>

<!-- Top Freelancers Section -->
<?php if (count($top_freelancers) > 0): ?>
<section class="container mb-5">
    <div class="text-center mb-4 animate-on-scroll">
        <h2 style="font-weight: 700; color: var(--text-primary);">
            <i class="fas fa-trophy text-warning"></i> Top Rated Freelancers
        </h2>
        <p style="color: var(--text-secondary);">Work with the best professionals in the industry</p>
    </div>
    
    <div class="row">
        <?php foreach ($top_freelancers as $freelancer): ?>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card text-center animate-on-scroll card-hover" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-body p-4">
                        <a href="<?php echo $base_path; ?>/freelancer-profile.php?id=<?php echo $freelancer['id']; ?>" class="text-decoration-none">
                            <?php if ($freelancer['profile_pic']): ?>
                                <img src="<?php echo $base_path; ?>/uploads/profiles/<?php echo htmlspecialchars($freelancer['profile_pic']); ?>"
                                     alt="<?php echo htmlspecialchars($freelancer['name']); ?>"
                                     class="profile-image profile-image-lg"
                                     style="display: block; margin: 0 auto 1rem; border: 4px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <?php else: ?>
                                <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: 600; margin: 0 auto 1rem; border: 4px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                    <?php echo strtoupper(substr($freelancer['name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>

                            <h5 style="color: var(--text-primary); font-weight: 600; margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($freelancer['name']); ?>
                            </h5>
                        </a>

                        <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                            <i class="fas fa-tag me-1"></i>
                            <?php echo htmlspecialchars($freelancer['category'] ?? 'Freelancer'); ?>
                        </p>

                        <div class="rating" style="justify-content: center; margin-bottom: 1.5rem;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $freelancer['rating'] ? 'star' : 'text-muted'; ?>"></i>
                            <?php endfor; ?>
                            <div class="mt-2">
                                <span style="color: var(--text-primary); font-weight: 600; font-size: 1.1rem;">
                                    <?php echo number_format($freelancer['rating'], 1); ?>
                                </span>
                                <span style="color: var(--text-secondary); font-size: 0.9rem;">
                                    (<?php echo $freelancer['total_reviews']; ?> reviews)
                                </span>
                            </div>
                        </div>

                        <a href="<?php echo $base_path; ?>/freelancer-profile.php?id=<?php echo $freelancer['id']; ?>" class="btn btn-outline-primary btn-sm btn-hover-lift w-100">
                            <i class="fas fa-eye me-1"></i> View Profile
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
<!-- Categories Section -->
<section class="container mb-5">
    <div class="text-center mb-4">
        <h2 style="font-weight: 700; color: var(--text-primary);">Popular Categories</h2>
        <p style="color: var(--text-secondary);">Explore services by category</p>
    </div>
    
    <div class="row">
        <?php foreach ($categories as $category): ?>
            <div class="col-md-3 col-sm-6 mb-3">
                <a href="<?php echo $base_path; ?>/browse-gigs.php?category=<?php echo urlencode($category['name']); ?>" style="text-decoration: none;">
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
<!-- Features Section -->
<section style="background: var(--card-bg); border-top: 1px solid var(--border-color); padding: 4rem 0; margin-bottom: 3rem;">
    <div class="container">
        <div class="text-center mb-5 animate-on-scroll">
            <h2 style="font-weight: 700; color: var(--text-primary);">Why Choose FreelanceHub?</h2>
            <p style="color: var(--text-secondary);">Everything you need for successful freelancing</p>
        </div>
        
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="text-center animate-on-scroll">
                    <div class="feature-icon mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <i class="fas fa-shield-alt fa-2x text-white"></i>
                    </div>
                    <h5 style="color: var(--text-primary); font-weight: 600; margin-bottom: 1rem;">Secure Payments</h5>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">Protected transactions and secure payment processing for peace of mind</p>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="text-center animate-on-scroll">
                    <div class="feature-icon mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--success-color), #10b981); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <i class="fas fa-user-check fa-2x text-white"></i>
                    </div>
                    <h5 style="color: var(--text-primary); font-weight: 600; margin-bottom: 1rem;">Verified Freelancers</h5>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">Work with trusted, verified professionals with proven track records</p>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="text-center animate-on-scroll">
                    <div class="feature-icon mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--info-color), #3b82f6); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <i class="fas fa-headset fa-2x text-white"></i>
                    </div>
                    <h5 style="color: var(--text-primary); font-weight: 600; margin-bottom: 1rem;">24/7 Support</h5>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">Round-the-clock customer support to help you succeed</p>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="text-center animate-on-scroll">
                    <div class="feature-icon mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--warning-color), #f59e0b); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <i class="fas fa-rocket fa-2x text-white"></i>
                    </div>
                    <h5 style="color: var(--text-primary); font-weight: 600; margin-bottom: 1rem;">Fast Delivery</h5>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">Get your projects done quickly with our efficient workflow</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: white; padding: 4rem 0; margin-bottom: 0;">
    <div class="container text-center">
        <div class="animate-on-scroll">
            <h2 style="font-weight: 700; margin-bottom: 1rem; font-size: 2.5rem;">
                Ready to Get Started?
            </h2>
            <p style="font-size: 1.2rem; margin-bottom: 2.5rem; opacity: 0.95;">
                Join thousands of freelancers and clients who trust FreelanceHub for their success
            </p>
            <?php if (!isLoggedIn()): ?>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="<?php echo $base_path; ?>/register.php?role=freelancer" class="btn btn-lg btn-hover-lift" style="background: white; color: var(--primary-color); box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                        <i class="fas fa-user-tie me-2"></i> Join as Freelancer
                    </a>
                    <a href="<?php echo $base_path; ?>/register.php?role=client" class="btn btn-lg btn-hover-lift" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white; backdrop-filter: blur(10px);">
                        <i class="fas fa-user-plus me-2"></i> Join as Client
                    </a>
                </div>
            <?php else: ?>
                <a href="<?php echo $base_path; ?>/browse-gigs.php" class="btn btn-lg btn-hover-lift" style="background: white; color: var(--primary-color); box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    <i class="fas fa-search me-2"></i> Start Browsing
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.hero-illustration {
    animation: float 6s ease-in-out infinite;
}

.floating-shape {
    animation: float 8s ease-in-out infinite;
}

.stat-card {
    transition: all 0.3s ease;
    border-radius: 15px;
}

.stat-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
}

.feature-icon {
    transition: all 0.3s ease;
}

.animate-on-scroll:hover .feature-icon {
    transform: scale(1.1) rotate(5deg);
}

.search-box input::placeholder {
    color: #9ca3af;
}
</style>

<?php require_once 'includes/footer.php'; ?>
