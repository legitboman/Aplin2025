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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #4A00E0, #8E2DE2);
        }

        .navbar {
            background-color: #4A00E0;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .navbar a {
            color: white;
            margin: 0 10px;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.2s;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .navbar form {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .navbar input[type="text"] {
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 8px;
            border: 1px solid #ccc;
            width: 300px;
        }

        .navbar input[type="submit"] {
            margin-left: 10px;
            padding: 8px 15px;
            font-size: 14px;
            background-color: white;
            border: none;
            border-radius: 8px;
            color: #4A00E0;
            font-weight: bold;
            cursor: pointer;
        }

        .title {
            color: white;
            font-size: 40px;
            text-align: center;
            margin: 40px 0 20px;
        }

        .category-filter {
            text-align: center;
            margin-bottom: 30px;
        }

        .category-filter select {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            width: 220px;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            padding: 0 40px 60px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .card-content {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .card-content h3 {
            margin: 0 0 10px;
            font-size: 18px;
            font-weight: bold;
            color: #4A00E0;
        }

        .card-content p {
            margin: 5px 0;
            color: #555;
            font-size: 14px;
        }

        .likes {
            color: red;
            font-weight: bold;
            font-size: 14px;
            margin-top: 8px;
        }

        .username {
            color: #888;
            font-size: 14px;
        }

        .card-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: auto;
        }

        .card-actions button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, background-color 0.3s;
        }

        .card-actions button:hover {
            transform: translateY(-2px);
        }

        .like-btn {
            background-color: red;
            color: white;
        }

        .save-btn {
            background-color: #4A90E2;
            color: white;
        }

        .buy-btn {
            background-color: #4A00E0;
            color: white;
            margin-top: 10px;
        }

        .back-btn {
            display: block;
            width: 300px;
            margin: 30px auto;
            padding: 12px 20px;
            background-color: #8E2DE2;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
        }

        .back-btn:hover {
            background-color: #6c1ab9;
            transform: translateY(-2px);
        }

        footer {
            background-color: rgba(255, 255, 255, 0.2);
            text-align: center;
            padding: 10px;
            color: white;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .navbar form {
                justify-content: flex-start;
                width: 100%;
                margin-top: 10px;
            }

            .navbar input[type="text"] {
                width: 100%;
            }

            .grid-container {
                padding: 0 10px;
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            }

            .card-content h3 {
                font-size: 16px;
            }

            .card-content p {
                font-size: 13px;
            }

            .card-actions button {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #4A00E0;">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="discover.php">Tomboypedia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 text-center">
                <li class="nav-item"><a class="nav-link" href="discover.php">Discover</a></li>
                <li class="nav-item"><a class="nav-link" href="saved.php">Cart</a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php">My Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="topup.php">Top Up</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <h1 class="title">My Cart</h1>
    <?php if (count($posts) == 0): ?>
        <p class="text-center text-white">You have no saved posts.</p>
    <?php else: ?>
        <div class="grid-container">
            <?php foreach ($posts as $post): ?>
                <div class="card">
                    <img src="assets/<?php echo ($post['image']); ?>" alt="Post Image">
                    <div class="card-content">
                        <span class="username"><?php echo htmlspecialchars($post['display_name']); ?></span>
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p><?php echo htmlspecialchars($post['caption']); ?></p>
                        <div class="likes">Likes: <?php echo $post['like_count']; ?></div>
                        <div class="card-actions">
                            <form action="likes.php" method="POST">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" class="like-btn">
                                    <?php echo ($post['already_liked'] > 0) ? 'Already Liked' : 'Like'; ?>
                                </button>
                            </form>
                            <form action="saved_post.php" method="POST">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" class="save-btn">
                                    <?php echo ($post['already_saved'] > 0) ? 'Already Saved' : 'Save'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <footer>
        &copy; <?php echo date('Y'); ?> Tomboypedia. All rights reserved.
    </footer>
</body>

</html>