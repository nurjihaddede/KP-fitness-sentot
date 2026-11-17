<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: member/dashboard.php");
    }
    exit();
}

$error = '';
if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $database = new Database();
    $conn = $database->getConnection();
    
    // Cek admin
    $query = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['nama'] = $admin['nama_lengkap'];
            $_SESSION['role'] = 'admin';
            header("Location: admin/dashboard.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        // Cek member
        $query = "SELECT * FROM member WHERE kode_member = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            // Untuk demo, password member = kode member
            if ($password === $member['kode_member']) {
                $_SESSION['user_id'] = $member['id'];
                $_SESSION['username'] = $member['kode_member'];
                $_SESSION['nama'] = $member['nama'];
                $_SESSION['role'] = 'member';
                header("Location: member/dashboard.php");
                exit();
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    }
}

// Handle logout message
if (isset($_GET['message']) && $_GET['message'] == 'logout_success') {
    $success = "Anda telah berhasil logout dari sistem.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="description" content="Login ke Sistem Manajemen Member Fitness Sentot">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/icon-32x32.png">
    <title>Login - Fitness Sentot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-600 to-blue-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-6 md:p-8 rounded-lg shadow-lg w-full max-w-md">
        <div class="text-center mb-8">
            <i class="fas fa-dumbbell text-blue-600 text-5xl mb-4"></i>
            <h1 class="text-3xl font-bold text-gray-800">Fitness Sentot</h1>
            <p class="text-gray-600 mt-2">Sistem Manajemen Member</p>
        </div>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                    Username / Kode Member
                </label>
                <input type="text" id="username" name="username" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Password
                </label>
                <input type="password" id="password" name="password" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <button type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline transition duration-200 touch-manipulation min-h-[44px]">
                <i class="fas fa-sign-in-alt mr-2"></i>Masuk
            </button>
        </form>
        
        <div class="mt-6 text-center text-xs md:text-sm text-gray-600">
            <p class="mb-2">Demo Login:</p>
            <p class="mb-1"><strong>Admin:</strong> username: admin, password: password</p>
            <p><strong>Member:</strong> username: MBR001, password: MBR001</p>
        </div>
    </div>
    
    <script>
    // Service Worker Registration untuk PWA
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('sw.js')
                .then(function(registration) {
                    console.log('ServiceWorker registered');
                })
                .catch(function(err) {
                    console.log('ServiceWorker registration failed');
                });
        });
    }
    </script>
</body>
</html>