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
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
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

    header('Content-Type: application/json');
    echo json_encode($posts);
    exit;
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

$fakeStoreData = [];
try {
    $apiResponse = file_get_contents("https://fakestoreapi.com/products");
    $fakeStoreData = json_decode($apiResponse, true);
} catch (Exception $e) {
    error_log("FakeStoreAPI Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Discover</title>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #4A00E0;">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="discover.php">Tomboypedia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <form class="d-flex mx-auto my-2 my-lg-0" method="GET" action="discover.php" style="width: 100%; max-width: 500px;">
                    <input class="form-control me-2" type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                    <input type="hidden" name="brand" value="<?php echo htmlspecialchars($selectedBrand); ?>">
                    <button class="btn btn-light" type="submit">Search</button>
                </form>
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 text-center">
                    <li class="nav-item"><a class="nav-link" href="discover.php">Discover</a></li>
                    <li class="nav-item"><a class="nav-link" href="saved.php">Cart</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">My Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="topup.php">Top Up</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <br>
    <div id="mainCarousel" class="carousel slide mb-4" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="d-flex justify-content-center">
                    <img src="./assets/c.jpg" class="d-block w-75 rounded" style="height: 400px; object-fit: cover;" alt="Slide 1">
                </div>
            </div>
            <div class="carousel-item">
                <div class="d-flex justify-content-center">
                    <img src="./assets/s.jpg" class="d-block w-75 rounded" style="height: 400px; object-fit: cover;" alt="Slide 2">
                </div>
            </div>
            <div class="carousel-item">
                <div class="d-flex justify-content-center">
                    <img src="./assets/b.jpg" class="d-block w-75 rounded" style="height: 400px; object-fit: cover;" alt="Slide 3">
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <h1 class="title">Discover</h1>
    <div style="text-align: center; margin: 20px 0;">
        <form method="GET" action="discover.php" style="display: inline-block;">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <select name="category" onchange="this.form.submit()" style="padding: 8px 15px; border-radius: 8px; border: 1px solid #ddd;">
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
            <select name="brand" onchange="this.form.submit()" style="padding: 8px 15px; border-radius: 8px; border: 1px solid #ddd;">
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
        <h3 style="text-align: center; color: white;">No items found</h3>
        <div style="text-align: center;">
            <a href="discover.php" class="back-btn">Back to Discover</a>
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
                                Buy - <?php echo $post['total']; ?> IDR</button>
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
            const searchInput = $('input[name="search"]');
            const categorySelect = $('select[name="category"]');
            const brandSelect = $('select[name="brand"]');
            const gridContainer = $('.grid-container');

            function performSearch() {
                const searchTerm = searchInput.val().trim();
                const category = categorySelect.val();
                const brand = brandSelect.val();
                $.ajax({
                    url: 'discover.php?ajax=true',
                    method: 'GET',
                    data: {
                        search: searchTerm,
                        category: category,
                        brand: brand
                    },
                    success: function(response) {
                        if (response.length === 0) {
                            gridContainer.html('<h3 style="text-align: center; color: white;">No items found</h3><div style="text-align: center;"><a href="discover.php" class="back-btn">Back to Discover</a></div>');
                            return;
                        }
                        let html = '';
                        response.forEach(post => {
                            html += `
                                <div class="card">
                                    <img src="assets/${post.image}" alt="Post Image">
                                    <div class="card-content">
                                        <h3>${post.title}</h3>
                                        <p>${post.caption}</p>
                                        <p class="likes">Likes: ${post.like_count}</p>
                                        <div class="card-actions">
                                            <form action="likes.php" method="POST">
                                                <input type="hidden" name="post_id" value="${post.id}">
                                                <button type="submit" class="like-btn">
                                                    ${post.already_liked > 0 ? 'Already Liked' : 'Like'}
                                                </button>
                                            </form>
                                            <form action="saved_post.php" method="POST">
                                                <input type="hidden" name="post_id" value="${post.id}">
                                                <button type="submit" class="save-btn">
                                                    ${post.already_saved > 0 ? 'Already Saved' : 'Save'}
                                                </button>
                                            </form>
                                        </div>
                                        <form method="GET" action="confirm_buy.php" style="margin-top: 10px;">
                                            <input type="hidden" name="post_id" value="${post.id}">
                                            <button type="submit" class="buy-btn" style="width: 100%; height: 30px; border:none; border-radius:5px">
                                                Buy - ${post.total} IDR</button>
                                        </form>
                                    </div>
                                </div>
                            `;
                        });
                        gridContainer.html(html);
                    },
                    error: function() {
                        alert('Error occurred while searching');
                    }
                });
            }
            let searchTimer;
            searchInput.on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(performSearch, 300);
            });
            categorySelect.add(brandSelect).on('change', function() {
                performSearch();
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>