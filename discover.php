<?php
    require_once 'db.php';
    require_once 'models/buy.php';
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
    $user = $_SESSION['user'];
    $buy = new Models\Buy($pdo);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_post_id'])) {
        $postId = $_POST['buy_post_id'];
        $result = $buy->processBuy($user['id'], $postId);
        if ($result['status'] === 'success') {
            $pesan = urlencode($result['message']);
            header("Location: discover.php?message=$pesan");
            exit;
        } else {
            echo "<script>alert('" . $result['message'] . "');</script>";
        }
    }
    if (isset($_GET['message'])) {
        echo "<script>alert('" . htmlspecialchars($_GET['message']) . "');</script>";
    }
    $categoriesQuery = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $categoriesQuery->fetchAll();
    $selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'all';
    $brandsQuery = $pdo->query("SELECT * FROM brands ORDER BY name");
    $brands = $brandsQuery->fetchAll();
    $selectedBrand = isset($_GET['brand']) ? $_GET['brand'] : 'all';
    $searchQuery = '';
    $posts = [];
    $filterConditions = [];
    $params = [':user_id' => $user['id']];
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $searchQuery = trim($_GET['search']);
        $filterConditions[] = "LOWER(posts.title) LIKE LOWER(:search_query)";
        $params[':search_query'] = "%$searchQuery%";
    }
    if ($selectedCategory !== 'all') {
        $filterConditions[] = "posts.category_id = :category_id";
        $params[':category_id'] = $selectedCategory;
    }
    if ($selectedBrand !== 'all') {
        $filterConditions[] = "posts.brand_id = :brand_id";
        $params[':brand_id'] = $selectedBrand;
    }
    $whereClause = '';
    if (count($filterConditions) > 0) {
        $whereClause = "WHERE " . implode(' AND ', $filterConditions);
    }
    $query = $pdo->prepare("
        SELECT posts.*, users.display_name, 
               (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count, 
               (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = :user_id) AS already_liked, 
               (SELECT COUNT(*) FROM saved_posts WHERE saved_posts.post_id = posts.id AND saved_posts.user_id = :user_id) AS already_saved
        FROM posts
        JOIN users ON posts.user_id = users.id
        $whereClause
        ORDER BY posts.id DESC
    ");
    $query->execute($params);
    $posts = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Discover</title>
    <style>
        body {margin: 0; font-family: Arial, sans-serif; background-color: white;}
        .navbar {background-color: #03AC0E; padding: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;}
        .navbar a {color: white; margin: 0 10px; text-decoration: none;}
        .navbar form {flex: 1; display: flex; justify-content: center;}
        .navbar input[type="text"] {padding: 5px 10px; font-size: 14px; border-radius: 5px; border: none; width: 300px;}
        .navbar input[type="submit"] {margin-left: 10px; padding: 5px 15px; font-size: 14px; background-color: white; border: none; border-radius: 5px; color: #03AC0E; cursor: pointer; font-weight: bold;}
        .title {color: black; font-size: 40px; text-align: center; margin-top: 50px;}
        .grid-container {display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px; padding: 40px; max-width: 1200px; margin: auto;}
        .card {background-color: #ffffff; border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s;}
        .card:hover {transform: scale(1.02);}
        .card img {width: 150px; height: 150px;}
        .card-content {padding: 15px; flex-grow: 1; display: flex; flex-direction: column;}
        .card-content h3 {margin: 10px 0 5px; font-size: 18px;}
        .card-content p {margin: 5px 0; color: #555; font-size: 14px;}
        .username {color: #b67a00; font-weight: bold; font-size: 14px;}
        .likes {color: red; font-weight: bold; font-size: 14px;}
        .card-actions {display: flex; gap: 5px; margin-top: auto; flex-wrap: wrap;}
        .card-actions form {flex: 1;}
        .card-actions button {width: 100%; padding: 8px; border: none; border-radius: 5px; font-size: 14px; cursor: pointer;}
        .like-btn {background-color: red; color: white;}
        .save-btn {background-color: #3c99dc; color: white;}
        .buy-btn {background-color: green; color: white;}
        .back-btn {display: block; text-align: center; margin: 20px auto; padding: 10px 20px; background-color: #03AC0E; color: white; text-decoration: none; border-radius: 5px; width: fit-content;}
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="navbar">
        <a href="discover.php"><div style="font-size: 20px; font-weight: bold;">Tomboypedia</div></a>
        <form method="GET" action="discover.php">
            <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
            <input type="submit" value="Search">
        </form>
        <div>
            <a href="discover.php">Discover</a>
            <a href="saved.php">Cart</a>
            <a href="profile.php">My Profile</a>
            <a href="topup.php">Top Up</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <h1 class="title">Discover</h1>
    <div style="text-align: center; margin: 20px 0;">
        <form method="GET" action="discover.php" style="display: inline-block;">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <select name="category" onchange="this.form.submit()" style="padding: 8px 15px; border-radius: 5px; border: 1px solid #ddd;">
                <option value="all" <?php echo $selectedCategory === 'all' ? 'selected' : ''; ?>>All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $selectedCategory == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <form method="GET" action="discover.php" style="display: inline-block; margin-left: 10px;">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
            <select name="brand" onchange="this.form.submit()" style="padding: 8px 15px; border-radius: 5px; border: 1px solid #ddd;">
                <option value="all" <?php echo $selectedBrand === 'all' ? 'selected' : ''; ?>>All Brands</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?php echo $brand['id']; ?>" <?php echo $selectedBrand == $brand['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($brand['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <?php if (count($posts) == 0): ?>
        <h3 style="text-align: center;">Barang tidak ada</h3>
        <div style="text-align: center;">
            <a href="discover.php" class="back-btn">Kembali</a>
        </div>
    <?php else: ?>
        <div class="grid-container">
            <?php foreach ($posts as $post): ?>
                <div class="card">
                    <img  src="assets/<?php echo ($post['image']); ?>" alt="Post Image">
                    <div class="card-content">
                        <h3><?php echo ($post['title']); ?></h3>
                        <p><?php echo ($post['caption']); ?></p>
                        <p class="likes">Likes: <?php echo $post['like_count']; ?></p>
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
                        
                        <form method="GET" action="confirm_buy.php" style="margin-top: 10px;">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <button type="submit" class="buy-btn" style="width: 100%; height: 30px; border:none; border-radius:5px">
                                Buy - <?php echo $post['harga']; ?> IDR</button>
                        </form>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
   
</body>
</html>