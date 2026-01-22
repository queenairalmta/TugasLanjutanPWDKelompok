<?php

require_once 'config.php';
requireLogin();

require_once 'User.php';

$userModel = new User();
$user = $userModel->getUserById($_SESSION['user_id']);

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($username) || empty($email)) {
        $message = 'Username and email are required!';
        $message_type = 'danger';
    } else {
        $result = $userModel->updateProfile($_SESSION['user_id'], $username, $email);
        
        if ($result['success']) {
            $message = '✅ Profile updated successfully!';
            $message_type = 'success';
            
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $user = $userModel->getUserById($_SESSION['user_id']);
        } else {
            $message = '❌ ' . $result['message'];
            $message_type = 'danger';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = 'All password fields are required!';
        $message_type = 'danger';
    } elseif ($new_password !== $confirm_password) {
        $message = 'New passwords do not match!';
        $message_type = 'danger';
    } elseif (strlen($new_password) < 6) {
        $message = 'New password must be at least 6 characters!';
        $message_type = 'danger';
    } else {
        $result = $userModel->changePassword($_SESSION['user_id'], $current_password, $new_password);
        
        if ($result['success']) {
            $message = '✅ Password changed successfully!';
            $message_type = 'success';
        } else {
            $message = '❌ ' . $result['message'];
            $message_type = 'danger';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_preferences'])) {
    $theme = $_POST['theme'] ?? 'light';
    $notifications = isset($_POST['notifications']) ? 1 : 0;
    $language = $_POST['language'] ?? 'en';
    
    $message = '✅ Preferences updated successfully!';
    $message_type = 'success';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Bisnis project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .settings-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }
        .settings-title {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            margin: 0 auto 20px;
            border: 5px solid white;
            box-shadow: var(--shadow);
        }
        .tab-content {
            padding: 20px;
            background: white;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
        }
        .nav-tabs .nav-link {
            border: none;
            color: var(--gray);
            font-weight: 500;
            padding: 12px 20px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }
        .nav-tabs .nav-link.active {
            color: var(--primary);
            background: white;
            border-bottom: 2px solid var(--primary);
        }
        .danger-zone {
            border: 2px solid var(--danger);
            border-radius: var(--border-radius);
            padding: 20px;
            background: linear-gradient(135deg, rgba(247, 37, 133, 0.05) 0%, rgba(181, 23, 158, 0.05) 100%);
        }
        .system-info {
            background: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 15px;
            margin-top: 20px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-modern navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            
            <div class="navbar-nav ms-auto align-items-center">
                <div class="nav-item dropdown user-dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <div class="me-2">
                            <div class="fw-semibold"><?php echo htmlspecialchars($user['username']); ?></div>
                            <small class="text-white-50">Settings</small>
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
                            <a href="analytics.php">
                                <i class="fas fa-chart-line analytics-icon"></i>
                                <span>Analytics</span>
                            </a>
                        </li>
                        <li>
                            <a href="settings.php" class="active">
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
                        <h3 class="text-black"><i class="fas fa-cog me-2"></i>Settings</h3>
                        <p class="text-muted mb-0">Manage your account and application preferences</p>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-primary">Account ID: <?php echo $user['id']; ?></div>
                        <small class="text-muted">Member since <?php echo date('d M Y', strtotime($user['created_at'])); ?></small>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert-modern alert-<?php echo $message_type; ?>-modern fade-in mb-4">
                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close float-end" onclick="this.parentElement.remove()"></button>
                    </div>
                <?php endif; ?>

                <div class="settings-card">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    
                    <ul class="nav nav-tabs" id="settingsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button">
                                <i class="fas fa-user me-2"></i>Profile
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button">
                                <i class="fas fa-shield-alt me-2"></i>Security
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button">
                                <i class="fas fa-sliders-h me-2"></i>Preferences
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button">
                                <i class="fas fa-info-circle me-2"></i>System Info
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="settingsTabContent">
                        
                        <div class="tab-pane fade show active" id="profile">
                            <form method="POST" action="">
                                <input type="hidden" name="update_profile" value="1">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-modern">Username</label>
                                        <input type="text" name="username" class="form-control form-control-modern" 
                                               value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-modern">Email Address</label>
                                        <input type="email" name="email" class="form-control form-control-modern" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label-modern">Account Created</label>
                                    <input type="text" class="form-control form-control-modern" 
                                           value="<?php echo date('d M Y H:i', strtotime($user['created_at'])); ?>" readonly>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary-modern">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="tab-pane fade" id="security">
                            <form method="POST" action="">
                                <input type="hidden" name="change_password" value="1">
                                
                                <div class="mb-3">
                                    <label class="form-label-modern">Current Password</label>
                                    <input type="password" name="current_password" class="form-control form-control-modern" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label-modern">New Password</label>
                                    <input type="password" name="new_password" class="form-control form-control-modern" required>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label-modern">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control form-control-modern" required>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-warning text-white">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </button>
                                </div>
                            </form>
                            
                            <div class="danger-zone mt-4">
                                <h6 class="text-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                                </h6>
                                <p class="small">These actions are irreversible. Please proceed with caution.</p>
                                
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure? This will delete all your data.')">
                                        <i class="fas fa-trash me-2"></i>Delete Account
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="return confirm('Are you sure? This will log you out from all devices.')">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout All Devices
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="preferences">
                            <form method="POST" action="">
                                <input type="hidden" name="update_preferences" value="1">
                                
                                <div class="mb-3">
                                    <label class="form-label-modern">Theme</label>
                                    <select name="theme" class="form-control form-control-modern">
                                        <option value="light">Light</option>
                                        <option value="dark">Dark</option>
                                        <option value="auto">Auto (System)</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label-modern">Language</label>
                                    <select name="language" class="form-control form-control-modern">
                                        <option value="en">English</option>
                                        <option value="id">Bahasa Indonesia</option>
                                        <option value="es">Español</option>
                                        <option value="fr">Français</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="notifications" name="notifications" checked>
                                        <label class="form-check-label" for="notifications">
                                            Enable Notifications
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emailUpdates" name="email_updates" checked>
                                        <label class="form-check-label" for="emailUpdates">
                                            Email Updates
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success-modern">
                                        <i class="fas fa-save me-2"></i>Save Preferences
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="tab-pane fade" id="system">
                            <div class="system-info">
                                <div class="info-item">
                                    <span>PHP Version</span>
                                    <span class="fw-semibold"><?php echo phpversion(); ?></span>
                                </div>
                                <div class="info-item">
                                    <span>Server Software</span>
                                    <span class="fw-semibold"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                                </div>
                                <div class="info-item">
                                    <span>Database Driver</span>
                                    <span class="fw-semibold">MySQL PDO</span>
                                </div>
                                <div class="info-item">
                                    <span>Session Status</span>
                                    <span class="badge-modern badge-success">Active</span>
                                </div>
                                <div class="info-item">
                                    <span>Last Login</span>
                                    <span class="fw-semibold"><?php echo date('d M Y H:i'); ?></span>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>System Information</strong><br>
                                This information is for debugging purposes. Contact support if you encounter any issues.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
        const triggerTabList = document.querySelectorAll('#settingsTab button');
        triggerTabList.forEach(triggerEl => {
            const tabTrigger = new bootstrap.Tab(triggerEl);
            
            triggerEl.addEventListener('click', event => {
                event.preventDefault();
                tabTrigger.show();
            });
        });
        
        setTimeout(() => {
            document.querySelectorAll('.alert-modern').forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
        
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const passwordInputs = this.querySelectorAll('input[type="password"]');
                let isValid = true;
                
                passwordInputs.forEach(input => {
                    if (input.name === 'new_password' && input.value.length < 6) {
                        alert('Password must be at least 6 characters');
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>