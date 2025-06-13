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
    echo "ID brand tidak ditemukan.";
    exit;
}

$brandId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
$stmt->execute([$brandId]);
$brand = $stmt->fetch();

if (!$brand) {
    echo "Brand tidak ditemukan.";
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['name']);
    if (empty($newName)) {
        $error = 'Nama brand tidak boleh kosong.';
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE brands SET name = ? WHERE id = ?");
            $updateStmt->execute([$newName, $brandId]);
            header('Location: brands.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Nama brand sudah digunakan.';
            } else {
                $error = 'Terjadi kesalahan saat mengupdate brand.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Brand</title>
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
    <h2>Edit Brand</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nama Brand</label>
            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($brand['name']); ?>" required>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-update">Update</button>
            <a href="brands.php" class="btn btn-back">Kembali</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
