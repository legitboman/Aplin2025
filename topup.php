<?php
    require_once 'db.php';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $saldo = $_POST["saldo"];
        $user_id = $_SESSION['user']['id'];
        if ($saldo <= 0) {
            echo "<script>alert('Saldo harus lebih besar dari 0');</script>";
        } else {
            $query = $pdo->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?");
            $query->execute([$saldo, $user_id]);
            echo "<script>alert('Saldo berhasil ditambahkan!'); window.location='dashboard.php';</script>";
        }
    }
?>
<!DOCTYPE html>
<html>
<head><title>Top Up Saldo</title></head>
<body style="margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #f2f2f2;">
    <div style="padding: 30px; border: 1px solid #ccc; border-radius: 10px; background-color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center;">
        <h1>Top Up Saldo</h1>
        <form method="POST" style="text-align: left;">
            <label>Jumlah Saldo:</label><br>
            <input type="number" name="saldo" style="margin-bottom: 15px; width: 90%; padding: 8px;" required><br>
            <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
                <button type="submit" style="height: 50px; width: 100px; font-size: 18px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Tambah</button>
                <a href="dashboard.php">
                    <button type="button" style="height: 50px; width: 100px; font-size: 18px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Back</button>
                </a>
            </div>
        </form>
    </div>
</body>
</html>
