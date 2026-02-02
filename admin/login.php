<?php
$page_title = 'Admin Login';
require_once '../includes/header.php';

// Initialize session for tracking failed attempts
initSession();

// Check if user is blocked due to too many failed attempts
$max_attempts = 3;
$block_duration = 5 * 60; // 5 minutes in seconds

if (isset($_SESSION['admin_login_attempts']) && $_SESSION['admin_login_attempts'] >= $max_attempts) {
    $time_since_last_attempt = time() - ($_SESSION['admin_last_attempt_time'] ?? 0);

    if ($time_since_last_attempt < $block_duration) {
        // Still blocked, redirect to 403
        header('Location: ' . BASE_PATH . '/402.php');
        exit;
    } else {
        // Block duration expired, reset attempts
        unset($_SESSION['admin_login_attempts']);
        unset($_SESSION['admin_last_attempt_time']);
    }
}

// If already logged in and admin, redirect to dashboard
if (isLoggedIn() && getCurrentUserRole() === 'admin') {
    header('Location: ' . BASE_PATH . '/admin/dashboard.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = sanitize($_POST['login_input'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login_input) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $conn = getDBConnection();

            // Check if user exists and is admin (by email or username)
            $stmt = $conn->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND role = 'admin'");
            $stmt->bind_param("ss", $login_input, $login_input);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && verifyPassword($password, $user['password'])) {
                if ($user['status'] === 'active') {
                    // Login successful - reset failed attempts
                    unset($_SESSION['admin_login_attempts']);
                    unset($_SESSION['admin_last_attempt_time']);

                    initSession();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];

                    redirectWithMessage('/admin/dashboard.php', 'Welcome back, ' . htmlspecialchars($user['name']) . '!', 'success');
                } elseif ($user['status'] === 'pending') {
                    $error = 'Your account is pending approval';
                } elseif ($user['status'] === 'suspended_7days') {
                    $error = 'Your account is suspended for 7 days';
                } elseif ($user['status'] === 'suspended_permanent') {
                    $error = 'Your account is permanently suspended';
                } else {
                    $error = 'Account status unknown';
                }
            } else {
                // Failed login attempt
                $_SESSION['admin_login_attempts'] = ($_SESSION['admin_login_attempts'] ?? 0) + 1;
                $_SESSION['admin_last_attempt_time'] = time();

                $remaining_attempts = $max_attempts - $_SESSION['admin_login_attempts'];

                if ($_SESSION['admin_login_attempts'] >= $max_attempts) {
                    $error = 'Too many failed login attempts. Access blocked for 5 minutes.';
                } else {
                    $error = 'Invalid username/email or password. ' . $remaining_attempts . ' attempt(s) remaining.';
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = 'Login failed. Please try again.';
        }
    }
}
?>

<style>
.admin-login-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
}

.admin-login-card {
    background: var(--card-bg);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    border: 1px solid var(--border-color);
    overflow: hidden;
    position: relative;
    animation: slideUp 0.6s ease-out;
}

.admin-login-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
}

.admin-icon {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: var(--spacing-lg);
}

.admin-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--spacing-md);
}

.admin-subtitle {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-xl);
    font-size: 1.1rem;
}

.form-group {
    margin-bottom: var(--spacing-lg);
}

.form-label {
    display: block;
    margin-bottom: var(--spacing-sm);
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.95rem;
}

.form-control {
    width: 100%;
    padding: var(--spacing-md);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    background-color: var(--input-bg);
    color: var(--text-primary);
    font-size: 1rem;
    transition: var(--transition-normal);
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    transform: translateY(-2px);
}

.btn-admin-login {
    width: 100%;
    padding: var(--spacing-md);
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
    border: none;
    border-radius: var(--radius-lg);
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: var(--transition-normal);
    box-shadow: 0 4px 14px 0 rgba(99, 102, 241, 0.4);
}

.btn-admin-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
}

.btn-admin-login:active {
    transform: translateY(0);
}

.back-link {
    text-align: center;
    margin-top: var(--spacing-lg);
}

.back-link a {
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.95rem;
    transition: var(--transition-fast);
}

.back-link a:hover {
    color: var(--primary-color);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .admin-login-card {
        margin: var(--spacing-lg);
    }

    .admin-icon {
        font-size: 2.5rem;
    }

    .admin-title {
        font-size: 1.75rem;
    }
}
</style>

<div class="admin-login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7 col-sm-9">
                <div class="admin-login-card">
                    <div class="card-body p-5">
                        <!-- Admin Icon -->
                        <div class="text-center">
                            <div class="admin-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h1 class="admin-title">Admin Login</h1>
                            <p class="admin-subtitle">Access the administration panel</p>
                        </div>

                        <!-- Error Message -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form method="POST">
                            <div class="form-group">
                                <label for="login_input" class="form-label">
                                    <i class="fas fa-user"></i> Username or Email
                                </label>
                                <input type="text" class="form-control" id="login_input" name="login_input"
                                       placeholder="admin or admin@freelancehub.com" required
                                       value="<?php echo htmlspecialchars($_POST['login_input'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Enter your password" required>
                            </div>

                            <button type="submit" class="btn-admin-login">
                                <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
                            </button>
                        </form>

                        <!-- Back Link -->
                        <div class="back-link">
                            <a href="<?php echo BASE_PATH; ?>/">
                                <i class="fas fa-arrow-left"></i> Back to Homepage
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
