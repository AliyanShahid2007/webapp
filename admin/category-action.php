<?php
require_once __DIR__ . '/../includes/header.php';

// Check admin access
requireRole('admin');

$action = $_GET['action'] ?? '';
$category_id = (int)($_GET['id'] ?? 0);

if (!in_array($action, ['edit', 'activate', 'deactivate', 'delete'])) {
    redirectWithMessage('/admin/categories.php', 'Invalid action', 'danger');
}

// Handle edit form submission
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getDBConnection();
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $icon = trim($_POST['icon']);
        $status = $_POST['status'];

        if (empty($name)) {
            redirectWithMessage('/admin/category-action.php?action=edit&id=' . $category_id, 'Category name is required', 'danger');
        }

        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, icon = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $description, $icon, $status, $category_id);
        $stmt->execute();
        $stmt->close();

        redirectWithMessage('/admin/categories.php', 'Category updated successfully', 'success');
    } catch (Exception $e) {
        error_log($e->getMessage());
        redirectWithMessage('/admin/category-action.php?action=edit&id=' . $category_id, 'Failed to update category: ' . $e->getMessage(), 'danger');
    }
}

// Handle other actions
if ($action !== 'edit' && (!$category_id)) {
    redirectWithMessage('/admin/categories.php', 'Invalid category ID', 'danger');
}

try {
    $conn = getDBConnection();

    // Get category data for edit or other actions
    if ($action === 'edit' || in_array($action, ['activate', 'deactivate', 'delete'])) {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();

        if (!$category) {
            redirectWithMessage('/admin/categories.php', 'Category not found', 'danger');
        }
    }

    switch ($action) {
        case 'edit':
            // Display edit form
            ?>
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mt-5">
                    <h1>Edit Category</h1>
                    <a href="<?php echo BASE_PATH; ?>/admin/categories.php" class="btn btn-outline">Back to Categories</a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="name">Category Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="status">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="active" <?php echo $category['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo $category['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="icon">Icon Class (FontAwesome)</label>
                                        <input type="text" class="form-control" id="icon" name="icon" value="<?php echo htmlspecialchars($category['icon'] ?? 'fa-tags'); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="description">Description</label>
                                        <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($category['description'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Category</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php
            require_once __DIR__ . '/../includes/footer.php';
            exit;

        case 'activate':
            $stmt = $conn->prepare("UPDATE categories SET status = 'active' WHERE id = ?");
            $stmt->bind_param("i", $category_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/admin/categories.php', 'Category activated successfully', 'success');
            break;

        case 'deactivate':
            $stmt = $conn->prepare("UPDATE categories SET status = 'inactive' WHERE id = ?");
            $stmt->bind_param("i", $category_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/admin/categories.php', 'Category deactivated successfully', 'success');
            break;

        case 'delete':
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->bind_param("i", $category_id);
            $stmt->execute();
            $stmt->close();
            redirectWithMessage('/admin/categories.php', 'Category deleted successfully', 'success');
            break;
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    redirectWithMessage('/admin/categories.php', 'An error occurred while processing the action: ' . $e->getMessage(), 'danger');
}
