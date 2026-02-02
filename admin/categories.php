<?php
$page_title = 'Categories Management';
require_once '../includes/header.php';

$base_path = '/webapp';

if (!isLoggedIn() || getCurrentUserRole() != 'admin') {
    header('Location: ' . $base_path . '/login.php');
    exit;
}

// Handle form submission for adding new category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    try {
        $conn = getDBConnection();
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $icon = trim($_POST['icon']);

        if (empty($name)) {
            $error = 'Category name is required';
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $description, $icon);
            $stmt->execute();
            $stmt->close();
            $success = 'Category added successfully';
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $error = 'Failed to add category: ' . $e->getMessage();
    }
}

// Get categories
try {
    $conn = getDBConnection();
    $result = $conn->query("
        SELECT * FROM categories
        ORDER BY created_at DESC
    ");
    $categories = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $categories = [];
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mt-5">
        <h1>Categories Management</h1>
        <a href="<?php echo BASE_PATH; ?>/admin/dashboard.php" class="btn btn-outline">Back to Dashboard</a>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Add New Category Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Add New Category</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="name">Category Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="icon">Icon Class (FontAwesome)</label>
                            <input type="text" class="form-control" id="icon" name="icon" placeholder="fa-code" value="fa-tags" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="description">Description</label>
                            <input type="text" class="form-control" id="description" name="description">
                        </div>
                    </div>
                </div>
                <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
            </form>
        </div>
    </div>

    <!-- Categories List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">All Categories</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Icon</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                                <td><i class="fas <?php echo htmlspecialchars($category['icon'] ?? 'fa-tags'); ?>"></i></td>
                                <td>
                                    <span class="badge bg-<?php echo $category['status'] == 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo htmlspecialchars($category['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo BASE_PATH; ?>/admin/category-action.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <?php if ($category['status'] == 'active'): ?>
                                        <a href="<?php echo BASE_PATH; ?>/admin/category-action.php?action=deactivate&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-warning">Deactivate</a>
                                    <?php elseif ($category['status'] == 'inactive'): ?>
                                        <a href="<?php echo BASE_PATH; ?>/admin/category-action.php?action=activate&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-success">Activate</a>
                                    <?php endif; ?>
                                    <a href="<?php echo BASE_PATH; ?>/admin/category-action.php?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
