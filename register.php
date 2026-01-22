<?php

require_once 'config.php';
redirectIfLoggedIn();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree_terms = isset($_POST['agree_terms']);
    
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username wajib diisi';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username minimal 3 karakter';
    }
    
    if (empty($email)) {
        $errors[] = 'Email wajib diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    }
    
    if (empty($password)) {
        $errors[] = 'Password wajib diisi';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Password tidak cocok';
    }
    
    if (!$agree_terms) {
        $errors[] = 'Anda harus menyetujui terms & conditions';
    }
    
    if (empty($errors)) {
        require_once 'User.php';
        
        try {
            $userModel = new User();
            $result = $userModel->register($username, $email, $password);
            
            if ($result['success']) {

                $loginResult = $userModel->login($email, $password);
                
                if ($loginResult['success']) {
                    $_SESSION['user_id'] = $loginResult['user']['id'];
                    $_SESSION['username'] = $loginResult['user']['username'];
                    $_SESSION['email'] = $loginResult['user']['email'];
                    $_SESSION['role'] = $loginResult['user']['role'] ?? 'user';
                    
                    setFlash('success', 'Registrasi berhasil! Selamat datang ' . $username);
                    redirect('dashboard.php');
                }
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bisnis project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3852c7;
            --success: #0014aefd;
            --gradient: linear-gradient(135deg, #ffffff 100%);
        }
        
        body {
            background: var(--gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .register-header {
            background: linear-gradient(135deg, var(--success) 0%, #170da74e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .register-header h2 {
            margin: 0;
            font-weight: 700;
        }
        
        .register-header p {
            opacity: 0.9;
            margin: 5px 0 0 0;
        }
        
        .register-body {
            padding: 30px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--success);
            box-shadow: 0 0 0 0.25rem rgba(24, 12, 193, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, var(--success) 0%, #170da74e 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(20, 44, 165, 0.2);
        }
        
        .terms-link {
            color: var(--success);
            text-decoration: none;
            cursor: pointer;
        }
        
        .terms-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h2><i class="fas fa-user-plus me-2"></i>BUAT AKUN BARU</h2>
                <p>Bergabung dengan project bisnis ini</p>
            </div>
            
            <div class="register-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="registerForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="username" class="form-control" 
                                       placeholder="Masukkan username" required
                                       value="<?php echo $_POST['username'] ?? ''; ?>">
                            </div>
                            <small class="text-muted">Minimal 3 karakter</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" 
                                       placeholder="Masukkan email" required
                                       value="<?php echo $_POST['email'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="password" 
                                       class="form-control" placeholder="Masukkan password" required>
                            </div>
                            <small class="text-muted">Minimal 6 karakter</small>
                            <div class="password-strength" id="passwordStrength"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konfirmasi Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="confirm_password" id="confirmPassword" 
                                       class="form-control" placeholder="Ulangi password" required>
                            </div>
                            <small class="text-muted" id="passwordMatch"></small>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="agree_terms" name="agree_terms">
                        <label class="form-check-label" for="agree_terms">
                            Saya menyetujui 
                            <a href="#" class="terms-link" data-bs-toggle="modal" data-bs-target="#termsModal">
                                sarat & ketentuan
                            </a>
                        </label>
                    </div>
                    
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-register">
                            <i class="fas fa-user-plus me-2"></i>BUAT AKUN
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <p class="mb-0">Sudah punya akun? 
                            <a href="login.php" class="text-decoration-none fw-bold">
                                <i class="fas fa-sign-in-alt me-1"></i>Login di sini
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
