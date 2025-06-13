<?php
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

if (!isset($_GET['id'])) {
    echo "<script>alert('Produk tidak ditemukan'); window.location='dashboard.php';</script>";
    exit();
}

$post_id = $_GET['id'];
$query = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$query->execute([$post_id, $user['id']]);
$post = $query->fetch();

if (!$post) {
    echo "<script>alert('Produk tidak ditemukan atau bukan milik Anda'); window.location='dashboard.php';</script>";
    exit();
}

$categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();
$brands = $pdo->query("SELECT id, name FROM brands")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $caption = $_POST['caption'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $category_id = $_POST['category_id'];
    $brand_id = $_POST['brand_id'];
    $image = $post['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024;

        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFilename = "post_{$user['id']}_{$post_id}." . $ext;
            $uploadPath = "assets/" . $newFilename;

            if ($image && file_exists("assets/" . $image)) {
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

    $update = $pdo->prepare("UPDATE posts SET title = ?, caption = ?, image = ?, harga = ?, stok = ?, category_id = ?, brand_id = ? WHERE id = ? AND user_id = ?");
    $update->execute([$title, $caption, $image, $harga, $stok, $category_id, $brand_id, $post_id, $user['id']]);

    echo "<script>alert('Produk berhasil diperbarui!'); window.location='dashboard.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e9f7f3;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #05445E;
            text-align: center;
            margin-bottom: 25px;
        }
        .img-preview {
            text-align: center;
            margin-bottom: 20px;
        }
        .img-preview img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 1px solid black;
            border-radius: 5px;
        }
        .btn-update,
        .btn-back {
            width: 100%;
            padding: 12px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            font-size: 16px;
        }
        .btn-update {
            background-color: #03AC0E;
            color: white;
        }
        .btn-update:hover {
            background-color: #02940c;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Produk</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="img-preview">
            <img src="assets/<?= $post['image'] ?>" alt="Gambar Produk">
        </div>
        <div class="mb-3">
            <label class="form-label">Gambar Baru (Opsional)</label>
            <input type="file" name="image" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Judul Produk</label>
            <input type="text" name="title" class="form-control" value="<?= $post['title'] ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Harga</label>
            <input type="number" name="harga" class="form-control" value="<?= $post['harga'] ?>" min="0" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Stok</label>
            <input type="number" name="stok" class="form-control" value="<?= $post['stok'] ?>" min="0" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Deskripsi / Caption</label>
            <textarea name="caption" class="form-control" rows="3" required><?= $post['caption'] ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select class="form-select" name="category_id" required>
                <option disabled value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $post['category_id'] ? 'selected' : '' ?>>
                        <?= $cat['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Brand</label>
            <select class="form-select" name="brand_id" required>
                <option disabled value="">-- Pilih Brand --</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?= $brand['id'] ?>" <?= $brand['id'] == $post['brand_id'] ? 'selected' : '' ?>>
                        <?= $brand['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-update">Update</button>
            <a href="dashboard.php" class="btn btn-back">Kembali</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
