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
    $user = $query->fetch();

    if (!$user) {
        header('Location: users.php');
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $display_name = $_POST['display_name'];
        $about_me = $_POST['about_me'];
        $status = $_POST['status'];
        $profile_picture = $user['profile_picture'];
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $file = $_FILES['profile_picture'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024;
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $slugDisplayName = preg_replace('/[^a-z0-9]+/', '-', strtolower($display_name));
                $newFilename = "user_{$user['id']}_{$slugDisplayName}.{$ext}";
                $uploadPath = "assets/" . $newFilename;
                if ($profile_picture !== 'default_img.png' && file_exists('assets/' . $profile_picture)) {
                    unlink('assets/' . $profile_picture);
                }
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $profile_picture = $newFilename;
                }
            }
        }
        $query = $pdo->prepare("UPDATE users SET email = ?, display_name = ?, about_me = ?, profile_picture = ?, status = ? WHERE id = ?");
        $query->execute([$email, $display_name, $about_me, $profile_picture, $status, $userId]);
        $_SESSION['success'] = "User updated successfully!";
        header("Location: users.php");
        exit;
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f2f2f2;
            font-family: Arial, sans-serif;
        }
        .edit-container {
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 500px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], 
        input[type="email"],
        input[type="number"],
        textarea,
        select {
            width: 95%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        button {
            height: 45px;
            width: 100px;
            font-size: 16px;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .save-btn {
            background-color: #03AC0E;
        }
        .cancel-btn {
            background-color: #6c757d;
        }
        .profile-pic-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 2px solid black;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <h2 style="text-align: center;">Edit User (Admin)</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-pic-container">
                <img src="assets/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-pic">
            </div>
            <div class="form-group">
                <label>Profile Picture:</label>
                <input type="file" name="profile_picture">
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Display Name:</label>
                <input type="text" name="display_name" value="<?php echo htmlspecialchars($user['display_name']); ?>" required>
            </div>
            <div class="form-group">
                <label>About Me:</label>
                <textarea name="about_me" rows="4"><?php echo htmlspecialchars($user['about_me']); ?></textarea>
            </div>
            <div class="form-group">
                <label>Saldo:</label>
                <input type="number" value="<?php echo htmlspecialchars($user['saldo']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Status:</label>
                <select name="status" required>
                    <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="nonactive" <?php echo $user['status'] === 'nonactive' ? 'selected' : ''; ?>>Nonactive</option>
                </select>
            </div>
            <div class="button-group">
                <button type="submit" class="save-btn">Save</button>
                <a href="users.php"><button type="button" class="cancel-btn">Cancel</button></a>
            </div>
        </form>
    </div>
</body>
</html>