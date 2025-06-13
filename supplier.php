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

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    $deleteStmt = $pdo->prepare("DELETE FROM supplier WHERE id = ?");
    $deleteStmt->execute([$deleteId]);
}

// Ambil semua data supplier (tanpa join ke posts)
$query = $pdo->prepare("SELECT * FROM supplier ORDER BY id ASC");
$query->execute();
$suppliers = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Suppliers - Tomboypedia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #e9f7f3; }
        .navbar { background: linear-gradient(90deg, #A0EEC0, #9AD8F1); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #05445E; text-decoration: none; margin-right: 20px; font-weight: 500; }
        .navbar a:last-child { margin-right: 0; }
        h1 { margin-top: 40px; text-align: center; color: #05445E; }
        .table-container { margin: 30px auto; width: 95%; overflow-x: auto; }
        table { background-color: white; border-radius: 10px; box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1); }
        th { color: #05445E; }
        td, th { text-align: center; vertical-align: middle; }
        tr:hover { background-color: #f2fdfc; }
        .btn-edit { background-color: #FFDD57; color: #333; border: none; }
        .btn-edit:hover { background-color: #ffd633; }
        .btn-delete { background-color: #F67280; color: white; border: none; }
        .btn-delete:hover { background-color: #f34253; }
        .btn-add { margin: 30px auto; width: 200px; display: block; background-color: #57CC99; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; text-align: center; }
        .btn-add:hover { background-color: #38b37c; }
        @media (max-width: 768px) {
            .navbar { flex-direction: column; align-items: flex-start; gap: 10px; }
            .table-container { width: 100%; padding: 10px; }
            table { font-size: 14px; }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-dark" href="#">Tomboypedia</a>
            <div class="collapse navbar-collapse">
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

    <h1>All Suppliers</h1>
    <a href="add_supplier.php" class="btn-add">+ Add Supplier</a>

    <?php if (count($suppliers) === 0): ?>
        <p style="text-align: center; font-size: 18px; color: #666;">Belum ada supplier.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Supplier</th>
                        <th>Harga Supplier</th>
                        <th style="width: 160px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $index => $supplier): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($supplier['supplier_name']) ?></td>
                            <td><?= number_format($supplier['supplier_price'], 0, ',', '.') ?> IDR</td>
                            <td>
                                <div style="display: flex; justify-content: center; gap: 8px;">
                                    <a href="edit_supplier.php?id=<?= $supplier['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                                    <form method="POST" onsubmit="return confirm('Yakin ingin menghapus supplier ini?');">
                                        <input type="hidden" name="delete_id" value="<?= $supplier['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
