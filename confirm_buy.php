<?php
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$postId = $_GET['post_id'] ?? null;

if (!$postId) {
    echo "<script>alert('Barang tidak ditemukan.'); window.location.href='discover.php';</script>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    echo "<script>alert('Barang tidak ditemukan.'); window.location.href='discover.php';</script>";
    exit;
}

$saldoStmt = $pdo->prepare("SELECT saldo FROM users WHERE id = ?");
$saldoStmt->execute([$user['id']]);
$userSaldo = $saldoStmt->fetchColumn();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'] ?? '';
    $pdo->beginTransaction();

    try {
        if ($post['stok'] <= 0) throw new Exception("Stok habis.");
        if ($post['user_id'] == $user['id']) throw new Exception("Anda tidak dapat membeli barang sendiri.");

        if ($method === 'saldo') {
            if ($userSaldo < $post['total']) throw new Exception("Saldo tidak cukup.");
            $pdo->prepare("UPDATE users SET saldo = saldo - ? WHERE id = ?")->execute([$post['total'], $user['id']]);
        } elseif ($method !== 'emoney') {
            throw new Exception("Metode pembayaran tidak valid.");
        }

        $pdo->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?")->execute([$post['total'], $post['user_id']]);
        $pdo->prepare("UPDATE posts SET stok = stok - 1 WHERE id = ?")->execute([$postId]);

        $pdo->prepare("INSERT INTO TRANSACTION (post_id, post_title, category_id, category_name, buyer_id, seller_id, price, metode, STATUS)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'berhasil')")
            ->execute([
                $post['id'],
                $post['title'],
                $post['category_id'],
                $post['category_name'],
                $user['id'],
                $post['user_id'],
                $post['total'],
                $method
            ]);

        $pdo->commit();

        if ($method === 'emoney') {
            header("Location: midtrans/examples/snap/checkout-process-simple-version.php?id=$postId");
        } else {
            header("Location: discover.php?message=" . urlencode("Pembelian berhasil!"));
        }
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pembelian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="profile.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #e0f7fa, #ffffff);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            max-width: 500px;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            padding: 25px;
            background-color: #ffffff;
        }

        .card img {
            max-height: 160px;
            object-fit: cover;
            border-radius: 10px;
        }

        .form-select,
        .btn {
            border-radius: 8px;
        }

        .btn-success {
            background-color: #03AC0E;
            border: none;
        }

        .btn-success:hover {
            background-color: #02930C;
        }

        .text-muted {
            font-size: 14px;
        }

        .back-btn {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            margin-bottom: 15px;
            display: inline-block;
        }

        .error-msg {
            color: red;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="card">
    <a href="discover.php" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <h3 class="text-center mb-3">Konfirmasi Pembelian</h3>
    <img src="assets/<?= $post['image'] ?>" class="img-fluid mb-3" alt="Gambar Barang">
    
    <h5><?= $post['title'] ?></h5>
    <p class="mb-1"><strong>total:</strong> <?= number_format($post['total'], 0, ',', '.') ?> IDR</p>
    <p class="text-muted mb-3">Stok tersedia: <?= $post['stok'] ?></p>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="payment_method" class="form-label">Metode Pembayaran</label>
            <select name="payment_method" id="payment_method" class="form-select" required>
                <option value="saldo">Saldo Pedia (<?= number_format($userSaldo, 0, ',', '.') ?> IDR)</option>
                <option value="emoney">E-Money</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success w-100">Konfirmasi Pembelian</button>
    </form>
</div>

</body>
</html>
