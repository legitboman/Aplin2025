<?php
require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    if (!empty($name)) {
        // Cek apakah kategori sudah ada
        $check = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $check->execute([$name]);

        if ($check->rowCount() > 0) {
            $message = "<p style='color: red;'>Kategori sudah ada!</p>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");

            if ($stmt->execute([$name])) {
                $message = "<p style='color: green;'>Kategori berhasil ditambahkan!</p>";
            } else {
                $message = "<p style='color: red;'>Gagal menambahkan kategori.</p>";
            }
        }
    } else {
        $message = "<p style='color: red;'>Nama kategori tidak boleh kosong.</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Category</title>
</head>
<body style="margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #f2f2f2;">
    <div style="padding: 30px; border: 1px solid #ccc; border-radius: 10px; background-color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; width: 400px;">
        <h2 style="margin-bottom: 20px;">Add Category</h2>

        <?= $message ?>

        <form method="POST" style="text-align: left;">
            <label>Category Name:</label><br>
            <input type="text" name="name" style="margin-bottom: 15px; width: 100%; padding: 8px;" required><br>

            <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
                <button type="submit" style="height: 50px; width: 100px; font-size: 16px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Add</button>
                <a href="dashboard.php">
                    <button type="button" style="height: 50px; width: 100px; font-size: 16px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Back</button>
                </a>
            </div>
        </form>
    </div>
</body>
</html>
