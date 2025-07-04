<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Cari Praktikum';
$activePage = 'cari_praktikum'; 

$header_path = __DIR__ . '/templates/header_mahasiswa.php';
$footer_path = __DIR__ . '/templates/footer_mahasiswa.php';

require_once __DIR__ . '/../config.php';

$notification = [];

// ... (Logika pendaftaran tetap sama) ...
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['daftar'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
        $notification = ['type' => 'error', 'text' => 'Anda harus login sebagai mahasiswa untuk mendaftar praktikum.'];
    } else {
        $mahasiswa_id = $_SESSION['user_id'];
        $praktikum_id = $_POST['praktikum_id'];
        $sql_check = "SELECT id FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $mahasiswa_id, $praktikum_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            $notification = ['type' => 'info', 'text' => 'Anda sudah terdaftar pada praktikum ini.'];
        } else {
            $sql_insert = "INSERT INTO pendaftaran_praktikum (mahasiswa_id, praktikum_id) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ii", $mahasiswa_id, $praktikum_id);
            if ($stmt_insert->execute() && $stmt_insert->affected_rows > 0) {
                $notification = ['type' => 'success', 'text' => 'Berhasil mendaftar praktikum!'];
            } else {
                $notification = ['type' => 'error', 'text' => 'Gagal mendaftar. Silakan coba lagi.'];
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}

// ===================== LOGIKA PENCARIAN BARU =====================
$searchTerm = '';
if (isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
}

// Modifikasi query SQL untuk menyertakan pencarian
$sql = "SELECT id, nama_praktikum, deskripsi, created_at FROM mata_praktikum";
if (!empty($searchTerm)) {
    // Tambahkan WHERE clause jika ada kata kunci pencarian
    $sql .= " WHERE nama_praktikum LIKE ? OR deskripsi LIKE ?";
}
$sql .= " ORDER BY created_at DESC";

$stmt_praktikum = $conn->prepare($sql);

if (!empty($searchTerm)) {
    // Bind parameter untuk pencarian
    $likeTerm = "%" . $searchTerm . "%";
    $stmt_praktikum->bind_param("ss", $likeTerm, $likeTerm);
}

$stmt_praktikum->execute();
$result = $stmt_praktikum->get_result();
// ===============================================================

// ... (Logika ambil praktikum diikuti tetap sama) ...
$praktikum_diikuti = [];
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'mahasiswa') {
    $mahasiswa_id = $_SESSION['user_id'];
    $sql_diikuti = "SELECT praktikum_id FROM pendaftaran_praktikum WHERE mahasiswa_id = ?";
    $stmt_diikuti = $conn->prepare($sql_diikuti);
    $stmt_diikuti->bind_param("i", $mahasiswa_id);
    $stmt_diikuti->execute();
    $result_diikuti = $stmt_diikuti->get_result();
    while ($row_diikuti = $result_diikuti->fetch_assoc()) {
        $praktikum_diikuti[] = $row_diikuti['praktikum_id'];
    }
    $stmt_diikuti->close();
}

if (file_exists($header_path)) {
    include_once $header_path;
} else {
    die("Error: File header tidak ditemukan.");
}
?>

<!-- Judul Halaman -->
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-2">📝 Daftar Mata Praktikum</h2>
    <p class="text-gray-600">Silakan pilih mata praktikum yang ingin kamu ikuti.</p>
</div>

<!-- ===================== FORM PENCARIAN BARU ===================== -->
<div class="mb-6">
    <form action="katalog.php" method="GET" class="flex items-center">
        <input 
            type="text" 
            name="search" 
            placeholder="Cari nama praktikum..." 
            value="<?php echo htmlspecialchars($searchTerm); ?>"
            class="w-full px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
        <button 
            type="submit"
            class="px-4 py-2 bg-blue-500 text-white font-semibold rounded-r-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
            Cari
        </button>
    </form>
</div>
<!-- ============================================================= -->

<!-- ... (Blok notifikasi dinamis tetap sama) ... -->
<?php if (!empty($notification)): 
    $bgColor = 'bg-gray-100'; $borderColor = 'border-gray-400'; $textColor = 'text-gray-700';
    if ($notification['type'] === 'success') { $bgColor = 'bg-green-100'; $borderColor = 'border-green-400'; $textColor = 'text-green-700'; } 
    elseif ($notification['type'] === 'error') { $bgColor = 'bg-red-100'; $borderColor = 'border-red-400'; $textColor = 'text-red-700'; } 
    elseif ($notification['type'] === 'info') { $bgColor = 'bg-blue-100'; $borderColor = 'border-blue-400'; $textColor = 'text-blue-700'; }
?>
    <div class="<?php echo "$bgColor $borderColor $textColor"; ?> border px-4 py-3 rounded-lg relative mb-6" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($notification['text']); ?></span>
    </div>
<?php endif; ?>

<!-- Daftar Praktikum -->
<?php if ($result && $result->num_rows > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php while ($row = $result->fetch_assoc()): ?>
            <!-- ... (Isi kartu praktikum tetap sama) ... -->
            <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col">
                <div class="flex-grow">
                    <h3 class="text-xl font-semibold text-blue-600 mb-2"><?php echo htmlspecialchars($row['nama_praktikum']); ?></h3>
                    <p class="text-gray-600 mb-3 text-sm line-clamp-3"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-400 mb-4">Dibuat pada: <?php echo date('d M Y', strtotime($row['created_at'])); ?></p>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'mahasiswa'): ?>
                        <?php if (in_array($row['id'], $praktikum_diikuti)): ?>
                            <button class="w-full bg-green-500 text-white font-bold py-2 px-4 rounded-lg cursor-not-allowed" disabled>Sudah Terdaftar</button>
                        <?php else: ?>
                            <form method="POST" action="katalog.php">
                                <input type="hidden" name="praktikum_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="daftar" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">Daftar Praktikum</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center"><p class="text-gray-500 mb-2">Login untuk mendaftar</p><a href="../login.php" class="text-blue-500 hover:underline">Masuk Sekarang</a></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="text-center text-gray-500 bg-white p-8 rounded-xl shadow-md">
        <p>Praktikum tidak ditemukan.</p>
        <?php if (!empty($searchTerm)): ?>
            <a href="katalog.php" class="mt-4 inline-block text-blue-500 hover:underline">Tampilkan semua praktikum</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
$stmt_praktikum->close();
$conn->close();

if (file_exists($footer_path)) {
    include_once $footer_path;
} else {
    die("Error: File footer tidak ditemukan.");
}
?>