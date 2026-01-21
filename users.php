<?php

require_once 'config.php';
requireLogin();

require_once 'User.php';
require_once 'Product.php';

$userModel = new User();
$productModel = new Product();

$current_user = $userModel->getUserById($_SESSION['user_id']);
$all_users = $userModel->getAllUsers();
$total_users = count($all_users);


$users_with_stats = [];
foreach ($all_users as $user) {
    $user['product_count'] = $productModel->countByUser($user['id']);
    $users_with_stats[] = $user;
}


usort($users_with_stats, function($a, $b) {
    return $b['product_count'] - $a['product_count'];
});
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Bisnis project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
       
        .users-header {
            background: var(--users-gradient);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
            border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);
            position: relative;
            overflow: hidden;
        }
        
        .users-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.1) 0%, transparent 50%);
        }
        
        .user-profile-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            text-align: center;
            border-top: 4px solid var(--users-primary);
            position: relative;
            overflow: hidden;
        }
        
        .user-profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: var(--users-gradient);
            border-radius: 50%;
            transform: translate(50%, -50%);
            opacity: 0.1;
        }
        
        .user-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--users-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 20px;
            border: 5px solid white;
            box-shadow: 0 5px 20px rgba(114, 9, 183, 0.3);
        }
        
        .user-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .user-stat {
            background: rgba(114, 9, 183, 0.05);
            border-radius: var(--border-radius-sm);
            padding: 15px;
            text-align: center;
        }
        
        .user-stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--users-primary);
        }
        
        .user-stat-label {
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        .users-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .user-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            border-left: 4px solid var(--users-primary);
        }
        
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(114, 9, 183, 0.15);
        }
        
        .user-card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .user-avatar-small {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--users-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .user-info {
            flex-grow: 1;
        }
        
        .user-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-online {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
        }
        
        .status-offline {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            color: white;
        }
        
        .user-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        
        .meta-item {
            text-align: center;
        }
        
        .meta-value {
            font-weight: 600;
            color: var(--users-primary);
        }
        
        .meta-label {
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        .user-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .leaderboard {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .leaderboard-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-radius: var(--border-radius-sm);
            margin-bottom: 10px;
            background: #f8f9fa;
            transition: var(--transition);
        }
        
        .leaderboard-item:hover {
            background: rgba(114, 9, 183, 0.05);
        }
        
        .rank {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--users-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .rank-1 {
            background: linear-gradient(135deg, #ffd700, #ffa500);
        }
        
        .rank-2 {
            background: linear-gradient(135deg, #c0c0c0, #a0a0a0);
        }
        
        .rank-3 {
            background: linear-gradient(135deg, #cd7f32, #a0522d);
        }
        
        .user-search {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }
    </style>
</head>
<body class="users-page">
    
    <nav class="navbar navbar-modern navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-users users-icon"></i>
                <span>Users</span>
                <span class="page-indicator">Community</span>
            </a>
            
            <div class="navbar-nav ms-auto align-items-center">
                <div class="nav-item dropdown user-dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <div class="me-2">
                            <div class="fw-semibold"><?php echo htmlspecialchars($current_user['username']); ?></div>
                            <small class="text-white-50">Community</small>
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

    
    <div class="users-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="fw-bold"><i class="fas fa-users me-2"></i>Users Community</h1>
                    <p class="mb-0 opacity-75">Meet and manage all registered users</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-opacity-50 bg-white rounded-pill px-4 py-2 d-inline-block">
                        <small class="text-white opacity-75">Total Users</small>
                        <div class="fw-bold text-white"><?php echo $total_users; ?> Members</div>
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
                        <h5><i class="fas fa-bars users-icon me-2"></i>Community Menu</h5>
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
                            <a href="users.php" class="active">
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
                
                <div class="user-profile-card">
                    <div class="user-avatar-large">
                        <?php echo strtoupper(substr($current_user['username'], 0, 2)); ?>
                    </div>
                    <h4 class="text-black"><?php echo htmlspecialchars($current_user['username']); ?></h4>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($current_user['email']); ?></p>
                     
                    <div class="user-stats-grid">
                        <div class="user-stat">
                            <div class="user-stat-value"><?php echo $productModel->countByUser($current_user['id']); ?></div>
                            <div class="user-stat-label">Products</div>
                        </div>
                        <div class="user-stat">
                            <div class="user-stat-value"><?php echo date('M Y', strtotime($current_user['created_at'])); ?></div>
                            <div class="user-stat-label">Joined</div>
                        </div>
                        <div class="user-stat">
                            <div class="user-stat-value">#<?php echo array_search($current_user['id'], array_column($users_with_stats, 'id')) + 1; ?></div>
                            <div class="user-stat-label">Rank</div>
                        </div>
                        <div class="user-stat">
                            <div class="user-stat-value">
                                <span class="status-online user-status">Online</span>
                            </div>
                            <div class="user-stat-label">Status</div>
                        </div>
                    </div>
                </div>

                <div class="user-search">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" placeholder="Search users..." id="userSearch">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="sortUsers">
                                <option value="products">Most Products</option>
                                <option value="recent">Recently Joined</option>
                                <option value="name">Name: A to Z</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="filterUsers">
                                <option value="all">All Users</option>
                                <option value="active">Active Users</option>
                                <option value="admins">Admins Only</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="leaderboard">
                    <h5 class="text-black"><i class="fas fa-trophy users-icon me-2"></i>Top Contributors</h5>
                    <?php for ($i = 0; $i < min(5, count($users_with_stats)); $i++): ?>
                    <div class="leaderboard-item">
                        <div class="rank rank-<?php echo $i + 1; ?>">
                            <?php echo $i + 1; ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold"><?php echo htmlspecialchars($users_with_stats[$i]['username']); ?></div>
                            <small class="text-muted"><?php echo $users_with_stats[$i]['product_count']; ?> products</small>
                        </div>
                        <?php if ($users_with_stats[$i]['id'] == $_SESSION['user_id']): ?>
                        <span class="badge-modern badge-primary">You</span>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>

                <div class="users-list" id="usersGrid">
                    <?php foreach ($users_with_stats as $index => $user): ?>
                    <div class="user-card" data-products="<?php echo $user['product_count']; ?>" 
                         data-date="<?php echo strtotime($user['created_at']); ?>"
                         data-name="<?php echo htmlspecialchars(strtolower($user['username'])); ?>"
                         data-role="<?php echo $user['id'] == 1 ? 'admin' : 'user'; ?>">
                        <div class="user-card-header">
                            <div class="user-avatar-small">
                                <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                            </div>
                            <div class="user-info">
                                <h6 class="text-black"><?php echo htmlspecialchars($user['username']); ?></h6>
                                <small class="text-muted">
                                    <i class="fas fa-envelope me-1"></i>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </small>
                            </div>
                            <span class="status-<?php echo $index < 3 ? 'online' : 'offline'; ?> user-status">
                                <?php echo $index < 3 ? 'Online' : 'Offline'; ?>
                            </span>
                        </div>
                        
                        <div class="user-meta">
                            <div class="meta-item">
                                <div class="meta-value"><?php echo $user['product_count']; ?></div>
                                <div class="meta-label">Products</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-value">#<?php echo $index + 1; ?></div>
                                <div class="meta-label">Rank</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-value">
                                    <?php echo $user['id'] == 1 ? 'Admin' : 'User'; ?>
                                </div>
                                <div class="meta-label">Role</div>
                            </div>
                        </div>
                        
                        <div class="user-actions">
                            <button class="btn btn-modern btn-primary-modern btn-sm-modern flex-grow-1">
                                <i class="fas fa-eye me-1"></i>View Profile
                            </button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <button class="btn btn-modern btn-success-modern btn-sm-modern">
                                <i class="fas fa-comment"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
        const userSearch = document.getElementById('userSearch');
        userSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const userCards = document.querySelectorAll('.user-card');
            
            userCards.forEach(card => {
                const userName = card.dataset.name;
                if (userName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        const sortSelect = document.getElementById('sortUsers');
        sortSelect.addEventListener('change', function() {
            const container = document.getElementById('usersGrid');
            const items = Array.from(container.querySelectorAll('.user-card'));
            
            items.sort((a, b) => {
                switch(this.value) {
                    case 'products':
                        return parseInt(b.dataset.products) - parseInt(a.dataset.products);
                    case 'recent':
                        return parseInt(b.dataset.date) - parseInt(a.dataset.date);
                    case 'name':
                        return a.dataset.name.localeCompare(b.dataset.name);
                    default:
                        return 0;
                }
            });
            
            items.forEach(item => container.appendChild(item));
        });

        const filterSelect = document.getElementById('filterUsers');
        filterSelect.addEventListener('change', function() {
            const userCards = document.querySelectorAll('.user-card');
            
            userCards.forEach(card => {
                const role = card.dataset.role;
                
                switch(this.value) {
                    case 'all':
                        card.style.display = 'block';
                        break;
                    case 'active':
                        
                        const products = parseInt(card.dataset.products);
                        card.style.display = products > 0 ? 'block' : 'none';
                        break;
                    case 'admins':
                        card.style.display = role === 'admin' ? 'block' : 'none';
                        break;
                }
            });
        });

        document.querySelectorAll('.user-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

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