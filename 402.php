<?php
$page_title = 'Access Denied - 402 Forbidden';
require_once 'includes/header.php';

// Check if user is logged in to show appropriate buttons
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $isLoggedIn ? getCurrentUserRole() : null;

// Check if user is blocked due to admin login attempts
$isBlocked = false;
$remainingTime = 0;
$blockDuration = 5 * 60; // 5 minutes in seconds

if (isset($_SESSION['admin_login_attempts']) && $_SESSION['admin_login_attempts'] >= 3) {
    $timeSinceLastAttempt = time() - ($_SESSION['admin_last_attempt_time'] ?? 0);
    if ($timeSinceLastAttempt < $blockDuration) {
        $isBlocked = true;
        $remainingTime = $blockDuration - $timeSinceLastAttempt;
    }
}
?>

<style>
.error-403-container {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
}

.error-card {
    background: var(--card-bg);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    border: 1px solid var(--border-color);
    overflow: hidden;
    position: relative;
    animation: slideUp 0.6s ease-out;
}

.error-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--danger-color) 0%, #dc2626 100%);
}

.error-icon {
    font-size: 5rem;
    color: var(--danger-color);
    margin-bottom: var(--spacing-lg);
    animation: bounceIn 0.8s ease-out;
    filter: drop-shadow(0 4px 8px rgba(239, 68, 68, 0.3));
}

.error-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: var(--spacing-md);
    background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.error-subtitle {
    font-size: 1.125rem;
    color: var(--text-secondary);
    margin-bottom: var(--spacing-lg);
    font-weight: 500;
}

.error-message {
    color: var(--text-muted);
    margin-bottom: var(--spacing-xl);
    line-height: 1.7;
}

.help-section {
    background: var(--bg-tertiary);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    margin-top: var(--spacing-xl);
    border-left: 4px solid var(--warning-color);
}

.help-section h5 {
    color: var(--warning-color);
    margin-bottom: var(--spacing-md);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.help-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.help-section li {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-sm);
    padding-left: var(--spacing-md);
    position: relative;
}

.help-section li::before {
    content: '•';
    color: var(--warning-color);
    font-weight: bold;
    position: absolute;
    left: 0;
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
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

.btn-group-custom {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
    flex-wrap: wrap;
}

.btn-custom {
    padding: var(--spacing-md) var(--spacing-xl);
    border-radius: var(--radius-lg);
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition-normal);
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-sm);
    min-width: 140px;
    justify-content: center;
}

.btn-custom:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-primary-custom {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
    box-shadow: 0 4px 14px 0 rgba(99, 102, 241, 0.4);
}

.btn-outline-custom {
    background: transparent;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
}

.btn-outline-custom:hover {
    background: var(--primary-color);
    color: white;
}

@media (max-width: 768px) {
    .error-icon {
        font-size: 4rem;
    }

    .error-title {
        font-size: 2rem;
    }

    .btn-group-custom {
        flex-direction: column;
        align-items: center;
    }

    .btn-custom {
        width: 100%;
        max-width: 250px;
    }
}
</style>

<div class="error-403-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="error-card">
                    <div class="card-body text-center p-5">
                        <!-- Error Icon -->
                        <div class="error-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>

                        <!-- Error Title -->
                        <h1 class="error-title">402 Forbidden</h1>
                        <h2 class="error-subtitle">Access Denied</h2>

                        <!-- Error Message -->
                        <div class="error-message">
                            <p class="mb-3">
                                Sorry, you don't have permission to access this page or perform this action.
                            </p>
                            <p>
                                This might be because:
                            </p>
                            <ul class="text-start d-inline-block">
                                <li>You need to log in with appropriate privileges</li>
                                <li>Your account doesn't have the required permissions</li>
                                <li>The page you're trying to access is restricted</li>
                            </ul>
                        </div>

                        <!-- Login Block Timer -->
                        <?php if ($isBlocked): ?>
                        <div class="timer-section">
                            <div class="timer-container">
                                <div class="timer-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="timer-content">
                                    <h4>Access Temporarily Blocked</h4>
                                    <p>You have exceeded the maximum number of login attempts. Please wait before trying again.</p>
                                    <div class="countdown-timer">
                                        <div class="timer-display" id="countdown-timer">
                                            <span id="minutes">--</span>:<span id="seconds">--</span>
                                        </div>
                                        <p class="timer-label">Time remaining to access admin login</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="btn-group-custom">
                            <a href="<?php echo BASE_PATH; ?>/" class="btn-custom btn-primary-custom">
                                <i class="fas fa-home"></i>
                                Go to Home
                            </a>

                            <?php if (!$isLoggedIn): ?>
                                <a href="<?php echo BASE_PATH; ?>/admin/login.php" class="btn-custom btn-outline-custom">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Login
                                </a>
                            <?php elseif ($userRole === 'admin'): ?>
                                <a href="<?php echo BASE_PATH; ?>/admin/dashboard.php" class="btn-custom btn-outline-custom">
                                    <i class="fas fa-tachometer-alt"></i>
                                    Admin Dashboard
                                </a>
                            <?php elseif ($userRole === 'freelancer'): ?>
                                <a href="<?php echo BASE_PATH; ?>/freelancer/dashboard.php" class="btn-custom btn-outline-custom">
                                    <i class="fas fa-briefcase"></i>
                                    Freelancer Dashboard
                                </a>
                            <?php elseif ($userRole === 'client'): ?>
                                <a href="<?php echo BASE_PATH; ?>/client/dashboard.php" class="btn-custom btn-outline-custom">
                                    <i class="fas fa-user-tie"></i>
                                    Client Dashboard
                                </a>
                            <?php endif; ?>

                            <button onclick="history.back()" class="btn-custom btn-outline-custom">
                                <i class="fas fa-arrow-left"></i>
                                Go Back
                            </button>
                        </div>

                        <!-- Help Section -->
                        <div class="help-section">
                            <h5>
                                <i class="fas fa-question-circle"></i>
                                Need Help?
                            </h5>
                            <ul>
                                <li>Check if you're logged in with the correct account</li>
                                <li>Contact support if you believe this is an error</li>
                                <li>Try accessing the page again after refreshing</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
<?php if ($isBlocked): ?>
// Initialize countdown timer
let remainingTime = <?php echo $remainingTime; ?>;

function updateTimer() {
    const minutesElement = document.getElementById('minutes');
    const secondsElement = document.getElementById('seconds');

    if (remainingTime <= 0) {
        // Timer expired, redirect to admin login
        window.location.href = '<?php echo BASE_PATH; ?>/admin/login.php';
        return;
    }

    const minutes = Math.floor(remainingTime / 60);
    const seconds = remainingTime % 60;

    // Format with leading zeros
    minutesElement.textContent = minutes.toString().padStart(2, '0');
    secondsElement.textContent = seconds.toString().padStart(2, '0');

    remainingTime--;
}

// Update timer immediately and then every second
updateTimer();
setInterval(updateTimer, 1000);
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>
