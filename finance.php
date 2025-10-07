<?php 
$page_title = "Manajemen Keuangan";
include 'includes/header.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Handle add transaction
if (isset($_POST['add_transaction'])) {
    $type = $_POST['type'];
    $category = trim($_POST['category']);
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    $transaction_date = $_POST['transaction_date'];
    
    if (!empty($type) && !empty($category) && $amount > 0 && !empty($transaction_date)) {
        $query = "INSERT INTO transactions (user_id, type, category, amount, description, transaction_date) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$user_id, $type, $category, $amount, $description, $transaction_date])) {
            $success = "Transaksi berhasil dicatat!";
        } else {
            $error = "Gagal menambahkan transaksi.";
        }
    } else {
        $error = "Semua field harus diisi dengan benar.";
    }
}

// Get filter parameters
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Build query for transactions
$where_conditions = ["user_id = ?"];
$params = [$user_id];

if ($month) {
    $where_conditions[] = "DATE_FORMAT(transaction_date, '%Y-%m') = ?";
    $params[] = $month;
}

if ($type_filter) {
    $where_conditions[] = "type = ?";
    $params[] = $type_filter;
}

if ($category_filter) {
    $where_conditions[] = "category = ?";
    $params[] = $category_filter;
}

$where_clause = implode(" AND ", $where_conditions);

// Get transactions
$query = "SELECT * FROM transactions WHERE $where_clause ORDER BY transaction_date DESC, created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get summary
$summary_query = "SELECT 
    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense,
    COUNT(*) as total_transactions
    FROM transactions WHERE $where_clause";
$stmt = $db->prepare($summary_query);
$stmt->execute($params);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

$balance = ($summary['total_income'] ?? 0) - ($summary['total_expense'] ?? 0);

// Get categories for filter
$cat_query = "SELECT DISTINCT category FROM transactions WHERE user_id = ? ORDER BY category";
$cat_stmt = $db->prepare($cat_query);
$cat_stmt->execute([$user_id]);
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get monthly data for chart
$chart_query = "SELECT 
    DATE_FORMAT(transaction_date, '%Y-%m') as month,
    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
    FROM transactions 
    WHERE user_id = ? AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
    ORDER BY month";
$chart_stmt = $db->prepare($chart_query);
$chart_stmt->execute([$user_id]);
$chart_data = $chart_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-info text-white rounded-3 p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="fw-bold mb-2">
                                <i class="fas fa-chart-line"></i> Manajemen Keuangan
                            </h1>
                            <p class="lead mb-0">Kelola dan pantau keuangan bisnis Anda dengan mudah</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                                <i class="fas fa-plus"></i> Catat Transaksi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Pemasukan</h6>
                                <h4 class="fw-bold"><?php echo formatCurrency($summary['total_income'] ?? 0); ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-arrow-up fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Pengeluaran</h6>
                                <h4 class="fw-bold"><?php echo formatCurrency($summary['total_expense'] ?? 0); ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-arrow-down fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-<?php echo $balance >= 0 ? 'primary' : 'warning'; ?> text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Saldo</h6>
                                <h4 class="fw-bold"><?php echo formatCurrency($balance); ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-wallet fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-secondary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Transaksi</h6>
                                <h4 class="fw-bold"><?php echo $summary['total_transactions'] ?? 0; ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-list fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chart Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar"></i> Grafik Keuangan (6 Bulan Terakhir)
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="financialChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-lg-3">
                                <label class="form-label">Bulan</label>
                                <input type="month" class="form-control" name="month" value="<?php echo $month; ?>">
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Jenis</label>
                                <select class="form-select" name="type">
                                    <option value="">Semua Jenis</option>
                                    <option value="income" <?php echo $type_filter == 'income' ? 'selected' : ''; ?>>Pemasukan</option>
                                    <option value="expense" <?php echo $type_filter == 'expense' ? 'selected' : ''; ?>>Pengeluaran</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Kategori</label>
                                <select class="form-select" name="category">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>" <?php echo $category_filter == $cat ? 'selected' : ''; ?>>
                                            <?php echo $cat; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-3">
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
        
        <!-- Transactions Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list"></i> Riwayat Transaksi
                        </h5>
                        <span class="badge bg-primary"><?php echo count($transactions); ?> transaksi</span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($transactions)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th>Kategori</th>
                                        <th>Keterangan</th>
                                        <th class="text-end">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo formatDate($transaction['transaction_date']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $transaction['type'] == 'income' ? 'success' : 'danger'; ?>">
                                                <i class="fas fa-arrow-<?php echo $transaction['type'] == 'income' ? 'up' : 'down'; ?>"></i>
                                                <?php echo $transaction['type'] == 'income' ? 'Pemasukan' : 'Pengeluaran'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $transaction['category']; ?></td>
                                        <td><?php echo $transaction['description']; ?></td>
                                        <td class="text-end fw-bold text-<?php echo $transaction['type'] == 'income' ? 'success' : 'danger'; ?>">
                                            <?php echo $transaction['type'] == 'income' ? '+' : '-'; ?><?php echo formatCurrency($transaction['amount']); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <h5>Belum ada transaksi</h5>
                            <p class="text-muted">Mulai catat transaksi keuangan Anda</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                                <i class="fas fa-plus"></i> Catat Transaksi Pertama
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Catat Transaksi Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="type" class="form-label">Jenis Transaksi *</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Pilih Jenis</option>
                            <option value="income">Pemasukan</option>
                            <option value="expense">Pengeluaran</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori *</label>
                        <input type="text" class="form-control" id="category" name="category" 
                               list="categoryList" required 
                               placeholder="Contoh: Penjualan, Operasional, Marketing">
                        <datalist id="categoryList">
                            <option value="Penjualan">
                            <option value="Konsultasi">
                            <option value="Kursus">
                            <option value="Operasional">
                            <option value="Marketing">
                            <option value="Transportasi">
                            <option value="Makan">
                            <option value="Peralatan">
                            <option value="Sewa">
                            <option value="Listrik">
                            <option value="Internet">
                        </datalist>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Jumlah (Rp) *</label>
                        <input type="number" class="form-control" id="amount" name="amount" min="1" step="1000" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="transaction_date" class="form-label">Tanggal Transaksi *</label>
                        <input type="date" class="form-control" id="transaction_date" name="transaction_date" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Keterangan *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required 
                                  placeholder="Deskripsikan transaksi ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_transaction" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Financial Chart
const ctx = document.getElementById('financialChart').getContext('2d');
const chartData = <?php echo json_encode($chart_data); ?>;

const months = chartData.map(data => {
    const date = new Date(data.month + '-01');
    return date.toLocaleDateString('id-ID', { month: 'short', year: '2-digit' });
});
const incomeData = chartData.map(data => parseFloat(data.income));
const expenseData = chartData.map(data => parseFloat(data.expense));

const chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Pemasukan',
            data: incomeData,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4,
            fill: false
        }, {
            label: 'Pengeluaran',
            data: expenseData,
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            tension: 0.4,
            fill: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                    }
                }
            }
        }
    }
});

// Auto close modal on success
<?php if (isset($success)): ?>
    setTimeout(function() {
        $('#addTransactionModal').modal('hide');
        location.reload();
    }, 2000);
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>