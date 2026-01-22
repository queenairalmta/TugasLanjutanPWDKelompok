<?php
require_once 'config.php';

class User {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDB();
    }
    
    public function register($username, $email, $password) {
        if (strlen($username) < 3) {
            return ['success' => false, 'message' => 'Username minimal 3 karakter'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Format email tidak valid'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password minimal 6 karakter'];
        }
        
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email sudah terdaftar'];
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([$username, $email, $hashedPassword]);
        
        if ($result) {
            $userId = $this->pdo->lastInsertId();
            return [
                'success' => true, 
                'user_id' => $userId,
                'message' => 'Registrasi berhasil!'
            ];
        }
        
        return ['success' => false, 'message' => 'Gagal registrasi'];
    }
    
    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email tidak ditemukan'];
        }
        
        if (password_verify($password, $user['password'])) {
            unset($user['password']);
            
            return [
                'success' => true, 
                'user' => $user,
                'message' => 'Login berhasil!'
            ];
        }
        
        return ['success' => false, 'message' => 'Password salah'];
    }
    
    public function getUserById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, email, created_at 
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getUserById: " . $e->getMessage());
            return null;
        }
    }
    
    public function getAllUsers() {
        try {
            $stmt = $this->pdo->query("
                SELECT id, username, email, created_at 
                FROM users 
                ORDER BY created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getAllUsers: " . $e->getMessage());
            return [];
        }
    }
    
    public function count() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM users");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    public function updateProfile($id, $username, $email) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET username = ?, email = ? 
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                htmlspecialchars($username),
                htmlspecialchars($email),
                intval($id)
            ]);
            
            return [
                'success' => $result,
                'message' => $result ? 'Profile berhasil diupdate' : 'Gagal mengupdate profile'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function changePassword($id, $current_password, $new_password) {
        $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($current_password, $user['password'])) {
            return ['success' => false, 'message' => 'Password saat ini salah'];
        }
        
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $result = $stmt->execute([$hashedPassword, $id]);
        
        return [
            'success' => $result,
            'message' => $result ? 'Password berhasil diubah' : 'Gagal mengubah password'
        ];
    }
}
?>