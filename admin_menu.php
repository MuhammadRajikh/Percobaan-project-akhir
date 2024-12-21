<?php
session_start();
require 'vendor/autoload.php';

// Koneksi ke database MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->beanskopi;
$menuCollection = $database->menu;

// Memastikan pengguna adalah admin
if (!isset($_SESSION['email']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Menghapus menu jika ada permintaan
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $menuId = $_GET['id'];
    $menuCollection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($menuId)]);
    header("Location: admin_menu.php");
    exit();
}

// Mendapatkan daftar menu
$menuItems = $menuCollection->find();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .header-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .btn-with-icon {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-add {
            background-color: #68A357;
            border-color: #2C1810;
        }

        .btn-add:hover {
            background-color: #557F47;
        }

        .btn-cart {
            background-color: #D4A373;
            border-color: #2C1810;
        }

        .btn-cart:hover {
            background-color: #B88B61;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px dashed #A67C52;
        }

        .btn-edit, .btn-delete {
            padding: 8px 15px;
            font-size: 0.9em;
        }

        .btn-edit {
            background-color: #3498db;
            border-color: #2C1810;
        }

        .btn-edit:hover {
            background-color: #2980b9;
        }

        .btn-delete {
            background-color: #e74c3c;
            border-color: #2C1810;
        }

        .btn-delete:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Menu Admin</h2>
        
        <div class="header-buttons">
            <a href="add_menu.php" class="btn btn-with-icon btn-add">
                <i class="fas fa-plus"></i>
                Tambah Menu Baru
            </a>
            <a href="admin_cart.php" class="btn btn-with-icon btn-cart">
                <i class="fas fa-shopping-cart"></i>
                Lihat Keranjang
            </a>
            <a href="logout.php" class="btn btn-with-icon btn-danger">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>

        <div id="menuItems">
            <?php foreach ($menuItems as $item): ?>
                <div class="card">
                    <h4 class="menu-title"><?= $item['jenisBean'] ?></h4>
                    <div class="menu-info">
                        <p><strong>Asal:</strong> <?= $item['asal'] ?></p>
                        <p><strong>Deskripsi:</strong> <?= $item['deskripsi'] ?></p>
                    </div>
                    
                    <?php if (isset($item['stok'])): ?>
                        <div class="variant-container">
                            <div class="variant-title">Varian Stok:</div>
                            <?php foreach ($item['stok'] as $stok): ?>
                                <div class="variant-item">
                                    <?= $stok['gram'] ?> gram - Rp <?= number_format($stok['harga'], 0, ',', '.') ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="action-buttons">
                        <a href="edit_menu.php?id=<?= $item['_id'] ?>" class="btn btn-with-icon btn-edit">
                            <i class="fas fa-edit"></i>
                            Edit
                        </a>
                        <a href="admin_menu.php?id=<?= $item['_id'] ?>" 
                           onclick="return confirm('Yakin ingin menghapus menu ini?')" 
                           class="btn btn-with-icon btn-delete">
                            <i class="fas fa-trash"></i>
                            Hapus
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
