<?php
require_once 'config.php';
requireLogin();

require_once 'User.php';
require_once 'Product.php';

$userModel = new User();
$productModel = new Product();

$user = $userModel->getUserById($_SESSION['user_id']);

$message = '';
$message_type = '';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    if ($id > 0) {
        
        $product = $productModel->getById($id);
        if ($product && $product['user_id'] == $_SESSION['user_id']) {
            $result = $productModel->delete($id);
            
            if ($result['success']) {
                $message = '✅ Produk berhasil dihapus!';
                $message_type = 'success';
            } else {
                $message = '❌ ' . $result['message'];
                $message_type = 'danger';
            }
        } else {
            $message = '❌ Anda tidak memiliki izin untuk menghapus produk ini';
            $message_type = 'danger';
        }
    }
}

$products = $productModel->getByUserId($_SESSION['user_id']);
$total_products = count($products);

$total_value = 0;
foreach ($products as $product) {
    $total_value += $product['price'];
}
$average_price = $total_products > 0 ? $total_value / $total_products : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - Bisnis project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        
        .product-header {
            background: var(--products-gradient);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
            border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);
            position: relative;
            overflow: hidden;
        }
        
        .product-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }
        
        .product-stats-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            border-top: 4px solid var(--products-primary);
            text-align: center;
            transition: var(--transition);
        }
        
        .product-stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .product-item {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            border: 1px solid #e9ecef;
        }
        
        .product-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(42, 157, 143, 0.15);
            border-color: var(--products-primary);
        }
        
        .product-item-header {
            background: var(--products-gradient);
            color: white;
            padding: 20px;
            position: relative;
        }
        
        .product-item-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 20px;
            width: 20px;
            height: 20px;
            background: var(--products-primary);
            transform: rotate(45deg);
        }
        
        .product-item-body {
            padding: 20px;
        }
        
        .product-price-tag {
            background: var(--products-gradient);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .product-category {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(42, 157, 143, 0.1);
            color: var(--products-primary);
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }
        
        .empty-products {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .empty-products-icon {
            font-size: 4rem;
            color: var(--products-primary);
            opacity: 0.3;
            margin-bottom: 20px;
        }
        
        .view-toggle {
            background: white;
            border-radius: var(--border-radius-sm);
            padding: 5px;
            display: inline-flex;
            gap: 5px;
            margin-bottom: 20px;
        }
        
        .view-toggle-btn {
            padding: 8px 15px;
            border: none;
            background: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .view-toggle-btn.active {
            background: var(--products-gradient);
            color: white;
        }
        
        .product-filters {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }
        
        .filter-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .filter-tag {
            padding: 5px 12px;
            background: rgba(42, 157, 143, 0.1);
            color: var(--products-primary);
            border-radius: 20px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .filter-tag:hover,
        .filter-tag.active {
            background: var(--products-gradient);
            color: white;
        }
    </style>
</head>
<body class="products-page">
    
    <nav class="navbar navbar-modern navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-box products-icon"></i>
                <span>My Products</span>
                <span class="page-indicator">Inventory</span>
            </a>
            
            <div class="navbar-nav ms-auto align-items-center">
                <div class="nav-item dropdown user-dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <div class="me-2">
                            <div class="fw-semibold"><?php echo htmlspecialchars($user['username']); ?></div>
                            <small class="text-white-50"><?php echo $total_products; ?> Products</small>
                        </div>
                        <div class="position-relative">
                            <i class="fas fa-user-circle fa-lg"></i>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                                <span class="visually-hidden">Online</span>
                            </span>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="dashboard.php">
                            <i class="fas fa-tachometer-alt dashboard-icon me-2"></i>Dashboard
                        </a>
                        <a class="dropdown-item" href="my_products.php">
                            <i class="fas fa-box products-icon me-2"></i>My Products
                        </a>
                        <a class="dropdown-item" href="users.php">
                            <i class="fas fa-users users-icon me-2"></i>Users
                        </a>
                        <a class="dropdown-item" href="analytics.php">
                            <i class="fas fa-chart-line analytics-icon me-2"></i>Analytics
                        </a>
                        <a class="dropdown-item" href="settings.php">
                            <i class="fas fa-cog settings-icon me-2"></i>Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    
    <div class="product-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="fw-bold"><i class="fas fa-box me-2"></i>My Products</h1>
                    <p class="mb-0 opacity-75">Manage your product inventory efficiently</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class=" rounded-pill px-4 py-2 d-inline-block">
                        <small class="opacity-75">Total Value</small>
                        <div class="fw-bold">Rp <?php echo number_format($total_value, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            
            <div class="col-lg-2 col-md-3 p-0">
                <div class="sidebar-modern">
                    <div class="sidebar-header">
                        <h5><i class="fas fa-bars products-icon me-2"></i>Products Menu</h5>
                    </div>
                    <ul class="sidebar-menu">
                        <li>
                            <a href="dashboard.php">
                                <i class="fas fa-home dashboard-icon"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="my_products.php" class="active">
                                <i class="fas fa-box products-icon"></i>
                                <span>My Products</span>
                                <span class="badge-modern"><?php echo $total_products; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                <i class="fas fa-plus-circle products-icon"></i>
                                <span>Add Product</span>
                                <span class="badge-modern pulse">New</span>
                            </a>
                        </li>
                        <li>
                            <a href="users.php">
                                <i class="fas fa-users users-icon"></i>
                                <span>Users</span>
                            </a>
                        </li>
                        <li>
                            <a href="analytics.php">
                                <i class="fas fa-chart-line analytics-icon"></i>
                                <span>Analytics</span>
                            </a>
                        </li>
                        <li>
                            <a href="settings.php">
                                <i class="fas fa-cog settings-icon"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            
            <div class="col-lg-10 col-md-9 main-content">
                
                <?php if ($message): ?>
                    <div class="alert-modern alert-<?php echo $message_type; ?>-modern fade-in mb-4">
                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close float-end" onclick="this.parentElement.remove()"></button>
                    </div>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="product-stats-card">
                            <i class="fas fa-box fa-2x products-icon mb-3"></i>
                            <div class="stat-value"><?php echo $total_products; ?></div>
                            <div class="stat-label">Total Products</div>
                            <small class="text-muted">In your inventory</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="product-stats-card">
                            <i class="fas fa-money-bill-wave fa-2x products-icon mb-3"></i>
                            <div class="stat-value">Rp <?php echo number_format($total_value, 0, ',', '.'); ?></div>
                            <div class="stat-label">Total Value</div>
                            <small class="text-muted">Inventory worth</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="product-stats-card">
                            <i class="fas fa-chart-line fa-2x products-icon mb-3"></i>
                            <div class="stat-value">Rp <?php echo number_format($average_price, 0, ',', '.'); ?></div>
                            <div class="stat-label">Avg. Price</div>
                            <small class="text-muted">Per product</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="product-stats-card">
                            <i class="fas fa-calendar-alt fa-2x products-icon mb-3"></i>
                            <div class="stat-value"><?php echo date('M Y'); ?></div>
                            <div class="stat-label">Current Month</div>
                            <small class="text-muted"><?php echo $total_products; ?> active</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="view-toggle">
                            <button class="view-toggle-btn active" id="gridViewBtn">
                                <i class="fas fa-th-large"></i> Grid
                            </button>
                            <button class="view-toggle-btn" id="listViewBtn">
                                <i class="fas fa-list"></i> List
                            </button>
                        </div>
                        <button class="btn btn-primary-modern" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="fas fa-plus me-2"></i>Add Product
                        </button>
                    </div>
                    <div class="text-muted">
                        Showing <?php echo $total_products; ?> products
                    </div>
                </div>

                <div class="product-filters">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" placeholder="Search products..." id="productSearch">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" id="sortProducts">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="price-high">Price: High to Low</option>
                                <option value="price-low">Price: Low to High</option>
                                <option value="name">Name: A to Z</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-tags">
                        <span class="filter-tag active" data-filter="all">All Products</span>
                        <span class="filter-tag" data-filter="recent">Recent</span>
                        <span class="filter-tag" data-filter="high-value">High Value</span>
                        <span class="filter-tag" data-filter="low-value">Low Value</span>
                    </div>
                </div>

                <?php if (empty($products)): ?>
                    <div class="empty-products">
                        <div class="empty-products-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3>No Products Yet</h3>
                        <p class="text-muted mb-4">You haven't added any products to your inventory.</p>
                        <button class="btn btn-primary-modern btn-lg" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="fas fa-plus me-2"></i>Add Your First Product
                        </button>
                    </div>
                <?php else: ?>
                    
                    <div class="product-grid" id="gridView">
                        <?php foreach ($products as $product): ?>
                        <div class="product-item" data-price="<?php echo $product['price']; ?>" 
                             data-date="<?php echo strtotime($product['created_at']); ?>"
                             data-name="<?php echo htmlspecialchars(strtolower($product['name'])); ?>">
                            <div class="product-item-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($product['name']); ?></h6>
                                        <small class="opacity-75">ID: <?php echo $product['id']; ?></small>
                                    </div>
                                    <span class="product-category">
                                        <i class="fas fa-tag me-1"></i>Product
                                    </span>
                                </div>
                            </div>
                            <div class="product-item-body">
                                <div class="product-price-tag">
                                    Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                </div>
                                
                                <p class="text-muted small mb-3">
                                    <?php echo htmlspecialchars(substr($product['description'] ?? 'No description available', 0, 120)); ?>
                                    <?php if (strlen($product['description'] ?? '') > 120): ?>...<?php endif; ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        <?php echo date('d M Y', strtotime($product['created_at'])); ?>
                                    </small>
                                    <span class="badge-modern badge-success">
                                        Active
                                    </span>
                                </div>
                                
                                <div class="product-actions">
                                    <button class="btn btn-modern btn-warning btn-sm-modern flex-grow-1"
                                            onclick="editProduct(<?php echo $product['id']; ?>)"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editProductModal">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                    <a href="my_products.php?delete=<?php echo $product['id']; ?>" 
                                       class="btn btn-modern btn-danger-modern btn-sm-modern"
                                       onclick="return confirm('Delete this product?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-none" id="listView">
                        <div class="products-card">
                            <div class="card-header">
                                <h5><i class="fas fa-list products-icon me-2"></i>Products List</h5>
                            </div>
                            <div class="table-container">
                                <table class="products-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Description</th>
                                            <th>Date Added</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-box text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($product['name']); ?></div>
                                                        <small class="text-muted">ID: <?php echo $product['id']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="product-description">
                                                    <?php echo htmlspecialchars(substr($product['description'] ?? 'No description', 0, 80)); ?>
                                                    <?php if (strlen($product['description'] ?? '') > 80): ?>...<?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo date('d M Y', strtotime($product['created_at'])); ?>
                                                <br>
                                                <small class="text-muted"><?php echo date('H:i', strtotime($product['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="product-actions">
                                                    <button class="btn btn-modern btn-success-modern btn-sm-modern"
                                                            onclick="editProduct(<?php echo $product['id']; ?>)"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editProductModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="my_products.php?delete=<?php echo $product['id']; ?>" 
                                                       class="btn btn-modern btn-danger-modern btn-sm-modern"
                                                       onclick="return confirm('Delete this product?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade modal-modern" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="dashboard.php">
                    <input type="hidden" name="add_product" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-plus-circle products-icon me-2"></i>Add New Product
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-modern">Product Name *</label>
                            <input type="text" name="name" class="form-control form-control-modern" required
                                   placeholder="Enter product name">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label-modern">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price" class="form-control form-control-modern" 
                                       step="1000" min="0" required
                                       placeholder="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label-modern">Description</label>
                            <textarea name="description" class="form-control form-control-modern" rows="3"
                                      placeholder="Describe your product..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-modern">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade modal-modern" id="editProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="dashboard.php">
                    <input type="hidden" name="edit_product" value="1">
                    <input type="hidden" name="product_id" id="editProductId">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>Edit Product
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-modern">Product Name *</label>
                            <input type="text" name="name" id="editProductName" class="form-control form-control-modern" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label-modern">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price" id="editProductPrice" class="form-control form-control-modern" 
                                       step="1000" min="0" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label-modern">Description</label>
                            <textarea name="description" id="editProductDescription" class="form-control form-control-modern" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-white">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
 
        document.getElementById('gridViewBtn').addEventListener('click', function() {
            document.getElementById('gridView').classList.remove('d-none');
            document.getElementById('listView').classList.add('d-none');
            this.classList.add('active');
            document.getElementById('listViewBtn').classList.remove('active');
        });
        
        document.getElementById('listViewBtn').addEventListener('click', function() {
            document.getElementById('gridView').classList.add('d-none');
            document.getElementById('listView').classList.remove('d-none');
            this.classList.add('active');
            document.getElementById('gridViewBtn').classList.remove('active');
        });

        function editProduct(productId) {
            fetch('ajax_get_product.php?id=' + productId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('editProductId').value = data.product.id;
                        document.getElementById('editProductName').value = data.product.name;
                        document.getElementById('editProductPrice').value = data.product.price;
                        document.getElementById('editProductDescription').value = data.product.description || '';
                    } else {
                        alert('Failed to load product data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading product data');
                });
        }

        setTimeout(() => {
            document.querySelectorAll('.alert-modern').forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        const filterTags = document.querySelectorAll('.filter-tag');
        filterTags.forEach(tag => {
            tag.addEventListener('click', function() {
                filterTags.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                const productItems = document.querySelectorAll('.product-item');
                
                productItems.forEach(item => {
                    const price = parseFloat(item.dataset.price);
                    
                    switch(filter) {
                        case 'all':
                            item.style.display = 'block';
                            break;
                        case 'recent':
                            const date = parseInt(item.dataset.date);
                            const oneWeekAgo = Date.now() / 1000 - (7 * 24 * 60 * 60);
                            item.style.display = date > oneWeekAgo ? 'block' : 'none';
                            break;
                        case 'high-value':
                            item.style.display = price > 500000 ? 'block' : 'none';
                            break;
                        case 'low-value':
                            item.style.display = price <= 500000 ? 'block' : 'none';
                            break;
                    }
                });
            });
        });

        const searchInput = document.getElementById('productSearch');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const productItems = document.querySelectorAll('.product-item');
            
            productItems.forEach(item => {
                const productName = item.dataset.name;
                if (productName.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        const sortSelect = document.getElementById('sortProducts');
        sortSelect.addEventListener('change', function() {
            const container = document.getElementById('gridView');
            const items = Array.from(container.querySelectorAll('.product-item'));
            
            items.sort((a, b) => {
                switch(this.value) {
                    case 'newest':
                        return parseInt(b.dataset.date) - parseInt(a.dataset.date);
                    case 'oldest':
                        return parseInt(a.dataset.date) - parseInt(b.dataset.date);
                    case 'price-high':
                        return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                    case 'price-low':
                        return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                    case 'name':
                        return a.dataset.name.localeCompare(b.dataset.name);
                    default:
                        return 0;
                }
            });
            
            items.forEach(item => container.appendChild(item));
        });

        document.querySelectorAll('.product-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>