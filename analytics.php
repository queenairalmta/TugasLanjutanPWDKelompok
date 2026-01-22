<?php

require_once 'config.php';
requireLogin();

require_once 'User.php';
require_once 'Product.php';

$userModel = new User();
$productModel = new Product();

$user = $userModel->getUserById($_SESSION['user_id']);
$all_users = $userModel->getAllUsers();
$all_products = $productModel->getAll();


$total_users = count($all_users);
$total_products = count($all_products);
$user_products = $productModel->getByUserId($_SESSION['user_id']);
$user_product_count = count($user_products);

$total_value = 0;
$user_total_value = 0;
foreach ($all_products as $product) {
    $total_value += $product['price'];
    if ($product['user_id'] == $_SESSION['user_id']) {
        $user_total_value += $product['price'];
    }
}


$average_price = $total_products > 0 ? $total_value / $total_products : 0;
$user_average_price = $user_product_count > 0 ? $user_total_value / $user_product_count : 0;


$products_per_user = [];
foreach ($all_users as $user_data) {
    $count = $productModel->countByUser($user_data['id']);
    $products_per_user[] = [
        'username' => $user_data['username'],
        'count' => $count
    ];
}


usort($products_per_user, function($a, $b) {
    return $b['count'] - $a['count'];
});


$monthly_data = [
    ['month' => 'Jan', 'products' => 5, 'users' => 2],
    ['month' => 'Feb', 'products' => 8, 'users' => 3],
    ['month' => 'Mar', 'products' => 12, 'users' => 4],
    ['month' => 'Apr', 'products' => 15, 'users' => 4],
    ['month' => 'May', 'products' => 18, 'users' => 5],
    ['month' => 'Jun', 'products' => 22, 'users' => 5],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Bisnis project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        .chart-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }
        .chart-title {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .stat-box {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
        }
        .stat-box .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin: 10px 0;
        }
        .stat-box .stat-label {
            font-size: 0.9rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-box .stat-change {
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .stat-box .stat-change.positive {
            color: var(--success);
        }
        .stat-box .stat-change.negative {
            color: var(--danger);
        }
        .top-users {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }
        .top-user-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .top-user-item:last-child {
            border-bottom: none;
        }
        .user-rank {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--primary-light);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .user-rank.rank-1 { background: #ffd700; }
        .user-rank.rank-2 { background: #c0c0c0; }
        .user-rank.rank-3 { background: #cd7f32; }
        .progress-custom {
            height: 8px;
            border-radius: 4px;
            background: #f0f0f0;
            overflow: hidden;
        }
        .progress-custom .progress-bar {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-modern navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </a>
            
            <div class="navbar-nav ms-auto align-items-center">
                <div class="nav-item dropdown user-dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <div class="me-2">
                            <div class="fw-semibold"><?php echo htmlspecialchars($user['username']); ?></div>
                            <small class="text-white-50">Analytics</small>
                        </div>
                        <i class="fas fa-user-circle fa-lg"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="dropdown-item" href="my_products.php">
                            <i class="fas fa-box me-2"></i>My Products
                        </a>
                        <a class="dropdown-item" href="users.php">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                        <a class="dropdown-item" href="analytics.php">
                            <i class="fas fa-chart-line me-2"></i>Analytics
                        </a>
                        <a class="dropdown-item" href="settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
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
                        <h5><i class="fas fa-bars me-2"></i>Menu</h5>
                    </div>
                    <ul class="sidebar-menu">
                        <li>
                            <a href="dashboard.php">
                                <i class="fas fa-home dashboard-icon"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="my_products.php">
                                <i class="fas fa-box products-icon"></i>
                                <span>My Products</span>
                            </a>
                        </li>
                        <li>
                            <a href="users.php">
                                <i class="fas fa-users users-icon"></i>
                                <span>Users</span>
                            </a>
                        </li>
                        <li>
                            <a href="analytics.php" class="active">
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
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="text-black"><i class="fas fa-chart-line me-2"></i>Analytics Dashboard</h3>
                        <p class="text-muted mb-0">Real-time insights and statistics</p>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-primary">Last Updated: <?php echo date('d M Y H:i'); ?></div>
                        <small class="text-muted">Data refreshes automatically</small>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-box">
                            <div class="stat-label">Total Products</div>
                            <div class="stat-value"><?php echo $total_products; ?></div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up me-1"></i>
                                12% from last month
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-box">
                            <div class="stat-label">Total Users</div>
                            <div class="stat-value"><?php echo $total_users; ?></div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up me-1"></i>
                                8% from last month
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-box">
                            <div class="stat-label">Total Value</div>
                            <div class="stat-value">Rp <?php echo number_format($total_value, 0, ',', '.'); ?></div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up me-1"></i>
                                15% from last month
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-box">
                            <div class="stat-label">Avg. Price</div>
                            <div class="stat-value">Rp <?php echo number_format($average_price, 0, ',', '.'); ?></div>
                            <div class="stat-change negative">
                                <i class="fas fa-arrow-down me-1"></i>
                                3% from last month
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="chart-container">
                            <h5 class="chart-title">
                                <i class="fas fa-chart-bar"></i>
                                Products Growth Over Time
                            </h5>
                            <canvas id="productsChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="top-users">
                            <h5 class="chart-title">
                                <i class="fas fa-trophy"></i>
                                Top Users by Products
                            </h5>
                            <?php for ($i = 0; $i < min(5, count($products_per_user)); $i++): ?>
                            <div class="top-user-item">
                                <div class="d-flex align-items-center">
                                    <div class="user-rank rank-<?php echo $i + 1; ?> me-3">
                                        <?php echo $i + 1; ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($products_per_user[$i]['username']); ?></div>
                                        <small class="text-muted"><?php echo $products_per_user[$i]['count']; ?> products</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary"><?php echo $products_per_user[$i]['count']; ?></div>
                                    <small class="text-muted">products</small>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="chart-title">
                                <i class="fas fa-chart-pie"></i>
                                Products Distribution by User
                            </h5>
                            <canvas id="distributionChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="chart-title">
                                <i class="fas fa-users"></i>
                                Users vs Products Comparison
                            </h5>
                            <canvas id="comparisonChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="chart-title">
                                <i class="fas fa-percentage"></i>
                                Your Performance Metrics
                            </h5>
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="p-3 bg-light rounded-modern">
                                        <div class="fw-bold text-primary"><?php echo $user_product_count; ?></div>
                                        <small class="text-muted">Your Products</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="p-3 bg-light rounded-modern">
                                        <div class="fw-bold text-success">Rp <?php echo number_format($user_total_value, 0, ',', '.'); ?></div>
                                        <small class="text-muted">Your Total Value</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-modern">
                                        <div class="fw-bold text-warning"><?php echo $total_products > 0 ? round(($user_product_count / $total_products) * 100, 1) : 0; ?>%</div>
                                        <small class="text-muted">Market Share</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-modern">
                                        <div class="fw-bold text-danger">Rp <?php echo number_format($user_average_price, 0, ',', '.'); ?></div>
                                        <small class="text-muted">Your Avg. Price</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="chart-title">
                                <i class="fas fa-bullseye"></i>
                                System Health
                            </h5>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Database Usage</span>
                                    <span>85%</span>
                                </div>
                                <div class="progress-custom">
                                    <div class="progress-bar" style="width: 85%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>System Performance</span>
                                    <span>92%</span>
                                </div>
                                <div class="progress-custom">
                                    <div class="progress-bar" style="width: 92%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>User Satisfaction</span>
                                    <span>78%</span>
                                </div>
                                <div class="progress-custom">
                                    <div class="progress-bar" style="width: 78%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Data Accuracy</span>
                                    <span>95%</span>
                                </div>
                                <div class="progress-custom">
                                    <div class="progress-bar" style="width: 95%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
        const productsCtx = document.getElementById('productsChart').getContext('2d');
        const productsChart = new Chart(productsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Total Products',
                    data: [5, 8, 12, 15, 18, <?php echo $total_products; ?>],
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Active Users',
                    data: [2, 3, 4, 4, 5, <?php echo $total_users; ?>],
                    borderColor: '#4cc9f0',
                    backgroundColor: 'rgba(76, 201, 240, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const distributionCtx = document.getElementById('distributionChart').getContext('2d');
        const distributionChart = new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    'Your Products',
                    'Other Users',
                    'System Products'
                ],
                datasets: [{
                    data: [
                        <?php echo $user_product_count; ?>,
                        <?php echo $total_products - $user_product_count; ?>,
                        5
                    ],
                    backgroundColor: [
                        '#4361ee',
                        '#4cc9f0',
                        '#7209b7'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
        const comparisonChart = new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: ['Products', 'Users', 'Value (millions)', 'Avg Price (hundreds)'],
                datasets: [{
                    label: 'Current Month',
                    data: [
                        <?php echo $total_products; ?>,
                        <?php echo $total_users; ?>,
                        <?php echo $total_value / 1000000; ?>,
                        <?php echo $average_price / 100000; ?>
                    ],
                    backgroundColor: 'rgba(67, 97, 238, 0.8)'
                }, {
                    label: 'Last Month',
                    data: [18, 4, 45, 3.5],
                    backgroundColor: 'rgba(76, 201, 240, 0.8)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        setInterval(() => {
            
            const now = new Date();
            document.querySelector('.fw-bold.text-primary').textContent = 
                `Last Updated: ${now.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })} ${now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })}`;
        }, 30000);
    </script>
</body>
</html>