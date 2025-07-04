<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login atau bukan mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Mahasiswa - <?php echo $pageTitle ?? 'SIMPRAK'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <nav class="bg-blue-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="dashboard.php" class="text-white text-2xl font-bold">SIMPRAK</a>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <?php 
                                // Mendefinisikan class untuk link aktif dan tidak aktif
                                $activeClass = 'bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium';
                                $inactiveClass = 'text-gray-200 hover:bg-blue-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium';
                            ?>

                            <!-- Dashboard -->
                            <a href="dashboard.php" class="<?php echo (isset($activePage) && $activePage == 'dashboard') ? $activeClass : $inactiveClass; ?>">
                                Dashboard
                            </a>

                            <!-- Praktikum Saya -->
                            <a href="my_courses.php" class="<?php echo (isset($activePage) && $activePage == 'praktikum_saya') ? $activeClass : $inactiveClass; ?>">
                                Praktikum Saya
                            </a>

                            <!-- Detail Praktikum -->
                            <a href="detail_praktikum.php" class="<?php echo (isset($activePage) && $activePage == 'detail_praktikum') ? $activeClass : $inactiveClass; ?>">
                                Detail Praktikum
                            </a>
                            
                            <!-- Cari Praktikum -->
                            <a href="katalog.php" class="<?php echo (isset($activePage) && $activePage == 'cari_praktikum') ? $activeClass : $inactiveClass; ?>">
                                Cari Praktikum
                            </a>
                        </div>
                    </div>
                </div>

                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md transition-colors duration-300">
                            Logout
                        </a>
                    </div>
                </div>

                <!-- Tambahkan tombol menu mobile jika diperlukan di sini -->

            </div>
        </div>
    </nav>

    <main class="container mx-auto p-6 lg:p-8">