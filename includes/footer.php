    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-graduation-cap"></i> EduCourse</h5>
                    <p>Platform Digital Business Technology yang menggabungkan pendidikan, bisnis, dan teknologi untuk membantu Anda berkembang di era digital.</p>
                </div>
                <div class="col-md-2">
                    <h6>Fitur</h6>
                    <ul class="list-unstyled">
                        <li><a href="courses.php" class="text-light">Kursus Online</a></li>
                        <li><a href="marketplace.php" class="text-light">Marketplace</a></li>
                        <li><a href="finance.php" class="text-light">Manajemen Keuangan</a></li>
                        <li><a href="business.php" class="text-light">Manajemen Bisnis</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Kategori Kursus</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light">Web Development</a></li>
                        <li><a href="#" class="text-light">Mobile Development</a></li>
                        <li><a href="#" class="text-light">Digital Marketing</a></li>
                        <li><a href="#" class="text-light">Business Management</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Kontak</h6>
                    <p><i class="fas fa-envelope"></i> info@educourse.com</p>
                    <p><i class="fas fa-phone"></i> +62 123 456 789</p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2025 EduCourse. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>Dibuat dengan <i class="fas fa-heart text-danger"></i> untuk kemajuan pendidikan digital Indonesia</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js for financial charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-success') || alert.classList.contains('alert-info')) {
                    alert.style.display = 'none';
                }
            });
        }, 5000);
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>