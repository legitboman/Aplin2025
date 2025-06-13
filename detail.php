<?php
    require_once 'db.php';
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }
    if (!isset($_GET['id'])) {
        echo "<script>alert('Post tidak ditemukan'); window.location='dashboard.php';</script>";
        exit();
    }
    $post_id = $_GET['id'];
    $query = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $query->execute([$post_id, $_SESSION['user']['id']]);
    $post = $query->fetch();
    if (!$post) {
        echo "<script>alert('Post tidak ditemukan atau bukan milik Anda'); window.location='dashboard.php';</script>";
        exit();
    }
    $likeQuery = $pdo->prepare("SELECT COUNT(*) AS total_likes FROM likes WHERE post_id = ?");
    $likeQuery->execute([$post_id]);
    $likes = $likeQuery->fetch()['total_likes'];
    if (isset($_POST['delete'])) {
        if ($post['image'] && file_exists("assets/" . $post['image'])) {
            unlink("assets/" . $post['image']);
        }
        $delete = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $delete->execute([$post_id]);
        echo "<script>alert('Post berhasil dihapus'); window.location='dashboard.php';</script>";
        exit();
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detail Post</title>
</head>
<body style="margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #f2f2f2;">
    <div style="padding: 30px; border: 1px solid #ccc; border-radius: 10px; background-color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; width: 400px;">
        <div style="text-align: left; margin-bottom: 20px;">
            <a href="dashboard.php">
                <button type="button" style="height: 40px; width: 40px; border-radius: 50%; font-size: 18px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;"><<</button>
            </a>
        </div>
        <img src="assets/<?php echo ($post['image']); ?>" width="150" height="150" style="border: 1px solid black; margin-bottom: 20px;"><br>
        <h2 style="margin: 10px 0;"><?php echo ($post['title']); ?></h2>
        <p style="margin: 10px 0;"><?php echo ($post['caption']); ?></p>
        <span style="color: grey;">Likes : <?php echo $likes; ?></span><br><br>
        <a href="liked_by.php?post_id=<?php echo $post['id']; ?>">
            <button type="button" style="width: 55%; height: 40px; font-size: 14px; background-color: #03AC0E; color: white; font-weight: bold; border: none; margin-bottom: 20px; cursor: pointer;">Saved By</button>
        </a>
        <div style="display: flex; justify-content: center; gap: 20px;">
            <a href="edit_post.php?id=<?php echo $post['id']; ?>">
                <button type="button" style="height: 40px; width: 100px; font-size: 14px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Edit</button>
            </a>
            <form method="POST" style="display: inline;">
                <button type="submit" name="delete" onclick="return confirm('Yakin ingin menghapus post ini?')" style="height: 40px; width: 100px; font-size: 14px; background-color: #d9534f; color: white; font-weight: bold; border: none; cursor: pointer;">Delete</button>
            </form>
        </div>
    </div>
</body>
</html>