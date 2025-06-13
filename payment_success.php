<?php
require_once 'db.php';
require_once 'models/buy.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$postId = $_GET['post_id'] ?? null;
$orderId = $_GET['order_id'] ?? null;
$method = $_GET['method'] ?? 'emoney';

if (!$postId || !$orderId) {
    echo "<script>alert('Data transaksi tidak lengkap.'); window.location.href='discover.php';</script>";
    exit;
}

// Verify the post exists
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    echo "<script>alert('Barang tidak ditemukan.'); window.location.href='discover.php';</script>";
    exit;
}

// Process the purchase through models/buy.php
$buy = new Models\Buy($pdo);
$result = $buy->processBuy($user['id'], $postId, $method);

if ($result['status'] === 'success') {
    // Log the successful e-money transaction
    try {
        // Update transaction record with order_id if needed
        $updateStmt = $pdo->prepare("UPDATE TRANSACTION SET metode = ?, STATUS = 'berhasil' WHERE buyer_id = ? AND post_id = ? ORDER BY transaction_date DESC LIMIT 1");
        $updateStmt->execute(['emoney', $user['id'], $postId]);
    } catch (Exception $e) {
        // Log error but don't fail the transaction
        error_log("Failed to update transaction record: " . $e->getMessage());
    }
    
    $successMessage = "Pembayaran e-money berhasil! Terima kasih atas pembelian Anda.";
} else {
    $successMessage = "Terjadi kesalahan: " . $result['message'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Berhasil</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f4f4f4; 
            padding: 30px; 
            text-align: center;
        }
        .container {
            max-width: 600px; 
            margin: auto; 
            background: #fff;
            padding: 40px; 
            border-radius: 10px; 
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .success-icon {
            font-size: 60px;
            color: #03AC0E;
            margin-bottom: 20px;
        }
        h2 { 
            color: #03AC0E; 
            margin-bottom: 20px;
        }
        .transaction-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        .transaction-details h3 {
            color: #333;
            margin-top: 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
            font-weight: bold;
            color: #03AC0E;
        }
        button {
            background-color: #03AC0E; 
            color: white; 
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px;
        }
        button:hover {
            background-color: #028a0c;
        }
        .secondary-button {
            background-color: #6c757d;
        }
        .secondary-button:hover {
            background-color: #545b62;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($result['status'] === 'success'): ?>
            <div class="success-icon">✅</div>
            <h2>Pembayaran Berhasil!</h2>
            <p><?= htmlspecialchars($successMessage) ?></p>
            
            <div class="transaction-details">
                <h3>Detail Transaksi</h3>
                <div class="detail-row">
                    <span>Order ID:</span>
                    <span><?= htmlspecialchars($orderId) ?></span>
                </div>
                <div class="detail-row">
                    <span>Nama Barang:</span>
                    <span><?= htmlspecialchars($post['title']) ?></span>
                </div>
                <div class="detail-row">
                    <span>Metode Pembayaran:</span>
                    <span>E-Money</span>
                </div>
                <div class="detail-row">
                    <span>Total Pembayaran:</span>
                    <span><?= number_format($post['harga'], 0, ',', '.') ?> IDR</span>
                </div>
            </div>
            
            <button onclick="window.location.href='discover.php'">Kembali ke Discover</button>
            <button onclick="window.location.href='profile.php'" class="secondary-button">Lihat Profil</button>
            
        <?php else: ?>
            <div class="success-icon" style="color: #dc3545;">❌</div>
            <h2 style="color: #dc3545;">Pembayaran Gagal</h2>
            <p><?= htmlspecialchars($successMessage) ?></p>
            
            <button onclick="window.location.href='confirm_buy.php?post_id=<?= $postId ?>'" style="background-color: #dc3545;">Coba Lagi</button>
            <button onclick="window.location.href='discover.php'" class="secondary-button">Kembali ke Discover</button>
        <?php endif; ?>
    </div>
</body>
</html>