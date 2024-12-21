<?php
session_start();
require 'vendor/autoload.php';

// Memastikan user sudah login
if (!isset($_SESSION['email'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Memastikan ada keranjang
if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang']['items'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Keranjang kosong']);
    exit();
}

// Koneksi ke MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->beanskopi;
$keranjangCollection = $database->keranjang;

try {
    // Simpan keranjang ke database
    $result = $keranjangCollection->insertOne([
        'userEmail' => $_SESSION['email'],
        'items' => $_SESSION['keranjang']['items'],
        'totalHarga' => $_SESSION['keranjang']['totalHarga'],
        'tanggalCheckout' => new MongoDB\BSON\UTCDateTime()
    ]);

    if ($result->getInsertedCount() > 0) {
        // Hapus keranjang dari session
        unset($_SESSION['keranjang']);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error saat checkout']);
    exit();
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Gagal checkout']);
exit();
?> 