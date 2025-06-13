<?php
require_once 'db.php'; // pastikan ini sudah konek ke $pdo

// Ambil data dari Fake Store API
$json = file_get_contents('https://fakestoreapi.com/products');
$products = json_decode($json, true);

if (!$products) {
    die("Gagal mengambil data dari Fake Store API");
}

// Mapping kategori dari API ke ID kategori lokal
$categoryMap = [
    'electronics' => 1,
    'jewelery' => 2,
    "men's clothing" => 3,
    "women's clothing" => 4
];

// ID user default yang dianggap sebagai pengunggah (pastikan ID ini ada di tabel users)
$defaultUserId = 1;

// Counter berhasil / gagal
$inserted = 0;
$skipped = 0;

foreach ($products as $product) {
    $title = $product['title'];
    $caption = $product['description'];
    $image = $product['image'];
    $harga = (int) ($product['price'] * 15000); // Ubah dari USD ke IDR
    $stok = rand(5, 50);
    $apiCategory = $product['category'];
    
    // Pastikan ada kategori
    if (!isset($categoryMap[$apiCategory])) {
        $skipped++;
        continue;
    }
    $categoryId = $categoryMap[$apiCategory];

    // Cek apakah produk sudah pernah dimasukkan (misalnya cek berdasarkan title)
    $check = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE title = :title");
    $check->execute([':title' => $title]);
    if ($check->fetchColumn() > 0) {
        $skipped++;
        continue;
    }

    // Masukkan ke tabel posts
    $stmt = $pdo->prepare("
        INSERT INTO posts (user_id, title, caption, image, harga, stok, category_id)
        VALUES (:user_id, :title, :caption, :image, :harga, :stok, :category_id)
    ");
    $stmt->execute([
        ':user_id' => $defaultUserId,
        ':title' => $title,
        ':caption' => $caption,
        ':image' => $image,
        ':harga' => $harga,
        ':stok' => $stok,
        ':category_id' => $categoryId
    ]);

    $inserted++;
}

echo "<h3>Import selesai</h3>";
echo "<p>Produk berhasil dimasukkan: $inserted</p>";
echo "<p>Produk dilewati (karena duplikat/kategori tidak cocok): $skipped</p>";
?>