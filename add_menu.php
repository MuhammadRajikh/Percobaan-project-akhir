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

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validasi input
        if (empty($_POST['jenisBean']) || empty($_POST['asal']) || empty($_POST['deskripsi']) || 
            empty($_POST['harga250']) || empty($_POST['harga500'])) {
            throw new Exception("Semua field harus diisi!");
        }

        // Data yang akan disimpan
        $menuData = [
            'jenisBean' => $_POST['jenisBean'],
            'asal' => $_POST['asal'],
            'deskripsi' => $_POST['deskripsi'],
            'stok' => [
                [
                    'gram' => 250,
                    'harga' => (int)$_POST['harga250']
                ],
                [
                    'gram' => 500,
                    'harga' => (int)$_POST['harga500']
                ]
            ]
        ];

        // Simpan ke database
        $result = $menuCollection->insertOne($menuData);

        if ($result->getInsertedId()) {
            header("Location: admin_menu.php");
            exit();
        } else {
            throw new Exception("Gagal menyimpan data ke database");
        }

    } catch (Exception $e) {
        echo "<div style='color: red; margin: 10px 0;'>";
        echo "Error: " . $e->getMessage() . "<br>";
        echo "</div>";
        echo "<a href='javascript:history.back()'>Kembali ke form</a>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Menu - Admin</title>
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
            justify-content: space-between;
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
                <h2>Tambah Menu Baru</h2>
                <a href="admin_menu.php" class="btn btn-with-icon">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
            </div>

            <form action="add_menu.php" method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="jenisBean">Jenis Bean:</label>
                    <input type="text" name="jenisBean" id="jenisBean" required>
                </div>

                <div class="form-group">
                    <label for="asal">Asal:</label>
                    <input type="text" name="asal" id="asal" required>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi:</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" required></textarea>
                </div>

                <div class="stok-container">
                    <div class="stok-title">Varian 250 gram</div>
                    <div class="form-group">
                        <label for="harga250">Harga (Rp):</label>
                        <input type="number" name="harga250" id="harga250" min="0" required>
                    </div>
                </div>

                <div class="stok-container">
                    <div class="stok-title">Varian 500 gram</div>
                    <div class="form-group">
                        <label for="harga500">Harga (Rp):</label>
                        <input type="number" name="harga500" id="harga500" min="0" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-with-icon btn-success">
                        <i class="fas fa-save"></i>
                        Simpan Menu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function validateForm() {
        var jenisBean = document.getElementById('jenisBean').value;
        var asal = document.getElementById('asal').value;
        var deskripsi = document.getElementById('deskripsi').value;
        var harga250 = document.getElementById('harga250').value;
        var harga500 = document.getElementById('harga500').value;

        if (!jenisBean || !asal || !deskripsi || !harga250 || !harga500) {
            alert('Semua field harus diisi!');
            return false;
        }

        if (harga250 <= 0 || harga500 <= 0) {
            alert('Harga harus lebih dari 0!');
            return false;
        }

        return true;
    }
    </script>
</body>
</html>
