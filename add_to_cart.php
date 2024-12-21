<?php
session_start();
require 'vendor/autoload.php';

// Memastikan user sudah login
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Memastikan ada data yang dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Koneksi ke MongoDB untuk mendapatkan detail menu
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->beanskopi;
    $menuCollection = $database->menu;

    // Mendapatkan data dari form
    $menuId = $_POST['menuId'];
    $gram = (int)$_POST['gram'];
    $harga = (int)$_POST['harga'];

    // Mendapatkan detail menu
    $menu = $menuCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($menuId)]);

    if ($menu) {
        // Inisialisasi keranjang session jika belum ada
        if (!isset($_SESSION['keranjang'])) {
            $_SESSION['keranjang'] = [
                'items' => [],
                'totalHarga' => 0
            ];
        }

        // Tambah item ke keranjang session
        $_SESSION['keranjang']['items'][] = [
            'jenisBean' => $menu['jenisBean'],
            'gram' => $gram,
            'harga' => $harga
        ];
        $_SESSION['keranjang']['totalHarga'] += $harga;

        // Return JSON response untuk AJAX
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'keranjang' => $_SESSION['keranjang']
        ]);
        exit();
    }
}

// Jika ada error, return error JSON
header('Content-Type: application/json');
echo json_encode(['success' => false]);
exit();
?> 