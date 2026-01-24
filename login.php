<?php
$page_title = 'Login';
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectToDashboard();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            $pdo = getPDOConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && verifyPassword($password, $user['password'])) {
                // Check if account is suspended
                if (isAccountSuspended($user['id'])) {
                    if ($user['status'] === 'suspended_permanent') {
                        $error = 'Your account has been permanently suspended';
                    } elseif ($user['status'] === 'suspended_7days') {
                        $suspended_until = date('M d, Y H:i', strtotime($user['suspended_until']));
                        $error = "Your account is suspended until $suspended_until";
                    }
                } elseif ($user['status'] === 'pending') {
                    $error = 'Your account is pending approval from admin';
                } else {
                    // Login successful
                    initSession();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['username'] = $user['username'];
                    
                    redirectToDashboard();
                }
            } else {
                $error = 'Invalid username or password';
            }
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again';
            error_log($e->getMessage());
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header text-center">
                    <h3 class="card-title">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </h3>
                    <p style="color: var(--text-secondary); margin-bottom: 0;">Welcome back to FreelanceHub</p>
                </div>
                
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label for="username" class="form-label">
                                <i class="fas fa-user"></i> Username or Email
                            </label>
                            <input type="text" id="username" name="username" class="form-control" 
                                   placeholder="Enter your username or email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Enter your password" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </div>
                    </form>
                    
                    <hr style="border-color: var(--border-color); margin: 1.5rem 0;">
                    
                    <div class="text-center">
                        <p style="color: var(--text-secondary);">
                            Don't have an account? 
                            <a href="/register.php" style="color: var(--primary-color); font-weight: 600;">Sign Up</a>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Demo Credentials -->
            <div class="card mt-3" style="background: var(--bg-tertiary); border: 1px dashed var(--border-color);">
                <div class="card-body">
                    <h6 style="color: var(--text-primary); margin-bottom: 1rem;">
                        <i class="fas fa-info-circle"></i> Demo Credentials
                    </h6>
                    <div style="font-size: 0.9rem; color: var(--text-secondary);">
                        <p style="margin-bottom: 0.5rem;"><strong>Admin:</strong> admin / admin123</p>
                        <p style="margin-bottom: 0;">Register as Freelancer or Client to test other features</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
