<?php
require_once 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_id'], $_POST['new_status'])) {
    $updateId = $_POST['update_user_id'];
    $newStatus = $_POST['new_status'];
    $updateStmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'user'");
    $updateStmt->execute([$newStatus, $updateId]);
}

$query = $pdo->prepare("SELECT * FROM users WHERE role = 'user'");
$query->execute();
$users = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Users | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9f7f3;
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

        h1 {
            color: #05445E;
            font-size: 36px;
            text-align: center;
            margin-top: 40px;
        }

        .table-container {
            margin: 30px auto;
            width: 95%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
            border-collapse: collapse;
        }

        thead th {
            color: #333;
            padding: 12px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            user-select: none;
        }

        tbody td {
            padding: 12px;
            text-align: center;
            vertical-align: middle;
            border-top: 1px solid #ccc;
            word-wrap: break-word;
        }

        tr:hover {
            background-color: #f2fdfc;
        }

        .profile-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
        }

        .btn-edit {
            background-color: #FFDD57;
            color: #333;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-edit:hover {
            background-color: #ffd633;
        }

        .btn-toggle {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: #fff;
            transition: background-color 0.3s ease;
        }

        .btn-toggle.active {
            background-color: #F67280;
        }

        .btn-toggle.nonactive {
            background-color: rgb(87, 168, 255);
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .table-container {
                width: 100%;
                padding: 10px;
            }

            table {
                font-size: 14px;
            }
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

    <h1>Manage Users</h1>

    <?php if (count($users) == 0): ?>
        <p style="text-align: center; font-size: 18px;">Belum ada user yang terdaftar.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Display Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Saldo</th>
                        <th>About Me</th>
                        <th>Profile Picture</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $index => $user): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($user['display_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>Rp<?php echo number_format($user['saldo'], 0, ',', '.'); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($user['about_me'])); ?></td>
                            <td>
                                <?php if ($user['profile_picture']): ?>
                                    <img src="assets/<?php echo htmlspecialchars($user['profile_picture']); ?>" class="profile-img">
                                <?php else: ?>
                                    <span>No Image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['status']); ?></td>
                            <td>
                                <a href="edit_user_admin.php?id=<?php echo $user['id']; ?>">
                                    <button type="button" class="btn-edit">Edit</button>
                                </a>
                                <form method="POST" onsubmit="return confirm('Yakin ingin mengubah status user ini?');" style="display:inline;">
                                    <input type="hidden" name="update_user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="new_status" value="<?php echo $user['status'] === 'active' ? 'nonactive' : 'active'; ?>">
                                    <button
                                        type="submit"
                                        class="btn-toggle <?php echo $user['status'] === 'active' ? 'active' : 'nonactive'; ?>">
                                        <?php echo $user['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                    </button>
                                </form>
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