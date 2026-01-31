<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Define base path for the project (change this if your project is in a different directory)
$base_path = BASE_PATH;

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
    <link rel="icon" type="image/png" href="<?php echo $base_path; ?>/assets/images/favicon.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>/assets/css/style.css">
    
    <?php if (isset($extra_css)): ?>
        <?php echo $extra_css; ?>
    <?php endif; ?>

    <script>window.basePath = '<?php echo $base_path; ?>';</script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <a href="<?php echo $base_path; ?>/" class="navbar-brand">
                    <i class="fas fa-briefcase"></i>
                    FreelanceHub
                </a>

                <ul class="navbar-menu" id="navbar-menu">
                    <li class="menu-close">
                        <button class="menu-close-btn" id="menu-close-btn" aria-label="Close menu">
                            <i class="fas fa-times"></i>
                        </button>
                    </li>
                    <?php if (!$is_logged_in): ?>
                        <li><a href="<?php echo $base_path; ?>/" class="<?php echo $current_page == 'index' ? 'active' : ''; ?>">Home</a></li>
                        <li><a href="<?php echo $base_path; ?>/browse-gigs.php" class="<?php echo $current_page == 'browse-gigs' ? 'active' : ''; ?>">Browse Gigs</a></li>
                        <li><a href="<?php echo $base_path; ?>/login.php" class="<?php echo $current_page == 'login' ? 'active' : ''; ?>">Login</a></li>
                        <li><a href="<?php echo $base_path; ?>/register.php" class="<?php echo $current_page == 'register' ? 'active' : ''; ?>"><span class="btn btn-primary btn-sm">Sign Up</span></a></li>
                    <?php else: ?>
                        <?php if ($user_role == 'admin'): ?>
                            <li><a href="<?php echo $base_path; ?>/admin/dashboard.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : ''; ?>">Dashboard</a></li>
                            <li><a href="<?php echo $base_path; ?>/admin/users.php">Users</a></li>
                            <li><a href="<?php echo $base_path; ?>/admin/gigs.php">Gigs</a></li>
                            <li><a href="<?php echo $base_path; ?>/admin/orders.php">Orders</a></li>
                        <?php elseif ($user_role == 'freelancer'): ?>
                            <li><a href="<?php echo $base_path; ?>/freelancer/dashboard.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/freelancer/') !== false ? 'active' : ''; ?>">Dashboard</a></li>
                            <li><a href="<?php echo $base_path; ?>/freelancer/gigs.php">My Gigs</a></li>
                            <li><a href="<?php echo $base_path; ?>/freelancer/orders.php">Orders</a></li>
                            <li><a href="<?php echo $base_path; ?>/freelancer/profile.php">Edit Profile</a></li>
                        <?php elseif ($user_role == 'client'): ?>
                            <li><a href="<?php echo $base_path; ?>/client/dashboard.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/client/') !== false ? 'active' : ''; ?>">Dashboard</a></li>
                            <li><a href="<?php echo $base_path; ?>/browse-gigs.php">Browse Gigs</a></li>
                            <li><a href="<?php echo $base_path; ?>/client/orders.php">My Orders</a></li>
                        <?php endif; ?>
                        
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">
                                <i class="fas fa-user-circle"></i>
                                <?php echo htmlspecialchars($user_data['name']); ?>
                            </a>
                        </li>
                        <li><a href="<?php echo $base_path; ?>/logout.php" class="btn btn-outline btn-sm">Logout</a></li>
                    <?php endif; ?>
                    
                    <li>
                        <button id="theme-toggle" class="theme-toggle" aria-label="Toggle theme">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                </ul>

                <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Toggle mobile menu">
                    <i class="fas fa-bars"></i>
                </button>
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
