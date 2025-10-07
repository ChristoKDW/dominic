<?php
/**
 * EduCourse Platform Setup
 * File ini akan membantu setup otomatis database dan konfigurasi awal
 */

// Cek apakah setup sudah pernah dijalankan
if (file_exists('.setup_complete')) {
    die('Setup sudah pernah dijalankan. Hapus file .setup_complete untuk menjalankan ulang.');
}

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';
$success = '';

// Step 1: Database Configuration
if ($step == 1 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = $_POST['host'] ?? 'localhost';
    $username = $_POST['username'] ?? 'root';
    $password = $_POST['password'] ?? '';
    $database = $_POST['database'] ?? 'educourse_db';
    
    try {
        // Test database connection
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database`");
        $pdo->exec("USE `$database`");
        
        // Save database config
        $config_content = "<?php
class Database {
    private \$host = \"$host\";
    private \$db_name = \"$database\";
    private \$username = \"$username\";
    private \$password = \"$password\";
    private \$conn;

    public function getConnection() {
        \$this->conn = null;
        try {
            \$this->conn = new PDO(
                \"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name,
                \$this->username,
                \$this->password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES utf8\")
            );
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException \$exception) {
            echo \"Connection error: \" . \$exception->getMessage();
        }
        return \$this->conn;
    }
}
?>";
        
        file_put_contents('config/database.php', $config_content);
        
        // Store config for next step
        session_start();
        $_SESSION['setup_config'] = [
            'host' => $host,
            'username' => $username,
            'password' => $password,
            'database' => $database
        ];
        
        header('Location: setup.php?step=2');
        exit;
        
    } catch (Exception $e) {
        $error = "Error koneksi database: " . $e->getMessage();
    }
}

// Step 2: Import Database Schema
if ($step == 2) {
    session_start();
    if (!isset($_SESSION['setup_config'])) {
        header('Location: setup.php?step=1');
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $config = $_SESSION['setup_config'];
        
        try {
            $pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['database']}", 
                $config['username'], 
                $config['password']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Read and execute SQL file
            $sql = file_get_contents('database.sql');
            $statements = explode(';', $sql);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            
            header('Location: setup.php?step=3');
            exit;
            
        } catch (Exception $e) {
            $error = "Error import database: " . $e->getMessage();
        }
    }
}

// Step 3: Create Admin User
if ($step == 3) {
    session_start();
    if (!isset($_SESSION['setup_config'])) {
        header('Location: setup.php?step=1');
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $config = $_SESSION['setup_config'];
        $admin_username = $_POST['admin_username'] ?? 'admin';
        $admin_email = $_POST['admin_email'] ?? 'admin@educourse.com';
        $admin_password = $_POST['admin_password'] ?? 'admin123';
        $admin_name = $_POST['admin_name'] ?? 'Administrator';
        
        try {
            $pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['database']}", 
                $config['username'], 
                $config['password']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if admin already exists
            $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check->execute([$admin_username, $admin_email]);
            
            if (!$check->fetch()) {
                // Create admin user
                $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                $insert = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
                $insert->execute([$admin_username, $admin_email, $hashed_password, $admin_name]);
            }
            
            header('Location: setup.php?step=4');
            exit;
            
        } catch (Exception $e) {
            $error = "Error create admin: " . $e->getMessage();
        }
    }
}

// Step 4: Completion
if ($step == 4) {
    // Create completion file
    file_put_contents('.setup_complete', date('Y-m-d H:i:s'));
    
    // Clear session
    session_start();
    unset($_SESSION['setup_config']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup EduCourse Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #20b2aa 0%, #48d1cc 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .setup-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .setup-header {
            background: linear-gradient(135deg, #20b2aa, #48d1cc);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .setup-progress {
            background: #f8f9fa;
            padding: 20px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            right: -50%;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }
        .step:last-child::before {
            display: none;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            position: relative;
            z-index: 2;
            font-weight: bold;
        }
        .step.active .step-number {
            background: #20b2aa;
            color: white;
        }
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        .step.completed::before {
            background: #28a745;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1><i class="fas fa-graduation-cap"></i> EduCourse Setup</h1>
            <p class="mb-0">Platform Digital Business Technology</p>
        </div>
        
        <div class="setup-progress">
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                    <div class="step-number">1</div>
                    <div class="step-label">Database</div>
                </div>
                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                    <div class="step-number">2</div>
                    <div class="step-label">Import</div>
                </div>
                <div class="step <?php echo $step >= 3 ? 'active' : ''; ?> <?php echo $step > 3 ? 'completed' : ''; ?>">
                    <div class="step-number">3</div>
                    <div class="step-label">Admin</div>
                </div>
                <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">
                    <div class="step-number">4</div>
                    <div class="step-label">Selesai</div>
                </div>
            </div>
        </div>
        
        <div class="p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
                <h4 class="mb-4">Step 1: Konfigurasi Database</h4>
                <p class="text-muted">Masukkan informasi database MySQL Anda.</p>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Host Database</label>
                        <input type="text" class="form-control" name="host" value="localhost" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username Database</label>
                        <input type="text" class="form-control" name="username" value="root" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Database</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Database</label>
                        <input type="text" class="form-control" name="database" value="educourse_db" required>
                        <small class="form-text text-muted">Database akan dibuat otomatis jika belum ada.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-database"></i> Test Koneksi & Lanjutkan
                    </button>
                </form>
                
            <?php elseif ($step == 2): ?>
                <h4 class="mb-4">Step 2: Import Database Schema</h4>
                <p class="text-muted">Impor struktur tabel dan data awal ke database.</p>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Proses ini akan membuat tabel-tabel yang diperlukan dan memasukkan data awal.
                </div>
                
                <form method="POST">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-download"></i> Import Database Schema
                    </button>
                </form>
                
            <?php elseif ($step == 3): ?>
                <h4 class="mb-4">Step 3: Buat Admin User</h4>
                <p class="text-muted">Buat akun administrator untuk mengelola platform.</p>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username Admin</label>
                        <input type="text" class="form-control" name="admin_username" value="admin" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Admin</label>
                        <input type="email" class="form-control" name="admin_email" value="admin@educourse.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Admin</label>
                        <input type="password" class="form-control" name="admin_password" value="admin123" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="admin_name" value="Administrator" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus"></i> Buat Admin User
                    </button>
                </form>
                
            <?php elseif ($step == 4): ?>
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-success mb-4">Setup Berhasil!</h4>
                    <p class="text-muted mb-4">EduCourse Platform telah berhasil disetup dan siap digunakan.</p>
                    
                    <div class="alert alert-info text-start">
                        <h6><i class="fas fa-info-circle"></i> Informasi Login:</h6>
                        <p class="mb-1"><strong>URL:</strong> <a href="index.php">index.php</a></p>
                        <p class="mb-1"><strong>Admin Username:</strong> admin</p>
                        <p class="mb-0"><strong>Admin Password:</strong> admin123</p>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="index.php" class="btn btn-success">
                            <i class="fas fa-home"></i> Ke Halaman Utama
                        </a>
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login sebagai Admin
                        </a>
                    </div>
                    
                    <div class="mt-4">
                        <small class="text-muted">
                            File setup.php dapat dihapus setelah setup selesai untuk keamanan.
                        </small>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>