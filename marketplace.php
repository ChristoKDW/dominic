<?php 
$page_title = "Marketplace Digital";
include 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Handle add product
if (isset($_POST['add_product']) && isLoggedIn()) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    
    if (!empty($name) && !empty($description) && $price >= 0 && $stock >= 0) {
        $query = "INSERT INTO products (user_id, category_id, name, description, price, stock) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$_SESSION['user_id'], $category_id, $name, $description, $price, $stock])) {
            $success = "Produk berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan produk.";
        }
    } else {
        $error = "Semua field harus diisi dengan benar.";
    }
}

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';

// Build query
$where_conditions = ["p.status = 'active'"];
$params = [];

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category;
}

if ($price_range) {
    switch ($price_range) {
        case 'free':
            $where_conditions[] = "p.price = 0";
            break;
        case 'under_100k':
            $where_conditions[] = "p.price BETWEEN 1 AND 100000";
            break;
        case '100k_500k':
            $where_conditions[] = "p.price BETWEEN 100000 AND 500000";
            break;
        case 'over_500k':
            $where_conditions[] = "p.price > 500000";
            break;
    }
}

$where_clause = implode(" AND ", $where_conditions);

// Sort options
$order_by = "p.created_at DESC";
switch ($sort) {
    case 'price_low':
        $order_by = "p.price ASC";
        break;
    case 'price_high':
        $order_by = "p.price DESC";
        break;
    case 'name':
        $order_by = "p.name ASC";
        break;
}

// Get products
$query = "SELECT p.*, u.full_name as seller_name, cat.name as category_name
          FROM products p 
          LEFT JOIN users u ON p.user_id = u.id 
          LEFT JOIN categories cat ON p.category_id = cat.id 
          WHERE $where_clause
          ORDER BY $order_by";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$cat_query = "SELECT * FROM categories WHERE type = 'product' ORDER BY name";
$cat_stmt = $db->prepare($cat_query);
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-success text-white rounded-3 p-4 text-center">
                    <h1 class="fw-bold mb-2">
                        <i class="fas fa-store"></i> Marketplace Mini
                    </h1>
                    <p class="lead mb-0">Jual beli produk digital untuk mengembangkan bisnis Anda</p>
                </div>
            </div>
        </div>
        
        <!-- Add Product Button -->
        <?php if (isLoggedIn()): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h4>Produk Digital</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus"></i> Jual Produk
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Search & Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-lg-4">
                                <label class="form-label">Cari Produk</label>
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
                                <label class="form-label">Harga</label>
                                <select class="form-select" name="price_range">
                                    <option value="">Semua Harga</option>
                                    <option value="free" <?php echo $price_range == 'free' ? 'selected' : ''; ?>>Gratis</option>
                                    <option value="under_100k" <?php echo $price_range == 'under_100k' ? 'selected' : ''; ?>>< Rp 100.000</option>
                                    <option value="100k_500k" <?php echo $price_range == '100k_500k' ? 'selected' : ''; ?>>Rp 100.000 - 500.000</option>
                                    <option value="over_500k" <?php echo $price_range == 'over_500k' ? 'selected' : ''; ?>>> Rp 500.000</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Urutkan</label>
                                <select class="form-select" name="sort">
                                    <option value="latest" <?php echo $sort == 'latest' ? 'selected' : ''; ?>>Terbaru</option>
                                    <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Nama A-Z</option>
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
                <h5>Ditemukan <?php echo count($products); ?> produk</h5>
            </div>
        </div>
        
        <!-- Products Grid -->
        <?php if (!empty($products)): ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card h-100 shadow-sm product-card">
                    <?php if ($product['image']): ?>
                        <img src="<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-gradient-success text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-box fa-3x opacity-75"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-secondary"><?php echo $product['category_name']; ?></span>
                            <?php if ($product['stock'] <= 5 && $product['stock'] > 0): ?>
                                <span class="badge bg-warning">Stok Terbatas</span>
                            <?php elseif ($product['stock'] == 0): ?>
                                <span class="badge bg-danger">Habis</span>
                            <?php endif; ?>
                        </div>
                        
                        <h5 class="card-title"><?php echo $product['name']; ?></h5>
                        <p class="card-text text-muted flex-grow-1">
                            <?php echo substr($product['description'], 0, 100); ?>...
                        </p>
                        
                        <div class="product-meta mb-3">
                            <small class="text-muted d-block">
                                <i class="fas fa-user"></i> <?php echo $product['seller_name']; ?>
                            </small>
                            <small class="text-muted d-block">
                                <i class="fas fa-boxes"></i> Stok: <?php echo $product['stock']; ?>
                            </small>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="text-success mb-0 fw-bold">
                                <?php if ($product['price'] == 0): ?>
                                    <span class="text-primary">GRATIS</span>
                                <?php else: ?>
                                    <?php echo formatCurrency($product['price']); ?>
                                <?php endif; ?>
                            </h5>
                            <div>
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($product['stock'] > 0): ?>
                                    <button class="btn btn-success btn-sm" onclick="addToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h4>Tidak ada produk ditemukan</h4>
            <p class="text-muted">Coba ubah filter pencarian atau kata kunci Anda</p>
            <a href="marketplace.php" class="btn btn-success">Lihat Semua Produk</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Product Modal -->
<?php if (isLoggedIn()): ?>
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Tambah Produk Baru
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
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">Nama Produk *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="category_id" class="form-label">Kategori *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi *</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Harga (Rp) *</label>
                            <input type="number" class="form-control" id="price" name="price" min="0" step="1000" required>
                            <small class="form-text text-muted">Masukkan 0 untuk produk gratis</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stok *</label>
                            <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_product" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.product-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.product-meta small {
    line-height: 1.5;
}
</style>

<script>
function addToCart(productId) {
    <?php if (isLoggedIn()): ?>
        // In a real application, you would implement cart functionality
        alert('Fitur keranjang akan segera hadir! Silakan kontak penjual untuk pembelian.');
    <?php else: ?>
        alert('Silakan login terlebih dahulu untuk membeli produk.');
        window.location.href = 'login.php';
    <?php endif; ?>
}

// Auto close modal on success
<?php if (isset($success)): ?>
    setTimeout(function() {
        $('#addProductModal').modal('hide');
        location.reload();
    }, 2000);
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>