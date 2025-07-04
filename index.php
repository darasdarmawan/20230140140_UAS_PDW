<?php
// Selalu mulai sesi untuk memeriksa status login di navigasi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di SIMPRAK - Sistem Informasi Manajemen Praktikum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Opsi tambahan untuk gradient background yang lebih menarik */
        .hero-gradient {
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Navigasi Publik untuk Landing Page -->
    <nav class="bg-white shadow-md sticky top-0 z-10">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-blue-600">SIMPRAK</a>
            <div class="flex items-center space-x-4">
                <a href="katalog.php" class="text-gray-600 hover:text-blue-600 font-medium">Lihat Katalog</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Jika sudah login, arahkan ke dashboard yang sesuai -->
                    <a href="<?php echo $_SESSION['role']; ?>/dashboard.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm font-semibold transition-colors">
                        Dashboard Saya
                    </a>
                    <a href="logout.php" class="text-gray-600 hover:text-red-600">Logout</a>
                <?php else: ?>
                    <!-- Jika belum login, tampilkan tombol Login & Register -->
                    <a href="login.php" class="text-gray-600 hover:text-blue-600">Login</a>
                    <a href="register.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-semibold transition-colors">
                        Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section / Bagian Utama -->
    <header class="hero-gradient text-white">
        <div class="container mx-auto text-center px-6 py-24">
            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-4">
                Manajemen Praktikum Jadi Lebih Mudah
            </h1>
            <p class="text-lg md:text-xl text-blue-100 max-w-3xl mx-auto mb-8">
                SIMPRAK membantu mahasiswa dan asisten mengelola seluruh kegiatan praktikum, mulai dari pendaftaran, materi, hingga penilaian dalam satu platform terpadu.
            </p>
            <a href="katalog.php" class="bg-white text-blue-600 font-bold py-3 px-8 rounded-full text-lg hover:bg-gray-200 transition-transform transform hover:scale-105">
                Jelajahi Praktikum Sekarang →
            </a>
        </div>
    </header>

    <!-- Bagian Fitur (Opsional tapi bagus) -->
    <section class="py-20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800">Kenapa Memilih SIMPRAK?</h2>
                <p class="text-gray-600 mt-2">Fitur unggulan yang kami tawarkan.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Fitur 1 -->
                <div class="bg-white p-8 rounded-xl shadow-lg text-center">
                    <div class="bg-blue-100 text-blue-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Manajemen Terpusat</h3>
                    <p class="text-gray-600">Semua materi, laporan, dan nilai terkumpul dalam satu sistem yang mudah diakses.</p>
                </div>
                <!-- Fitur 2 -->
                <div class="bg-white p-8 rounded-xl shadow-lg text-center">
                    <div class="bg-green-100 text-green-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Proses Efisien</h3>
                    <p class="text-gray-600">Mengurangi pekerjaan manual bagi asisten dan memudahkan mahasiswa melacak progres.</p>
                </div>
                <!-- Fitur 3 -->
                <div class="bg-white p-8 rounded-xl shadow-lg text-center">
                    <div class="bg-red-100 text-red-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Akses Kapan Saja</h3>
                    <p class="text-gray-600">Sebagai aplikasi berbasis web, SIMPRAK dapat diakses dari mana saja dan kapan saja.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto text-center">
            <p>© <?php echo date('Y'); ?> SIMPRAK - Sistem Informasi Manajemen Praktikum. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>