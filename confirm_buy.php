<?php
require_once 'db.php';
 // jangan lupa ini kalau pakai $_SESSION

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

// Ambil saldo terbaru
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
            if ($userSaldo < $post['harga']) throw new Exception("Saldo tidak cukup.");

            $pdo->prepare("UPDATE users SET saldo = saldo - ? WHERE id = ?")
                ->execute([$post['harga'], $user['id']]);
        } elseif ($method !== 'emoney') {
            throw new Exception("Metode pembayaran tidak valid.");
        }

        $pdo->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?")
            ->execute([$post['harga'], $post['user_id']]);
        $pdo->prepare("UPDATE posts SET stok = stok - 1 WHERE id = ?")
            ->execute([$postId]);

        $pdo->prepare("INSERT INTO TRANSACTION (post_id, post_title, category_id, category_name, buyer_id, seller_id, price, metode, STATUS)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'berhasil')")
            ->execute([
                $post['id'], $post['title'], $post['category_id'], $post['category_name'],
                $user['id'], $post['user_id'], $post['harga'], $method
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
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px; }
        .container {
            max-width: 600px; margin: auto; background: #fff;
            padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        img {
            display: block; margin: auto; max-width: 20%; height: auto;
            border-radius: 10px;
        }
        h2 { text-align: center; color: #03AC0E; }
        .detail { margin: 20px 0; }
        .detail p { margin: 5px 0; font-size: 16px; }
        .form-group { margin: 15px 0; }
        select, button {
            width: 100%; padding: 10px; font-size: 16px;
            border-radius: 5px; border: 1px solid #ccc;
        }
        button {
            background-color: #03AC0E; color: white; font-weight: bold; border: none;
        }
        .back-link {
            text-align: center; margin-top: 20px;
        }
        .back-link a {
            color: #03AC0E; text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Konfirmasi Pembelian</h2>
        <img src="assets/<?= htmlspecialchars($post['image']) ?>" alt="Gambar Barang">
        <div class="detail">
            <p><strong>Nama Barang:</strong> <?= htmlspecialchars($post['title']) ?></p>
            <p><strong>Harga:</strong> <?= number_format($post['harga'], 0, ',', '.') ?> IDR</p>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="payment_method">Metode Pembayaran:</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="saldo">Saldo Pedia (<?= number_format($userSaldo, 0, ',', '.') ?> IDR)</option>
                    <option value="emoney">E-Money</option>
                </select>
            </div>
            <button type="submit">Konfirmasi Pembelian</button>
        </form>

        <div class="back-link">
            <a href="discover.php">← Kembali ke Discover</a>
        </div>
    </div>
</body>
</html>
