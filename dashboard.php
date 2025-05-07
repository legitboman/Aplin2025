<?php 
    require_once 'db.php'; 
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
    $user = $_SESSION['user'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $deleteId = $_POST['delete_id'];
        $deleteStmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $deleteStmt->execute([$deleteId]);
    }

    $query = $pdo->prepare("SELECT * FROM posts WHERE user_id = ?");
    $query->execute([$user['id']]);
    $posts = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body style="margin:0; font-family: Arial, sans-serif; background-color: white;">
    <div style="background-color: #03AC0E; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
        <a href="dashboard.php" style="text-decoration: none;"><div style="color: white; font-size: 20px; font-weight: bold;">Tomboypedia</div></a>
        <div>
            <a href="dashboard.php" style="color: white; margin-right: 20px; text-decoration: none;">Products</a>
            <a href="add.php" style="color: white; margin-right: 20px; text-decoration: none;">Add Product</a>
            <a href="users.php" style="color: white; margin-right: 20px; text-decoration: none;">Users</a>
            <a href="logout.php" style="color: white; text-decoration: none;">Logout</a>
        </div>
    </div>
    <h1 style="color: black; font-size: 40px; text-align: center; margin-top: 50px;">All Product</h1>
    <?php if (count($posts) == 0): ?>
        <p style="text-align: center; font-size: 18px;">Kamu belum memiliki post apapun.</p>
    <?php else: ?>
        <table style="width: 90%; margin: 0 auto; border-collapse: collapse; margin-top: 30px;">
            <thead>
                <tr style="background-color: #03AC0E; color: white;">
                    <th style="padding: 12px; border: 1px solid #ccc;">No</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Image</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Title</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Caption</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Harga</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Stok</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Likes</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $index => $post): ?>
                    <tr style="text-align: center;">
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo $index + 1; ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;">
                            <img src="assets/<?php echo ($post['image']); ?>" style="width: 100px; height: 80px; object-fit: cover;">
                        </td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo htmlspecialchars($post['title']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo htmlspecialchars($post['caption']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;">Rp<?php echo number_format($post['harga'], 0, ',', '.'); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo $post['stok']; ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;">
                            <?php
                                $likeQuery = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
                                $likeQuery->execute([$post['id']]);
                                echo $likeQuery->fetchColumn();
                            ?>
                        </td>
                        <td style="padding: 12px; border: 1px solid #ccc;">
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" style="margin-right: 10px; background-color: #FFC107; color: black; padding: 5px 10px; text-decoration: none; border-radius: 5px;">Edit</a>
                            <form method="POST" onsubmit="return confirm('Yakin ingin menghapus post ini?');" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" style="background-color: #DC3545; color: white; padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
