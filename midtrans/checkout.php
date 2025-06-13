<?php
namespace Midtrans;

require_once dirname(__FILE__) . '/../Midtrans.php';
require_once '../../db.php';
session_start();

// Configure Midtrans
Config::$serverKey = 'SB-Mid-server-MXzIVSzZ_c2ZfzTgDhBmNAjF';
Config::$clientKey = 'SB-Mid-client-6YePO6WHXtRjMMQH';
Config::$isSanitized = Config::$is3ds = true;

// Get transaction IDs
$transactionIds = explode(',', $_GET['transaction_ids'] ?? '');
if (empty($transactionIds)) {
    die("Invalid transaction IDs");
}

// Get transactions data
$placeholders = implode(',', array_fill(0, count($transactionIds), '?'));
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id IN ($placeholders)");
$stmt->execute($transactionIds);
$transactions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

if (empty($transactions)) {
    die("No transactions found");
}

// Prepare Midtrans data
$total = 0;
$items = [];
foreach ($transactions as $t) {
    $total += $t['price'];
    $items[] = [
        'id' => 'item-'.$t['id'],
        'price' => $t['price'],
        'quantity' => 1,
        'name' => $t['post_title'] ?: "Product Purchase"
    ];
}

// Customer data
$customer = [
    'first_name' => $_SESSION['user']['name'] ?? 'Customer',
    'last_name' => '',
    'email' => $_SESSION['user']['email'] ?? 'customer@example.com',
    'phone' => $_SESSION['user']['phone'] ?? ''
];

// Transaction details
$transaction = [
    'transaction_details' => [
        'order_id' => 'TMBP-'.time().'-'.implode('-', $transactionIds),
        'gross_amount' => $total
    ],
    'item_details' => $items,
    'customer_details' => $customer,
    'callbacks' => [
        'finish' => 'http://yourwebsite.com/payment_finish.php'
    ]
];

// Get Snap Token
try {
    $snapToken = Snap::getSnapToken($transaction);
} catch (\Exception $e) {
    die("Midtrans Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .payment-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        #pay-button {
            width: 100%;
            padding: 12px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-container">
            <h2 class="text-center mb-4">Complete Your Payment</h2>
            
            <div class="payment-summary">
                <h4>Order Summary</h4>
                <ul class="list-group mb-3">
                    <?php foreach ($transactions as $t): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= htmlspecialchars($t['post_title']) ?></span>
                        <span>Rp <?= number_format($t['price'], 0, ',', '.') ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Total:</span>
                    <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                </div>
            </div>
            
            <button id="pay-button" class="btn btn-primary btn-lg">PAY NOW</button>
            
            <div id="payment-error" class="alert alert-danger mt-3 d-none"></div>
        </div>
    </div>

    <script src="https://app.sandbox.midtrans.com/snap/snap.js" 
        data-client-key="<?= Config::$clientKey ?>"></script>
    <script>
    document.getElementById('pay-button').onclick = function() {
        snap.pay('<?= $snapToken ?>', {
            onSuccess: function(result) {
                window.location.href = 'payment_success.php?order_id=' + result.order_id;
            },
            onPending: function(result) {
                window.location.href = 'payment_pending.php?order_id=' + result.order_id;
            },
            onError: function(result) {
                document.getElementById('payment-error').classList.remove('d-none');
                document.getElementById('payment-error').innerText = 
                    'Payment failed: ' + (result.status_message || 'Unknown error');
            },
            onClose: function() {
                // Optional: Handle when user closes payment popup
            }
        });
    };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>