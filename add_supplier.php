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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['supplier_name']);
    $price = trim($_POST['supplier_price']);

    if (empty($name) || empty($price)) {
        $error = 'Nama dan harga supplier tidak boleh kosong.';
    } elseif (!is_numeric($price) || $price < 0) {
        $error = 'Harga supplier harus berupa angka positif.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO supplier (supplier_name, supplier_price) VALUES (?, ?)");
            $stmt->execute([$name, $price]);
            $success = 'Supplier berhasil ditambahkan.';
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e9f7f3;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-container {
            max-width: 500px;
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
    <h2>Tambah Supplier</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success text-center"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nama Supplier</label>
            <input type="text" class="form-control" name="supplier_name" placeholder="Masukkan nama supplier" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Harga Supplier (IDR)</label>
            <input type="number" class="form-control" name="supplier_price" placeholder="Masukkan harga" required min="0">
        </div>
        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-add">Tambah</button>
            <a href="supplier.php" class="btn btn-back">Kembali</a>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>