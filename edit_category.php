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
    echo "ID kategori tidak ditemukan.";
    exit;
}
$categoryId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$categoryId]);
$category = $stmt->fetch();
if (!$category) {
    echo "Kategori tidak ditemukan.";
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['name']);
    if (empty($newName)) {
        $error = 'Nama kategori tidak boleh kosong.';
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $updateStmt->execute([$newName, $categoryId]);
            header('Location: category.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Nama kategori sudah digunakan.';
            } else {
                $error = 'Terjadi kesalahan saat mengupdate kategori.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Kategori</title>
</head>
<body style="margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #f2f2f2;">
    <div style="padding: 30px; border: 1px solid #ccc; border-radius: 10px; background-color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; width: 400px;">
        <h2 style="margin-bottom: 20px;">Edit Kategori</h2>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" style="text-align: left;">
            <label>Nama Kategori:</label><br>
            <input type="text" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" style="margin-bottom: 15px; width: 100%; padding: 8px;" required><br>
            <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
                <button type="submit" style="height: 50px; width: 100px; font-size: 16px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Update</button>
                <a href="category.php">
                    <button type="button" style="height: 50px; width: 100px; font-size: 16px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Kembali</button>
                </a>
            </div>
        </form>
    </div>
</body>
</html>