<?php
/**
 * Logout Script - Fitness Sentot
 * 
 * Script untuk menangani proses logout pengguna
 * Membersihkan semua session dan redirect ke halaman login
 */

// Start session
session_start();

// Log activity sebelum logout
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    $username = $_SESSION['username'] ?? 'Unknown';
    
    // Log logout activity (bisa disimpan di database jika diperlukan)
    error_log("User logout: ID=$user_id, Role=$role, Username=$username, Time=" . date('Y-m-d H:i:s'));
}

// Hapus semua session variables
$_SESSION = array();

// Hapus session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman login dengan pesan logout
header("Location: login.php?message=logout_success");
exit();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Fitness Sentot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-600 to-blue-900 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <div class="loading-spinner mx-auto mb-4"></div>
        
        <i class="fas fa-sign-out-alt text-4xl text-blue-600 mb-4"></i>
        
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Sedang Keluar...</h1>
        
        <p class="text-gray-600 mb-6">
            Anda sedang keluar dari sistem. Harap tunggu sebentar.
        </p>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-center">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                <p class="text-sm text-blue-700">
                    Anda akan diarahkan ke halaman login dalam beberapa detik.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Redirect otomatis setelah 2 detik
        setTimeout(function() {
            window.location.href = 'login.php?message=logout_success';
        }, 2000);

        // Fallback redirect jika JavaScript disabled
        setTimeout(function() {
            window.location.href = 'login.php?message=logout_success';
        }, 3000);
    </script>
</body>
</html>