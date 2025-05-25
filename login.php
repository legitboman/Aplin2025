<?php
    require_once 'models/validasi_login.php';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"];
        $password = $_POST["password"];
        prosesLogin($username, $password, $pdo);
    }
?>
<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body style="margin: 0; height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #f2f2f2;">
    <div style="padding: 30px; border: 1px solid #ccc; border-radius: 10px; background-color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center;">
        <h1>Login</h1>
        <form method="POST" style="text-align: left;">
            <label>Username:</label><br>
            <input type="text" name="username" style="margin-bottom: 15px; width: 90%; padding: 8px;"><br>
            <label>Password:</label><br>
            <input type="password" name="password" style="margin-bottom: 15px; width: 90%; padding: 8px;"><br>
            <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
                <button type="submit" style="height: 50px; width: 100px; font-size: 18px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Login</button>
                <a href="landing.php">
                    <button type="button" style="height: 50px; width: 100px; font-size: 18px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Back</button>
                </a>
            </div>
        </form>
    </div>
</body>
</html>