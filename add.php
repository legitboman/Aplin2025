<?php
    require_once 'db.php';
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }
    $user = $_SESSION['user'];
    $categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();
    $brands = $pdo->query("SELECT id, name FROM brands")->fetchAll();
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $title = $_POST['title'];
        $caption = $_POST['caption'];
        $harga = $_POST['harga'];
        $stok = $_POST['stok'];
        $category_id = $_POST['category_id'];
        $brand_id = $_POST['brand_id'];
        $image = null;
        if (empty($title) || empty($caption) || empty($_FILES['image']['name']) || $harga === '' || $stok === '') {
            echo "<script>alert('Semua field harus diisi termasuk gambar, harga, dan stok!'); window.location='add.php';</script>";
            exit;
        } else {
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $file = $_FILES['image'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 2 * 1024 * 1024;
                if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $slugTitle = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
                    $newFilename = "post_{$user['id']}_{$slugTitle}." . $ext;
                    $uploadPath = "assets/" . $newFilename;
                    move_uploaded_file($file['tmp_name'], $uploadPath);
                    $image = $newFilename;
                } else {
                    echo "<script>alert('File harus JPG, JPEG, PNG, GIF dan maksimal 2MB');</script>";
                }
            }
            if ($image) {
                $query = $pdo->prepare("INSERT INTO posts (user_id, title, caption, image, harga, stok, category_id, brand_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"); // Tambahan kolom brand_id
                $query->execute([$user['id'], $title, $caption, $image, $harga, $stok, $category_id, $brand_id]); // Tambahan nilai brand_id
                echo "<script>alert('Post berhasil ditambahkan!'); window.location='dashboard.php';</script>";
            }
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Post</title>
</head>
<body style="margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #f2f2f2;">
    <div style="padding: 30px; border: 1px solid #ccc; border-radius: 10px; background-color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; width: 400px;">
        <h2 style="margin-bottom: 20px;">Add Post</h2>
        <form method="POST" enctype="multipart/form-data" style="text-align: left;">
            <label>Image:</label><br>
            <input type="file" name="image" style="margin-bottom: 15px; width: 100%; padding: 8px;"><br>
            <label>Title:</label><br>
            <input type="text" name="title" style="margin-bottom: 15px; width: 100%; padding: 8px;"><br>
            <label>Price:</label><br>
            <input type="number" name="harga" min="0" style="margin-bottom: 15px; width: 100%; padding: 8px;"><br>
            <label>Stock:</label><br>
            <input type="number" name="stok" min="0" style="margin-bottom: 15px; width: 100%; padding: 8px;"><br>
            <label>Caption:</label><br>
            <textarea name="caption" style="margin-bottom: 15px; width: 100%; padding: 8px;"></textarea><br>
            <label>Category:</label><br>
            <select name="category_id" required style="margin-bottom: 15px; width: 100%; padding: 8px;">
                <option value="" disabled selected>-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select><br>
            <label>Brand:</label><br>
            <select name="brand_id" required style="margin-bottom: 15px; width: 100%; padding: 8px;">
                <option value="" disabled selected>-- Pilih Brand --</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?= $brand['id'] ?>"><?= htmlspecialchars($brand['name']) ?></option>
                <?php endforeach; ?>
            </select><br>
            <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
                <button type="submit" style="height: 50px; width: 100px; font-size: 16px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Add</button>
                <a href="dashboard.php">
                    <button type="button" style="height: 50px; width: 100px; font-size: 16px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Back</button>
                </a>
            </div>
        </form>
    </div>
</body>
</html>
