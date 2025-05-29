<?php
    require_once 'models/validasi_login.php';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"];
        $password = $_POST["password"];
        prosesLogin($username, $password, $pdo);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(to right, #4A00E0, #8E2DE2);
        }

        .login-container {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
        }

        .login-container h1 {
            margin-bottom: 25px;
            color: #4A00E0;
            text-align: center;
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
            padding: 12px 15px;
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
            .login-container {
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
    <div class="login-container">
        <h1>Login</h1>
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <div class="button-group">
                <button type="submit">Login</button>
                <a href="landing.php"><button type="button" class="button-back">Back</button></a>
            </div>
        </form>
    </div>
</body>
</html>
