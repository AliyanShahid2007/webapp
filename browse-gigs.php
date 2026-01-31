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
        $search_lower = strtolower($search);

        // Create multiple search terms for common variations
        $search_terms = ["%" . $search_lower . "%"];

        // Common misspellings and their corrections
        $spelling_corrections = [
            'developement' => 'development',
            'desing' => 'design',
            'programing' => 'programming',
            'marketting' => 'marketing',
            'writting' => 'writing',
            'grafic' => 'graphic',
            'websit' => 'website',
            'mobil' => 'mobile',
            'aplication' => 'app',
            'softwere' => 'software',
            'bussiness' => 'business',
            'managment' => 'management',
            'consultting' => 'consulting',
            'fotografy' => 'photography',
            'vidio' => 'video',
            'editting' => 'editing',
            'translatin' => 'translation',
            'datta' => 'data',
            'analisis' => 'analysis',
            'reserch' => 'research'
        ];

        // Add variations for common typos (bidirectional)
        foreach ($spelling_corrections as $misspelled => $correct) {
            if (strpos($search_lower, $misspelled) !== false) {
                $search_terms[] = "%" . str_replace($misspelled, $correct, $search_lower) . "%";
            }
            if (strpos($search_lower, $correct) !== false) {
                $search_terms[] = "%" . str_replace($correct, $misspelled, $search_lower) . "%";
            }
        }

        // Add common abbreviations
        if (strpos($search_lower, 'development') !== false) {
            $search_terms[] = "%" . str_replace('development', 'dev', $search_lower) . "%";
        }
        if (strpos($search_lower, 'developement') !== false) {
            $search_terms[] = "%" . str_replace('developement', 'dev', $search_lower) . "%";
        }

        $gig_conditions = [];
        foreach ($search_terms as $term) {
            $gig_conditions[] = "(LOWER(g.title) LIKE ? OR LOWER(g.description) LIKE ? OR LOWER(u.name) LIKE ? OR LOWER(fp.category) LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $where_clauses[] = "(" . implode(' OR ', $gig_conditions) . ")";
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
    
    // Get freelancers if searching
    $freelancers = [];
    if ($search) {
        $search_lower = strtolower($search);
        $search_term = "%" . $search_lower . "%";

        // Create multiple search terms for common variations
        $search_terms = [$search_term];

        // Common misspellings and their corrections
        $spelling_corrections = [
            'developement' => 'development',
            'desing' => 'design',
            'programing' => 'programming',
            'marketting' => 'marketing',
            'writting' => 'writing',
            'grafic' => 'graphic',
            'websit' => 'website',
            'mobil' => 'mobile',
            'aplication' => 'app',
            'softwere' => 'software',
            'bussiness' => 'business',
            'managment' => 'management',
            'consultting' => 'consulting',
            'fotografy' => 'photography',
            'vidio' => 'video',
            'editting' => 'editing',
            'translatin' => 'translation',
            'datta' => 'data',
            'analisis' => 'analysis',
            'reserch' => 'research'
        ];

        // Add variations for common typos (bidirectional)
        foreach ($spelling_corrections as $misspelled => $correct) {
            if (strpos($search_lower, $misspelled) !== false) {
                $search_terms[] = "%" . str_replace($misspelled, $correct, $search_lower) . "%";
            }
            if (strpos($search_lower, $correct) !== false) {
                $search_terms[] = "%" . str_replace($correct, $misspelled, $search_lower) . "%";
            }
        }

        // Add common abbreviations
        if (strpos($search_lower, 'development') !== false) {
            $search_terms[] = "%" . str_replace('development', 'dev', $search_lower) . "%";
        }
        if (strpos($search_lower, 'developement') !== false) {
            $search_terms[] = "%" . str_replace('developement', 'dev', $search_lower) . "%";
        }

        $freelancer_conditions = [];
        $freelancer_params = [];
        foreach ($search_terms as $term) {
            $freelancer_conditions[] = "(LOWER(u.name) LIKE ? OR LOWER(fp.category) LIKE ? OR LOWER(fp.bio) LIKE ?)";
            $freelancer_params[] = $term;
            $freelancer_params[] = $term;
            $freelancer_params[] = $term;
        }

        $freelancer_sql = "SELECT u.id, u.name, u.username, fp.profile_pic, fp.category, fp.rating, fp.total_reviews, fp.bio
                          FROM users u
                          JOIN freelancer_profiles fp ON u.id = fp.user_id
                          WHERE u.status = 'active' AND u.role = 'freelancer'
                          AND (" . implode(' OR ', $freelancer_conditions) . ")
                          ORDER BY fp.rating DESC, fp.total_reviews DESC
                          LIMIT 6";
        $freelancer_stmt = $pdo->prepare($freelancer_sql);
        $freelancer_stmt->execute($freelancer_params);
        $freelancers = $freelancer_stmt->fetchAll();
    }

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
                        
                        <a href="<?php echo $base_path; ?>/browse-gigs.php" class="btn btn-outline btn-block" style="margin-top: 0.5rem;">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Gigs Grid -->
        <div class="col-md-9">
            <!-- Freelancers Section -->
            <?php if ($search && count($freelancers) > 0): ?>
            <div class="mb-4">
                <h3 style="color: var(--text-primary); margin-bottom: 1rem;">
                    <i class="fas fa-users"></i> Matching Freelancers
                </h3>
                <div class="row">
                    <?php foreach ($freelancers as $freelancer): ?>
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
            </div>
            <?php endif; ?>

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
                                        <a href="<?php echo $base_path; ?>/gig-details.php?id=<?php echo $gig['id']; ?>" class="btn btn-primary btn-sm">
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
                    <a href="<?php echo $base_path; ?>/browse-gigs.php" class="btn btn-primary">
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
