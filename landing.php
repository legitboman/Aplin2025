<?php 
    require_once 'db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Landing</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            background: linear-gradient(to right, #4A00E0, #8E2DE2);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
        }

        h1 {
            font-size: 64px;
            margin-bottom: 40px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .button-group {
            display: flex;
            gap: 20px;
        }

        .button-group a button {
            height: 50px;
            width: 130px;
            font-size: 18px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            background-color: white;
            color: #4A00E0;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .button-group a button:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
        }

        @media (max-width: 500px) {
            h1 {
                font-size: 42px;
            }

            .button-group {
                flex-direction: column;
            }

            .button-group a button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <h1>Tomboypedia</h1>
    <div class="button-group">
        <a href="login.php">
            <button>Login</button>
        </a>
        <a href="register.php">
            <button>Register</button>
        </a>
    </div>
</body>
</html>
