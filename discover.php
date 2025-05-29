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
$searchQuery = '';
$posts = [];
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $searchQuery = trim($_GET['search']);
    $query = $pdo->prepare("
            SELECT posts.*, users.display_name, 
                   (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count, 
                   (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = :user_id) AS already_liked, 
                   (SELECT COUNT(*) FROM saved_posts WHERE saved_posts.post_id = posts.id AND saved_posts.user_id = :user_id) AS already_saved
            FROM posts
            JOIN users ON posts.user_id = users.id
            WHERE LOWER(posts.title) LIKE LOWER(:search_query)
            " . ($selectedCategory !== 'all' ? " AND posts.category_id = :category_id" : "") . "
            ORDER BY posts.id DESC
        ");
    $params = [
        ':user_id' => $user['id'],
        ':search_query' => "%$searchQuery%"
    ];
    if ($selectedCategory !== 'all') {
        $params[':category_id'] = $selectedCategory;
    }
    $query->execute($params);
    $posts = $query->fetchAll();
} else {
    $query = $pdo->prepare("
            SELECT posts.*, users.display_name, 
                   (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count, 
                   (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = :user_id) AS already_liked, 
                   (SELECT COUNT(*) FROM saved_posts WHERE saved_posts.post_id = posts.id AND saved_posts.user_id = :user_id) AS already_saved
            FROM posts
            JOIN users ON posts.user_id = users.id
            " . ($selectedCategory !== 'all' ? " WHERE posts.category_id = :category_id" : "") . "
            ORDER BY posts.id DESC
        ");
    $params = [
        ':user_id' => $user['id']
    ];
    if ($selectedCategory !== 'all') {
        $params[':category_id'] = $selectedCategory;
    }
    $query->execute($params);
    $posts = $query->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Discover</title>
    <style>
        /* Global Styles */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #4A00E0, #8E2DE2);
        }

        /* Navbar */
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

        /* Page Title */
        .title {
            color: white;
            font-size: 40px;
            text-align: center;
            margin: 40px 0 20px;
        }

        /* Filter Dropdown */
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

        /* Card Grid */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            padding: 0 40px 60px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Card Styles */
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

        /* Likes & Username */
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

        /* Actions */
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

        /* Back Button */
        .back-btn {
            display: block;
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
            background-color: #f1f1f1;
            text-align: center;
            padding: 1rem;
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>

<body>
    <div class="navbar">
        <a href="discover.php">
            <div style="font-size: 20px; font-weight: bold;">Tomboypedia</div>
        </a>
        <!-- <form method="GET" action="discover.php">
            <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
            <input type="submit" value="Search">
        </form> -->

        <form id="search-form" method="GET" action="discover.php">
            <input id="search-input" type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($searchQuery); ?>">
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

    <div id="mainCarousel" class="carousel slide mb-4" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="d-flex justify-content-center">
                    <img src="./assets/c.jpg" class="d-block w-75 rounded" style="height: 400px; object-fit: cover;" alt="Slide 1">
                </div>
                <div class="carousel-caption d-none d-md-block">
                    <h5>First slide label</h5>
                    <p>Some representative placeholder content for the first slide.</p>
                </div>
            </div>
            <div class="carousel-item">
                <div class="d-flex justify-content-center">
                    <img src="./assets/c.jpg" class="d-block w-75 rounded" style="height: 400px; object-fit: cover;" alt="Slide 2">
                </div>
                <div class="carousel-caption d-none d-md-block">
                    <h5>Second slide label</h5>
                    <p>Some representative placeholder content for the second slide.</p>
                </div>
            </div>
            <div class="carousel-item">
                <div class="d-flex justify-content-center">
                    <img src="./assets/c.jpg" class="d-block w-75 rounded" style="height: 400px; object-fit: cover;" alt="Slide 3">
                </div>
                <div class="carousel-caption d-none d-md-block">
                    <h5>Third slide label</h5>
                    <p>Some representative placeholder content for the third slide.</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Sebelumnya</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Selanjutnya</span>
        </button>
    </div>


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
                    <img src="assets/<?php echo ($post['image']); ?>" alt="Post Image">
                    <div class="card-content">
                        <h3><?php echo ($post['title']); ?></h3>
                        <p><?php echo ($post['caption']); ?></p>
                        <p class="likes">Likes: <?php echo $post['like_count']; ?></p>
                        <div class="card-actions">
                            <form action="likes.php" method="POST">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" class="like-btn">
                                    <?php echo ($post['already_liked'] > 0) ? 'Already Liked' : '❤️ Like'; ?>
                                </button>
                            </form>
                            <form action="saved_post.php" method="POST">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" class="save-btn">
                                    <?php echo ($post['already_saved'] > 0) ? 'Remove' : '🛒 Add to Cart'; ?>
                                </button>
                            </form>
                        </div>
                        <form action="buy_detail.php" method="GET" style="margin-top: 10px;">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="buy-btn" style="width: 100%; height: 30px; border:none; border-radius:5px">
                                Buy - <?php echo $post['harga']; ?> IDR
                            </button>
                        </form>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <footer>
            <p>&copy; 2025 Tomboypedia. All rights reserved.</p>
        </footer>

    <?php endif; ?>
    <script>
        $(document).ready(function() {
            $('.buy-btn').click(function() {
                var form = $(this).closest('.buy-form');
                var postId = form.find('input[name="buy_post_id"]').val();
                window.location.href = 'buy_details.php?post_id=' + postId;
            });
        });

        $(document).ready(function() {
            let typingTimer;
            const doneTypingInterval = 500; // ms

            $('#search-input').on('keyup', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    $('#search-form').submit();
                }, doneTypingInterval);
            });

            $('#search-input').on('keydown', function() {
                clearTimeout(typingTimer);
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</body>

</html>