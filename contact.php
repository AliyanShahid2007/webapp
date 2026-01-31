<?php
$page_title = 'Contact Us';
require_once 'includes/header.php';

$message_sent = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message_content = sanitize($_POST['message'] ?? '');
    
    if (!$name || !$email || !$subject || !$message_content) {
        $error_message = 'Please fill in all fields.';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // In a real application, you would save this to a database or send an email
        // For now, we'll just show a success message
        $message_sent = true;
    }
}
?>

<div class="container" style="padding: 2rem 0;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 style="margin-bottom: 2rem;">Contact Us</h1>
            
            <?php if ($message_sent): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Thank You!</strong> Your message has been sent successfully. We'll get back to you soon.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span style="color: red;">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message <span style="color: red;">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                    </form>
                </div>
            </div>

            <div style="margin-top: 3rem; text-align: center;">
                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Other Ways to Reach Us</h3>
                <p>
                    <i class="fas fa-envelope" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                    <strong>Email:</strong> support@freelancehub.com
                </p>
                <p>
                    <i class="fas fa-phone" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                    <strong>Phone:</strong> +1 (555) 123-4567
                </p>
                <p>
                    <i class="fas fa-map-marker-alt" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                    <strong>Address:</strong> 123 Freelance Street, Tech City, TC 12345
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
