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
                $newFilename = "user_{$user['id']}_{$slugDisplayName}.{$ext}";
                $uploadPath = "assets/" . $newFilename;
                if ($profile_picture !== 'default_img.png' && file_exists('assets/' . $profile_picture)) {
                    unlink('assets/' . $profile_picture);
                }
                move_uploaded_file($file['tmp_name'], $uploadPath);
                $profile_picture = $newFilename;
            } else {
                echo "<script>alert('File harus JPG, JPEG, PNG, GIF dan maksimal 2MB');</script>";
            }
        }
        $query = $pdo->prepare("UPDATE users SET email = ?, display_name = ?, about_me = ?, profile_picture = ? WHERE id = ?");
        $query->execute([$email, $display_name, $about_me, $profile_picture, $user['id']]);
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['display_name'] = $display_name;
        $_SESSION['user']['about_me'] = $about_me;
        $_SESSION['user']['profile_picture'] = $profile_picture;
        echo "<script>alert('Profile updated!'); window.location='profile.php';</script>";
    }
?>
<!DOCTYPE html>
<html>
<head><title>Edit Profile</title></head>
<body style="margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #f2f2f2;">
    <div style="padding: 30px; border: 1px solid #ccc; border-radius: 10px; background-color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 400px;">
        <h2 style="text-align: center;">Edit Profile</h2>

        <form method="POST" enctype="multipart/form-data" style="text-align: left;">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="assets/<?php echo $user['profile_picture']; ?>" alt="Profile Picture" width="150" height="150" style="border-radius: 50%; border: 2px solid black;">
            </div>

            <label>Profile Picture:</label><br>
            <input type="file" name="profile_picture" style="margin-bottom: 15px;"><br>

            <label>Email:</label><br>
            <input type="text" name="email" value="<?php echo $user['email']; ?>" style="margin-bottom: 15px; width: 95%; padding: 8px;"><br>

            <label>Display Name:</label><br>
            <input type="text" name="display_name" value="<?php echo $user['display_name']; ?>" style="margin-bottom: 15px; width: 95%; padding: 8px;"><br>

            <label>About Me:</label><br>
            <textarea name="about_me" style="margin-bottom: 15px; width: 95%; padding: 8px;" rows="4"><?php echo $user['about_me']; ?></textarea><br>

            <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
                <button type="submit" style="height: 45px; width: 100px; font-size: 16px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Edit</button>
                <a href="profile.php">
                    <button type="button" style="height: 45px; width: 100px; font-size: 16px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Back</button>
                </a>
            </div>
        </form>
    </div>
</body>
</html>
