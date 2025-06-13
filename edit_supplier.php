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

if (!isset($_GET['id'])) {
    echo "ID supplier tidak ditemukan.";
    exit;
}

$supplierId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM supplier WHERE id = ?");
$stmt->execute([$supplierId]);
$supplier = $stmt->fetch();
if (!$supplier) {
    echo "Supplier tidak ditemukan.";
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['supplier_name']);
    $newPrice = trim($_POST['supplier_price']);

    if (empty($newName) || empty($newPrice)) {
        $error = 'Nama dan harga supplier tidak boleh kosong.';
    } elseif (!is_numeric($newPrice) || $newPrice < 0) {
        $error = 'Harga supplier harus berupa angka positif.';
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE supplier SET supplier_name = ?, supplier_price = ? WHERE id = ?");
            $updateStmt->execute([$newName, $newPrice, $supplierId]);
            header('Location: supplier.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan saat mengupdate supplier.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Supplier</title>
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
    <h2>Edit Supplier</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nama Supplier</label>
            <input type="text" class="form-control" name="supplier_name" value="<?php echo htmlspecialchars($supplier['supplier_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Harga Supplier (IDR)</label>
            <input type="number" class="form-control" name="supplier_price" value="<?php echo htmlspecialchars($supplier['supplier_price']); ?>" required min="0">
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-update">Update</button>
            <a href="supplier.php" class="btn btn-back">Kembali</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
