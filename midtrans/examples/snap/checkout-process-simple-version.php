<?php
namespace Midtrans;


require_once dirname(__FILE__) . '/../../Midtrans.php';
Config::$serverKey = 'SB-Mid-server-MXzIVSzZ_c2ZfzTgDhBmNAjF';
Config::$clientKey = 'SB-Mid-client-6YePO6WHXtRjMMQH';

Config::$isSanitized = Config::$is3ds = true;

include "../../../db.php";

// Ambil data transaksi terakhir (ID terbesar)
$stmt = $pdo->query("SELECT * FROM `transaction` ORDER BY id DESC LIMIT 1");
$data = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$data) {
    die("Data transaksi terakhir tidak ditemukan.");
}

$order_id = $data['id'];
$harga = $data['price'];

// Data dari session login user
$nama  = isset($_SESSION['name']) ? $_SESSION['name'] : 'Pelanggan';
$email = (isset($_SESSION['email']) && filter_var($_SESSION['email'], FILTER_VALIDATE_EMAIL)) 
         ? $_SESSION['email'] 
         : 'guest@example.com';

// Detail transaksi untuk Midtrans
$transaction_details = array(
    'order_id' => $order_id,
    'gross_amount' => $harga,
);

$item_details = array(
    array(
        'id' => 'item1',
        'price' => $harga,
        'quantity' => 1,
        'name' => $data['post_title'] ?? "Pembayaran"
    ),
);

$customer_details = array(
    'first_name' => $nama,
    'last_name' => '',
    'email' => $email,
    'phone' => ''
);

$transaction = array(
    'transaction_details' => $transaction_details,
    'item_details' => $item_details,
    'customer_details' => $customer_details
);

$snap_token = '';
try {
    $snap_token = Snap::getSnapToken($transaction);
} catch (\Exception $e) {
    echo $e->getMessage();
    exit;
}

function printExampleWarningMessage() {
    if (strpos(Config::$serverKey, 'your ') !== false) {
        echo "<code>";
        echo "<h4>Please set your server key from sandbox</h4>";
        echo "In file: " . __FILE__;
        echo "<br><br>";
        echo htmlspecialchars('Config::$serverKey = \'<server key anda di midtrans>\';');
        die();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pembayaran</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<br><br>
<div class="container">
    <div class="card">
        <div class="card-body">
            <p>Registrasi Berhasil, Selesaikan Pembayaran Sekarang</p>
            <button id="pay-button" class="btn btn-primary">PILIH METODE PEMBAYARAN</button>

            <script src="https://app.sandbox.midtrans.com/snap/snap.js"
                data-client-key="<?php echo Config::$clientKey; ?>"></script>
            <script type="text/javascript">
                document.getElementById('pay-button').onclick = function () {
                    snap.pay('<?php echo $snap_token ?>');
                };
            </script>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
