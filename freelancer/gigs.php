<?php
$page_title = 'My Gigs';
require_once __DIR__ . '/../includes/header.php';

// Check freelancer access
requireRole('freelancer');

try {
    $conn = getDBConnection();

    // Get freelancer's gigs
    $stmt = $conn->prepare("
        SELECT g.*,
               COUNT(DISTINCT o.id) as total_orders,
               COUNT(DISTINCT CASE WHEN o.status = 'pending' THEN o.id END) as pending_orders
        FROM gigs g
        LEFT JOIN orders o ON g.id = o.gig_id
        WHERE g.freelancer_id = ?
        GROUP BY g.id
        ORDER BY g.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $gigs = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get categories for create form
    $stmt = $conn->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

} catch (Exception $e) {
    error_log($e->getMessage());
    $gigs = [];
    $categories = [];
}

$show_create_form = isset($_GET['action']) && $_GET['action'] == 'create';
$show_edit_form = false;
$edit_gig = null;

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM gigs WHERE id = ? AND freelancer_id = ?");
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_gig = $result->fetch_assoc();
    $stmt->close();
    if ($edit_gig) {
        $show_edit_form = true;
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="color: var(--text-primary);">
            <i class="fas fa-briefcase"></i> My Gigs
        </h2>
        <?php if (!$show_create_form): ?>
            <a href="?action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Gig
            </a>
        <?php endif; ?>
    </div>
    
    <?php if ($show_create_form || $show_edit_form): ?>
        <!-- Create/Edit Gig Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title" style="margin-bottom: 0;">
                    <i class="fas fa-<?php echo $show_edit_form ? 'edit' : 'plus'; ?>"></i>
                    <?php echo $show_edit_form ? 'Edit Gig' : 'Create New Gig'; ?>
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_PATH; ?>/freelancer/gig-action.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $show_edit_form ? 'edit' : 'create'; ?>">
                    <?php if ($show_edit_form): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_gig['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="title" class="form-label">Gig Title *</label>
                                <input type="text" id="title" name="title" class="form-control"
                                       placeholder="I will..." required maxlength="100"
                                       value="<?php echo $show_edit_form ? htmlspecialchars($edit_gig['title']) : ''; ?>">
                                <small style="color: var(--text-muted);">
                                    Example: "I will create a professional WordPress website"
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category" class="form-label">Category *</label>
                                <select id="category" name="category" class="form-control" required>
                                    <option value="">Select category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['name']); ?>"
                                                <?php echo $show_edit_form && $edit_gig['category'] == $cat['name'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="budget" class="form-label">Price ($) *</label>
                                <input type="number" id="budget" name="budget" class="form-control"
                                       placeholder="50" required min="5" step="0.01"
                                       value="<?php echo $show_edit_form ? $edit_gig['budget'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="delivery_time" class="form-label">Delivery Time (days) *</label>
                                <input type="number" id="delivery_time" name="delivery_time" class="form-control" 
                                       placeholder="3" required min="1" max="90">
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description" class="form-label">Description *</label>
                                <textarea id="description" name="description" class="form-control" rows="6"
                                          placeholder="Describe what you will do, your experience, and what clients can expect..."
                                          required><?php echo $show_edit_form ? htmlspecialchars($edit_gig['description']) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $show_edit_form ? 'Update Gig' : 'Create Gig'; ?>
                        </button>
                        <a href="<?php echo BASE_PATH; ?>/freelancer/gigs.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Gigs List -->
    <?php if (count($gigs) > 0): ?>
        <div class="row">
            <?php foreach ($gigs as $gig): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 style="color: var(--text-primary); margin-bottom: 0.5rem;">
                                        <?php echo htmlspecialchars($gig['title']); ?>
                                    </h5>
                                    <div>
                                        <span class="badge badge-<?php echo $gig['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($gig['status']); ?>
                                        </span>
                                        <span class="badge badge-secondary">
                                            <?php echo htmlspecialchars($gig['category']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm" style="background: var(--bg-tertiary);" 
                                            data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="<?php echo BASE_PATH; ?>/gig-details.php?id=<?php echo $gig['id']; ?>" target="_blank">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="?action=edit&id=<?php echo $gig['id']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <?php if ($gig['status'] == 'active'): ?>
                                                <a class="dropdown-item" href="<?php echo BASE_PATH; ?>/freelancer/gig-action.php?action=deactivate&id=<?php echo $gig['id']; ?>">
                                                    <i class="fas fa-pause"></i> Deactivate
                                                </a>
                                            <?php elseif ($gig['status'] == 'inactive' && !$gig['deactivated_by_admin']): ?>
                                                <a class="dropdown-item" href="<?php echo BASE_PATH; ?>/freelancer/gig-action.php?action=activate&id=<?php echo $gig['id']; ?>">
                                                    <i class="fas fa-play"></i> Activate
                                                </a>
                                            <?php elseif ($gig['status'] == 'inactive' && $gig['deactivated_by_admin']): ?>
                                                <span class="dropdown-item text-muted" title="This gig was deactivated by an admin">
                                                    <i class="fas fa-lock"></i> Deactivated by Admin
                                                </span>
                                            <?php endif; ?>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger"
                                               href="<?php echo BASE_PATH; ?>/freelancer/gig-action.php?action=delete&id=<?php echo $gig['id']; ?>"
                                               data-confirm="Are you sure you want to delete this gig?">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 1rem;">
                                <?php echo htmlspecialchars(substr($gig['description'], 0, 120)); ?>...
                            </p>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div style="padding: 0.75rem; background: var(--bg-tertiary); border-radius: var(--radius-md); text-align: center;">
                                        <div style="color: var(--primary-color); font-size: 1.5rem; font-weight: 700;">
                                            $<?php echo number_format($gig['budget'], 2); ?>
                                        </div>
                                        <div style="color: var(--text-muted); font-size: 0.85rem;">
                                            Price
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="padding: 0.75rem; background: var(--bg-tertiary); border-radius: var(--radius-md); text-align: center;">
                                        <div style="color: var(--success-color); font-size: 1.5rem; font-weight: 700;">
                                            <?php echo $gig['delivery_time']; ?>
                                        </div>
                                        <div style="color: var(--text-muted); font-size: 0.85rem;">
                                            Days
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                                <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                    <i class="fas fa-eye"></i> <?php echo $gig['views']; ?> views
                                </div>
                                <div>
                                    <span style="color: var(--text-secondary); font-size: 0.9rem;">
                                        <i class="fas fa-shopping-cart"></i> 
                                        <?php echo $gig['total_orders']; ?> order<?php echo $gig['total_orders'] != 1 ? 's' : ''; ?>
                                    </span>
                                    <?php if ($gig['pending_orders'] > 0): ?>
                                        <span class="badge badge-warning" style="margin-left: 0.5rem;">
                                            <?php echo $gig['pending_orders']; ?> pending
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif (!$show_create_form): ?>
        <div class="card text-center" style="padding: 3rem;">
            <i class="fas fa-briefcase" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h4 style="color: var(--text-primary);">No Gigs Yet</h4>
            <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                Create your first gig to start receiving orders from clients
            </p>
            <a href="?action=create" class="btn btn-primary btn-lg">
                <i class="fas fa-plus"></i> Create Your First Gig
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
