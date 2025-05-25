<?php
    namespace Models;
    require_once 'db.php';
    class Buy {
        private $pdo;
        public function __construct($pdo) {
            $this->pdo = $pdo;
        }
        public function processBuy($userId, $postId) {
            $this->pdo->beginTransaction();
            try {
                $postQuery = $this->pdo->prepare("SELECT posts.*, categories.id as category_id, categories.name as category_name FROM posts LEFT JOIN categories ON posts.category_id = categories.id WHERE posts.id = ?");
                $postQuery->execute([$postId]);
                $post = $postQuery->fetch();
                $userQuery = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
                $userQuery->execute([$userId]);
                $user = $userQuery->fetch();
                if ($post && $post['stok'] > 0) {
                    if ($user['saldo'] >= $post['harga']) {
                        $updatePostQuery = $this->pdo->prepare("UPDATE posts SET stok = stok - 1 WHERE id = ?");
                        $updatePostQuery->execute([$postId]);
                        $updateUserQuery = $this->pdo->prepare("UPDATE users SET saldo = saldo - ? WHERE id = ?");
                        $updateUserQuery->execute([$post['harga'], $userId]);
                        $insertTransaction = $this->pdo->prepare("
                            INSERT INTO transaction 
                            (post_id, post_title, category_id, category_name, buyer_id, seller_id, price) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $insertTransaction->execute([
                            $post['id'],
                            $post['title'],
                            $post['category_id'],
                            $post['category_name'],
                            $userId,
                            $post['user_id'],
                            $post['harga']
                        ]);
                        
                        $this->pdo->commit();
                        return [
                            'status' => 'success',
                            'message' => "Pembelian berhasil! Saldo kamu: " . ($user['saldo'] - $post['harga'])
                        ];
                    } else {
                        return [
                            'status' => 'error',
                            'message' => "Saldo mu tidak cukup! Saldo kamu: " . $user['saldo']
                        ];
                    }
                } else {
                    return [
                        'status' => 'error',
                        'message' => "Stok Habis!"
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
    }
?>