<?php 
$page_title = "Profil Pengguna";
include 'includes/header.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Handle profile update
if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if (!empty($full_name) && !empty($email)) {
        // Check if email is already used by another user
        $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$email, $user_id]);
        
        if ($check_stmt->fetch()) {
            $error = "Email sudah digunakan oleh pengguna lain.";
        } else {
            $query = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            if ($stmt->execute([$full_name, $email, $phone, $address, $user_id])) {
                $_SESSION['full_name'] = $full_name;
                $success = "Profil berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui profil.";
            }
        }
    } else {
        $error = "Nama lengkap dan email harus diisi.";
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                // Verify current password
                $user_query = "SELECT password FROM users WHERE id = ?";
                $user_stmt = $db->prepare($user_query);
                $user_stmt->execute([$user_id]);
                $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($current_password, $user_data['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE users SET password = ? WHERE id = ?";
                    $update_stmt = $db->prepare($update_query);
                    
                    if ($update_stmt->execute([$hashed_password, $user_id])) {
                        $success_password = "Password berhasil diubah!";
                    } else {
                        $error_password = "Gagal mengubah password.";
                    }
                } else {
                    $error_password = "Password lama tidak sesuai.";
                }
            } else {
                $error_password = "Password baru harus minimal 6 karakter.";
            }
        } else {
            $error_password = "Konfirmasi password tidak cocok.";
        }
    } else {
        $error_password = "Semua field password harus diisi.";
    }
}

// Get current user data
$user = getCurrentUser();
?>

<div class="main-content">
    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-primary text-white rounded-3 p-4">
                    <h1 class="fw-bold mb-2">
                        <i class="fas fa-user-edit"></i> Profil Pengguna
                    </h1>
                    <p class="lead mb-0">Kelola informasi akun dan pengaturan pribadi Anda</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Profile Info -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="profile-avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            <?php echo substr($user['full_name'], 0, 1); ?>
                        </div>
                        <h4><?php echo $user['full_name']; ?></h4>
                        <p class="text-muted"><?php echo $user['email']; ?></p>
                        <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?> mb-3">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                        
                        <div class="profile-stats mt-3">
                            <div class="row">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h5 class="text-primary mb-1">
                                            <?php
                                            $course_query = "SELECT COUNT(*) as total FROM enrollments WHERE user_id = ?";
                                            $course_stmt = $db->prepare($course_query);
                                            $course_stmt->execute([$user_id]);
                                            echo $course_stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                            ?>
                                        </h5>
                                        <small class="text-muted">Kursus</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h5 class="text-primary mb-1">
                                            <?php
                                            $product_query = "SELECT COUNT(*) as total FROM products WHERE user_id = ?";
                                            $product_stmt = $db->prepare($product_query);
                                            $product_stmt->execute([$user_id]);
                                            echo $product_stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                            ?>
                                        </h5>
                                        <small class="text-muted">Produk</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h5 class="text-primary mb-1">
                                            <?php
                                            $business_query = "SELECT COUNT(*) as total FROM businesses WHERE user_id = ?";
                                            $business_stmt = $db->prepare($business_query);
                                            $business_stmt->execute([$user_id]);
                                            echo $business_stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                            ?>
                                        </h5>
                                        <small class="text-muted">Bisnis</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> 
                                Bergabung: <?php echo formatDate($user['created_at']); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Profile Forms -->
            <div class="col-lg-8">
                <!-- Update Profile -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-edit"></i> Perbarui Profil
                        </h5>
                    </div>
                    <div class="card-body">
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
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                    <small class="form-text text-muted">Username tidak dapat diubah</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat</label>
                                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-key"></i> Ubah Password
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_password)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $success_password; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_password)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error_password; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Lama *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                        <i class="fas fa-eye" id="toggleIcon1"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                        <i class="fas fa-eye" id="toggleIcon2"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Minimal 6 karakter</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye" id="toggleIcon3"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" name="change_password" class="btn btn-warning">
                                <i class="fas fa-key"></i> Ubah Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Account Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Zona Berbahaya
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6>Hapus Akun</h6>
                                <p class="text-muted mb-0">
                                    Menghapus akun akan menghilangkan semua data Anda secara permanen. 
                                    Tindakan ini tidak dapat dibatalkan.
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-danger" onclick="confirmDeleteAccount()">
                                    <i class="fas fa-trash"></i> Hapus Akun
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const toggleIcon = fieldId === 'current_password' ? document.getElementById('toggleIcon1') : 
                      fieldId === 'new_password' ? document.getElementById('toggleIcon2') : 
                      document.getElementById('toggleIcon3');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

function confirmDeleteAccount() {
    if (confirm('Apakah Anda yakin ingin menghapus akun? Tindakan ini tidak dapat dibatalkan!')) {
        if (confirm('Konfirmasi sekali lagi. Semua data Anda akan hilang permanen!')) {
            // Here you would implement account deletion
            alert('Fitur hapus akun akan segera tersedia. Silakan hubungi administrator.');
        }
    }
}

// Validate password match
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword !== confirmPassword && confirmPassword !== '') {
        this.setCustomValidity('Password tidak cocok');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});

// Auto dismiss alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert-success');
    alerts.forEach(function(alert) {
        alert.style.display = 'none';
    });
}, 5000);
</script>

<?php include 'includes/footer.php'; ?>