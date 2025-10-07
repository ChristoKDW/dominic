<?php 
$page_title = "Detail Kursus";
include 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Get course ID from URL
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id <= 0) {
    header('Location: courses.php');
    exit;
}

// Get course details
$query = "SELECT c.*, u.full_name as instructor_name, cat.name as category_name,
                 COUNT(e.id) as enrollment_count
          FROM courses c 
          LEFT JOIN users u ON c.instructor_id = u.id 
          LEFT JOIN categories cat ON c.category_id = cat.id 
          LEFT JOIN enrollments e ON c.id = e.course_id
          WHERE c.id = ? AND c.status = 'active'
          GROUP BY c.id";

$stmt = $db->prepare($query);
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: courses.php');
    exit;
}

// Check if user is enrolled
$is_enrolled = false;
if (isLoggedIn()) {
    $enroll_query = "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?";
    $enroll_stmt = $db->prepare($enroll_query);
    $enroll_stmt->execute([$_SESSION['user_id'], $course_id]);
    $is_enrolled = $enroll_stmt->fetch() ? true : false;
}

// Handle enrollment
if (isset($_POST['enroll']) && isLoggedIn() && !$is_enrolled) {
    $enroll_query = "INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)";
    $enroll_stmt = $db->prepare($enroll_query);
    if ($enroll_stmt->execute([$_SESSION['user_id'], $course_id])) {
        $success = "Anda berhasil mendaftar kursus ini!";
        $is_enrolled = true;
    } else {
        $error = "Gagal mendaftar kursus. Silakan coba lagi.";
    }
}

// Get lessons
$lesson_query = "SELECT * FROM lessons WHERE course_id = ? ORDER BY order_number ASC";
$lesson_stmt = $db->prepare($lesson_query);
$lesson_stmt->execute([$course_id]);
$lessons = $lesson_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                <li class="breadcrumb-item"><a href="courses.php">Kursus</a></li>
                <li class="breadcrumb-item active"><?php echo $course['title']; ?></li>
            </ol>
        </nav>
        
        <!-- Course Header -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <?php if ($course['image']): ?>
                        <img src="<?php echo $course['image']; ?>" class="card-img-top" alt="<?php echo $course['title']; ?>" style="height: 300px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-gradient-primary text-white d-flex align-items-center justify-content-center" style="height: 300px;">
                            <i class="fas fa-book fa-4x opacity-75"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-secondary"><?php echo $course['category_name']; ?></span>
                            <span class="badge bg-<?php echo $course['level'] == 'beginner' ? 'success' : ($course['level'] == 'intermediate' ? 'warning' : 'danger'); ?>">
                                <?php echo ucfirst($course['level']); ?>
                            </span>
                        </div>
                        
                        <h1 class="card-title"><?php echo $course['title']; ?></h1>
                        <p class="card-text"><?php echo $course['description']; ?></p>
                        
                        <div class="course-meta mb-3">
                            <div class="row text-muted">
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <i class="fas fa-user text-primary"></i> 
                                        <strong>Instruktur:</strong> <?php echo $course['instructor_name']; ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-clock text-info"></i> 
                                        <strong>Durasi:</strong> <?php echo $course['duration_hours']; ?> jam
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <i class="fas fa-users text-success"></i> 
                                        <strong>Peserta:</strong> <?php echo $course['enrollment_count']; ?> orang
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-list text-warning"></i> 
                                        <strong>Materi:</strong> <?php echo count($lessons); ?> pelajaran
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-body text-center">
                        <h3 class="text-primary mb-3">
                            <?php if ($course['price'] == 0): ?>
                                <span class="text-success">GRATIS</span>
                            <?php else: ?>
                                <?php echo formatCurrency($course['price']); ?>
                            <?php endif; ?>
                        </h3>
                        
                        <?php if (isLoggedIn()): ?>
                            <?php if ($is_enrolled): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> Anda sudah terdaftar
                                </div>
                                <a href="#lessons" class="btn btn-primary w-100 mb-3">
                                    <i class="fas fa-play"></i> Mulai Belajar
                                </a>
                            <?php else: ?>
                                <form method="POST">
                                    <button type="submit" name="enroll" class="btn btn-success w-100 mb-3">
                                        <i class="fas fa-user-plus"></i> Daftar Kursus
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt"></i> Login untuk Mendaftar
                            </a>
                        <?php endif; ?>
                        
                        <div class="course-features">
                            <h6 class="mb-3">Yang Akan Anda Dapatkan:</h6>
                            <ul class="list-unstyled text-start">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i> 
                                    Akses seumur hidup
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i> 
                                    <?php echo count($lessons); ?> materi pembelajaran
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i> 
                                    Sertifikat kelulusan
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i> 
                                    Diskusi dengan instruktur
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i> 
                                    Update materi terbaru
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Course Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#lessons-tab">
                                    <i class="fas fa-list"></i> Materi Kursus
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#about-tab">
                                    <i class="fas fa-info-circle"></i> Tentang Kursus
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#instructor-tab">
                                    <i class="fas fa-user"></i> Instruktur
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Lessons Tab -->
                            <div class="tab-pane fade show active" id="lessons-tab">
                                <h5 class="mb-4">Daftar Materi Pembelajaran</h5>
                                
                                <?php if (!empty($lessons)): ?>
                                    <div class="lessons-list">
                                        <?php foreach ($lessons as $index => $lesson): ?>
                                        <div class="lesson-item mb-3 p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="lesson-content">
                                                    <h6 class="mb-1">
                                                        <span class="badge bg-primary me-2"><?php echo $index + 1; ?></span>
                                                        <?php echo $lesson['title']; ?>
                                                    </h6>
                                                    <?php if ($lesson['content']): ?>
                                                        <p class="text-muted mb-1"><?php echo substr($lesson['content'], 0, 150); ?>...</p>
                                                    <?php endif; ?>
                                                    <?php if ($lesson['duration_minutes']): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock"></i> <?php echo $lesson['duration_minutes']; ?> menit
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <?php if ($is_enrolled): ?>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-play"></i> Mulai
                                                    </button>
                                                <?php else: ?>
                                                    <i class="fas fa-lock text-muted"></i>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                        <h5>Materi Sedang Disiapkan</h5>
                                        <p class="text-muted">Instruktur sedang menyiapkan materi pembelajaran untuk kursus ini.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- About Tab -->
                            <div class="tab-pane fade" id="about-tab">
                                <h5 class="mb-4">Tentang Kursus Ini</h5>
                                <div class="course-description">
                                    <p><?php echo nl2br($course['description']); ?></p>
                                    
                                    <h6 class="mt-4">Apa yang akan Anda pelajari:</h6>
                                    <ul>
                                        <li>Konsep dasar dan fundamental</li>
                                        <li>Praktik dan implementasi nyata</li>
                                        <li>Best practices industri</li>
                                        <li>Project-based learning</li>
                                        <li>Tips dan trik dari expert</li>
                                    </ul>
                                    
                                    <h6 class="mt-4">Prasyarat:</h6>
                                    <ul>
                                        <li>Tidak ada prasyarat khusus</li>
                                        <li>Akses internet yang stabil</li>
                                        <li>Motivasi untuk belajar</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Instructor Tab -->
                            <div class="tab-pane fade" id="instructor-tab">
                                <h5 class="mb-4">Profil Instruktur</h5>
                                <div class="instructor-profile">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            <div class="instructor-avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2rem;">
                                                <?php echo substr($course['instructor_name'], 0, 1); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <h4><?php echo $course['instructor_name']; ?></h4>
                                            <p class="text-muted">Instruktur Profesional</p>
                                            <p>Instruktur berpengalaman dengan expertise di bidang teknologi dan bisnis digital. Telah mengajar ribuan siswa dan membantu mereka mencapai tujuan karir.</p>
                                            
                                            <div class="instructor-stats mt-3">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <div class="stat-item text-center">
                                                            <h5 class="text-primary">5+</h5>
                                                            <small class="text-muted">Tahun Pengalaman</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="stat-item text-center">
                                                            <h5 class="text-primary">1000+</h5>
                                                            <small class="text-muted">Siswa</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="stat-item text-center">
                                                            <h5 class="text-primary">4.8</h5>
                                                            <small class="text-muted">Rating</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Courses -->
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">Kursus Lainnya</h4>
                
                <?php
                // Get related courses
                $related_query = "SELECT c.*, u.full_name as instructor_name, cat.name as category_name 
                                  FROM courses c 
                                  LEFT JOIN users u ON c.instructor_id = u.id 
                                  LEFT JOIN categories cat ON c.category_id = cat.id 
                                  WHERE c.category_id = ? AND c.id != ? AND c.status = 'active' 
                                  ORDER BY c.created_at DESC LIMIT 3";
                $related_stmt = $db->prepare($related_query);
                $related_stmt->execute([$course['category_id'], $course_id]);
                $related_courses = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <?php if (!empty($related_courses)): ?>
                <div class="row">
                    <?php foreach ($related_courses as $related): ?>
                    <div class="col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <span class="badge bg-secondary mb-2"><?php echo $related['category_name']; ?></span>
                                <h5 class="card-title"><?php echo $related['title']; ?></h5>
                                <p class="card-text text-muted"><?php echo substr($related['description'], 0, 100); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="text-primary mb-0">
                                        <?php echo $related['price'] == 0 ? 'GRATIS' : formatCurrency($related['price']); ?>
                                    </h6>
                                    <a href="course_detail.php?id=<?php echo $related['id']; ?>" class="btn btn-primary btn-sm">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">Belum ada kursus lain dalam kategori ini.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>