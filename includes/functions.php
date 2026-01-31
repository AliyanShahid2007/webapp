<?php
/**
 * Helper Functions
 */

// Start session if not already started
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    initSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Get current user ID
function getCurrentUserId() {
    initSession();
    return $_SESSION['user_id'] ?? null;
}

// Get current user role
function getCurrentUserRole() {
    initSession();
    return $_SESSION['user_role'] ?? null;
}

// Check if user has specific role
function hasRole($role) {
    return getCurrentUserRole() === $role;
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_PATH . '/login.php');
        exit;
    }
}

// Redirect to dashboard based on role
function redirectToDashboard() {
    $role = getCurrentUserRole();

    switch ($role) {
        case 'admin':
            header('Location: ' . BASE_PATH . '/admin/dashboard.php');
            break;
        case 'freelancer':
            header('Location: ' . BASE_PATH . '/freelancer/dashboard.php');
            break;
        case 'client':
            header('Location: ' . BASE_PATH . '/client/dashboard.php');
            break;
        default:
            header('Location: ' . BASE_PATH . '/index.php');
    }
    exit;
}

// Check if user has specific role, redirect if not
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /403.php');
        exit;
    }
}

// Sanitize input
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate CSRF token
function generateCSRFToken() {
    initSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    initSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Format date
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Calculate time ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'just now';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}

// Upload file
function uploadFile($file, $uploadDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file upload'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred'];
    }
    
    if ($file['size'] > 5242880) { // 5MB
        return ['success' => false, 'message' => 'File size exceeds 5MB limit'];
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $targetPath = $uploadDir . '/' . $fileName;
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $fileName, 'path' => $targetPath];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

// Delete file
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

// Get user data by ID
function getUserById($userId) {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

// Check if account is suspended
function isAccountSuspended($userId) {
    $user = getUserById($userId);
    
    if (!$user) {
        return true;
    }
    
    if ($user['status'] === 'suspended_permanent') {
        return true;
    }
    
    if ($user['status'] === 'suspended_7days') {
        if ($user['suspended_until'] && strtotime($user['suspended_until']) > time()) {
            return true;
        } else {
            // Suspension period over, reactivate account
            $pdo = getPDOConnection();
            $stmt = $pdo->prepare("UPDATE users SET status = 'active', suspended_until = NULL WHERE id = ?");
            $stmt->execute([$userId]);
            return false;
        }
    }
    
    return false;
}

// Send JSON response
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    initSession();
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: " . BASE_PATH . $url);
    exit;
}

// Get and clear flash message
function getFlashMessage() {
    initSession();
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return $message;
    }
    return null;
}

// Pagination helper
function paginate($page, $perPage, $totalItems) {
    $totalPages = ceil($totalItems / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    
    return [
        'page' => $page,
        'per_page' => $perPage,
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'offset' => $offset
    ];
}
