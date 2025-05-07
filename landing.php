<?php 
    require_once 'db.php'; 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Landing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            overflow: hidden;
        }
        h1 {
            font-size: 60px;
        }
    </style>
</head>
<body>
    <h1 style="color: #03AC0E;">Tomboypedia</h1>
    <div style="display: flex; gap: 20px;">
        <a href="login.php">
            <button style="height: 50px; width: 100px; font-size: 18px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Login</button>
        </a>
        <a href="register.php">
            <button style="height: 50px; width: 100px; font-size: 18px; background-color: #03AC0E; color: white; font-weight: bold; border: none; cursor: pointer;">Register</button>
        </a>
    </div>
</body>
</html>