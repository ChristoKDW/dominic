<?php 
$page_title = "Dashboard";
include 'includes/header.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Get user statistics
$stats = [];

// Total courses enrolled
$query = "SELECT COUNT(*) as total FROM enrollments WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$stats['courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total products in marketplace
$query = "SELECT COUNT(*) as total FROM products WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$stats['products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total businesses
$query = "SELECT COUNT(*) as total FROM businesses WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$stats['businesses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Recent transactions
$query = "SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                 SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
          FROM transactions WHERE user_id = ? AND MONTH(transaction_date) = MONTH(CURDATE())";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$financial = $stmt->fetch(PDO::FETCH_ASSOC);

// Recent activities
$query = "SELECT 'enrollment' as type, c.title as title, e.enrolled_at as date 
          FROM enrollments e 
          JOIN courses c ON e.course_id = c.id 
          WHERE e.user_id = ? 
          UNION ALL
          SELECT 'product' as type, p.name as title, p.created_at as date 
          FROM products p 
          WHERE p.user_id = ? 
          UNION ALL
          SELECT 'business' as type, b.name as title, b.created_at as date 
          FROM businesses b 
          WHERE b.user_id = ? 
          ORDER BY date DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute([$user_id, $user_id, $user_id]);
$recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="container py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-primary text-white rounded-3 p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="fw-bold mb-2">
                                <i class="fas fa-tachometer-alt"></i> Selamat Datang, <?php echo $_SESSION['full_name']; ?>!
                            </h2>
                            <p class="mb-0">Kelola aktivitas digital business Anda dari satu tempat</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <p class="mb-1"><i class="fas fa-calendar"></i> <?php echo date('d F Y'); ?></p>
                            <p class="mb-0"><i class="fas fa-clock"></i> <?php echo date('H:i'); ?> WIB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Kursus Diikuti</h6>
                                <h3 class="fw-bold"><?php echo $stats['courses']; ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-graduation-cap fa-2x opacity-75"></i>
                            </div>
                        </div>
                        <a href="courses.php" class="text-white text-decoration-none small">
                            <i class="fas fa-arrow-right"></i> Lihat Kursus
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Produk Dijual</h6>
                                <h3 class="fw-bold"><?php echo $stats['products']; ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-store fa-2x opacity-75"></i>
                            </div>
                        </div>
                        <a href="marketplace.php" class="text-white text-decoration-none small">
                            <i class="fas fa-arrow-right"></i> Kelola Produk
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Bisnis Aktif</h6>
                                <h3 class="fw-bold"><?php echo $stats['businesses']; ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-building fa-2x opacity-75"></i>
                            </div>
                        </div>
                        <a href="business.php" class="text-white text-decoration-none small">
                            <i class="fas fa-arrow-right"></i> Kelola Bisnis
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Saldo Bulan Ini</h6>
                                <h3 class="fw-bold"><?php echo formatCurrency(($financial['total_income'] ?? 0) - ($financial['total_expense'] ?? 0)); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fa-2x opacity-75"></i>
                            </div>
                        </div>
                        <a href="finance.php" class="text-white text-decoration-none small">
                            <i class="fas fa-arrow-right"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-rocket"></i> Aksi Cepat
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="courses.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center">
                                    <i class="fas fa-plus fa-2x mb-2"></i>
                                    <span>Ikuti Kursus Baru</span>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="marketplace.php?action=add" class="btn btn-outline-success w-100 h-100 d-flex flex-column justify-content-center">
                                    <i class="fas fa-box fa-2x mb-2"></i>
                                    <span>Tambah Produk</span>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="finance.php?action=add" class="btn btn-outline-info w-100 h-100 d-flex flex-column justify-content-center">
                                    <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                    <span>Catat Transaksi</span>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="business.php?action=add" class="btn btn-outline-warning w-100 h-100 d-flex flex-column justify-content-center">
                                    <i class="fas fa-briefcase fa-2x mb-2"></i>
                                    <span>Daftarkan Bisnis</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Activities -->
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock"></i> Aktivitas Terbaru
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_activities)): ?>
                            <div class="timeline">
                                <?php foreach ($recent_activities as $activity): ?>
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="timeline-marker me-3">
                                            <?php
                                            $icon = '';
                                            $color = '';
                                            switch($activity['type']) {
                                                case 'enrollment':
                                                    $icon = 'fas fa-graduation-cap';
                                                    $color = 'primary';
                                                    $text = 'Mengikuti kursus';
                                                    break;
                                                case 'product':
                                                    $icon = 'fas fa-box';
                                                    $color = 'success';
                                                    $text = 'Menambah produk';
                                                    break;
                                                case 'business':
                                                    $icon = 'fas fa-building';
                                                    $color = 'info';
                                                    $text = 'Mendaftarkan bisnis';
                                                    break;
                                            }
                                            ?>
                                            <div class="bg-<?php echo $color; ?> text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="<?php echo $icon; ?>"></i>
                                            </div>
                                        </div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1"><?php echo $text; ?>: <?php echo $activity['title']; ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> <?php echo formatDate($activity['date']); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada aktivitas. Mulai eksplorasi platform!</p>
                                <a href="courses.php" class="btn btn-primary">Mulai Sekarang</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Financial Summary -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie"></i> Ringkasan Keuangan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <canvas id="financialChart" style="max-height: 200px;"></canvas>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h6 class="text-success mb-1">Pemasukan</h6>
                                    <h5 class="fw-bold text-success"><?php echo formatCurrency($financial['total_income'] ?? 0); ?></h5>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="text-danger mb-1">Pengeluaran</h6>
                                <h5 class="fw-bold text-danger"><?php echo formatCurrency($financial['total_expense'] ?? 0); ?></h5>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <a href="finance.php" class="btn btn-primary w-100">
                                <i class="fas fa-eye"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Financial Chart
const ctx = document.getElementById('financialChart').getContext('2d');
const income = <?php echo $financial['total_income'] ?? 0; ?>;
const expense = <?php echo $financial['total_expense'] ?? 0; ?>;

const chart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Pemasukan', 'Pengeluaran'],
        datasets: [{
            data: [income, expense],
            backgroundColor: ['#28a745', '#dc3545'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>