<?php
session_start();
require 'vendor/autoload.php'; // Include MongoDB client

// Koneksi ke database MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->beanskopi;
$usersCollection = $database->users; // Koleksi users

// Memastikan form login disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Mencari pengguna berdasarkan email
    $user = $usersCollection->findOne(['email' => $email]);

    if ($user && password_verify($password, $user['password'])) {
        // Set session untuk email dan role
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        // Redirect berdasarkan role
        if ($user['role'] == 'admin') {
            header("Location: admin_menu.php"); // Admin ke halaman menu admin
        } else {
            header("Location: user_menu.php"); // User ke halaman menu user
        }
        exit();
    } else {
        $error = "Email atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Beans Kopi</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-box {
            max-width: 400px;
            margin: 100px auto;
            background: #FFF9F0;
            padding: 30px;
            border: 2px solid #A67C52;
            border-radius: 12px;
            box-shadow: 6px 6px 0px #A67C52;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed #A67C52;
        }
        
        .login-header h2 {
            color: #2C1810;
            margin-bottom: 10px;
            font-size: 2em;
            text-shadow: 2px 2px 0px #D4A373;
        }
        
        .login-header p {
            color: #6B4423;
            font-size: 0.9em;
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
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #A67C52;
            border-radius: 8px;
            background: #FFF;
            color: #2C1810;
            font-size: 1em;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2C1810;
            box-shadow: 3px 3px 0px #A67C52;
        }
        
        .password-group {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #A67C52;
            padding: 5px;
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: #2C1810;
        }
        
        .login-btn {
            width: 100%;
            padding: 15px;
            font-size: 1.1em;
            margin-top: 20px;
            background: #A67C52;
            color: #FFF9F0;
            border: 2px solid #2C1810;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 3px 3px 0px #2C1810;
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            background: #8B5E3C;
            transform: translateY(-2px);
            box-shadow: 5px 5px 0px #2C1810;
        }

        .error-message {
            background: #F2DEDE;
            border: 1px solid #D35D47;
            color: #A94442;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="login-header">
                <h2>Beans Kopi</h2>
                <p>Silakan login untuk melanjutkan</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <div class="password-group">
                        <input type="password" id="password" name="password" required>
                        <i class="password-toggle fas fa-eye" onclick="togglePassword()"></i>
                    </div>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>
        </div>
    </div>

    <script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.querySelector('.password-toggle');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
    </script>
</body>
</html>
