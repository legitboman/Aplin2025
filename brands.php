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
        $deleteId = intval($_POST['delete_id']);
        $check = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE brand_id = ?");
        $check->execute([$deleteId]);
        $count = $check->fetchColumn();

        if ($count == 0) {
            $delete = $pdo->prepare("DELETE FROM brands WHERE id = ?");
            $delete->execute([$deleteId]);
        }
    }
    $query = $pdo->prepare("
        SELECT b.*, COUNT(p.id) AS jumlah_barang
        FROM brands b
        LEFT JOIN posts p ON b.id = p.brand_id
        GROUP BY b.id
        ORDER BY b.id ASC
    ");
    $query->execute();
    $brands = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Brands</title>
</head>
<body style="margin:0; font-family: Arial, sans-serif; background-color: white;">
    <div style="background-color: #03AC0E; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
        <a href="dashboard.php" style="text-decoration: none;"><div style="color: white; font-size: 20px; font-weight: bold;">Tomboypedia</div></a>
        <div>
            <a href="dashboard.php" style="color: white; margin-right: 20px; text-decoration: none;">Products</a>
            <a href="users.php" style="color: white; margin-right: 20px; text-decoration: none;">Users</a>
            <a href="transaction.php" style="color: white; margin-right: 20px; text-decoration: none;">Transaction</a>
            <a href="category.php" style="color: white; margin-right: 20px; text-decoration: none;">Category</a>
            <a href="brand.php" style="color: white; margin-right: 20px; text-decoration: none;">Brands</a>
            <a href="logout.php" style="color: white; text-decoration: none;">Logout</a>
        </div>
    </div>
    <h1 style="color: black; font-size: 40px; text-align: center; margin-top: 50px;">All Brands</h1>
    <?php if (count($brands) == 0): ?>
        <p style="text-align: center; font-size: 18px;">Belum ada brand.</p>
    <?php else: ?>
        <table style="width: 90%; margin: 0 auto; border-collapse: collapse; margin-top: 30px;">
            <thead>
                <tr style="background-color: #03AC0E; color: white;">
                    <th style="padding: 12px; border: 1px solid #ccc;">No</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">ID Brand</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Nama Brand</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Jumlah Barang</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($brands as $index => $brand): ?>
                    <tr style="text-align: center;">
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo $index + 1; ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo $brand['id']; ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo htmlspecialchars($brand['name']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo $brand['jumlah_barang']; ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;">
                            <a href="edit_brands.php?id=<?php echo $brand['id']; ?>" style="background-color: #FFC107; color: black; padding: 5px 10px; text-decoration: none; border-radius: 5px; margin-right: 5px;">Edit</a>
                            <?php if ($brand['jumlah_barang'] == 0): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo $brand['id']; ?>">
                                    <button type="submit" onclick="return confirm('Yakin ingin menghapus brand ini?')" style="background-color: #DC3545; color: white; padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer;">Delete</button>
                                </form>
                            <?php else: ?>
                                <button style="background-color: #DC3545; color: white; padding: 5px 10px; border: none; border-radius: 5px; cursor: not-allowed;" disabled>Delete</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <div style="width: 90%; margin: 30px auto 0;">
        <a href="add_brands.php" style="background-color: #128FC8; color: white; text-decoration: none; display: inline-block; padding: 10px 15px; border-radius: 5px;">Add Brand</a>
    </div>
</body>
</html>