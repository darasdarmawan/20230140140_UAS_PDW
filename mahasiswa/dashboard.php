<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Dashboard';
$activePage = 'dashboard';

$header_path = __DIR__ . '/templates/header_mahasiswa.php';
$footer_path = __DIR__ . '/templates/footer_mahasiswa.php';

require_once __DIR__ . '/../config.php';

if (file_exists($header_path)) {
    include_once $header_path;
} else {
    die("<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #fff0f0; border: 1px solid #ffbaba; color: #d8000c;'>
            <strong>Error:</strong> File <code>header_mahasiswa.php</code> tidak ditemukan di folder <code>mahasiswa/templates/</code>.
         </div>");
}

if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

$mahasiswa_id = $_SESSION['user_id'];
$nama_mahasiswa = $_SESSION['nama'];

// --- STATISTIK ---
$stmt_praktikum = $conn->prepare("SELECT COUNT(*) as total FROM pendaftaran_praktikum WHERE mahasiswa_id = ?");
$stmt_praktikum->bind_param("i", $mahasiswa_id);
$stmt_praktikum->execute();
$total_praktikum = $stmt_praktikum->get_result()->fetch_assoc()['total'];
$stmt_praktikum->close();

$stmt_selesai = $conn->prepare("SELECT COUNT(*) as total FROM laporan_praktikum WHERE mahasiswa_id = ? AND nilai IS NOT NULL");
$stmt_selesai->bind_param("i", $mahasiswa_id);
$stmt_selesai->execute();
$total_selesai = $stmt_selesai->get_result()->fetch_assoc()['total'];
$stmt_selesai->close();

$stmt_menunggu = $conn->prepare("SELECT COUNT(*) as total FROM laporan_praktikum WHERE mahasiswa_id = ? AND nilai IS NULL");
$stmt_menunggu->bind_param("i", $mahasiswa_id);
$stmt_menunggu->execute();
$total_menunggu = $stmt_menunggu->get_result()->fetch_assoc()['total'];
$stmt_menunggu->close();

// Notifikasi nilai terakhir
$sql_notif = "SELECT lp.praktikum_id, m.nama_modul, lp.nilai, lp.submitted_at
              FROM laporan_praktikum lp
              JOIN modul_praktikum m ON lp.modul_id = m.id
              WHERE lp.mahasiswa_id = ? AND lp.nilai IS NOT NULL
              ORDER BY lp.submitted_at DESC
              LIMIT 3";
$stmt_notif = $conn->prepare($sql_notif);
$stmt_notif->bind_param("i", $mahasiswa_id);
$stmt_notif->execute();
$notifikasi_list = $stmt_notif->get_result();
$stmt_notif->close();
?>

<!-- Selamat Datang -->
<div class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white p-8 rounded-xl shadow-lg mb-8">
    <h1 class="text-3xl font-bold">Halo, <?php echo htmlspecialchars(strtok($nama_mahasiswa, ' ')); ?> 👋</h1>
    <p class="mt-2 text-white text-opacity-80">Selamat datang kembali di SIMPRAK. Semangat terus menyelesaikan tugasmu!</p>
</div>

<!-- Statistik -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="bg-white shadow-md p-6 rounded-xl text-center hover:shadow-lg transition">
        <div class="text-blue-600 text-4xl font-extrabold"><?php echo $total_praktikum; ?></div>
        <p class="mt-2 text-gray-600">Praktikum Diikuti</p>
    </div>
    <div class="bg-white shadow-md p-6 rounded-xl text-center hover:shadow-lg transition">
        <div class="text-green-500 text-4xl font-extrabold"><?php echo $total_selesai; ?></div>
        <p class="mt-2 text-gray-600">Tugas Selesai</p>
    </div>
    <div class="bg-white shadow-md p-6 rounded-xl text-center hover:shadow-lg transition">
        <div class="text-yellow-500 text-4xl font-extrabold"><?php echo $total_menunggu; ?></div>
        <p class="mt-2 text-gray-600">Tugas Menunggu</p>
    </div>
</div>

<!-- Notifikasi Terbaru -->
<div class="bg-white shadow-md p-6 rounded-xl">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">🔔 Notifikasi Terbaru</h2>
    <ul class="space-y-4">
        <?php if ($notifikasi_list && $notifikasi_list->num_rows > 0): ?>
            <?php while($notif = $notifikasi_list->fetch_assoc()): ?>
                <li class="border-b pb-3 last:border-b-0">
                    <div class="text-gray-700">
                        Nilai untuk <a href="detail_praktikum.php?id=<?php echo $notif['praktikum_id']; ?>" class="text-blue-600 font-semibold hover:underline">
                            <?php echo htmlspecialchars($notif['nama_modul']); ?>
                        </a> telah diberikan.
                    </div>
                    <div class="text-sm text-gray-400">
                        Dikirim: <?php echo date('d M Y, H:i', strtotime($notif['submitted_at'])); ?>
                    </div>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li class="text-gray-500">Tidak ada notifikasi terbaru.</li>
        <?php endif; ?>
    </ul>
</div>

<?php
$conn->close();
if (file_exists($footer_path)) {
    include_once $footer_path;
} else {
    die("<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #fff0f0; border: 1px solid #ffbaba; color: #d8000c;'>
            <strong>Error:</strong> File <code>footer_mahasiswa.php</code> tidak ditemukan.
         </div>");
}
?>
