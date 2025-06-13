<?php
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$userId = $_GET['id'];
$query = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$query->execute([$userId]);
$userData = $query->fetch();

if (!$userData) {
    header('Location: users.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $display_name = $_POST['display_name'];
    $about_me = $_POST['about_me'];
    $status = $_POST['status'];
    $profile_picture = $userData['profile_picture'];

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024;

        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $slugDisplayName = preg_replace('/[^a-z0-9]+/', '-', strtolower($display_name));
            $newFilename = "user_{$userData['id']}_{$slugDisplayName}.{$ext}";
            $uploadPath = "assets/" . $newFilename;

            if ($profile_picture !== 'default_img.png' && file_exists("assets/" . $profile_picture)) {
                unlink("assets/" . $profile_picture);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $profile_picture = $newFilename;
            }
        }
    }

    $query = $pdo->prepare("UPDATE users SET email = ?, display_name = ?, about_me = ?, profile_picture = ?, status = ? WHERE id = ?");
    $query->execute([$email, $display_name, $about_me, $profile_picture, $status, $userId]);

    $_SESSION['success'] = "User berhasil diperbarui!";
    header("Location: users.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit User (Admin)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e9f7f3;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            max-width: 600px;
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
        .img-preview {
            text-align: center;
            margin-bottom: 20px;
        }
        .img-preview img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 2px solid black;
            border-radius: 50%;
        }
        .btn-save,
        .btn-cancel {
            width: 100%;
            padding: 12px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            font-size: 16px;
        }
        .btn-save {
            background-color: #03AC0E;
            color: white;
        }
        .btn-save:hover {
            background-color: #02940c;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit User (Admin)</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="img-preview">
            <img src="assets/<?= htmlspecialchars($userData['profile_picture']) ?>" alt="Foto Profil">
        </div>
        <div class="mb-3">
            <label class="form-label">Gambar Profil Baru (Opsional)</label>
            <input type="file" name="profile_picture" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($userData['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Tampilan</label>
            <input type="text" name="display_name" class="form-control" value="<?= htmlspecialchars($userData['display_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Tentang Saya</label>
            <textarea name="about_me" class="form-control" rows="4"><?= htmlspecialchars($userData['about_me']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Saldo</label>
            <input type="number" value="<?= htmlspecialchars($userData['saldo']) ?>" class="form-control" disabled>
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <option value="active" <?= $userData['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="nonactive" <?= $userData['status'] === 'nonactive' ? 'selected' : '' ?>>Nonactive</option>
            </select>
        </div>
        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-save">Simpan</button>
            <a href="users.php" class="btn btn-cancel">Batal</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
