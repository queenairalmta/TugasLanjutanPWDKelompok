<?php

require_once 'config.php';

class Product {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDB();
    }
    

    public function create($name, $price, $description, $user_id) {
        
        if (empty($name)) {
            return ['success' => false, 'message' => 'Nama produk wajib diisi'];
        }
        
        if ($price <= 0) {
            return ['success' => false, 'message' => 'Harga harus lebih dari 0'];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO products (name, price, description, user_id, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                htmlspecialchars($name),
                floatval($price),
                htmlspecialchars($description),
                intval($user_id)
            ]);
            
            if ($result) {
                return [
                    'success' => true, 
                    'message' => 'Produk berhasil ditambahkan',
                    'product_id' => $this->pdo->lastInsertId()
                ];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
        
        return ['success' => false, 'message' => 'Gagal menambahkan produk'];
    }
    

    public function getAll() {
        try {
            $stmt = $this->pdo->query("
                SELECT p.*, u.username as created_by 
                FROM products p 
                LEFT JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getAll: " . $e->getMessage());
            return [];
        }
    }
    
    
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, u.username as created_by 
                FROM products p 
                LEFT JOIN users u ON p.user_id = u.id 
                WHERE p.id = ?
            ");
            $stmt->execute([intval($id)]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getById: " . $e->getMessage());
            return null;
        }
    }
    
    
    public function update($id, $name, $price, $description) {
        if (empty($name) || $price <= 0) {
            return ['success' => false, 'message' => 'Nama dan harga harus diisi'];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                UPDATE products 
                SET name = ?, price = ?, description = ? 
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                htmlspecialchars($name),
                floatval($price),
                htmlspecialchars($description),
                intval($id)
            ]);
            
            return [
                'success' => $result,
                'message' => $result ? 'Produk berhasil diupdate' : 'Gagal mengupdate produk'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
            $result = $stmt->execute([intval($id)]);
            
            return [
                'success' => $result,
                'message' => $result ? 'Produk berhasil dihapus' : 'Gagal menghapus produk'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    
    public function count() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM products");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    
    public function countByUser($user_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM products WHERE user_id = ?");
            $stmt->execute([intval($user_id)]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error countByUser: " . $e->getMessage());
            return 0;
        }
    }
    
    
    public function getByUserId($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM products 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([intval($user_id)]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    
    public function getByCategory($category_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM products 
                WHERE category_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([intval($category_id)]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>