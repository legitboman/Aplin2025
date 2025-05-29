<?php 
    require_once 'db.php'; 
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
    $user = $_SESSION['user'];
    if ($user['role'] !== 'admin') {
        header('Location: dashboard.php');
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $deleteId = $_POST['delete_id'];
        $deleteStmt = $pdo->prepare("DELETE FROM transaction WHERE id = ?");
        $deleteStmt->execute([$deleteId]);
    }
    $query = $pdo->prepare("
        SELECT t.*, 
               buyer.username AS buyer_username
        FROM transaction t
        JOIN users buyer ON t.buyer_id = buyer.id
        ORDER BY t.transaction_date ASC
    ");
    $query->execute();
    $transactions = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction History</title>
</head>
<body style="margin:0; font-family: Arial, sans-serif; background-color: white;">
    <div style="background-color: #03AC0E; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
        <a href="dashboard.php" style="text-decoration: none;"><div style="color: white; font-size: 20px; font-weight: bold;">Tomboypedia</div></a>
        <div>
            <a href="dashboard.php" style="color: white; margin-right: 20px; text-decoration: none;">Products</a>
            <a href="add.php" style="color: white; margin-right: 20px; text-decoration: none;">Add Product</a>
            <a href="users.php" style="color: white; margin-right: 20px; text-decoration: none;">Users</a>
            <a href="transaction.php" style="color: white; margin-right: 20px; text-decoration: none;">Transaction</a>
            <a href="category.php" style="color: white; margin-right: 20px; text-decoration: none;">Category</a>
            <a href="logout.php" style="color: white; text-decoration: none;">Logout</a>
        </div>
    </div>
    <h1 style="color: black; font-size: 40px; text-align: center; margin-top: 50px;">Transaction History</h1>
    <?php if (count($transactions) == 0): ?>
        <p class="no-data" style="text-align: center; font-size: 18px;">Belum ada transaksi.</p>
    <?php else: ?>
        <table style="width: 90%; margin: 0 auto; border-collapse: collapse; margin-top: 30px;">
            <thead>
                <tr>
                    <th style="background-color: #03AC0E; color: white; padding: 12px; border: 1px solid #ccc;">No</th>
                    <th style="background-color: #03AC0E; color: white; padding: 12px; border: 1px solid #ccc;">Transaction ID</th>
                    <th style="background-color: #03AC0E; color: white; padding: 12px; border: 1px solid #ccc;">Product</th>
                    <th style="background-color: #03AC0E; color: white; padding: 12px; border: 1px solid #ccc;">ID Barang</th>
                    <th style="background-color: #03AC0E; color: white; padding: 12px; border: 1px solid #ccc;">Category</th>
                    <th style="background-color: #03AC0E; color: white; padding: 12px; border: 1px solid #ccc;">Buyer</th>
                    <th style="background-color: #03AC0E; color: white; padding: 12px; border: 1px solid #ccc;">Price</th>
                    <th style="background-color: #03AC0E; color: white; padding: 12px; border: 1px solid #ccc;">Date</th>
                    <th style="background-color: #03AC0E; color: white; padding: 12px; border: 1px solid #ccc;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $index => $transaction): ?>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ccc; text-align: center;"><?php echo $index + 1; ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc; text-align: center;"><?php echo $transaction['id']; ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc; text-align: center;"><?php echo htmlspecialchars($transaction['post_title']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc; text-align: center;"><?php echo $transaction['post_id']; ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc; text-align: center;"><?php echo htmlspecialchars($transaction['category_name']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc; text-align: center;"><?php echo htmlspecialchars($transaction['buyer_username']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc; text-align: center;">Rp<?php echo number_format($transaction['price'], 0, ',', '.'); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc; text-align: center;"><?php echo date('d M Y H:i', strtotime($transaction['transaction_date'])); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc; text-align: center;">
                            <form method="POST" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?');" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $transaction['id']; ?>">
                                <button type="submit" style="background-color: #DC3545; color: white; padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <script>
        setTimeout(() => {
            location.reload();
        }, 10000);
    </script>
</body>
</body>
</html>
