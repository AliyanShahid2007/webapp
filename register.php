<?php
$page_title = 'Register';
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectToDashboard();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'client');
    
    // Validation
    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!isValidEmail($email)) {
        $error = 'Invalid email address';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters long';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!in_array($role, ['client', 'freelancer'])) {
        $error = 'Invalid role selected';
    } else {
        try {
            $conn = getDBConnection();

            // Check if username exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->fetch_assoc()) {
                $error = 'Username already exists';
                $stmt->close();
            } else {
                $stmt->close();
                // Check if email exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->fetch_assoc()) {
                    $error = 'Email already registered';
                    $stmt->close();
                } else {
                    $stmt->close();
                    // Create user
                    $hashed_password = hashPassword($password);
                    $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, 'pending')");
                    $stmt->bind_param("sssss", $name, $username, $email, $hashed_password, $role);
                    $stmt->execute();
                    $user_id = $conn->insert_id;
                    $stmt->close();

                    // Create freelancer profile if role is freelancer
                    if ($role === 'freelancer') {
                        $stmt = $conn->prepare("INSERT INTO freelancer_profiles (user_id) VALUES (?)");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $stmt->close();
                    }

                    $success = 'Registration successful! Your account is pending approval. You will be able to login once admin approves your account.';
                }
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
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus"></i> Create Account
                    </h3>
                    <p style="color: var(--text-secondary); margin-bottom: 0;">Join FreelanceHub today</p>
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
                        <div class="text-center">
                            <a href="<?php echo $base_path?>/login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Go to Login
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="form-group">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user"></i> Full Name
                                </label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       placeholder="Enter your full name" required 
                                       value="<?php echo htmlspecialchars($name ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    <i class="fas fa-at"></i> Username
                                </label>
                                <input type="text" id="username" name="username" class="form-control" 
                                       placeholder="Choose a username" required 
                                       value="<?php echo htmlspecialchars($username ?? ''); ?>"
                                       pattern="[a-zA-Z0-9_]{3,}">
                                <small style="color: var(--text-muted);">At least 3 characters, letters, numbers and underscore only</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email Address
                                </label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       placeholder="Enter your email" required 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <input type="password" id="password" name="password" class="form-control" 
                                       placeholder="Choose a password" required minlength="6">
                                <small style="color: var(--text-muted);">At least 6 characters</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock"></i> Confirm Password
                                </label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                       placeholder="Confirm your password" required minlength="6">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user-tag"></i> I want to
                                </label>
                                <div style="display: flex; gap: 1rem;">
                                    <label style="flex: 1; cursor: pointer;">
                                        <input type="radio" name="role" value="freelancer" 
                                               <?php echo (isset($role) && $role == 'freelancer') || !isset($role) ? 'checked' : ''; ?>>
                                        <div class="card" style="padding: 1rem; text-align: center; border: 2px solid var(--border-color);">
                                            <i class="fas fa-laptop-code" style="font-size: 2rem; color: var(--primary-color);"></i>
                                            <h6 style="margin-top: 0.5rem; color: var(--text-primary);">Become a Seller</h6>
                                            <small style="color: var(--text-secondary);">As a Freelancer</small>
                                        </div>
                                    </label>
                                    
                                    <label style="flex: 1; cursor: pointer;">
                                        <input type="radio" name="role" value="client" 
                                               <?php echo isset($role) && $role == 'client' ? 'checked' : ''; ?>>
                                        <div class="card" style="padding: 1rem; text-align: center; border: 2px solid var(--border-color);">
                                            <i class="fas fa-shopping-cart" style="font-size: 2rem; color: var(--secondary-color);"></i>
                                            <h6 style="margin-top: 0.5rem; color: var(--text-primary);">Buy Services</h6>
                                            <small style="color: var(--text-secondary);">As a Client</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-user-plus"></i> Create Account
                                </button>
                            </div>
                        </form>
                        
                        <hr style="border-color: var(--border-color); margin: 1.5rem 0;">
                        
                        <div class="text-center">
                            <p style="color: var(--text-secondary);">
                                Already have an account?
                                <a href="<?php echo $base_path; ?>/login.php" style="color: var(--primary-color); font-weight: 600;">Login</a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
input[type="radio"] {
    display: none;
}

input[type="radio"]:checked + .card {
    border-color: var(--primary-color) !important;
    background-color: rgba(99, 102, 241, 0.05);
}
</style>

<?php require_once 'includes/footer.php'; ?>
