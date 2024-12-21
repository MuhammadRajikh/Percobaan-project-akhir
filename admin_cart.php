<?php
session_start();
require 'vendor/autoload.php';

// Memastikan pengguna adalah admin
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Koneksi ke MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->beanskopi;
$keranjangCollection = $database->keranjang;
$usersCollection = $database->users;

// Mendapatkan semua keranjang dengan informasi user menggunakan $lookup
$keranjangItems = $keranjangCollection->aggregate([
    [
        '$lookup' => [
            'from' => 'users',
            'localField' => 'userEmail',
            'foreignField' => 'email',
            'as' => 'user_info'
        ]
    ],
    [
        '$sort' => ['tanggalCheckout' => -1]
    ]
]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .customer-info {
            background: #FFE8CC;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .customer-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #A67C52;
        }

        .customer-details p {
            margin: 5px 0;
            color: #6B4423;
        }
        
        .order-date {
            color: #6B4423;
            font-size: 0.9em;
            margin-top: 5px;
            font-style: italic;
        }
        
        .items-title {
            font-size: 1.2em;
            margin: 20px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #D4A373;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            background: #A67C52;
            color: #FFF9F0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Daftar Pesanan</h2>
            <a href="admin_menu.php" class="btn btn-with-icon">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Menu
            </a>
        </div>

        <?php
        $hasItems = false;
        foreach ($keranjangItems as $keranjang):
            $hasItems = true;
            $user = isset($keranjang['user_info'][0]) ? $keranjang['user_info'][0] : null;
            $tanggalOrder = $keranjang['tanggalCheckout']->toDateTime();
        ?>
            <div class="card">
                <div class="customer-info">
                    <div class="customer-header">
                        <strong>Pembeli:</strong> <?= $user ? $user['nama'] : 'Pengguna' ?>
                        <span class="badge"><?= $user ? $user['role'] : 'unknown' ?></span>
                    </div>
                    <?php if ($user): ?>
                    <div class="customer-details">
                        <p><i class="fas fa-envelope"></i> <?= $user['email'] ?></p>
                        <p><i class="fas fa-clock"></i> Member sejak: <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
                    </div>
                    <?php endif; ?>
                    <div class="order-date">
                        <i class="fas fa-calendar"></i>
                        Tanggal Order: <?= $tanggalOrder->format('d/m/Y H:i:s') ?>
                    </div>
                </div>

                <div class="cart-items">
                    <div class="items-title">
                        <i class="fas fa-shopping-basket"></i>
                        Daftar Pesanan:
                    </div>
                    <?php foreach ($keranjang['items'] as $item): ?>
                        <div class="item-details">
                            <p class="item-name"><?= $item['jenisBean'] ?></p>
                            <p>Ukuran: <?= $item['gram'] ?> gram</p>
                            <p class="price-tag">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                        </div>
                        <?php if (next($keranjang['items'])): ?>
                            <div class="divider"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="cart-total">
                    Total Pesanan: Rp <?= number_format($keranjang['totalHarga'], 0, ',', '.') ?>
                </div>
            </div>
        <?php 
        endforeach;
        
        if (!$hasItems):
        ?>
            <div class="empty-cart">
                <h3>Tidak ada pesanan saat ini</h3>
                <p>Belum ada pesanan yang masuk.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 