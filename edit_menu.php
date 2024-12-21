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
$menuCollection = $database->menu;

// Mendapatkan ID menu dari parameter URL
$menuId = $_GET['id'];

// Mendapatkan detail menu
try {
    $menu = $menuCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($menuId)]);
    if (!$menu) {
        header("Location: admin_menu.php?error=Menu tidak ditemukan");
        exit();
    }
} catch (Exception $e) {
    header("Location: admin_menu.php?error=Invalid menu ID");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #A67C52;
        }

        .form-header h2 {
            margin: 0;
        }

        .btn-with-icon {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px dashed #A67C52;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2C1810;
            font-weight: bold;
        }

        .stok-container {
            background: #FFE8CC;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px dashed #A67C52;
        }

        .stok-title {
            font-family: 'DM Serif Display', serif;
            color: #2C1810;
            font-size: 1.2em;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container card">
            <div class="form-header">
                <h2>Edit Menu</h2>
                <a href="admin_menu.php" class="btn btn-with-icon">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
            </div>

            <form action="update_menu.php" method="POST">
                <input type="hidden" name="id" value="<?= $menu['_id'] ?>">
                
                <div class="form-group">
                    <label for="jenisBean">Jenis Bean:</label>
                    <input type="text" id="jenisBean" name="jenisBean" value="<?= htmlspecialchars($menu['jenisBean']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="asal">Asal:</label>
                    <input type="text" id="asal" name="asal" value="<?= htmlspecialchars($menu['asal']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" required><?= htmlspecialchars($menu['deskripsi']) ?></textarea>
                </div>

                <div class="stok-container">
                    <div class="stok-title">Varian 250 gram</div>
                    <input type="hidden" name="gram250" value="250">
                    <div class="form-group">
                        <label for="harga250">Harga (Rp):</label>
                        <input type="number" id="harga250" name="harga250" value="<?= $menu['stok'][0]['harga'] ?>" required>
                    </div>
                </div>

                <div class="stok-container">
                    <div class="stok-title">Varian 500 gram</div>
                    <input type="hidden" name="gram500" value="500">
                    <div class="form-group">
                        <label for="harga500">Harga (Rp):</label>
                        <input type="number" id="harga500" name="harga500" value="<?= $menu['stok'][1]['harga'] ?>" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-with-icon btn-success">
                        <i class="fas fa-save"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
