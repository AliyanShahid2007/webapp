<?php
/**
 * API Endpoint for Platform Statistics
 * Returns real-time statistics for the homepage
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include necessary files
require_once '../config/database.php';
require_once '../includes/functions.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $conn = getDBConnection();

    // Get platform statistics
    $result = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'freelancer' AND status = 'active'");
    $total_freelancers = $result->fetch_row()[0];

    $result = $conn->query("SELECT COUNT(*) FROM gigs WHERE status = 'active'");
    $total_gigs = $result->fetch_row()[0];

    $result = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'");
    $completed_orders = $result->fetch_row()[0];

    $result = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'client' AND status = 'active'");
    $total_clients = $result->fetch_row()[0];

    $conn->close();

    // Return JSON response
    echo json_encode([
        'success' => true,
        'data' => [
            'total_freelancers' => (int)$total_freelancers,
            'total_gigs' => (int)$total_gigs,
            'completed_orders' => (int)$completed_orders,
            'total_clients' => (int)$total_clients
        ],
        'timestamp' => time()
    ]);

} catch (Exception $e) {
    error_log('Stats API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
