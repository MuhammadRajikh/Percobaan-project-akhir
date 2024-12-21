<?php
session_start();

// Memastikan user sudah login
if (!isset($_SESSION['email'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Memastikan ada data yang dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['index'])) {
    $index = (int)$_POST['index'];
    
    if (isset($_SESSION['keranjang']) && isset($_SESSION['keranjang']['items'][$index])) {
        // Kurangi total harga
        $_SESSION['keranjang']['totalHarga'] -= $_SESSION['keranjang']['items'][$index]['harga'];
        
        // Hapus item dari array
        array_splice($_SESSION['keranjang']['items'], $index, 1);
        
        // Jika keranjang kosong, reset keranjang
        if (empty($_SESSION['keranjang']['items'])) {
            unset($_SESSION['keranjang']);
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'keranjang' => isset($_SESSION['keranjang']) ? $_SESSION['keranjang'] : ['items' => [], 'totalHarga' => 0]
        ]);
        exit();
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit();
?> 