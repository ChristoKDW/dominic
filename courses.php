<?php 
$page_title = "Kursus Online";
include 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$level = isset($_GET['level']) ? $_GET['level'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';

// Build query
$where_conditions = ["c.status = 'active'"];
$params = [];

if ($search) {
    $where_conditions[] = "(c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $where_conditions[] = "c.category_id = ?";
    $params[] = $category;
}

if ($level) {
    $where_conditions[] = "c.level = ?";
    $params[] = $level;
}

$where_clause = implode(" AND ", $where_conditions);

// Sort options
$order_by = "c.created_at DESC";
switch ($sort) {
    case 'price_low':
        $order_by = "c.price ASC";
        break;
    case 'price_high':
        $order_by = "c.price DESC";
        break;
    case 'popular':
        $order_by = "enrollment_count DESC";
        break;
    case 'rating':
        $order_by = "c.created_at DESC"; // Would implement rating later
        break;
}

// Get courses
$query = "SELECT c.*, u.full_name as instructor_name, cat.name as category_name,
                 COUNT(e.id) as enrollment_count
          FROM courses c 
          LEFT JOIN users u ON c.instructor_id = u.id 
          LEFT JOIN categories cat ON c.category_id = cat.id 
          LEFT JOIN enrollments e ON c.id = e.course_id
          WHERE $where_clause
          GROUP BY c.id
          ORDER BY $order_by";

$stmt = $db->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$cat_query = "SELECT * FROM categories WHERE type = 'course' ORDER BY name";
$cat_stmt = $db->prepare($cat_query);
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-primary text-white rounded-3 p-4 text-center">
                    <h1 class="fw-bold mb-2">
                        <i class="fas fa-graduation-cap"></i> Platform Edukasi Bisnis
                    </h1>
                    <p class="lead mb-0">Tingkatkan skill digital business Anda dengan kursus berkualitas</p>
                </div>
            </div>
        </div>
        
        <!-- Search & Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-lg-4">
                                <label class="form-label">Cari Kursus</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Kata kunci...">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Kategori</label>
                                <select class="form-select" name="category">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" 
                                                <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo $cat['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Level</label>
                                <select class="form-select" name="level">
                                    <option value="">Semua Level</option>
                                    <option value="beginner" <?php echo $level == 'beginner' ? 'selected' : ''; ?>>Pemula</option>
                                    <option value="intermediate" <?php echo $level == 'intermediate' ? 'selected' : ''; ?>>Menengah</option>
                                    <option value="advanced" <?php echo $level == 'advanced' ? 'selected' : ''; ?>>Lanjutan</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Urutkan</label>
                                <select class="form-select" name="sort">
                                    <option value="latest" <?php echo $sort == 'latest' ? 'selected' : ''; ?>>Terbaru</option>
                                    <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Terpopuler</option>
                                    <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Harga Terendah</option>
                                    <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Results -->
        <div class="row mb-3">
            <div class="col-12">
                <h5>Ditemukan <?php echo count($courses); ?> kursus</h5>
                <?php if ($search || $category || $level): ?>
                    <p class="text-muted">
                        Filter aktif: 
                        <?php if ($search): ?>
                            <span class="badge bg-primary">"<?php echo htmlspecialchars($search); ?>"</span>
                        <?php endif; ?>
                        <?php if ($category): ?>
                            <?php 
                            $cat_name = '';
                            foreach ($categories as $cat) {
                                if ($cat['id'] == $category) {
                                    $cat_name = $cat['name'];
                                    break;
                                }
                            }
                            ?>
                            <span class="badge bg-secondary"><?php echo $cat_name; ?></span>
                        <?php endif; ?>
                        <?php if ($level): ?>
                            <span class="badge bg-info"><?php echo ucfirst($level); ?></span>
                        <?php endif; ?>
                        <a href="courses.php" class="btn btn-sm btn-outline-secondary ms-2">Reset Filter</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Courses Grid -->
        <?php if (!empty($courses)): ?>
        <div class="row">
            <?php foreach ($courses as $course): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm course-card">
                    <?php if ($course['image']): ?>
                        <img src="<?php echo $course['image']; ?>" class="card-img-top" alt="<?php echo $course['title']; ?>" style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-gradient-primary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-book fa-3x opacity-75"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-secondary"><?php echo $course['category_name']; ?></span>
                            <span class="badge bg-<?php echo $course['level'] == 'beginner' ? 'success' : ($course['level'] == 'intermediate' ? 'warning' : 'danger'); ?>">
                                <?php echo ucfirst($course['level']); ?>
                            </span>
                        </div>
                        
                        <h5 class="card-title"><?php echo $course['title']; ?></h5>
                        <p class="card-text text-muted flex-grow-1">
                            <?php echo substr($course['description'], 0, 120); ?>...
                        </p>
                        
                        <div class="course-meta mb-3">
                            <small class="text-muted d-block">
                                <i class="fas fa-user"></i> <?php echo $course['instructor_name']; ?>
                            </small>
                            <small class="text-muted d-block">
                                <i class="fas fa-clock"></i> <?php echo $course['duration_hours']; ?> jam
                                <span class="ms-2">
                                    <i class="fas fa-users"></i> <?php echo $course['enrollment_count']; ?> peserta
                                </span>
                            </small>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="text-primary mb-0 fw-bold">
                                <?php if ($course['price'] == 0): ?>
                                    <span class="text-success">GRATIS</span>
                                <?php else: ?>
                                    <?php echo formatCurrency($course['price']); ?>
                                <?php endif; ?>
                            </h5>
                            <a href="course_detail.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h4>Tidak ada kursus ditemukan</h4>
            <p class="text-muted">Coba ubah filter pencarian atau kata kunci Anda</p>
            <a href="courses.php" class="btn btn-primary">Lihat Semua Kursus</a>
        </div>
        <?php endif; ?>
        
        <!-- Add Course (for instructors) -->
        <?php if (isLoggedIn()): ?>
        <div class="row mt-5">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-plus-circle"></i> Menjadi Instruktur</h5>
                        <p class="text-muted">Bagikan pengetahuan Anda dan dapatkan penghasilan dengan menjadi instruktur di platform kami</p>
                        <a href="instructor_apply.php" class="btn btn-success">
                            <i class="fas fa-chalkboard-teacher"></i> Daftar Sebagai Instruktur
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.course-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.course-meta small {
    line-height: 1.5;
}
</style>

<?php include 'includes/footer.php'; ?>