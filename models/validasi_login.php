<?php
    require_once 'db.php';
    function prosesLogin($username, $password, $pdo) {
        if (empty($username) || empty($password)) {
            echo "<script>alert('Semua field harus diisi'); window.location='login.php';</script>";
            exit;
        } else {
            $query = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $query->execute([$username]);
            $user = $query->fetch();
            if (!$user) {
                echo "<script>alert('Username tidak terdaftar'); window.location='login.php';</script>";
                exit;
            } elseif ($user['status'] != 'active') {
                echo "<script>alert('Akun kamu non-aktif'); window.location='landing.php';</script>";
                exit;
            } elseif ($user['password'] != $password) {
                echo "<script>alert('Username atau Password salah'); window.location='login.php';</script>";
                exit;
            } else {
                $_SESSION['user'] = $user;
                if ($user['role'] == 'admin') {
                    echo "<script>window.location='dashboard.php';</script>";
                } else {
                    echo "<script>window.location='discover.php';</script>";
                }
                exit;
            }
        }
    }    
?>