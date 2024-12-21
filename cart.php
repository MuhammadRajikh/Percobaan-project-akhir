<?php
session_start();
require 'vendor/autoload.php'; // Include MongoDB client

// Koneksi ke database MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->beanskopi;
$keranjangCollection = $database->keranjang; // Koleksi keranjang

// Memastikan pengguna adalah user
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Mendapatkan keranjang untuk pengguna
$keranjangItems = $keranjangCollection->find(['userEmail' => $_SESSION['email']]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Keranjang Anda</h2>
    <a href="logout.php">Logout</a>
    <h3>Items di Keranjang</h3>
    <div id="keranjangItems">
        <?php 
        $hasItems = false;
        foreach ($keranjangItems as $keranjang): 
            $hasItems = true;
            foreach ($keranjang['items'] as $item):
        ?>
            <div>
                <p><?= $item['jenisBean'] ?> - <?= $item['gram'] ?>g - Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
            </div>
        <?php 
            endforeach;
        endforeach;
        
        if (!$hasItems):
        ?>
            <p>Keranjang masih kosong</p>
        <?php endif; ?>
    </div>
</body>
</html>
