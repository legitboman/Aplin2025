<?php
    require_once 'db.php';
    require_once 'models/buy.php';
    if (!isset($_SESSION['user'])) {
        echo json_encode(['status' => 'error', 'message' => 'User  not logged in.']);
        exit;
    }
    $user = $_SESSION['user'];
    $buy = new Models\Buy($pdo);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_post_id'])) {
        $postId = $_POST['buy_post_id'];
        $result = $buy->processBuy($user['id'], $postId);
        echo json_encode($result);
    }
?>