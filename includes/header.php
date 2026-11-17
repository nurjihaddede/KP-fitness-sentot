<?php
$page_title = isset($page_title) ? $page_title : "Fitness Sentot";
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
    <meta name="apple-mobile-web-app-title" content="Fitness Sentot">
    <meta name="description" content="Sistem Manajemen Member Fitness Sentot - Aplikasi untuk mengelola member, pembayaran, dan kehadiran">
    <meta name="keywords" content="fitness, gym, member management, fitness sentot">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="../manifest.json">
    
    <!-- Favicon & Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/icons/icon-32x32.png">
    <link rel="apple-touch-icon" href="../assets/icons/icon-192x192.png">
    
    <title><?php echo $page_title; ?></title>
    
    <!-- Preconnect untuk performa -->
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-50">
    <?php if (isLoggedIn()): ?>
    <div class="flex h-screen">
        <!-- Mobile Overlay -->
        <div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden transition-opacity duration-300"></div>
        
        <!-- Sidebar -->
        <div id="sidebar" class="bg-blue-900 text-white w-64 space-y-6 py-7 px-2 fixed md:relative inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-50 shadow-lg md:shadow-none">
            <div class="text-white flex items-center justify-between px-4 mb-6">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-dumbbell text-2xl"></i>
                    <span class="text-xl md:text-2xl font-extrabold">Fitness Sentot</span>
                </div>
                <button onclick="window.closeSidebar()" class="md:hidden text-white hover:text-gray-300 touch-manipulation p-2 -mr-2" aria-label="Close Menu">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <nav>
                <?php if (isAdmin()): ?>
                <a href="../admin/dashboard.php" class="block py-3 px-4 rounded transition duration-200 hover:bg-blue-800 active:bg-blue-700 touch-manipulation">
                    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                </a>
                <a href="../admin/member.php" class="block py-3 px-4 rounded transition duration-200 hover:bg-blue-800 active:bg-blue-700 touch-manipulation">
                    <i class="fas fa-users mr-3"></i>Data Member
                </a>
                <a href="../admin/pembayaran.php" class="block py-3 px-4 rounded transition duration-200 hover:bg-blue-800 active:bg-blue-700 touch-manipulation">
                    <i class="fas fa-money-bill-wave mr-3"></i>Pembayaran
                </a>
                <a href="../admin/kehadiran.php" class="block py-3 px-4 rounded transition duration-200 hover:bg-blue-800 active:bg-blue-700 touch-manipulation">
                    <i class="fas fa-calendar-check mr-3"></i>Kehadiran
                </a>
                <a href="../admin/laporan.php" class="block py-3 px-4 rounded transition duration-200 hover:bg-blue-800 active:bg-blue-700 touch-manipulation">
                    <i class="fas fa-chart-bar mr-3"></i>Laporan
                </a>
                <?php elseif (isMember()): ?>
                <a href="../member/dashboard.php" class="block py-3 px-4 rounded transition duration-200 hover:bg-blue-800 active:bg-blue-700 touch-manipulation">
                    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                </a>
                <a href="../member/profil.php" class="block py-3 px-4 rounded transition duration-200 hover:bg-blue-800 active:bg-blue-700 touch-manipulation">
                    <i class="fas fa-user mr-3"></i>Profil Saya
                </a>
                <?php endif; ?>
                <a href="../logout.php" class="block py-3 px-4 rounded transition duration-200 hover:bg-red-600 active:bg-red-700 touch-manipulation mt-4">
                    <i class="fas fa-sign-out-alt mr-3"></i>Logout
                </a>
            </nav>
        </div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow sticky top-0 z-30">
                <div class="flex items-center justify-between px-4 md:px-6 py-3 md:py-4">
                    <div class="flex items-center flex-1">
                        <button onclick="window.toggleSidebar(); return false;" class="text-gray-500 focus:outline-none md:hidden touch-manipulation p-2 -ml-2 active:bg-gray-100 rounded" id="sidebarToggle" aria-label="Toggle Menu" type="button">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="text-lg md:text-2xl font-semibold text-gray-800 ml-2 md:ml-4 truncate"><?php echo $page_title; ?></h1>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-700 mr-2 md:mr-4 text-sm md:text-base hidden sm:inline"><?php echo $_SESSION['nama']; ?></span>
                        <div class="relative">
                            <button class="relative z-10 block rounded-md bg-white p-2 focus:outline-none touch-manipulation active:bg-gray-100">
                                <i class="fas fa-user-circle text-xl md:text-2xl text-blue-600"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-6 pb-20 md:pb-6">
    <?php endif; ?>