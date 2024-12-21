<?php
session_start();
require 'vendor/autoload.php';

// Memastikan pengguna adalah admin
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Memastikan form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Koneksi ke MongoDB
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->beanskopi;
    $menuCollection = $database->menu;

    // Mendapatkan data dari form
    $id = $_POST['id'];
    $jenisBean = $_POST['jenisBean'];
    $asal = $_POST['asal'];
    $deskripsi = $_POST['deskripsi'];
    $harga250 = (int)$_POST['harga250'];
    $harga500 = (int)$_POST['harga500'];

    try {
        // Konversi string ID ke ObjectId
        $objectId = new MongoDB\BSON\ObjectId((string)$id);
        
        // Update menu
        $result = $menuCollection->updateOne(
            ['_id' => $objectId],
            [
                '$set' => [
                    'jenisBean' => $jenisBean,
                    'asal' => $asal,
                    'deskripsi' => $deskripsi,
                    'stok' => [
                        [
                            'gram' => 250,
                            'harga' => $harga250
                        ],
                        [
                            'gram' => 500,
                            'harga' => $harga500
                        ]
                    ]
                ]
            ]
        );

        if ($result->getModifiedCount() > 0 || $result->getMatchedCount() > 0) {
            // Redirect ke halaman menu admin dengan pesan sukses
            header("Location: admin_menu.php?message=Menu berhasil diupdate");
            exit();
        } else {
            // Tidak ada dokumen yang diupdate
            header("Location: admin_menu.php?error=Menu tidak ditemukan");
            exit();
        }
    } catch (MongoDB\Driver\Exception\InvalidArgumentException $e) {
        // Error saat konversi ObjectId
        header("Location: admin_menu.php?error=ID menu tidak valid");
        exit();
    } catch (Exception $e) {
        // Error lainnya
        header("Location: admin_menu.php?error=Gagal mengupdate menu: " . $e->getMessage());
        exit();
    }
}

// Jika ada error atau tidak ada POST data
header("Location: admin_menu.php?error=Invalid request");
exit();
?> 