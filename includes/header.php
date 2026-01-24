<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$is_logged_in = isLoggedIn();
$user_role = getCurrentUserRole();
$user_id = getCurrentUserId();

// Get user data if logged in
$user_data = null;
if ($is_logged_in) {
    $user_data = getUserById($user_id);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>FreelanceHub</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <?php if (isset($extra_css)): ?>
        <?php echo $extra_css; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <a href="/" class="navbar-brand">
                    <i class="fas fa-briefcase"></i>
                    FreelanceHub
                </a>
                
                <ul class="navbar-menu">
                    <?php if (!$is_logged_in): ?>
                        <li><a href="/" class="<?php echo $current_page == 'index' ? 'active' : ''; ?>">Home</a></li>
                        <li><a href="/browse-gigs.php" class="<?php echo $current_page == 'browse-gigs' ? 'active' : ''; ?>">Browse Gigs</a></li>
                        <li><a href="/login.php" class="<?php echo $current_page == 'login' ? 'active' : ''; ?>">Login</a></li>
                        <li><a href="/register.php" class="<?php echo $current_page == 'register' ? 'active' : ''; ?>"><span class="btn btn-primary btn-sm">Sign Up</span></a></li>
                    <?php else: ?>
                        <?php if ($user_role == 'admin'): ?>
                            <li><a href="/admin/dashboard.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : ''; ?>">Dashboard</a></li>
                            <li><a href="/admin/users.php">Users</a></li>
                            <li><a href="/admin/gigs.php">Gigs</a></li>
                            <li><a href="/admin/orders.php">Orders</a></li>
                        <?php elseif ($user_role == 'freelancer'): ?>
                            <li><a href="/freelancer/dashboard.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/freelancer/') !== false ? 'active' : ''; ?>">Dashboard</a></li>
                            <li><a href="/freelancer/gigs.php">My Gigs</a></li>
                            <li><a href="/freelancer/orders.php">Orders</a></li>
                            <li><a href="/freelancer/profile.php">Profile</a></li>
                        <?php elseif ($user_role == 'client'): ?>
                            <li><a href="/client/dashboard.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/client/') !== false ? 'active' : ''; ?>">Dashboard</a></li>
                            <li><a href="/browse-gigs.php">Browse Gigs</a></li>
                            <li><a href="/client/orders.php">My Orders</a></li>
                        <?php endif; ?>
                        
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">
                                <?php if ($user_data && $user_data['role'] == 'freelancer'): ?>
                                    <?php
                                    $pdo = getPDOConnection();
                                    $stmt = $pdo->prepare("SELECT profile_pic FROM freelancer_profiles WHERE user_id = ?");
                                    $stmt->execute([$user_id]);
                                    $profile = $stmt->fetch();
                                    ?>
                                    <?php if ($profile && $profile['profile_pic']): ?>
                                        <img src="/uploads/profiles/<?php echo htmlspecialchars($profile['profile_pic']); ?>" alt="Profile" class="profile-image">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-user-circle"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($user_data['name']); ?>
                            </a>
                        </li>
                        <li><a href="/logout.php" class="btn btn-outline btn-sm">Logout</a></li>
                    <?php endif; ?>
                    
                    <li>
                        <button id="theme-toggle" class="theme-toggle" aria-label="Toggle theme">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    <?php
    $flash = getFlashMessage();
    if ($flash):
    ?>
    <div class="container mt-3">
        <div class="alert alert-<?php echo $flash['type']; ?>" data-auto-hide="5000">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="main-content">
