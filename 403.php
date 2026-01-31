<?php
$page_title = 'Access Denied';
require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="fas fa-lock fa-4x text-danger"></i>
                    </div>
                    <h1 class="h3 mb-3">Access Denied</h1>
                    <p class="text-muted mb-4">
                        You don't have permission to access this page.
                    </p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="/" class="btn btn-primary">Go to Home</a>
                        <a href="/login.php" class="btn btn-outline-primary">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
