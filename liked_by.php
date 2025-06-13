<?php
require_once 'db.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_GET['post_id'])) {
    echo "<script>alert('Post tidak ditemukan'); window.location='dashboard.php';</script>";
    exit();
}
$post_id = $_GET['post_id'];
$query = $pdo->prepare("
        SELECT u.username, u.profile_picture 
        FROM saved_posts s
        JOIN users u ON s.user_id = u.id
        WHERE s.post_id = ?
    ");
$query->execute([$post_id]);
$savers = $query->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Saved By</title>
</head>

<body style="margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #f3f4f6; font-family: Arial, sans-serif;">
    <div style="padding: 30px; border: 1px solid #ccc; border-radius: 10px; background-color: white; box-shadow: 0 0 1px rgba(0, 0, 0, 0.6), 5px 5px 5px rgba(0, 0, 0, 0.1); text-align: center; width: 600px;">
        <h2>Saved By</h2>
        <?php if (count($savers) === 0): ?>
            <p>Tidak ada yang menyimpan post ini.</p>
        <?php else: ?>
            <?php foreach ($savers as $saver): ?>
                <div style="width: 500px; background-color: white; border-radius: 10px; display: flex; align-items: center; padding: 10px; margin: 0 auto 15px auto; box-shadow: 0 0 1px rgba(0, 0, 0, 0.6), 5px 5px 5px rgba(0, 0, 0, 0.1); padding-right: 25px;">
                    <img src="assets/<?php echo ($saver['profile_picture'] ?? 'default_img.png'); ?>" alt="Profile Picture" style="width: 50px; height: 50px; border-radius: 999px; margin-right: 20px; object-fit: cover; border: 1px solid #ccc;">
                    <span style="font-weight: bold; font-size: 16px; margin-left: auto;"><?php echo ($saver['username'] ?? 'Unknown'); ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <a href="detail.php?id=<?php echo ($post_id); ?>">
            <button type="button" style="margin-top: 20px; height: 50px; width: 100px; font-size: 18px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Back</button>
        </a>
    </div>
</body>

</html>