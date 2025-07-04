<?php
session_start();
require_once '../config.php'; 

// Cek autentikasi dan otorisasi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

$mahasiswa_id = $_SESSION['user_id'];
$activePage = 'detail_praktikum'; 

// 1. Ambil data SEMUA laporan milik mahasiswa yang sedang login
$laporan_data = [];
$sql_laporan = "SELECT l.modul_id, l.file_laporan, l.nilai, l.feedback, l.status 
                FROM laporan_praktikum l
                WHERE l.mahasiswa_id = ?";
$stmt_laporan_all = $conn->prepare($sql_laporan);
$stmt_laporan_all->bind_param("i", $mahasiswa_id);
$stmt_laporan_all->execute();
$laporan_result_all = $stmt_laporan_all->get_result();
while ($row_laporan = $laporan_result_all->fetch_assoc()) {
    $laporan_data[$row_laporan['modul_id']] = $row_laporan;
}
$stmt_laporan_all->close();

// 2. Ambil SEMUA praktikum yang diikuti oleh mahasiswa
$sql_praktikum_diikuti = "SELECT mp.id, mp.nama_praktikum, mp.deskripsi 
                          FROM mata_praktikum mp
                          JOIN pendaftaran_praktikum pp ON mp.id = pp.praktikum_id
                          WHERE pp.mahasiswa_id = ?";
$stmt_praktikum = $conn->prepare($sql_praktikum_diikuti);
$stmt_praktikum->bind_param("i", $mahasiswa_id);
$stmt_praktikum->execute();
$result_praktikum_diikuti = $stmt_praktikum->get_result();

include_once 'templates/header_mahasiswa.php';
?>

<div class="container mx-auto p-4 md:p-8">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">📖 Detail Semua Praktikum Anda</h2>
        <p class="text-gray-600">Berikut adalah rincian materi dan tugas untuk semua praktikum yang Anda ikuti.</p>
    </div>

    <div class="space-y-12">
        <?php 
        if ($result_praktikum_diikuti->num_rows > 0):
            while ($praktikum = $result_praktikum_diikuti->fetch_assoc()):
                $current_praktikum_id = $praktikum['id'];
        ?>
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <div class="border-b border-gray-200 pb-4 mb-4">
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></h3>
                    <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></p>
                </div>
                <div class="space-y-6">
                    <h4 class="text-xl font-semibold text-gray-700">Daftar Modul & Tugas</h4>
                    <?php
                    $sql_modul = "SELECT id, nama_modul, file_materi FROM modul_praktikum WHERE praktikum_id = ? ORDER BY id ASC";
                    $stmt_modul = $conn->prepare($sql_modul);
                    $stmt_modul->bind_param("i", $current_praktikum_id);
                    $stmt_modul->execute();
                    $modul_result = $stmt_modul->get_result();

                    if ($modul_result->num_rows > 0):
                        while ($modul = $modul_result->fetch_assoc()):
                    ?>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <h5 class="text-lg font-bold text-blue-600 mb-3"><?php echo htmlspecialchars($modul['nama_modul']); ?></h5>
                                <div class="mb-4">
                                    <p class="text-gray-700 font-semibold">Materi:</p>
                                    <?php if (!empty($modul['file_materi'])): ?>
                                        <a href="../uploads/materi/<?php echo htmlspecialchars($modul['file_materi']); ?>" class="text-blue-500 hover:underline" download>Unduh Materi</a>
                                    <?php else: ?>
                                        <p class="text-gray-500 italic">Materi belum tersedia.</p>
                                    <?php endif; ?>
                                </div>
                                <hr class="my-3 border-gray-200">
                                <div>
                                    <p class="text-gray-700 font-semibold mb-2">Laporan/Tugas Anda:</p>
                                    <?php 
                                    if (isset($laporan_data[$modul['id']])):
                                        $laporan = $laporan_data[$modul['id']];

                                        /* 
                                        // --- OPSI DEBUG: HAPUS TANDA // DI BAWAH INI JIKA MASALAH MASIH ADA ---
                                        // echo "<pre style='background-color: #eee; padding: 10px; border: 1px solid red;'>";
                                        // var_dump($laporan);
                                        // echo "</pre>";
                                        */
                                    ?>
                                        <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3 rounded" role="alert">
                                            <p class="font-bold">Sudah Dikumpulkan</p>
                                            <p class="text-sm">File: <?php echo htmlspecialchars($laporan['file_laporan']); ?></p>
                                            
                                            <?php 
                                            // ======================= BAGIAN YANG PALING PENTING =======================
                                            // Pengecekan status yang lebih aman:
                                            // 1. Cek apakah 'status' ada
                                            // 2. Gunakan trim() untuk menghapus spasi yang tidak terlihat
                                            // 3. Gunakan strtolower() untuk mengubah menjadi huruf kecil
                                            if (isset($laporan['status']) && strtolower(trim($laporan['status'])) == 'dinilai'): 
                                            ?>
                                                <div class="mt-2 border-t border-green-300 pt-2">
                                                    <p><strong>Status:</strong> <span class="font-semibold">Sudah Dinilai</span></p>
                                                    <p><strong>Nilai:</strong> <span class="text-2xl font-bold"><?php echo isset($laporan['nilai']) ? htmlspecialchars($laporan['nilai']) : 'N/A'; ?></span></p>
                                                    <?php if (!empty($laporan['feedback'])): ?>
                                                        <div class="mt-1">
                                                            <strong>Feedback dari Asisten:</strong>
                                                            <blockquote class="border-l-2 border-green-400 pl-2 italic mt-1">"<?php echo nl2br(htmlspecialchars($laporan['feedback'])); ?>"</blockquote>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <p class="mt-2 italic">Menunggu penilaian.</p>
                                            <?php endif; ?>
                                            <!-- ====================================================================== -->
                                        </div>
                                    <?php else: ?>
                                        <form action="upload_laporan.php" method="post" enctype="multipart/form-data">
                                            <input type="hidden" name="modul_id" value="<?php echo $modul['id']; ?>">
                                            <input type="hidden" name="praktikum_id" value="<?php echo $current_praktikum_id; ?>">
                                            <input type="file" name="file_laporan" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                                            <button type="submit" class="mt-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Kumpulkan Laporan</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                    <?php 
                        endwhile;
                    else:
                        echo '<p class="text-gray-500 italic">Belum ada modul yang ditambahkan untuk praktikum ini.</p>';
                    endif;
                    $stmt_modul->close();
                    ?>
                </div>
            </div>
        <?php 
            endwhile;
        else:
        ?>
            <div class="text-center text-gray-500 bg-white p-8 rounded-xl shadow-md">
                <p class="text-lg">Anda belum terdaftar di praktikum manapun.</p>
                <a href="katalog.php" class="mt-4 inline-block text-blue-500 hover:underline">Cari praktikum untuk diikuti →</a>
            </div>
        <?php 
        endif; 
        $stmt_praktikum->close();
        ?>
    </div>
</div>

<?php
$conn->close();
include_once 'templates/footer_mahasiswa.php';
?>