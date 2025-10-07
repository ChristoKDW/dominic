<?php 
$page_title = "Manajemen Bisnis";
include 'includes/header.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Handle add business
if (isset($_POST['add_business'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $business_type = trim($_POST['business_type']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $website = trim($_POST['website']);
    
    if (!empty($name) && !empty($description) && !empty($business_type)) {
        $query = "INSERT INTO businesses (user_id, name, description, business_type, address, phone, email, website) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$user_id, $name, $description, $business_type, $address, $phone, $email, $website])) {
            $success = "Bisnis berhasil didaftarkan!";
        } else {
            $error = "Gagal mendaftarkan bisnis.";
        }
    } else {
        $error = "Nama, deskripsi, dan jenis bisnis harus diisi.";
    }
}

// Handle update business status
if (isset($_POST['update_status'])) {
    $business_id = intval($_POST['business_id']);
    $status = $_POST['status'];
    
    $query = "UPDATE businesses SET status = ? WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$status, $business_id, $user_id])) {
        $success = "Status bisnis berhasil diubah!";
    } else {
        $error = "Gagal mengubah status bisnis.";
    }
}

// Get user's businesses
$query = "SELECT * FROM businesses WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get business statistics
$stats_query = "SELECT 
    COUNT(*) as total_businesses,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_businesses,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_businesses
    FROM businesses WHERE user_id = ?";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute([$user_id]);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-warning text-dark rounded-3 p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="fw-bold mb-2">
                                <i class="fas fa-building"></i> Manajemen Usaha
                            </h1>
                            <p class="lead mb-0">Kelola dan pantau bisnis digital Anda dalam satu platform</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addBusinessModal">
                                <i class="fas fa-plus"></i> Daftar Bisnis Baru
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Bisnis</h6>
                                <h3 class="fw-bold"><?php echo $stats['total_businesses']; ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-briefcase fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Bisnis Aktif</h6>
                                <h3 class="fw-bold"><?php echo $stats['active_businesses']; ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card bg-secondary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Bisnis Nonaktif</h6>
                                <h3 class="fw-bold"><?php echo $stats['inactive_businesses']; ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-pause-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Business List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list"></i> Daftar Bisnis Anda
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($businesses)): ?>
                        <div class="row">
                            <?php foreach ($businesses as $business): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100 business-card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold"><?php echo $business['name']; ?></h6>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="business_id" value="<?php echo $business['id']; ?>">
                                                        <input type="hidden" name="status" value="<?php echo $business['status'] == 'active' ? 'inactive' : 'active'; ?>">
                                                        <button type="submit" name="update_status" class="dropdown-item">
                                                            <i class="fas fa-toggle-<?php echo $business['status'] == 'active' ? 'off' : 'on'; ?>"></i>
                                                            <?php echo $business['status'] == 'active' ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><a class="dropdown-item" href="#"><i class="fas fa-edit"></i> Edit</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash"></i> Hapus</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <span class="badge bg-<?php echo $business['status'] == 'active' ? 'success' : 'secondary'; ?> mb-2">
                                                <i class="fas fa-circle"></i> <?php echo ucfirst($business['status']); ?>
                                            </span>
                                            <span class="badge bg-info"><?php echo $business['business_type']; ?></span>
                                        </div>
                                        
                                        <p class="card-text text-muted"><?php echo $business['description']; ?></p>
                                        
                                        <div class="business-details">
                                            <?php if ($business['address']): ?>
                                                <p class="small mb-1">
                                                    <i class="fas fa-map-marker-alt text-primary"></i> 
                                                    <?php echo $business['address']; ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if ($business['phone']): ?>
                                                <p class="small mb-1">
                                                    <i class="fas fa-phone text-success"></i> 
                                                    <a href="tel:<?php echo $business['phone']; ?>" class="text-decoration-none">
                                                        <?php echo $business['phone']; ?>
                                                    </a>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if ($business['email']): ?>
                                                <p class="small mb-1">
                                                    <i class="fas fa-envelope text-info"></i> 
                                                    <a href="mailto:<?php echo $business['email']; ?>" class="text-decoration-none">
                                                        <?php echo $business['email']; ?>
                                                    </a>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if ($business['website']): ?>
                                                <p class="small mb-1">
                                                    <i class="fas fa-globe text-warning"></i> 
                                                    <a href="<?php echo $business['website']; ?>" target="_blank" class="text-decoration-none">
                                                        Website
                                                    </a>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted">
                                        <small>
                                            <i class="fas fa-calendar"></i> 
                                            Didaftarkan: <?php echo formatDate($business['created_at']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <h5>Belum ada bisnis terdaftar</h5>
                            <p class="text-muted">Mulai daftarkan bisnis digital Anda dan kelola dengan mudah</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBusinessModal">
                                <i class="fas fa-plus"></i> Daftar Bisnis Pertama
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Business Tips -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-lightbulb text-warning"></i> Tips Manajemen Bisnis Digital
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check text-success"></i> Lakukan analisis pasar secara berkala</li>
                                    <li class="mb-2"><i class="fas fa-check text-success"></i> Gunakan media sosial untuk marketing</li>
                                    <li class="mb-2"><i class="fas fa-check text-success"></i> Kelola keuangan dengan tertib</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check text-success"></i> Bangun relasi dengan customer</li>
                                    <li class="mb-2"><i class="fas fa-check text-success"></i> Terus belajar teknologi terbaru</li>
                                    <li class="mb-2"><i class="fas fa-check text-success"></i> Monitor kompetitor secara aktif</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Business Modal -->
<div class="modal fade" id="addBusinessModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Daftar Bisnis Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">Nama Bisnis *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="business_type" class="form-label">Jenis Bisnis *</label>
                            <select class="form-select" id="business_type" name="business_type" required>
                                <option value="">Pilih Jenis</option>
                                <option value="E-commerce">E-commerce</option>
                                <option value="Digital Marketing">Digital Marketing</option>
                                <option value="Web Development">Web Development</option>
                                <option value="Mobile App">Mobile App</option>
                                <option value="Content Creator">Content Creator</option>
                                <option value="Online Course">Online Course</option>
                                <option value="Consulting">Consulting</option>
                                <option value="SaaS">Software as a Service</option>
                                <option value="Marketplace">Marketplace</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi Bisnis *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required 
                                  placeholder="Jelaskan tentang bisnis Anda..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control" id="address" name="address" rows="2" 
                                  placeholder="Alamat lengkap bisnis..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   placeholder="Contoh: +62812345678">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Bisnis</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="Contoh: info@bisnis.com">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="website" class="form-label">Website</label>
                        <input type="url" class="form-control" id="website" name="website" 
                               placeholder="Contoh: https://www.bisnis.com">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_business" class="btn btn-primary">
                        <i class="fas fa-save"></i> Daftar Bisnis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.business-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.business-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.business-details p {
    margin-bottom: 8px;
}

.business-details i {
    width: 16px;
    margin-right: 8px;
}
</style>

<script>
// Auto close modal on success
<?php if (isset($success)): ?>
    setTimeout(function() {
        $('#addBusinessModal').modal('hide');
    }, 2000);
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>