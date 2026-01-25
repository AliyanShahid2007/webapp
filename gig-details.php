<?php
$page_title = 'Gig Details';
require_once 'includes/header.php';

$gig_id = (int)($_GET['id'] ?? 0);

if (!$gig_id) {
    header('Location: /browse-gigs.php');
    exit;
}

try {
    $pdo = getPDOConnection();
    
    // Get gig details with freelancer info
    $stmt = $pdo->prepare("
        SELECT g.*,
               u.id as freelancer_user_id,
               u.name as freelancer_name,
               u.username as freelancer_username,
               fp.bio,
               fp.profile_pic,
               fp.rating,
               fp.total_reviews,
               fp.skills,
               fp.portfolio_images
        FROM gigs g
        JOIN users u ON g.freelancer_id = u.id
        JOIN freelancer_profiles fp ON u.id = fp.user_id
        WHERE g.id = ? AND g.status = 'active' AND u.status = 'active'
    ");
    $stmt->execute([$gig_id]);
    $gig = $stmt->fetch();
    
    if (!$gig) {
        redirectWithMessage('/browse-gigs.php', 'Gig not found', 'danger');
    }
    
    // Increment views
    $stmt = $pdo->prepare("UPDATE gigs SET views = views + 1 WHERE id = ?");
    $stmt->execute([$gig_id]);
    
    // Get freelancer's other gigs
    $stmt = $pdo->prepare("
        SELECT * FROM gigs 
        WHERE freelancer_id = ? AND id != ? AND status = 'active'
        ORDER BY created_at DESC
        LIMIT 3
    ");
    $stmt->execute([$gig['freelancer_id'], $gig_id]);
    $other_gigs = $stmt->fetchAll();
    
    $portfolio_images = $gig['portfolio_images'] ? json_decode($gig['portfolio_images'], true) : [];
    $skills = $gig['skills'] ? explode(',', $gig['skills']) : [];
    
} catch (Exception $e) {
    error_log($e->getMessage());
    redirectWithMessage('/browse-gigs.php', 'Error loading gig', 'danger');
}

$page_title = $gig['title'];
?>

<div class="container mt-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8 mb-4">
            <!-- Gig Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge badge-secondary">
                                <?php echo htmlspecialchars($gig['category']); ?>
                            </span>
                        </div>
                        <div style="text-align: right; color: var(--text-muted); font-size: 0.9rem;">
                            <i class="fas fa-eye"></i> <?php echo $gig['views']; ?> views
                        </div>
                    </div>
                    
                    <h1 style="color: var(--text-primary); font-size: 2rem; margin-bottom: 1.5rem;">
                        <?php echo htmlspecialchars($gig['title']); ?>
                    </h1>
                    
                    <!-- Freelancer Info -->
                    <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-tertiary); border-radius: var(--radius-md);">
                        <a href="/freelancer-profile.php?id=<?php echo $gig['freelancer_user_id']; ?>">
                            <?php if ($gig['profile_pic']): ?>
                                <img src="/uploads/profiles/<?php echo htmlspecialchars($gig['profile_pic']); ?>" 
                                     alt="<?php echo htmlspecialchars($gig['freelancer_name']); ?>" 
                                     class="profile-image profile-image-lg">
                            <?php else: ?>
                                <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: 600;">
                                    <?php echo strtoupper(substr($gig['freelancer_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </a>
                        <div style="flex: 1;">
                            <h5 style="color: var(--text-primary); margin-bottom: 0.25rem;">
                                <a href="/freelancer-profile.php?id=<?php echo $gig['freelancer_user_id']; ?>" 
                                   style="color: var(--text-primary); text-decoration: none;">
                                    <?php echo htmlspecialchars($gig['freelancer_name']); ?>
                                </a>
                            </h5>
                            <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">
                                @<?php echo htmlspecialchars($gig['freelancer_username']); ?>
                            </p>
                            <div class="rating">
                                <i class="fas fa-star star"></i>
                                <span style="color: var(--text-primary); font-weight: 600;">
                                    <?php echo number_format($gig['rating'], 1); ?>
                                </span>
                                <span style="color: var(--text-secondary);">
                                    (<?php echo $gig['total_reviews']; ?> reviews)
                                </span>
                            </div>
                        </div>
                        <a href="/freelancer-profile.php?id=<?php echo $gig['freelancer_user_id']; ?>" 
                           class="btn btn-outline">
                            View Profile
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- About This Gig -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title" style="margin-bottom: 0;">
                        <i class="fas fa-info-circle"></i> About This Gig
                    </h4>
                </div>
                <div class="card-body">
                    <p style="color: var(--text-primary); white-space: pre-line; line-height: 1.8;">
                        <?php echo htmlspecialchars($gig['description']); ?>
                    </p>
                </div>
            </div>
            
            <!-- Portfolio -->
            <?php if (count($portfolio_images) > 0): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title" style="margin-bottom: 0;">
                        <i class="fas fa-images"></i> Portfolio
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($portfolio_images as $image): ?>
                            <div class="col-md-4 mb-3">
                                <img src="/uploads/portfolio/<?php echo htmlspecialchars($image); ?>" 
                                     alt="Portfolio" 
                                     style="width: 100%; height: 200px; object-fit: cover; border-radius: var(--radius-md); cursor: pointer;"
                                     onclick="window.open(this.src, '_blank')">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- About Freelancer -->
            <?php if ($gig['bio']): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title" style="margin-bottom: 0;">
                        <i class="fas fa-user"></i> About the Freelancer
                    </h4>
                </div>
                <div class="card-body">
                    <p style="color: var(--text-primary); white-space: pre-line; line-height: 1.8;">
                        <?php echo htmlspecialchars($gig['bio']); ?>
                    </p>
                    
                    <?php if (count($skills) > 0): ?>
                        <div style="margin-top: 1.5rem;">
                            <h6 style="color: var(--text-primary); margin-bottom: 1rem;">
                                <i class="fas fa-code"></i> Skills
                            </h6>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <?php foreach ($skills as $skill): ?>
                                    <span class="badge badge-primary" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                        <?php echo htmlspecialchars(trim($skill)); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- More Gigs from Freelancer -->
            <?php if (count($other_gigs) > 0): ?>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title" style="margin-bottom: 0;">
                        <i class="fas fa-briefcase"></i> More Gigs from <?php echo htmlspecialchars($gig['freelancer_name']); ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($other_gigs as $other_gig): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card" style="height: 100%;">
                                    <div class="card-body">
                                        <h6 style="color: var(--text-primary); margin-bottom: 1rem;">
                                            <?php echo htmlspecialchars(substr($other_gig['title'], 0, 50)); ?>...
                                        </h6>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div style="color: var(--primary-color); font-weight: 700; font-size: 1.1rem;">
                                                $<?php echo number_format($other_gig['budget'], 2); ?>
                                            </div>
                                            <a href="/gig-details.php?id=<?php echo $other_gig['id']; ?>" class="btn btn-primary btn-sm">
                                                View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar - Order Card -->
        <div class="col-md-4 mb-4">
            <div class="card" style="position: sticky; top: 20px;">
                <div class="card-body">
                    <div style="text-align: center; margin-bottom: 1.5rem;">
                        <div style="font-size: 3rem; font-weight: 700; color: var(--primary-color);">
                            $<?php echo number_format($gig['budget'], 2); ?>
                        </div>
                        <div style="color: var(--text-secondary);">
                            Total Price
                        </div>
                    </div>
                    
                    <div style="background: var(--bg-tertiary); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                            <span style="color: var(--text-secondary);">
                                <i class="fas fa-clock"></i> Delivery Time
                            </span>
                            <strong style="color: var(--text-primary);">
                                <?php echo $gig['delivery_time']; ?> days
                            </strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-secondary);">
                                <i class="fas fa-tag"></i> Category
                            </span>
                            <strong style="color: var(--text-primary);">
                                <?php echo htmlspecialchars($gig['category']); ?>
                            </strong>
                        </div>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('client')): ?>
                            <form method="POST" action="/order-gig.php">
                                <input type="hidden" name="gig_id" value="<?php echo $gig['id']; ?>">
                                
                                <div class="form-group">
                                    <label class="form-label">Order Notes (Optional)</label>
                                    <textarea name="client_notes" class="form-control" rows="3" 
                                              placeholder="Any special requirements or details..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg btn-block">
                                    <i class="fas fa-shopping-cart"></i> Order Now
                                </button>
                            </form>
                        <?php elseif (hasRole('freelancer')): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> You're viewing this as a freelancer. Clients can order this gig.
                            </div>
                        <?php elseif (hasRole('admin')): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-user-shield"></i> Admin view
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/login.php?redirect=<?php echo urlencode('/gig-details.php?id=' . $gig['id']); ?>" 
                           class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-sign-in-alt"></i> Login to Order
                        </a>
                        <p style="text-align: center; color: var(--text-secondary); margin-top: 1rem; font-size: 0.9rem;">
                            Don't have an account? <a href="/register.php" style="color: var(--primary-color);">Sign up</a>
                        </p>
                    <?php endif; ?>
                    
                    <hr style="border-color: var(--border-color); margin: 1.5rem 0;">
                    
                    <div style="text-align: center;">
                        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem;">
                            <i class="fas fa-shield-alt"></i> Safe and secure
                        </p>
                        <p style="color: var(--text-muted); font-size: 0.85rem;">
                            Your payment is protected
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
