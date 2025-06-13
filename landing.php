<?php 
    require_once 'db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Landing</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0f0f0f;
            background-image: radial-gradient(circle at top left, #2b1055, transparent),
                              radial-gradient(circle at bottom right, #7597de, transparent);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: #ffffff;
            overflow: hidden;
        }

        h1 {
            font-size: 60px;
            font-weight: bold;
            margin-bottom: 40px;
            text-shadow: 0 0 20px #00f2ff;
            animation: glow 2s infinite alternate;
        }

        @keyframes glow {
            from {
                text-shadow: 0 0 10px #00f2ff;
            }
            to {
                text-shadow: 0 0 30px #00f2ff, 0 0 60px #00f2ff;
            }
        }

        .button-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .button-group a {
            text-decoration: none;
        }

        .button-group a button {
            padding: 15px 30px;
            font-size: 18px;
            font-weight: bold;
            background: transparent;
            border: 2px solid #00f2ff;
            border-radius: 10px;
            color: #00f2ff;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 0 10px #00f2ff50;
        }

        .button-group a button:hover {
            background-color: #00f2ff;
            color: #000;
            box-shadow: 0 0 20px #00f2ff, 0 0 40px #00f2ff;
            transform: translateY(-3px);
        }

        @media (max-width: 500px) {
            h1 {
                font-size: 42px;
                text-align: center;
            }

            .button-group {
                flex-direction: column;
                align-items: center;
            }

            .button-group a button {
                width: 200px;
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
