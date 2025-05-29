<?php
    require_once 'db.php';
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }
    $user_id = $_SESSION['user']['id'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
        $post_id = $_POST['post_id'];
        $check = $pdo->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
        $check->execute([$user_id, $post_id]);
        $alreadyLiked = $check->fetch();
        if ($alreadyLiked) {
            $delete = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
            $delete->execute([$user_id, $post_id]);
        } else {
            $insert = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
            $insert->execute([$user_id, $post_id]);
        }
        header("Location: discover.php");
        exit();
    } else {
        echo "<script>alert('Invalid request'); window.location='discover.php';</script>";
        exit();
    }
?>