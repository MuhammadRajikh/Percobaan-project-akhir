<?php
session_start();
require 'vendor/autoload.php';

// Memastikan user sudah login
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Koneksi ke MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->beanskopi;
$menuCollection = $database->menu;
$keranjangCollection = $database->keranjang;

// Mendapatkan keranjang user
$keranjang = $keranjangCollection->findOne(['userEmail' => $_SESSION['email']]);

// Mendapatkan daftar menu
$menuItems = $menuCollection->find();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Kopi</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .menu-container {
            max-width: 800px;
            margin-right: 320px;
        }
        
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: 300px;
            height: 100vh;
            background: #FFF9F0;
            box-shadow: -4px 0 8px rgba(44, 24, 16, 0.1);
            padding: 20px;
            overflow-y: auto;
            border-left: 2px solid #A67C52;
        }

        .cart-sidebar .header {
            border-bottom: 2px solid #A67C52;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .cart-sidebar .header h3 {
            margin: 0;
            font-size: 1.5em;
        }

        .cart-item {
            margin-bottom: 15px;
        }

        .cart-item-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .cart-item-info {
            margin-bottom: 10px;
        }

        .variant-container {
            margin: 15px 0;
        }

        .variant-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .variant-item form {
            margin: 0;
        }

        .menu-title {
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        .menu-info {
            margin-bottom: 15px;
        }

        .menu-info p {
            margin: 5px 0;
        }

        .cart-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px dashed #A67C52;
            text-align: right;
            font-weight: bold;
        }

        .btn-checkout {
            width: 100%;
            margin-top: 15px;
        }

        .btn-with-icon {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-add-cart {
            background-color: #68A357;
            border-color: #2C1810;
            padding: 8px 15px;
            font-size: 0.9em;
        }

        .btn-add-cart:hover {
            background-color: #557F47;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #A67C52;
        }

        .cart-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .cart-actions .btn {
            width: auto;
            padding: 8px 15px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="menu-container">
            <div class="header">
                <h2>Menu Kopi</h2>
                <a href="logout.php" class="btn btn-with-icon btn-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>

            <?php foreach ($menuItems as $item): ?>
                <div class="card">
                    <div class="menu-title"><?= $item['jenisBean'] ?></div>
                    <div class="menu-info">
                        <p><strong>Asal:</strong> <?= $item['asal'] ?></p>
                        <p><strong>Deskripsi:</strong> <?= $item['deskripsi'] ?></p>
                    </div>

                    <div class="variant-container">
                        <div class="variant-title">Pilih Varian:</div>
                        <?php foreach ($item['stok'] as $stok): ?>
                            <div class="variant-item">
                                <div>
                                    <span><?= $stok['gram'] ?> gram - </span>
                                    <span class="price-tag">Rp <?= number_format($stok['harga'], 0, ',', '.') ?></span>
                                </div>
                                <form onsubmit="addToCart(this); return false;">
                                    <input type="hidden" name="menuId" value="<?= $item['_id'] ?>">
                                    <input type="hidden" name="gram" value="<?= $stok['gram'] ?>">
                                    <input type="hidden" name="harga" value="<?= $stok['harga'] ?>">
                                    <button type="submit" class="btn btn-with-icon btn-add-cart">
                                        <i class="fas fa-plus"></i>
                                        Keranjang
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sidebar Keranjang -->
        <div class="cart-sidebar">
            <div class="header">
                <h3>Keranjang</h3>
            </div>

            <div id="cart-items">
                <?php if (isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang']['items'])): ?>
                    <?php foreach ($_SESSION['keranjang']['items'] as $index => $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-title"><?= $item['jenisBean'] ?></div>
                            <div class="cart-item-info">
                                <?= $item['gram'] ?> gram
                                <br>
                                <span class="price-tag">Rp <?= number_format($item['harga'], 0, ',', '.') ?></span>
                            </div>
                            <button onclick="removeFromCart(<?= $index ?>)" class="btn btn-with-icon btn-danger">
                                <i class="fas fa-trash"></i>
                                Hapus
                            </button>
                        </div>
                    <?php endforeach; ?>

                    <div class="cart-total">
                        Total: Rp <?= number_format($_SESSION['keranjang']['totalHarga'], 0, ',', '.') ?>
                    </div>

                    <button onclick="checkout()" class="btn btn-with-icon btn-success btn-checkout">
                        <i class="fas fa-shopping-cart"></i>
                        Checkout
                    </button>
                <?php else: ?>
                    <p class="text-center">Keranjang masih kosong</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function addToCart(formElement) {
        event.preventDefault();
        const formData = new FormData(formElement);
        
        fetch('add_to_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartDisplay(data.keranjang);
            }
        });
    }

    function removeFromCart(index) {
        fetch('remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'index=' + index
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartDisplay(data.keranjang);
            }
        });
    }

    function updateCartDisplay(keranjang) {
        const cartItems = document.getElementById('cart-items');
        let html = '';

        if (keranjang.items && keranjang.items.length > 0) {
            keranjang.items.forEach((item, index) => {
                html += `
                    <div class="cart-item">
                        <div class="cart-item-title">${item.jenisBean}</div>
                        <div class="cart-item-info">
                            ${item.gram} gram
                            <br>
                            <span class="price-tag">Rp ${new Intl.NumberFormat('id-ID').format(item.harga)}</span>
                        </div>
                        <button onclick="removeFromCart(${index})" class="btn btn-with-icon btn-danger">
                            <i class="fas fa-trash"></i>
                            Hapus
                        </button>
                    </div>
                `;
            });

            html += `
                <div class="cart-total">
                    Total: Rp ${new Intl.NumberFormat('id-ID').format(keranjang.totalHarga)}
                </div>
                <button onclick="checkout()" class="btn btn-with-icon btn-success btn-checkout">
                    <i class="fas fa-shopping-cart"></i>
                    Checkout
                </button>
            `;
        } else {
            html = '<p class="text-center">Keranjang masih kosong</p>';
        }

        cartItems.innerHTML = html;
    }

    function checkout() {
        fetch('checkout.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pesanan berhasil dicheckout!');
                updateCartDisplay({ items: [], totalHarga: 0 });
            }
        });
    }
    </script>
</body>
</html>
