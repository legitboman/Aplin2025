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
    $user = $_SESSION['user'];
    $post_id = $_GET['id'];
    $query = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $query->execute([$post_id, $user['id']]);
    $post = $query->fetch();
    if (!$post) {
        echo "<script>alert('Post tidak ditemukan atau bukan milik Anda'); window.location='dashboard.php';</script>";
        exit();
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $title = $_POST['title'];
        $caption = $_POST['caption'];
        $harga = $_POST['harga'];
        $stok = $_POST['stok'];
        $image = $post['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $file = $_FILES['image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024;
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFilename = "post_{$user['id']}_{$post_id}." . $ext;
                $uploadPath = "assets/" . $newFilename;
                $sameName = ($newFilename === $image);
                if (!$sameName && $image && file_exists("assets/" . $image)) {
                    unlink("assets/" . $image);
                }
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $image = $newFilename;
                } else {
                    echo "<script>alert('Gagal upload gambar');</script>";
                }
            } else {
                echo "<script>alert('File harus JPG, JPEG, PNG, GIF dan maksimal 2MB');</script>";
            }
        }
        $update = $pdo->prepare("UPDATE posts SET title = ?, caption = ?, image = ?, harga = ?, stok = ? WHERE id = ? AND user_id = ?");
        $update->execute([$title, $caption, $image, $harga, $stok, $post_id, $user['id']]);
        echo "<script>alert('Post berhasil diupdate'); window.location='detail.php?id=$post_id';</script>";
        exit();
    }
?>
<!DOCTYPE html>
<html>
<head><title>Edit Post</title></head>
<body style="margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #f2f2f2;">
    <div style="padding: 30px; border: 1px solid #ccc; border-radius: 10px; background-color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center;">
        <h2 style="margin-bottom: 20px;">Edit Post</h2>
        <form method="POST" enctype="multipart/form-data" style="text-align: left;">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="assets/<?php echo ($post['image']); ?>" alt="Post Image" width="150" height="150" style="border: 1px solid black;">
            </div>
            <label>Gambar:</label><br>
            <input type="file" name="image" style="margin-bottom: 15px; width: 90%; padding: 8px;"><br>
            <label>Judul:</label><br>
            <input type="text" name="title" value="<?php echo ($post['title']); ?>" style="margin-bottom: 15px; width: 90%; padding: 8px;"><br>
            <label>Harga:</label><br>
            <input type="number" name="harga" value="<?php echo ($post['harga']); ?>" style="margin-bottom: 15px; width: 90%; padding: 8px;"><br>
            <label>Stock:</label><br>
            <input type="number" name="stok" value="<?php echo ($post['stok']); ?>" min="0" style="margin-bottom: 15px; width: 90%; padding: 8px;"><br>
            <label>Caption:</label><br>
            <textarea name="caption" style="margin-bottom: 15px; width: 90%; padding: 8px;"><?php echo ($post['caption']); ?></textarea><br>
            <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
                <button type="submit" style="height: 50px; width: 100px; font-size: 18px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Update</button>
                <a href="detail.php?id=<?php echo $post['id']; ?>">
                    <button type="button" style="height: 50px; width: 100px; font-size: 18px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Back</button>
                </a>
            </div>
        </form>
    </div>
</body>
</html>