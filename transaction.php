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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    $deleteStmt = $pdo->prepare("DELETE FROM transaction WHERE id = ?");
    $deleteStmt->execute([$deleteId]);
}
$query = $pdo->prepare("
    SELECT t.*, 
        c.name AS category_name,
        buyer.username AS buyer_username
    FROM transaction t
    JOIN users buyer ON t.buyer_id = buyer.id
    JOIN categories c ON t.category_id = c.id
    ORDER BY t.transaction_date ASC
");
$query->execute();
$transactions = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f7f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(90deg, #A0EEC0, #9AD8F1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: #05445E;
            text-decoration: none;
            margin-right: 20px;
            font-weight: 500;
        }

        .navbar a:last-child {
            margin-right: 0;
        }

        .navbar .brand {
            font-size: 24px;
            font-weight: bold;
            color: #05445E;
        }

        .table-container {
            max-width: 95%;
            margin: 40px auto;
            background-color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 14px rgba(0, 0, 0, 0.1);
        }

        table th {
            background-color: #03AC0E;
            color: white;
            text-align: center;
        }

        table td {
            vertical-align: middle;
            text-align: center;
        }

        .btn-delete {
            background-color: #DC3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-delete:hover {
            background-color: #b02a37;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg" style="background: linear-gradient(90deg, #A0EEC0, #9AD8F1); padding: 15px 30px;">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-dark" href="#">Tomboypedia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="dashboard.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="users.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="transaction.php">Transaction</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="category.php">Category</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="brands.php">Brands</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="supplier.php">Supplier</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="table-container">
        <h2 class="text-center mb-4 text-dark">Riwayat Transaksi</h2>
        <?php if (count($transactions) == 0): ?>
            <p class="text-center fs-5">Belum ada transaksi.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Transaksi</th>
                            <th>Produk</th>
                            <th>ID Barang</th>
                            <th>Kategori</th>
                            <th>Buyer</th>
                            <th>Harga</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $index => $transaction): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= $transaction['id'] ?></td>
                                <td><?= $transaction['post_title'] ?></td>
                                <td><?= $transaction['post_id'] ?></td>
                                <td><?= $transaction['category_name'] ?></td>
                                <td><?= $transaction['buyer_username'] ?></td>
                                <td>Rp<?= number_format($transaction['price'], 0, ',', '.') ?></td>
                                <td><?= date('d M Y H:i', strtotime($transaction['transaction_date'])) ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?');" style="display:inline;">
                                        <input type="hidden" name="delete_id" value="<?= $transaction['id'] ?>">
                                        <button type="submit" class="btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>
    </div>

    <script>
        setTimeout(() => {
            location.reload();
        }, 10000);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>