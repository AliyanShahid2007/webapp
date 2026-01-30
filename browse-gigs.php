<?php
$page_title = 'Browse Gigs';
require_once 'includes/header.php';

// Get filters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$min_budget = isset($_GET['min_budget']) ? (float)$_GET['min_budget'] : 0;
$max_budget = isset($_GET['max_budget']) ? (float)$_GET['max_budget'] : 0;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

try {
    $pdo = getPDOConnection();
    
    // Build query
    $where_clauses = ["g.status = 'active'", "u.status = 'active'"];
    $params = [];
    
    if ($category) {
        $where_clauses[] = "g.category = ?";
        $params[] = $category;
    }
    
    if ($search) {
        $where_clauses[] = "(g.title LIKE ? OR g.description LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if ($min_budget > 0) {
        $where_clauses[] = "g.budget >= ?";
        $params[] = $min_budget;
    }
    
    if ($max_budget > 0) {
        $where_clauses[] = "g.budget <= ?";
        $params[] = $max_budget;
    }
    
    $where_sql = implode(' AND ', $where_clauses);
    
    // Determine sorting
    $order_by = "g.created_at DESC"; // default: newest
    switch ($sort) {
        case 'price_low':
            $order_by = "g.budget ASC";
            break;
        case 'price_high':
            $order_by = "g.budget DESC";
            break;
        case 'rating':
            $order_by = "fp.rating DESC";
            break;
        case 'popular':
            $order_by = "g.views DESC";
            break;
    }
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as count FROM gigs g 
                  JOIN users u ON g.freelancer_id = u.id 
                  JOIN freelancer_profiles fp ON u.id = fp.user_id 
                  WHERE $where_sql";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_gigs = $count_stmt->fetch()['count'];
    
    // Get gigs
    $sql = "SELECT g.*, 
            u.name as freelancer_name, 
            u.username, 
            fp.profile_pic, 
            fp.rating,
            fp.total_reviews
            FROM gigs g 
            JOIN users u ON g.freelancer_id = u.id 
            JOIN freelancer_profiles fp ON u.id = fp.user_id 
            WHERE $where_sql 
            ORDER BY $order_by 
            LIMIT $per_page OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $gigs = $stmt->fetchAll();
    
    // Get categories for filter
    $categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();
    
    // Calculate pagination
    $total_pages = ceil($total_gigs / $per_page);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $gigs = [];
    $categories = [];
    $total_gigs = 0;
    $total_pages = 0;
}
?>

<div class="container mt-4">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title" style="margin-bottom: 0;">
                        <i class="fas fa-filter"></i> Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="" id="filter-form">
                        <!-- Search -->
                        <div class="form-group">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search gigs..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <!-- Category -->
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>" 
                                            <?php echo $category == $cat['name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Budget Range -->
                        <div class="form-group">
                            <label class="form-label">Budget Range</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="number" name="min_budget" class="form-control" 
                                       placeholder="Min" step="1" 
                                       value="<?php echo $min_budget > 0 ? $min_budget : ''; ?>">
                                <input type="number" name="max_budget" class="form-control" 
                                       placeholder="Max" step="1" 
                                       value="<?php echo $max_budget > 0 ? $max_budget : ''; ?>">
                            </div>
                        </div>
                        
                        <!-- Sort -->
                        <div class="form-group">
                            <label class="form-label">Sort By</label>
                            <select name="sort" class="form-control">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                                <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        
                        <a href="/browse-gigs.php" class="btn btn-outline btn-block" style="margin-top: 0.5rem;">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Gigs Grid -->
        <div class="col-md-9">
            <div class="mb-3" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="color: var(--text-primary); margin-bottom: 0;">
                    <i class="fas fa-briefcase"></i> Browse Gigs
                </h3>
                <span style="color: var(--text-secondary);">
                    <?php echo $total_gigs; ?> gig<?php echo $total_gigs != 1 ? 's' : ''; ?> found
                </span>
            </div>
            
            <?php if (count($gigs) > 0): ?>
                <div class="row">
                    <?php foreach ($gigs as $gig): ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="card gig-card">
                                <div class="card-body">
                                    <!-- Freelancer Info -->
                                    <a href="/freelancer-profile.php?id=<?php echo $gig['freelancer_id']; ?>" class="text-decoration-none" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
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
                                                <span style="color: var(--text-secondary); font-size: 0.9rem;">
                                                    <?php echo number_format($gig['rating'], 1); ?> 
                                                    (<?php echo $gig['total_reviews']; ?>)
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                    
                                    <!-- Gig Info -->
                                    <h5 class="gig-title">
                                        <?php echo htmlspecialchars($gig['title']); ?>
                                    </h5>
                                    
                                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                        <?php echo htmlspecialchars(substr($gig['description'], 0, 80)); ?>...
                                    </p>
                                    
                                    <div style="margin: 1rem 0;">
                                        <span class="badge badge-secondary">
                                            <?php echo htmlspecialchars($gig['category']); ?>
                                        </span>
                                        <span style="color: var(--text-muted); font-size: 0.85rem; margin-left: 0.5rem;">
                                            <i class="fas fa-clock"></i> <?php echo $gig['delivery_time']; ?> days
                                        </span>
                                    </div>
                                    
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
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $sort ? '&sort=' . $sort : ''; ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $sort ? '&sort=' . $sort : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $sort ? '&sort=' . $sort : ''; ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="card text-center" style="padding: 3rem;">
                    <i class="fas fa-search" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                    <h4 style="color: var(--text-primary);">No Gigs Found</h4>
                    <p style="color: var(--text-secondary);">
                        Try adjusting your filters or search terms
                    </p>
                    <a href="/browse-gigs.php" class="btn btn-primary">
                        Clear All Filters
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.pagination .page-link {
    color: var(--primary-color);
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    margin: 0 0.25rem;
    border-radius: var(--radius-md);
}

.pagination .page-link:hover {
    background-color: var(--hover-bg);
}

.pagination .page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}
</style>

<?php require_once 'includes/footer.php'; ?>
