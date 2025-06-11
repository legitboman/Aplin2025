<?php
namespace Models;
require_once 'db.php';

class Buy {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function processBuy($userId, $postId, $method = 'saldo') {
        $this->pdo->beginTransaction();
        try {
            // Get post with category info
            $postQuery = $this->pdo->prepare("
                SELECT posts.*, categories.id as category_id, categories.name as category_name 
                FROM posts 
                LEFT JOIN categories ON posts.category_id = categories.id 
                WHERE posts.id = ?
            ");
            $postQuery->execute([$postId]);
            $post = $postQuery->fetch();
            
            // Get user info
            $userQuery = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $userQuery->execute([$userId]);
            $user = $userQuery->fetch();
            
            // Check if post exists and has stock
            if (!$post || $post['stok'] <= 0) {
                $this->pdo->rollBack();
                return [
                    'status' => 'error',
                    'message' => "Stok Habis!"
                ];
            }
            
            // Check if buyer is not the seller
            if ($post['user_id'] == $userId) {
                $this->pdo->rollBack();
                return [
                    'status' => 'error',
                    'message' => "Anda tidak dapat membeli barang sendiri!"
                ];
            }
            
            // Process payment based on method
            if ($method === 'saldo') {
                // Check if user has enough balance
                if ($user['saldo'] < $post['harga']) {
                    $this->pdo->rollBack();
                    return [
                        'status' => 'error',
                        'message' => "Saldo mu tidak cukup! Saldo kamu: " . number_format($user['saldo'], 0, ',', '.')
                    ];
                }
                
                // Deduct buyer's balance
                $updateUserQuery = $this->pdo->prepare("UPDATE users SET saldo = saldo - ? WHERE id = ?");
                $updateUserQuery->execute([$post['harga'], $userId]);
                
            } elseif ($method === 'emoney') {
                // For e-money, payment is already processed by Midtrans
                // No need to deduct balance from buyer
            } else {
                $this->pdo->rollBack();
                return [
                    'status' => 'error',
                    'message' => "Metode pembayaran tidak valid!"
                ];
            }
            
            // Add money to seller's balance (for both payment methods)
            $updateSellerQuery = $this->pdo->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?");
            $updateSellerQuery->execute([$post['harga'], $post['user_id']]);
            
            // Update stock
            $updatePostQuery = $this->pdo->prepare("UPDATE posts SET stok = stok - 1 WHERE id = ?");
            $updatePostQuery->execute([$postId]);
            
            // Insert transaction record with payment method
            $insertTransaction = $this->pdo->prepare("
                INSERT INTO TRANSACTION 
                (post_id, post_title, category_id, category_name, buyer_id, seller_id, price, metode, STATUS) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'berhasil')
            ");
            $insertTransaction->execute([
                $post['id'],
                $post['title'],
                $post['category_id'],
                $post['category_name'],
                $userId,
                $post['user_id'],
                $post['harga'],
                $method
            ]);
            
            $this->pdo->commit();

            header("Location: ../midtrans/examples/snap/checkout-process-simple-version.php?order_id=$order_id");
            exit;
            
            // Return success message based on payment method
            if ($method === 'saldo') {
                $newBalance = $user['saldo'] - $post['harga'];
                return [
                    'status' => 'success',
                    'message' => "Pembelian berhasil! Saldo kamu: " . number_format($newBalance, 0, ',', '.')
                ];
            } else {
                return [
                    'status' => 'success',
                    'message' => "Pembelian berhasil dengan e-money!"
                ];
            }
            
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return [
                'status' => 'error',
                'message' => "Terjadi kesalahan: " . $e->getMessage()
            ];
        }
    }
    
    // Method untuk mendapatkan history pembelian
    public function getBuyHistory($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, p.image, u.display_name as seller_name 
                FROM TRANSACTION t 
                LEFT JOIN posts p ON t.post_id = p.id 
                LEFT JOIN users u ON t.seller_id = u.id 
                WHERE t.buyer_id = ? 
                ORDER BY t.transaction_date DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }
    
    // Method untuk mendapatkan history penjualan
    public function getSellHistory($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, p.image, u.display_name as buyer_name 
                FROM TRANSACTION t 
                LEFT JOIN posts p ON t.post_id = p.id 
                LEFT JOIN users u ON t.buyer_id = u.id 
                WHERE t.seller_id = ? 
                ORDER BY t.transaction_date DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }
}
?>