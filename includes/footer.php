    </main>
    
    <!-- Footer -->
    <footer class="footer" style="background-color: var(--card-bg); border-top: 1px solid var(--border-color); margin-top: 4rem; padding: 2rem 0;">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5 style="color: var(--primary-color); margin-bottom: 1rem;">
                        <i class="fas fa-briefcase"></i> FreelanceHub
                    </h5>
                    <p style="color: var(--text-secondary);">
                        Connect with talented freelancers and find quality services for your projects.
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 style="color: var(--text-primary); margin-bottom: 1rem;">Quick Links</h6>
                    <ul style="list-style: none; padding: 0;">
                        <li><a href="/browse-gigs.php" style="color: var(--text-secondary); text-decoration: none;">Browse Gigs</a></li>
                        <li><a href="/about.php" style="color: var(--text-secondary); text-decoration: none;">About Us</a></li>
                        <li><a href="/contact.php" style="color: var(--text-secondary); text-decoration: none;">Contact</a></li>
                        <li><a href="/terms.php" style="color: var(--text-secondary); text-decoration: none;">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 style="color: var(--text-primary); margin-bottom: 1rem;">Connect With Us</h6>
                    <div style="display: flex; gap: 1rem;">
                        <a href="#" style="color: var(--text-secondary); font-size: 1.5rem;"><i class="fab fa-facebook"></i></a>
                        <a href="#" style="color: var(--text-secondary); font-size: 1.5rem;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color: var(--text-secondary); font-size: 1.5rem;"><i class="fab fa-linkedin"></i></a>
                        <a href="#" style="color: var(--text-secondary); font-size: 1.5rem;"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr style="border-color: var(--border-color); margin: 2rem 0;">
            <div class="text-center" style="color: var(--text-muted);">
                <p>&copy; <?php echo date('Y'); ?> FreelanceHub. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/main.js"></script>
    
    <?php if (isset($extra_js)): ?>
        <?php echo $extra_js; ?>
    <?php endif; ?>
</body>
</html>
