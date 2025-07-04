<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan hanya mahasiswa yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../config.php';
$mahasiswa_id = $_SESSION['user_id'];

// SQL untuk mengambil praktikum yang diikuti oleh mahasiswa
$sql = "SELECT mp.id, mp.nama_praktikum, mp.deskripsi 
        FROM mata_praktikum mp
        JOIN pendaftaran_praktikum pp ON mp.id = pp.praktikum_id
        WHERE pp.mahasiswa_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mahasiswa_id);
$stmt->execute();
$result = $stmt->get_result();

// Include header
$pageTitle = 'Praktikum Saya';
$activePage = 'praktikum_saya';
$header_path = __DIR__ . '/templates/header_mahasiswa.php';
include_once $header_path;
?>

<!-- Judul Halaman -->
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-2">📚 Praktikum Saya</h2>
    <p class="text-gray-600">Berikut adalah daftar semua mata praktikum yang sedang Anda ikuti. Untuk melihat detail materi dan tugas, silakan kunjungi halaman <a href="detail_praktikum.php" class="text-blue-500 hover:underline">Detail Praktikum</a>.</p>
</div>

<!-- Daftar Praktikum yang Diikuti -->
<?php if ($result->num_rows > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php while ($row = $result->fetch_assoc()): ?>
            <!-- KARTU HANYA UNTUK MENAMPILKAN NAMA PRAKTIKUM -->
            <div class="bg-white p-6 rounded-xl shadow-lg flex flex-col justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-blue-700 mb-2"><?php echo htmlspecialchars($row['nama_praktikum']); ?></h3>
                    <p class="text-gray-600 mb-4 text-sm line-clamp-3"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                </div>
                <div class="mt-4 text-center w-full bg-gray-200 text-gray-600 font-bold py-2 px-4 rounded">
                    Terdaftar
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="text-center text-gray-500 bg-white p-8 rounded-xl shadow-md">
        <p class="text-lg">Anda belum terdaftar di praktikum manapun.</p>
        <a href="katalog.php" class="mt-4 inline-block text-blue-500 hover:underline">
            Cari praktikum untuk diikuti →
        </a>
    </div>
<?php endif; ?>

<?php
$stmt->close();
$conn->close();

// Include footer
$footer_path = __DIR__ . '/templates/footer_mahasiswa.php';
include_once $footer_path;
?>