<?php
    require_once 'db.php';
    require_once 'models/validasi_register.php';
    use Models\RegisterValidasi;
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $validasi = new RegisterValidasi($pdo);
        $email = $_POST["email"];
        $display_name = $_POST["display_name"];
        $username = $_POST["username"];
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        $role = 'user';
        if (!$validasi->isAllFieldsFilled([$email, $display_name, $username, $password, $confirm_password])) {
            echo "<script>alert('Semua field harus diisi');</script>";
        } elseif (!$validasi->isPasswordConfirmed($password, $confirm_password)) {
            echo "<script>alert('Password dan konfirmasi password harus sama');</script>";
        } elseif (!$validasi->isValidEmail($email)) {
            echo "<script>alert('Email harus mengandung @ dan .');</script>";
        } elseif ($validasi->isEmailOrUsernameTaken($username, $email)) {
            echo "<script>alert('Username atau email sudah digunakan');</script>";
        } else {
            $validasi->insertNewUser($email, $display_name, $username, $password, $role);
            echo "<script>alert('Berhasil register!'); window.location='landing.php';</script>";
        }
    }
?>
<!DOCTYPE html>
<html>
<head><title>Register</title></head>
<body style="margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #f2f2f2;">
    <div style="padding: 30px; border: 1px solid #ccc; border-radius: 10px; background-color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center;">
        <h1>Register</h1>
        <form method="POST" style="text-align: left;">
            <label>Email:</label><br>
            <input type="text" name="email" style="margin-bottom: 15px; width: 90%; padding: 8px;"><br>
            <label>Display Name:</label><br>
            <input type="text" name="display_name" style="margin-bottom: 15px; width: 90%; padding: 8px;"><br>
            <label>Username:</label><br>
            <input type="text" name="username" style="margin-bottom: 15px; width: 90%; padding: 8px;"><br>
            <label>Password:</label><br>
            <input type="password" name="password" style="margin-bottom: 15px; width: 90%; padding: 8px;"><br>
            <label>Confirm Password:</label><br>
            <input type="password" name="confirm_password" style="margin-bottom: 15px; width: 90%; padding: 8px;"><br>
            <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
                <button type="submit" style="height: 50px; width: 100px; font-size: 18px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Register</button>
                <a href="landing.php">
                    <button type="button" style="height: 50px; width: 100px; font-size: 18px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Back</button>
                </a>
            </div>
        </form>
    </div>
</body>
</html>