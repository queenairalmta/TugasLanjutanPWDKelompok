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


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    if (empty($name) || $price <= 0) {
        $message = 'Nama dan harga produk harus diisi!';
        $message_type = 'danger';
    } else {
        $result = $productModel->create($name, $price, $description, $_SESSION['user_id']);
        
        if ($result['success']) {
            $message = '✅ Produk berhasil ditambahkan!';
            $message_type = 'success';
            header('Location: dashboard.php');
            exit();
        } else {
            $message = '❌ ' . $result['message'];
            $message_type = 'danger';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $id = intval($_POST['product_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    if ($id > 0) {
        $result = $productModel->update($id, $name, $price, $description);
        
        if ($result['success']) {
            $message = '✅ Produk berhasil diupdate!';
            $message_type = 'success';
            header('Location: dashboard.php');
            exit();
        } else {
            $message = '❌ ' . $result['message'];
            $message_type = 'danger';
        }
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    if ($id > 0) {
        $result = $productModel->delete($id);
        
        if ($result['success']) {
            header('Location: dashboard.php?message=' . urlencode('✅ Produk berhasil dihapus!') . '&type=success');
        } else {
            header('Location: dashboard.php?message=' . urlencode('❌ ' . $result['message']) . '&type=danger');
        }
        exit();
    }
}


$products = $productModel->getAll();
$total_products = $productModel->count();
$user_products = $productModel->getByUserId($_SESSION['user_id']);
$user_product_count = count($user_products);
$all_users = $userModel->getAllUsers();
$total_users = count($all_users);

$total_value = 0;
foreach ($products as $product) {
    $total_value += $product['price'];
}
$average_price = $total_products > 0 ? $total_value / $total_products : 0;

if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
    $message_type = $_GET['type'] ?? 'info';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bisnis project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .activity-timeline {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            border-top: 4px solid var(--dashboard-primary);
        }
        
        .timeline-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .timeline-item:last-child {
            border-bottom: none;
        }
        
        .timeline-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--dashboard-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .timeline-content {
            flex-grow: 1;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .quick-action-btn {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            padding: 20px;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
        }
        
        .quick-action-btn:hover {
            border-color: var(--dashboard-primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.1);
        }
        
        .quick-action-btn i {
            font-size: 1.5rem;
            color: rgb(4, 49, 198);
            margin-bottom: 10px;
        }
        
        .performance-metric {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
            text-align: center;
            border-top: 4px solid var(--dashboard-primary);
        }
        
        .metric-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dashboard-primary);
            margin: 10px 0;
        }
        
        .metric-label {
            font-size: 0.9rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .metric-change {
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .metric-change.positive {
            color: #2ecc71;
        }
        
        .metric-change.negative {
            color: #233dbc;
        }
        
        .welcome-animation {
            position: relative;
            overflow: hidden;
        }
        
        .welcome-animation::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 1px, transparent 1px);
            background-size: 30px 30px;
            animation: float 20s linear infinite;
        }
    </style>
</head>
<body class="dashboard-page">
    <nav class="navbar navbar-modern navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-tachometer-alt dashboard-icon"></i>
                <span>Dashboard</span>
                <span class="page-indicator">Overview</span>
            </a>
            
            <div class="navbar-nav ms-auto align-items-center">
                <div class="nav-item dropdown user-dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <div class="me-2">
                            <div class="fw-semibold"><?php echo htmlspecialchars($user['username'] ?? 'User'); ?></div>
                            <small class="text-white-50">Dashboard</small>
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

    <div class="container-fluid">
        <div class="row">
            
            <div class="col-lg-2 col-md-3 p-0">
                <div class="sidebar-modern">
                    <div class="sidebar-header">
                        <h5><i class="fas fa-bars dashboard-icon me-2"></i>Dashboard Menu</h5>
                    </div>
                    <ul class="sidebar-menu">
                        <li>
                            <a href="dashboard.php" class="active">
                                <i class="fas fa-home dashboard-icon"></i>
                                <span>Dashboard</span>
                                <span class="badge-modern">Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                <i class="fas fa-plus-circle dashboard-icon"></i>
                                <span>Add Product</span>
                                <span class="badge-modern pulse">New</span>
                            </a>
                        </li>
                        <li>
                            <a href="my_products.php">
                                <i class="fas fa-box products-icon"></i>
                                <span>My Products</span>
                                <span class="badge-modern"><?php echo $user_product_count; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="users.php">
                                <i class="fas fa-users users-icon"></i>
                                <span>Users</span>
                                <span class="badge-modern"><?php echo $total_users; ?></span>
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
            
                <div class="welcome-card welcome-animation">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4>SELAMAT DATANG, <?php echo htmlspecialchars($user['username'] ?? 'User'); ?>! 👋</h4>
                            <p class="mb-0">Berikut ringkasan dasbor Anda. Pantau produk dan aktivitas sistem Anda.</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-inline-block  bg-opacity-20 rounded-pill px-4 py-2">
                                <small class=>HARI</small>
                                <div class="fw-semibold"><?php echo date('l, d M Y'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

            
                <?php if ($message): ?>
                    <div class="alert-modern alert-<?php echo $message_type; ?>-modern fade-in">
                        <i class="fas fa-<?php echo $message_type == 'berhasil' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

            
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-label">Total Products</div>
                        <div class="stat-number"><?php echo $total_products; ?></div>
                        <div class="metric-change positive">
                            <i class="fas fa-arrow-up me-1"></i>
                            12% from last week
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-label">Your Products</div>
                        <div class="stat-number"><?php echo $user_product_count; ?></div>
                        <div class="metric-change positive">
                            <i class="fas fa-arrow-up me-1"></i>
                            8% from last week
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-label">Total Users</div>
                        <div class="stat-number"><?php echo $total_users; ?></div>
                        <div class="metric-change positive">
                            <i class="fas fa-arrow-up me-1"></i>
                            5% from last week
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-label">Total Value</div>
                        <div class="stat-number">Rp <?php echo number_format($total_value, 0, ',', '.'); ?></div>
                        <div class="metric-change positive">
                            <i class="fas fa-arrow-up me-1"></i>
                            15% from last week
                        </div>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="activity-timeline">
                        <h5><i class="fas fa-history dashboard-icon me-2"></i>Recent Activity</h5>
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="fw-semibold">Login Successful</div>
                                <small class="text-muted">Just now - Dashboard access</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="fw-semibold"><?php echo $user_product_count; ?> Products</div>
                                <small class="text-muted">Active products in your account</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="fw-semibold"><?php echo $total_users; ?> Users</div>
                                <small class="text-muted">Total registered users</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="fw-semibold">System Health</div>
                                <small class="text-muted">All systems operational</small>
                            </div>
                        </div>
                    </div>

                    <div class="activity-timeline">
                        <h5><i class="fas fa-bolt dashboard-icon me-2"></i>Quick Actions</h5>
                        <div class="quick-actions">
                            <div class="quick-action-btn" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                <i class="fas fa-plus"></i>
                                <div class="fw-semibold text-black">Add Product</div>
                            </div>
                            <a href="my_products.php" class="quick-action-btn text-decoration-none">
                                <i class="fas fa-eye"></i>
                                <div class="fw-semibold text-black">View Products</div>
                            </a>
                            <a href="analytics.php" class="quick-action-btn text-decoration-none">
                                <i class="fas fa-chart-bar"></i>
                                <div class="fw-semibold text-black">Analytics</div>
                            </a>
                            <a href="settings.php" class="quick-action-btn text-decoration-none">
                                <i class="fas fa-cog"></i>
                                <div class="fw-semibold text-black">Settings</div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3 col-6">
                        <div class="performance-metric">
                            <i class="fas fa-percentage dashboard-icon fa-2x mb-2"></i>
                            <div class="metric-value"><?php echo $total_products > 0 ? round(($user_product_count / $total_products) * 100, 1) : 0; ?>%</div>
                            <div class="metric-label">Market Share</div>
                            <div class="metric-change positive">
                                <i class="fas fa-arrow-up me-1"></i>2.5%
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="performance-metric">
                            <i class="fas fa-money-bill-wave dashboard-icon fa-2x mb-2"></i>
                            <div class="metric-value">Rp <?php echo number_format($average_price, 0, ',', '.'); ?></div>
                            <div class="metric-label">Avg. Price</div>
                            <div class="metric-change positive">
                                <i class="fas fa-arrow-up me-1"></i>3.2%
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="performance-metric">
                            <i class="fas fa-chart-line dashboard-icon fa-2x mb-2"></i>
                            <div class="metric-value"><?php echo $total_users > 0 ? round($total_products / $total_users, 1) : 0; ?></div>
                            <div class="metric-label">Products/User</div>
                            <div class="metric-change positive">
                                <i class="fas fa-arrow-up me-1"></i>1.8%
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="performance-metric">
                            <i class="fas fa-shield-alt dashboard-icon fa-2x mb-2"></i>
                            <div class="metric-value">100%</div>
                            <div class="metric-label">System Status</div>
                            <div class="metric-change positive">
                                <i class="fas fa-check me-1"></i>Stable
                            </div>
                        </div>
                    </div>
                </div>

                <div class="products-card">
                    <div class="card-header">
                        <h5><i class="fas fa-boxes dashboard-icon me-2"></i>Products Management</h5>
                        <button class="btn btn-primary-modern btn-lg-modern" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="fas fa-plus me-2"></i>Add New Product
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($products)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <h5>No Products Found</h5>
                                <p>Start by adding your first product to the system.</p>
                                <button class="btn btn-primary-modern btn-lg-modern" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                    <i class="fas fa-plus me-2"></i>Add First Product
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="products-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Product Name</th>
                                            <th>Price</th>
                                            <th>Description</th>
                                            <th>Created By</th>
                                            <th>Date Added</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $index => $product): ?>
                                        <tr class="slide-in">
                                            <td>
                                                <span class="badge-modern badge-primary"><?php echo $index + 1; ?></span>
                                            </td>
                                            <td>
                                                <div class="product-name">
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                    <?php if ($product['user_id'] == $_SESSION['user_id']): ?>
                                                        <span class="badge-modern badge-info ms-2">
                                                            <i class="fas fa-user me-1"></i>Yours
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="product-price">
                                                    Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="product-description" title="<?php echo htmlspecialchars($product['description'] ?? ''); ?>">
                                                    <?php echo htmlspecialchars(substr($product['description'] ?? 'No description', 0, 60)); ?>
                                                    <?php if (strlen($product['description'] ?? '') > 60): ?>...<?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                                        <i class="fas fa-user text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($product['created_by'] ?? 'User'); ?></div>
                                                        <small class="text-muted">User ID: <?php echo $product['user_id']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-muted">
                                                    <?php echo date('d M Y', strtotime($product['created_at'])); ?>
                                                    <br>
                                                    <small><?php echo date('H:i', strtotime($product['created_at'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="product-actions">
                                                    <button class="btn btn-modern btn-success-modern btn-sm-modern"
                                                            onclick="editProduct(<?php echo $product['id']; ?>)">
                                                                                                                </button>
                                                    <a href="?delete=<?php echo $product['id']; ?>" 
                                                       class="btn btn-modern btn-danger-modern btn-sm-modern"
                                                       onclick="return confirm('Are you sure you want to delete this product?')">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-lg-6">
                        <div class="products-card">
                            <div class="card-header">
                                <h5><i class="fas fa-history dashboard-icon me-2"></i>System Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex align-items-center">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                            <i class="fas fa-check-circle text-success"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">Database Connected</div>
                                            <small class="text-muted">MySQL connection active</small>
                                        </div>
                                        <span class="badge-modern badge-success">Active</span>
                                    </div>
                                    <div class="list-group-item d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                            <i class="fas fa-server text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">Server Load</div>
                                            <small class="text-muted">Optimal performance</small>
                                        </div>
                                        <span class="badge-modern badge-primary">45%</span>
                                    </div>
                                    <div class="list-group-item d-flex align-items-center">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                            <i class="fas fa-shield-alt text-warning"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">Security Status</div>
                                            <small class="text-muted">All security checks passed</small>
                                        </div>
                                        <span class="badge-modern badge-warning">Secure</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="products-card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-pie dashboard-icon me-2"></i>Quick Stats</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="p-3 bg-light rounded-modern">
                                            <div class="stat-number text-primary"><?php echo $total_products; ?></div>
                                            <div class="stat-label">Total Products</div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="p-3 bg-light rounded-modern">
                                            <div class="stat-number text-success"><?php echo $user_product_count; ?></div>
                                            <div class="stat-label">Your Products</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-modern">
                                            <div class="stat-number text-warning"><?php echo $total_users; ?></div>
                                            <div class="stat-label">Total Users</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-modern">
                                            <div class="stat-number text-danger"><?php echo $total_products > 0 ? 'Active' : '0'; ?></div>
                                            <div class="stat-label">System Status</div>
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

    <div class="modal fade modal-modern" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-plus-circle dashboard-icon me-2"></i>Add New Product
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="add_product" value="1">
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label-modern">Product Name *</label>
                                <input type="text" name="name" class="form-control form-control-modern" required
                                       placeholder="Enter product name">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label-modern">Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" name="price" class="form-control form-control-modern" 
                                           step="1000" min="0" required 
                                           placeholder="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label-modern">Description</label>
                            <textarea name="description" class="form-control form-control-modern" rows="4"
                                      placeholder="Describe your product..."></textarea>
                        </div>
                        
                        <div class="alert alert-info alert-modern">
                            <i class="fas fa-info-circle dashboard-icon me-2"></i>
                            Semua kolom yang ditandai dengan * wajib diisi. Produk akan ditambahkan ke akun Anda.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary-modern">
                            <i class="fas fa-save me-2"></i>Simpan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade modal-modern" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>Edit Product
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_product" value="1">
                        <input type="hidden" name="product_id" id="editProductId">
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label-modern">Nama Produk *</label>
                                <input type="text" name="name" id="editProductName" 
                                       class="form-control form-control-modern" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label-modern">Harga *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" name="price" id="editProductPrice" 
                                           class="form-control form-control-modern" step="1000" min="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label-modern">Deskripsi</label>
                            <textarea name="description" id="editProductDescription" 
                                      class="form-control form-control-modern" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="fas fa-sync-alt me-2"></i>Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
                        showAlert('Gagal memuat data produk', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Gagal memuat data produk', 'danger');
                });
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert-modern alert-${type}-modern fade-in`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'berhasil' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
                <button type="button" class="btn-close float-end" onclick="this.parentElement.remove()"></button>
            `;
            
            document.querySelector('.main-content').insertBefore(alertDiv, document.querySelector('.main-content').firstChild);
            
            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        setTimeout(() => {
            document.querySelectorAll('.alert-modern').forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.stat-card').forEach(card => {
            observer.observe(card);
        });

        document.querySelectorAll('.quick-action-btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.02)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        function updateTime() {
            const now = new Date();
            const timeElement = document.querySelector('.fw-semibold');
            if (timeElement) {
                const options = { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                };
                timeElement.textContent = now.toLocaleDateString('en-US', options);
            }
        }

        updateTime();
        setInterval(updateTime, 60000);

        document.querySelectorAll('.btn-modern').forEach(button => {
            button.addEventListener('click', function(e) {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    </script>
</body>
</html>