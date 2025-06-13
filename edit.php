<?php
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $display_name = $_POST['display_name'];
    $about_me = $_POST['about_me'];
    $profile_picture = $user['profile_picture'];

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024;

        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $slugDisplayName = preg_replace('/[^a-z0-9]+/', '-', strtolower($display_name));
            $newFilename = "user_{$user['id']}_{$slugDisplayName}." . $ext;
            $uploadPath = "assets/" . $newFilename;

            if ($profile_picture !== 'default_img.png' && file_exists("assets/" . $profile_picture)) {
                unlink("assets/" . $profile_picture);
            }

            move_uploaded_file($file['tmp_name'], $uploadPath);
            $profile_picture = $newFilename;
        } else {
            echo "<script>alert('File harus JPG, JPEG, PNG, atau GIF dan maksimal 2MB');</script>";
        }
    }

    $query = $pdo->prepare("UPDATE users SET email = ?, display_name = ?, about_me = ?, profile_picture = ? WHERE id = ?");
    $query->execute([$email, $display_name, $about_me, $profile_picture, $user['id']]);

    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['display_name'] = $display_name;
    $_SESSION['user']['about_me'] = $about_me;
    $_SESSION['user']['profile_picture'] = $profile_picture;

    echo "<script>alert('Profil berhasil diperbarui!'); window.location='profile.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #4A00E0, #8E2DE2);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container-fluid {
            padding: 60px 15px;
            display: flex;
            justify-content: center;
        }

        .edit-card {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            position: relative;
        }

        .edit-card h2 {
            text-align: center;
            color: #4A00E0;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: bold;
            color: #333;
            margin-top: 15px;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px;
            font-size: 16px;
        }

        .form-control:focus {
            border-color: #8E2DE2;
            box-shadow: 0 0 5px rgba(142, 45, 226, 0.5);
        }

        .btn-save {
            background-color: #03AC0E;
            color: white;
            font-weight: bold;
            width: 100%;
            border: none;
            padding: 12px;
            font-size: 18px;
            border-radius: 10px;
            margin-top: 25px;
            transition: background-color 0.3s;
        }

        .btn-save:hover {
            background-color: #02880b;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #e5d8ff;
            color: #4A00E0;
            border: none;
            width: 40px;
            height: 40px;
            font-size: 20px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .back-btn:hover {
            background-color: #6A00F0;
            color: white;
        }

        .profile-img {
            display: block;
            margin: 0 auto 20px auto;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 2px solid #4A00E0;
            object-fit: cover;
        }

        @media (max-width: 576px) {
            .edit-card {
                padding: 25px;
            }

            .btn-save {
                font-size: 16px;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="edit-card">
            <a href="profile.php" class="back-btn"><i class="bi bi-arrow-left"></i></a>
            <h2>Edit Profil</h2>
            <form method="POST" enctype="multipart/form-data">
                <img src="assets/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-img">

                <label class="form-label">Ganti Foto Profil:</label>
                <input type="file" name="profile_picture" class="form-control">

                <label class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user['email']); ?>">

                <label class="form-label">Display Name:</label>
                <input type="text" name="display_name" class="form-control" required value="<?php echo htmlspecialchars($user['display_name']); ?>">

                <label class="form-label">About Me:</label>
                <textarea name="about_me" class="form-control" rows="4"><?php echo htmlspecialchars($user['about_me']); ?></textarea>

                <button type="submit" class="btn-save">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>