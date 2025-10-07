<?php 
$page_title = "Beranda";
include 'includes/header.php'; 
?>

<div class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">
            <i class="fas fa-rocket"></i> DIGITAL BUSINESS TECHNOLOGY
        </h1>
        <p class="lead mb-4">Platform Terpadu untuk Web & Apps Development</p>
        <p class="mb-4">Jenjang: SMP - SMA/K Sederajat | Format: Individu</p>
        
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto">
                <div class="bg-white rounded-3 p-4 text-dark">
                    <h3 class="text-primary mb-3">Ruang Lingkup Lomba</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-lightbulb text-warning"></i> Kreativitas & Inovasi</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Aplikasi web atau mobile sederhana</li>
                                <li><i class="fas fa-check text-success"></i> Sistem manajemen usaha</li>
                                <li><i class="fas fa-check text-success"></i> Marketplace mini</li>
                                <li><i class="fas fa-check text-success"></i> Aplikasi keuangan sederhana</li>
                                <li><i class="fas fa-check text-success"></i> Platform edukasi bisnis</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-target text-info"></i> Fokus Pengembangan</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-arrow-right text-primary"></i> Kreativitas ide</li>
                                <li><i class="fas fa-arrow-right text-primary"></i> Penerapan teknologi</li>
                                <li><i class="fas fa-arrow-right text-primary"></i> Dampak untuk dunia usaha/masyarakat</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <?php if(isLoggedIn()): ?>
                <a href="dashboard.php" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            <?php else: ?>
                <a href="register.php" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-user-plus"></i> Mulai Sekarang
                </a>
                <a href="login.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="container py-5">
        <!-- Features Section -->
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="fw-bold text-primary">Fitur Platform EduCourse</h2>
                <p class="lead">Semua yang Anda butuhkan untuk mengembangkan bisnis digital</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h4>Platform Edukasi</h4>
                    <p>Kursus online berkualitas tinggi tentang teknologi bisnis digital, web development, dan entrepreneurship.</p>
                    <a href="courses.php" class="btn btn-primary">Lihat Kursus</a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <h4>Marketplace Mini</h4>
                    <p>Jual dan beli produk digital, tools, template, dan sumber daya untuk mengembangkan bisnis Anda.</p>
                    <a href="marketplace.php" class="btn btn-primary">Jelajahi Produk</a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Manajemen Keuangan</h4>
                    <p>Kelola keuangan bisnis dengan mudah. Track pemasukan, pengeluaran, dan analisis finansial.</p>
                    <?php if(isLoggedIn()): ?>
                        <a href="finance.php" class="btn btn-primary">Kelola Keuangan</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">Login untuk Akses</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h4>Manajemen Usaha</h4>
                    <p>Sistem manajemen bisnis terintegrasi untuk mengelola operasional usaha digital Anda.</p>
                    <?php if(isLoggedIn()): ?>
                        <a href="business.php" class="btn btn-primary">Kelola Bisnis</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">Login untuk Akses</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Statistics Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="bg-primary text-white rounded-3 p-5">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h2 class="fw-bold">500+</h2>
                            <p>Pengguna Aktif</p>
                        </div>
                        <div class="col-md-3">
                            <h2 class="fw-bold">50+</h2>
                            <p>Kursus Tersedia</p>
                        </div>
                        <div class="col-md-3">
                            <h2 class="fw-bold">200+</h2>
                            <p>Produk Digital</p>
                        </div>
                        <div class="col-md-3">
                            <h2 class="fw-bold">98%</h2>
                            <p>Tingkat Kepuasan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Latest Courses -->
        <?php
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT c.*, u.full_name as instructor_name, cat.name as category_name 
                  FROM courses c 
                  LEFT JOIN users u ON c.instructor_id = u.id 
                  LEFT JOIN categories cat ON c.category_id = cat.id 
                  WHERE c.status = 'active' 
                  ORDER BY c.created_at DESC LIMIT 3";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $latest_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <?php if(!empty($latest_courses)): ?>
        <div class="row mt-5">
            <div class="col-12 text-center mb-4">
                <h2 class="fw-bold text-primary">Kursus Terbaru</h2>
                <p class="lead">Pelajari skill terbaru dalam teknologi bisnis digital</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach($latest_courses as $course): ?>
            <div class="col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if($course['image']): ?>
                        <img src="<?php echo $course['image']; ?>" class="card-img-top" alt="<?php echo $course['title']; ?>">
                    <?php else: ?>
                        <div class="card-img-top bg-primary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-book fa-3x"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="badge bg-secondary mb-2"><?php echo $course['category_name']; ?></span>
                        <h5 class="card-title"><?php echo $course['title']; ?></h5>
                        <p class="card-text"><?php echo substr($course['description'], 0, 100); ?>...</p>
                        <p class="text-muted small">
                            <i class="fas fa-user"></i> <?php echo $course['instructor_name']; ?> |
                            <i class="fas fa-clock"></i> <?php echo $course['duration_hours']; ?> jam |
                            <i class="fas fa-signal"></i> <?php echo ucfirst($course['level']); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="text-primary mb-0"><?php echo formatCurrency($course['price']); ?></h5>
                            <a href="course_detail.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Call to Action -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="bg-light rounded-3 p-5 text-center">
                    <h3 class="fw-bold mb-3">Siap Memulai Perjalanan Digital Anda?</h3>
                    <p class="lead mb-4">Bergabunglah dengan ribuan pengusaha digital yang telah mempercayai platform kami</p>
                    <?php if(!isLoggedIn()): ?>
                        <a href="register.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-rocket"></i> Mulai Gratis
                        </a>
                        <a href="courses.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-play"></i> Lihat Demo
                        </a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-tachometer-alt"></i> Ke Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>