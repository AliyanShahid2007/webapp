<?php
$page_title = 'Edit Profile';
require_once __DIR__ . '/../includes/header.php';

// Check freelancer access
requireRole('freelancer');

$error = '';
$success = '';

try {
    $pdo = getPDOConnection();
    
    // Get current profile
    $stmt = $pdo->prepare("SELECT * FROM freelancer_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch();
    
    // Get categories
    $categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $bio = sanitize($_POST['bio'] ?? '');
        $category = sanitize($_POST['category'] ?? '');
        $skills = sanitize($_POST['skills'] ?? '');
        
        // Handle profile picture upload
        $profile_pic = $profile['profile_pic'];
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
            $upload_result = uploadFile($_FILES['profile_pic'], __DIR__ . '/../uploads/profiles');
            if ($upload_result['success']) {
                // Delete old profile pic
                if ($profile_pic && file_exists(__DIR__ . '/../uploads/profiles/' . $profile_pic)) {
                    unlink(__DIR__ . '/../uploads/profiles/' . $profile_pic);
                }
                $profile_pic = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        }
        
        // Handle portfolio images
        $portfolio_images = $profile['portfolio_images'] ? json_decode($profile['portfolio_images'], true) : [];
        if (isset($_FILES['portfolio']) && !empty($_FILES['portfolio']['name'][0])) {
            foreach ($_FILES['portfolio']['name'] as $key => $name) {
                if ($_FILES['portfolio']['error'][$key] == UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['portfolio']['name'][$key],
                        'type' => $_FILES['portfolio']['type'][$key],
                        'tmp_name' => $_FILES['portfolio']['tmp_name'][$key],
                        'error' => $_FILES['portfolio']['error'][$key],
                        'size' => $_FILES['portfolio']['size'][$key]
                    ];
                    $upload_result = uploadFile($file, __DIR__ . '/../uploads/portfolio');
                    if ($upload_result['success']) {
                        $portfolio_images[] = $upload_result['filename'];
                    }
                }
            }
        }
        
        if (!$error) {
            // Update profile
            $stmt = $pdo->prepare("
                UPDATE freelancer_profiles 
                SET bio = ?, category = ?, skills = ?, profile_pic = ?, portfolio_images = ?
                WHERE user_id = ?
            ");
            $stmt->execute([
                $bio,
                $category,
                $skills,
                $profile_pic,
                json_encode($portfolio_images),
                $user_id
            ]);
            
            $success = 'Profile updated successfully!';
            
            // Refresh profile data
            $stmt = $pdo->prepare("SELECT * FROM freelancer_profiles WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $profile = $stmt->fetch();
        }
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while updating profile';
}

$portfolio_images = $profile['portfolio_images'] ? json_decode($profile['portfolio_images'], true) : [];
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </h3>
                </div>
                
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <!-- Profile Picture -->
                        <div class="form-group">
                            <label class="form-label">Profile Picture</label>
                            <div style="display: flex; align-items: center; gap: 1.5rem;">
                                <div>
                                    <?php if ($profile['profile_pic']): ?>
                                        <img src="<?php echo BASE_PATH; ?>/uploads/profiles/<?php echo htmlspecialchars($profile['profile_pic']); ?>"
                                             alt="Profile"
                                             id="profile-preview"
                                             class="profile-image profile-image-lg">
                                    <?php else: ?>
                                        <div id="profile-preview" style="width: 120px; height: 120px; border-radius: 50%; background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: 600;">
                                            <?php echo strtoupper(substr($user_data['name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div style="flex: 1;">
                                    <input type="file" id="profile_pic" name="profile_pic" class="form-control" 
                                           accept="image/*" data-preview="profile-preview">
                                    <small style="color: var(--text-muted);">
                                        Recommended: Square image, at least 300x300px. Max 5MB.
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bio -->
                        <div class="form-group">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea id="bio" name="bio" class="form-control" rows="5" 
                                      placeholder="Tell clients about yourself, your experience, and expertise..."
                                      data-max-length="1000" 
                                      data-counter="bio-counter"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                            <div style="display: flex; justify-content: space-between;">
                                <small style="color: var(--text-muted);">Write a compelling bio to attract clients</small>
                                <small id="bio-counter" style="color: var(--text-muted);"></small>
                            </div>
                        </div>
                        
                        <!-- Category -->
                        <div class="form-group">
                            <label for="category" class="form-label">Category</label>
                            <select id="category" name="category" class="form-control" required>
                                <option value="">Select your main category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>" 
                                            <?php echo $profile['category'] == $cat['name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Skills -->
                        <div class="form-group">
                            <label for="skills" class="form-label">Skills</label>
                            <input type="text" id="skills" name="skills" class="form-control" 
                                   placeholder="e.g., PHP, JavaScript, MySQL, WordPress, React"
                                   value="<?php echo htmlspecialchars($profile['skills'] ?? ''); ?>">
                            <small style="color: var(--text-muted);">Separate skills with commas</small>
                        </div>
                        
                        <!-- Portfolio Images -->
                        <div class="form-group">
                            <label class="form-label">Portfolio Images</label>
                            
                            <?php if (count($portfolio_images) > 0): ?>
                                <div class="row mb-3">
                                    <?php foreach ($portfolio_images as $index => $image): ?>
                                        <div class="col-md-4 mb-3">
                                            <div style="position: relative;">
                                                <img src="<?php echo BASE_PATH; ?>/uploads/portfolio/<?php echo htmlspecialchars($image); ?>"
                                                     alt="Portfolio"
                                                     style="width: 100%; height: 150px; object-fit: cover; border-radius: var(--radius-md);">
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        style="position: absolute; top: 0.5rem; right: 0.5rem;"
                                                        onclick="removePortfolioImage('<?php echo htmlspecialchars($image); ?>')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <input type="file" name="portfolio[]" class="form-control" 
                                   accept="image/*" multiple>
                            <small style="color: var(--text-muted);">
                                Upload portfolio images (Max 5 images, 5MB each)
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-save"></i> Save Profile
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="card-footer">
                    <a href="<?php echo BASE_PATH; ?>/freelancer/dashboard.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function removePortfolioImage(filename) {
    if (confirm('Remove this portfolio image?')) {
        // TODO: Implement AJAX removal
        showToast('Feature coming soon', 'info');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
