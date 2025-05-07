<?php
    require_once 'db.php';
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
    $user = $_SESSION['user'];
    $query = $pdo->prepare("
        SELECT posts.*, users.display_name, 
            (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count,
            (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) AS already_liked,
            (SELECT COUNT(*) FROM saved_posts WHERE saved_posts.post_id = posts.id AND saved_posts.user_id = ?) AS already_saved
        FROM saved_posts
        JOIN posts ON saved_posts.post_id = posts.id
        JOIN users ON posts.user_id = users.id
        WHERE saved_posts.user_id = ?
        ORDER BY posts.id DESC
    ");
    $query->execute([$user['id'], $user['id'], $user['id']]);
    $posts = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saved Posts</title>
</head>
<body style="margin:0; font-family: Arial, sans-serif; background-color: white;">
    <div style="background-color: #03AC0E; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
        <a href="discover.php" style="text-decoration: none;"><div style="color: white; font-size: 20px; font-weight: bold;">Tomboypedia</div></a>
        <div>
            <a href="discover.php" style="color: white; margin-right: 20px; text-decoration: none;">Discover</a>
            <a href="saved.php" style="color: white; margin-right: 20px; text-decoration: none;">Cart</a>
            <a href="profile.php" style="color: white; margin-right: 20px; text-decoration: none;">My Profile</a>
            <a href="topup.php" style="color: white; margin-right: 20px; text-decoration: none;">Top Up</a>
            <a href="logout.php" style="color: white; text-decoration: none;">Logout</a>
        </div>
    </div>
    <h1 style="color: black; font-size: 40px; text-align: center; margin-top: 50px;">My Cart</h1>
    <?php if (count($posts) == 0): ?>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; align-items: center; gap: 20px; padding: 40px;">
            <?php foreach ($posts as $post): ?>
                <div style="width: 500px; min-height: 200px; border: 1px solid #ccc; background-color: #f9f9f9; border-radius: 10px; padding: 10px; display: flex; flex-direction: column; align-items: flex-start;">
                    <img src="assets/<?php echo ($post['image']); ?>" alt="Post Image" style="width: 100%; height: 250px; object-fit: cover; border-radius: 5px;"><br>
                    <div style="color: #b67a00; font-weight: bold;"><?php echo ($post['display_name']); ?></div>
                    <h3 style="margin: 5px 0;"><?php echo ($post['title']); ?></h3>
                    <p style="margin: 5px 0; color: #424242;"><?php echo ($post['caption']); ?></p>
                    <p style="margin: 5px 0; color: red; font-weight: bold;">Likes: <?php echo $post['like_count']; ?></p>
                    <div style="display: flex; gap: 10px;">
                        <form action="likes.php" method="POST">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" style="background-color: red; color: white; padding: 6px 10px; border: none; border-radius: 5px;">
                                <?php echo ($post['already_liked'] > 0) ? 'Already Liked' : 'Like'; ?>
                            </button>
                        </form>
                        <form action="saved_post.php" method="POST">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" style="background-color: #3c99dc; color: white; padding: 6px 10px; border: none; border-radius: 5px;">
                                <?php echo ($post['already_saved'] > 0) ? 'Already Saved' : 'Save'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>
