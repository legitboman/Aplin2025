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
        $deleteStmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $deleteStmt->execute([$deleteId]);
    }
    $query = $pdo->prepare("
        SELECT c.*, COUNT(p.id) AS jumlah_barang
        FROM categories c
        LEFT JOIN posts p ON c.id = p.category_id
        GROUP BY c.id
        ORDER BY c.id ASC
    ");
    $query->execute();
    $categories = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Categories</title>
</head>
<body style="margin:0; font-family: Arial, sans-serif; background-color: white;">
    <div style="background-color: #03AC0E; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
        <a href="dashboard.php" style="text-decoration: none;"><div style="color: white; font-size: 20px; font-weight: bold;">Tomboypedia</div></a>
        <div>
            <a href="dashboard.php" style="color: white; margin-right: 20px; text-decoration: none;">Products</a>
            <a href="users.php" style="color: white; margin-right: 20px; text-decoration: none;">Users</a>
            <a href="transaction.php" style="color: white; margin-right: 20px; text-decoration: none;">Transaction</a>
            <a href="category.php" style="color: white; margin-right: 20px; text-decoration: none;">Category</a>
            <a href="brands.php" style="color: white; margin-right: 20px; text-decoration: none;">Brands</a>
            <a href="logout.php" style="color: white; text-decoration: none;">Logout</a>
        </div>
    </div>
    <h1 style="color: black; font-size: 40px; text-align: center; margin-top: 50px;">All Categories</h1>
    <?php if (count($categories) == 0): ?>
        <p style="text-align: center; font-size: 18px;">Belum ada kategori.</p>
    <?php else: ?>
        <table style="width: 90%; margin: 0 auto; border-collapse: collapse; margin-top: 30px;">
            <thead>
                <tr style="background-color: #03AC0E; color: white;">
                    <th style="padding: 12px; border: 1px solid #ccc;">No</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">ID Kategori</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Nama Kategori</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Jumlah Barang</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $index => $category): ?>
                    <tr style="text-align: center;">
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo $index + 1; ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo $category['id']; ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo htmlspecialchars($category['name']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo $category['jumlah_barang']; ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;">
                            <a href="edit_category.php?id=<?php echo $category['id']; ?>" style="background-color: #FFC107; color: black; padding: 5px 10px; text-decoration: none; border-radius: 5px; margin-right: 5px;">Edit</a>
                            <form method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini?');" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $category['id']; ?>">
                                <button type="submit" style="background-color: #DC3545; color: white; padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <div style="width: 90%; margin: 30px auto 0;">
        <a href="add_category.php" style="background-color: #128FC8; color: white; text-decoration: none; display: inline-block; padding: 10px 15px; border-radius: 5px;">Add Category</a>
    </div>
</body>
</html>