<?php
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $saldo = $_POST["saldo"];
    $user_id = $_SESSION['user']['id'];

    if ($saldo <= 0) {
        echo "<script>alert('Saldo harus lebih besar dari 0');</script>";
    } else {
        $query = $pdo->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?");
        $query->execute([$saldo, $user_id]);
        echo "<script>alert('Saldo berhasil ditambahkan!'); window.location='discover.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Top Up Saldo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background: linear-gradient(to right, #4A00E0, #8E2DE2);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .topup-card {
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            position: relative;
            text-align: center;
        }

        .topup-card h2 {
            color: #4A00E0;
            margin-bottom: 30px;
        }

        .form-label {
            text-align: left;
            display: block;
            color: #333;
            margin-bottom: 5px;
        }

        .form-control {
            padding: 10px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }

        .btn-submit {
            background-color: #03AC0E;
            color: white;
            border: none;
            font-size: 18px;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
        }

        .btn-submit:hover {
            background-color: #02880b;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 20px;
            color: #333;
            background-color: rgb(229, 216, 255);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #6A00F0;
            color: #fff;
        }

        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.2);
            text-align: center;
            padding: 10px;
            color: white;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <a href="discover.php" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <div class="topup-card">
        <h2>Top Up Saldo</h2>
        <form method="POST">
            <label class="form-label" for="saldo">Jumlah Saldo:</label>
            <input type="number" name="saldo" id="saldo" class="form-control" placeholder="Masukkan jumlah saldo" required min="1">
            <button type="submit" class="btn-submit">Tambah</button>
        </form>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> Tomboypedia. All rights reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>