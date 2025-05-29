<?php
    require_once 'db.php';
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }
    $query = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $query->execute([$_SESSION['user']['id']]);
    $user = $query->fetch();
    if (isset($_POST['delete'])) {
        $deleteQuery = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $deleteQuery->execute([$user['id']]);
        session_unset();
        session_destroy();
        echo "<script>alert('User berhasil dihapus'); window.location='landing.php';</script>";
        exit();
    }
?>
<!DOCTYPE html>
<html>
<head><title>My Profile</title></head>
<body style="margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #f2f2f2;">
    <div style="padding: 30px; border: 1px solid #ccc; border-radius: 10px; background-color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; width: 400px;">
        <div style="text-align: left; margin-bottom: 20px;">
            <a href="discover.php">
                <button type="button" style="height: 40px; width: 40px; border-radius: 50%; font-size: 18px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;"><<</button>
            </a>
        </div>
        <img src="assets/<?php echo $user['profile_picture']; ?>" alt="Profile Picture" width="100" height="100" style="border-radius: 50%; border: 2px solid black; margin-bottom: 20px;"><br>
        <h2 style="margin: 0;"><?php echo $user['username']; ?></h2>
        <p style="margin: 10px 0;"><?php echo $user['email']; ?></p>
        <p style="margin: 10px 0;"><?php echo $user['about_me'] ? $user['about_me'] : "belum ada deskripsi"; ?></p>
        <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
            <a href="edit.php">
                <button type="button" style="height: 40px; width: 100px; font-size: 14px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Edit</button>
            </a>
            <form method="POST" style="display: inline;">
                <button type="submit" name="delete" style="height: 40px; width: 100px; font-size: 14px; background-color: #d9534f; color: white; font-weight: bold; border: none; cursor: pointer;">Delete</button>
            </form>
        </div>
    </div>
</body>
</html>
