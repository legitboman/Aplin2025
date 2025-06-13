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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #4A00E0, #8E2DE2);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .register-container {
            background-color: white;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 450px;
        }

        .register-container h1 {
            text-align: center;
            color: #4A00E0;
            margin-bottom: 25px;
        }

        label {
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            width: 90%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #8E2DE2;
            outline: none;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        button {
            padding: 12px 20px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        button[type="submit"] {
            background-color: #4A00E0;
            color: white;
        }

        button[type="submit"]:hover {
            background-color: #3b00b0;
            transform: translateY(-2px);
        }

        .button-back {
            background-color: #8E2DE2;
            color: white;
        }

        .button-back:hover {
            background-color: #6c1ab9;
            transform: translateY(-2px);
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 30px 20px;
            }

            .button-group {
                flex-direction: column;
            }

            .button-group button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h1>Register</h1>
        <form method="POST">
            <label>Email:</label>
            <input type="text" name="email" required>

            <label>Display Name:</label>
            <input type="text" name="display_name" required>

            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" required>

            <div class="button-group">
                <button type="submit">Register</button>
                <a href="landing.php">
                    <button type="button" class="button-back">Back</button>
                </a>
            </div>
        </form>
    </div>
</body>

</html>