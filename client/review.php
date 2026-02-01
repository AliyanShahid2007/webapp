<?php
$page_title = 'Leave Review';
require_once __DIR__ . '/../includes/header.php';

// Check client access
requireRole('client');

$order_id = (int)($_GET['order_id'] ?? 0);

if (!$order_id) {
    redirectWithMessage('/client/orders.php', 'Invalid order ID', 'danger');
}

try {
    $conn = getDBConnection();

    // Get order details and verify ownership
    $stmt = $conn->prepare("
        SELECT o.*,
               g.title as gig_title,
               u.name as freelancer_name,
               fp.profile_pic
        FROM orders o
        JOIN gigs g ON o.gig_id = g.id
        JOIN users u ON o.freelancer_id = u.id
        JOIN freelancer_profiles fp ON u.id = fp.user_id
        WHERE o.id = ? AND o.client_id = ?
    ");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if (!$order) {
        redirectWithMessage('/client/orders.php', 'Order not found or access denied', 'danger');
    }

    if ($order['status'] !== 'completed') {
        redirectWithMessage('/client/orders.php', 'You can only review completed orders', 'warning');
    }

    // Check if review already exists
    $stmt = $conn->prepare("SELECT id FROM reviews WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_review = $result->fetch_assoc();
    $stmt->close();

    if ($existing_review) {
        redirectWithMessage('/client/orders.php', 'You have already reviewed this order', 'info');
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $error = 'Please select a valid rating (1-5 stars)';
        } else {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO reviews (order_id, freelancer_id, client_id, rating, comment)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iiiis", $order_id, $order['freelancer_id'], $user_id, $rating, $comment);
                $stmt->execute();
                $stmt->close();

                redirectWithMessage('/client/orders.php', 'Thank you for your review!', 'success');
            } catch (Exception $e) {
                error_log($e->getMessage());
                $error = 'Failed to submit review. Please try again.';
            }
        }
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    redirectWithMessage('/client/orders.php', 'An error occurred', 'danger');
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 style="color: var(--text-primary); margin-bottom: 0;">
                        <i class="fas fa-star"></i> Leave a Review
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Order Summary -->
                    <div class="order-summary mb-4" style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 8px;">
                        <h5 style="color: var(--text-primary); margin-bottom: 1rem;">Order Details</h5>
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if ($order['profile_pic']): ?>
                                    <img src="<?php echo $base_path; ?>/uploads/profiles/<?php echo htmlspecialchars($order['profile_pic']); ?>"
                                         alt="<?php echo htmlspecialchars($order['freelancer_name']); ?>"
                                         class="profile-image profile-image-lg">
                                <?php else: ?>
                                    <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: 600;">
                                        <?php echo strtoupper(substr($order['freelancer_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-7">
                                <h6 style="color: var(--text-primary); margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($order['gig_title']); ?>
                                </h6>
                                <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">
                                    by <?php echo htmlspecialchars($order['freelancer_name']); ?>
                                </p>
                                <div style="display: flex; gap: 1rem; font-size: 0.9rem;">
                                    <span style="color: var(--success-color); font-weight: 600;">
                                        $<?php echo number_format($order['budget'], 2); ?>
                                    </span>
                                    <span style="color: var(--text-muted);">
                                        <?php echo $order['delivery_time']; ?> days delivery
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <span class="badge badge-success" style="font-size: 0.9rem;">
                                    <i class="fas fa-check-circle"></i> Completed
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Review Form -->
                    <form method="POST">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <label class="form-label" style="color: var(--text-primary); font-weight: 600;">
                                <i class="fas fa-star"></i> Rating *
                            </label>
                            <div class="rating-input">
                                <div class="stars" id="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="far fa-star star-option" data-rating="<?php echo $i; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="rating-input" required>
                                <div class="rating-text mt-2" id="rating-text" style="color: var(--text-secondary); font-size: 0.9rem;">
                                    Click to rate this freelancer
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="comment" class="form-label" style="color: var(--text-primary); font-weight: 600;">
                                <i class="fas fa-comment"></i> Review Comment (Optional)
                            </label>
                            <textarea
                                class="form-control"
                                id="comment"
                                name="comment"
                                rows="4"
                                placeholder="Share your experience working with this freelancer..."
                                maxlength="500"
                            ><?php echo htmlspecialchars($_POST['comment'] ?? ''); ?></textarea>
                            <div class="form-text" style="color: var(--text-muted);">
                                Maximum 500 characters
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Review
                            </button>
                            <a href="/client/orders.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Orders
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating-input .stars {
    font-size: 2rem;
    color: var(--text-muted);
    cursor: pointer;
}

.rating-input .star-option {
    transition: color 0.2s;
}

.rating-input .star-option:hover,
.rating-input .star-option.active {
    color: var(--warning-color);
}

.rating-input .star-option.active ~ .star-option {
    color: var(--text-muted);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-option');
    const ratingInput = document.getElementById('rating-input');
    const ratingText = document.getElementById('rating-text');
    let currentRating = 0;

    const ratingTexts = {
        1: 'Poor - 1 star',
        2: 'Fair - 2 stars',
        3: 'Good - 3 stars',
        4: 'Very Good - 4 stars',
        5: 'Excellent - 5 stars'
    };

    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = index + 1;
            currentRating = rating;
            ratingInput.value = rating;
            ratingText.textContent = ratingTexts[rating];

            // Update star display
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.add('active');
                    s.classList.remove('far');
                    s.classList.add('fas');
                } else {
                    s.classList.remove('active');
                    s.classList.remove('fas');
                    s.classList.add('far');
                }
            });
        });

        star.addEventListener('mouseover', function() {
            const rating = index + 1;
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.add('fas');
                    s.classList.remove('far');
                } else {
                    s.classList.add('far');
                    s.classList.remove('fas');
                }
            });
        });

        star.addEventListener('mouseout', function() {
            stars.forEach((s, i) => {
                if (i < currentRating) {
                    s.classList.add('fas', 'active');
                    s.classList.remove('far');
                } else {
                    s.classList.remove('fas', 'active');
                    s.classList.add('far');
                }
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
