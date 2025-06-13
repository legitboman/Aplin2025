<?php
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();
$brands = $pdo->query("SELECT id, name FROM brands")->fetchAll();
$suppliers = $pdo->query("SELECT id, supplier_name FROM supplier")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $caption = $_POST['caption'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $category_id = $_POST['category_id'];
    $brand_id = $_POST['brand_id'];
    $supplier_id = $_POST['supplier_id'];
    $image = null;

    if (empty($title) || empty($caption) || empty($_FILES['image']['name']) || $harga === '' || $stok === '') {
        echo "<script>alert('Semua field harus diisi termasuk gambar, harga, dan stok!'); window.location='add.php';</script>";
        exit;
    } else {
        if ($_FILES['image']['error'] === 0) {
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
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, caption, image, harga, stok, category_id, brand_id, supplier_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user['id'], $title, $caption, $image, $harga, $stok, $category_id, $brand_id, $supplier_id]);
            echo "<script>alert('Produk berhasil ditambahkan!'); window.location='dashboard.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Product - Tomboypedia</title>
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

        label {
            font-weight: 500;
            color: #333;
        }

        .btn-add,
        .btn-back {
            width: 100%;
            padding: 12px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            font-size: 16px;
        }

        .btn-add {
            background-color: #03AC0E;
            color: white;
        }

        .btn-add:hover {
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
        <h2>Tambah Produk</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="image" class="form-label">Gambar</label>
                <input class="form-control" type="file" name="image" id="image" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Judul Produk</label>
                <input type="text" class="form-control" name="title" placeholder="Masukkan judul produk" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Harga</label>
                <input type="number" class="form-control" name="harga" min="0" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Stok</label>
                <input type="number" class="form-control" name="stok" min="0" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi / Caption</label>
                <textarea class="form-control" name="caption" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <select class="form-select" name="category_id" required>
                    <option disabled selected value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Brand</label>
                <select class="form-select" name="brand_id" required>
                    <option disabled selected value="">-- Pilih Brand --</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?= $brand['id'] ?>"><?= $brand['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Supplier</label>
                <select class="form-select" name="supplier_id" required>
                    <option disabled selected value="">-- Pilih Supplier --</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?= $supplier['id'] ?>"><?= $supplier['supplier_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-add">Tambah</button>
                <a href="dashboard.php" class="btn btn-back">Kembali</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
