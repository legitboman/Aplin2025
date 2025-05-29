<?php
require_once 'db.php';


if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

if (!isset($_GET['post_id'])) {
    header('Location: discover.php');
    exit;
}

$postId = $_GET['post_id'];

// Ambil detail produk
$postQuery = $pdo->prepare("
    SELECT posts.*, users.display_name AS seller_name, categories.name AS category_name 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    LEFT JOIN categories ON posts.category_id = categories.id 
    WHERE posts.id = ?
");
$postQuery->execute([$postId]);
$post = $postQuery->fetch();

if (!$post) {
    header('Location: discover.php');
    exit;
}

// Proses pembelian jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cityId = $_POST['city_id'] ?? '';

    // Validasi saldo
    if ($user['saldo'] >= $post['harga']) {
        try {
            $pdo->beginTransaction();

            // Kurangi saldo pembeli
            $updateBuyer = $pdo->prepare("UPDATE users SET saldo = saldo - ? WHERE id = ?");
            $updateBuyer->execute([$post['harga'], $user['id']]);

            // Tambah saldo penjual
            $updateSeller = $pdo->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?");
            $updateSeller->execute([$post['harga'], $post['user_id']]);

            // Catat transaksi
            $insertTransaction = $pdo->prepare("
                INSERT INTO TRANSACTION 
                (post_id, post_title, category_id, category_name, buyer_id, seller_id, price, transaction_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $insertTransaction->execute([
                $post['id'],
                $post['title'],
                $post['category_id'],
                $post['category_name'],
                $user['id'],
                $post['user_id'],
                $post['harga']
            ]);

            // Update stok
            $updateStock = $pdo->prepare("UPDATE posts SET stok = stok - 1 WHERE id = ?");
            $updateStock->execute([$post['id']]);

            $pdo->commit();

            // Update session saldo
            $_SESSION['user']['saldo'] = $user['saldo'] - $post['harga'];

            // Redirect dengan pesan sukses
            header("Location: discover.php?message=Pembelian+berhasil!");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    } else {
        $error = "Saldo tidak mencukupi!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Buy <?php echo htmlspecialchars($post['title']); ?></title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background-color: #f5f5f5; }
        .navbar { background-color: #03AC0E; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; margin: 0 10px; text-decoration: none; }
        .container { max-width: 800px; margin: 30px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .product-details { display: flex; gap: 20px; margin-bottom: 30px; }
        .product-image { flex: 1; max-width: 300px; }
        .product-image img { width: 100%; border-radius: 8px; }
        .product-info { flex: 2; }
        .product-title { font-size: 24px; margin-bottom: 10px; color: #333; }
        .product-price { font-size: 20px; color: #03AC0E; font-weight: bold; margin-bottom: 15px; }
        .product-seller { color: #666; margin-bottom: 15px; }
        .product-description { margin-bottom: 20px; line-height: 1.6; }
        .buy-form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group select, .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .buy-button { background: #03AC0E; color: white; border: none; padding: 12px 20px; font-size: 16px; border-radius: 4px; cursor: pointer; width: 100%; }
        .buy-button:hover { background: #028a0b; }
        .error { color: red; margin-bottom: 15px; }
        .loading { color: #666; font-style: italic; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="navbar">
        <a href="discover.php"><div style="font-size: 20px; font-weight: bold;">Tomboypedia</div></a>
        <div>
            <a href="discover.php">Discover</a>
            <a href="saved.php">Cart</a>
            <a href="profile.php">Profile</a>
            <a href="topup.php">Top Up</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="product-details">
            <div class="product-image">
                <img src="assets/<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" />
            </div>
            <div class="product-info">
                <h1 class="product-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                <div class="product-price"><?php echo number_format($post['harga'], 0, ',', '.'); ?> IDR</div>
                <div class="product-description">
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></p>
                    <p><strong>Description:</strong></p>
                    <p><?php echo htmlspecialchars($post['caption']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="buy-form">
            <h2>Complete Your Purchase</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div id="error-message" class="error" style="display:none;"></div>

            <form method="POST" id="purchaseForm">
                <div class="form-group">
                    <label for="city_id">Select Your City:</label>
                    <select id="city_id" name="city_id" required>
                        <option value="">-- Loading Cities... --</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Your Balance:</label>
                    <input type="text" value="<?php echo number_format($user['saldo'], 0, ',', '.'); ?> IDR" readonly />
                </div>
                
                <div class="form-group">
                    <label>Total Price:</label>
                    <input type="text" value="<?php echo number_format($post['harga'], 0, ',', '.'); ?> IDR" readonly />
                </div>
                
                <button type="submit" class="buy-button">Complete Purchase</button>
            </form>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        loadCities();
    });

    function showError(message) {
        $('#error-message').text(message).show();
    }

    function loadCities() {
  $.ajax({
    url: "get_city.php",
    method: "GET",
    dataType: "json",
    success: function(data) {
      const citySelect = $("#city_id");
      citySelect.empty().append('<option value="">-- Pilih Kota --</option>');
      
      if (Array.isArray(data)) {
        data.forEach(function(city) {
          citySelect.append(`<option value="${city.city_id}">${city.city_name}</option>`);
        });
      }
    },
    error: function() {
      loadFallbackCities(); // Gunakan data fallback
    }
  });
}

    
    </script>
</body>
</html>
