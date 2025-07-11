<?php
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';
require_once '../config.php';
include_once 'templates/header.php';

$message = '';
$message_type = '';

// --- LOGIKA PENILAIAN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['simpan_nilai'])) {
    $laporan_id = (int)$_POST['laporan_id'];
    $nilai = $_POST['nilai'];
    $feedback = trim($_POST['feedback']);

    // PERBAIKAN: Tambahkan "status = 'dinilai'" ke dalam query
    $stmt = $conn->prepare("UPDATE laporan_praktikum SET nilai = ?, feedback = ?, status = 'dinilai' WHERE id = ?");
    $stmt->bind_param("dsi", $nilai, $feedback, $laporan_id);

    if ($stmt->execute()) {
        $message = "Nilai berhasil disimpan dan status telah diperbarui!";
        $message_type = 'success';
    } else {
        $message = "Gagal menyimpan nilai. Error: " . $stmt->error;
        $message_type = 'error';
    }
    $stmt->close();
}

// --- LOGIKA FILTER ---
$filter_praktikum_id = isset($_GET['praktikum_id']) ? (int)$_GET['praktikum_id'] : 0;
$filter_modul_id = isset($_GET['modul_id']) ? (int)$_GET['modul_id'] : 0;
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_mahasiswa = isset($_GET['mahasiswa']) ? trim($_GET['mahasiswa']) : '';

// --- LOGIKA STATISTIK ---
$total_laporan_row = $conn->query("SELECT COUNT(id) as total FROM laporan_praktikum")->fetch_assoc();
$total_laporan = $total_laporan_row['total'];

$laporan_dinilai_row = $conn->query("SELECT COUNT(id) as total FROM laporan_praktikum WHERE status = 'dinilai'")->fetch_assoc();
$laporan_dinilai = $laporan_dinilai_row['total'];

$laporan_pending_row = $conn->query("SELECT COUNT(id) as total FROM laporan_praktikum WHERE status = 'dikumpulkan'")->fetch_assoc();
$laporan_pending = $laporan_pending_row['total'];

// --- PENGAMBILAN DATA UNTUK DROPDOWN FILTER ---
$praktikum_options = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum");

// --- LOGIKA PENGAMBILAN DATA LAPORAN UTAMA ---
$sql_base = "FROM laporan_praktikum lp 
             JOIN users u ON lp.mahasiswa_id = u.id 
             JOIN mata_praktikum mp ON lp.praktikum_id = mp.id 
             JOIN modul_praktikum m ON lp.modul_id = m.id";
$where_clauses = [];
$params = [];
$types = '';

if ($filter_praktikum_id > 0) {
    $where_clauses[] = "lp.praktikum_id = ?";
    $params[] = $filter_praktikum_id;
    $types .= 'i';
}
if ($filter_modul_id > 0) {
    $where_clauses[] = "lp.modul_id = ?";
    $params[] = $filter_modul_id;
    $types .= 'i';
}
if ($filter_status === 'dinilai') {
    $where_clauses[] = "lp.status = 'dinilai'";
}
if ($filter_status === 'belum_dinilai') {
    $where_clauses[] = "lp.status = 'dikumpulkan'";
}
if (!empty($filter_mahasiswa)) {
    // Asumsi nama kolom di tabel 'users' adalah 'nama'
    $where_clauses[] = "u.nama LIKE ?";
    $params[] = "%" . $filter_mahasiswa . "%";
    $types .= 's';
}

$where_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

// --- LOGIKA PAGINATION ---
$sql_count_query = "SELECT COUNT(lp.id) as total " . $sql_base . $where_sql;
$stmt_count = $conn->prepare($sql_count_query);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_results_row = $stmt_count->get_result()->fetch_assoc();
$total_results = $total_results_row ? $total_results_row['total'] : 0;
$stmt_count->close();

$limit = 10;
$total_pages = $total_results > 0 ? ceil($total_results / $limit) : 1;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, min($page, $total_pages));
$offset = ($page - 1) * $limit;

// --- Query Final untuk Mengambil Data Laporan ---
// Asumsi nama kolom tanggal adalah 'submitted_at'
$sql_data = "SELECT 
                lp.id, lp.status, lp.file_laporan, lp.nilai, lp.feedback, lp.submitted_at,
                u.nama as nama_mahasiswa, 
                mp.nama_praktikum, 
                m.nama_modul
             " . $sql_base . $where_sql . " 
             ORDER BY lp.submitted_at DESC 
             LIMIT ?, ?";

$final_params = $params;
$final_params[] = $offset;
$final_params[] = $limit;
$final_types = $types . 'ii';

$stmt_laporan = $conn->prepare($sql_data);
if (!empty($params)) {
    $stmt_laporan->bind_param($final_types, ...$final_params);
} else {
    $stmt_laporan->bind_param("ii", $offset, $limit);
}
$stmt_laporan->execute();
$laporan_list = $stmt_laporan->get_result();

function getInitials($name) {
    if (empty($name)) return '';
    $words = explode(' ', $name, 2);
    $initials = '';
    foreach ($words as $w) {
        if (!empty($w)) $initials .= strtoupper($w[0]);
    }
    return $initials;
}
?>

<!-- Kartu Statistik -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Total Laporan -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_laporan; ?></p>
        </div>
    </div>
    <!-- Sudah Dinilai -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Sudah Dinilai</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $laporan_dinilai; ?></p>
        </div>
    </div>
    <!-- Menunggu Penilaian -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-yellow-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Menunggu Penilaian</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $laporan_pending; ?></p>
        </div>
    </div>
</div>

<!-- Notifikasi Sukses/Error -->
<?php if (!empty($message)): ?>
    <div id="alert-box" class="mb-4 p-4 rounded-md <?php echo $message_type == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<!-- Filter dan Tabel Laporan -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <!-- Form Filter -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
        <h2 class="text-xl font-semibold text-gray-800">Daftar Laporan</h2>
        <form action="laporan.php" method="GET" class="flex items-center gap-2 flex-wrap">
            <input type="text" name="mahasiswa" placeholder="Cari mahasiswa..." class="shadow-sm border border-gray-300 rounded py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($filter_mahasiswa); ?>">
            <select name="praktikum_id" class="shadow-sm border border-gray-300 rounded py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                <option value="">Semua Praktikum</option>
                <?php if ($praktikum_options->num_rows > 0): mysqli_data_seek($praktikum_options, 0); while($p = $praktikum_options->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo ($filter_praktikum_id == $p['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['nama_praktikum']); ?></option>
                <?php endwhile; endif; ?>
            </select>
            <select name="status" class="shadow-sm border border-gray-300 rounded py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="belum_dinilai" <?php echo ($filter_status == 'belum_dinilai') ? 'selected' : ''; ?>>Belum Dinilai</option>
                <option value="dinilai" <?php echo ($filter_status == 'dinilai') ? 'selected' : ''; ?>>Sudah Dinilai</option>
            </select>
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Filter</button>
        </form>
    </div>

    <!-- Tabel Data Laporan -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detail Laporan</th>
                    <th class="py-3 px-6 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if ($laporan_list && $laporan_list->num_rows > 0): ?>
                    <?php while($row = $laporan_list->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-4 px-6 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="font-bold text-gray-500"><?php echo getInitials($row['nama_mahasiswa']); ?></span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></div>
                                        <div class="text-sm text-gray-500">Dikumpulkan: <?php echo date('d M Y, H:i', strtotime($row['submitted_at'])); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_praktikum']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['nama_modul']); ?></div>
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap text-center">
                                <?php if ($row['status'] === 'dinilai'): ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sudah Dinilai</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Belum Dinilai</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap text-center text-sm font-medium">
                                <button onclick='openGradeModal(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)' class="text-indigo-600 hover:text-indigo-900" title="Beri Nilai">
                                    <svg class="inline w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-8 text-gray-500">Tidak ada laporan yang sesuai dengan filter.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="mt-6">
        <?php if ($total_pages > 1): ?>
            <?php $query_params = $_GET; unset($query_params['page']); $query_string = http_build_query($query_params); ?>
            <nav class="flex items-center justify-between">
                <div class="text-sm text-gray-700">Halaman <span class="font-medium"><?php echo $page; ?></span> dari <span class="font-medium"><?php echo $total_pages; ?></span></div>
                <ul class="inline-flex items-center -space-x-px">
                    <li><a href="<?php echo $page > 1 ? '?page='.($page-1).'&'.$query_string : '#'; ?>" class="<?php echo $page <= 1 ? 'pointer-events-none text-gray-400' : 'text-gray-500 hover:bg-gray-100'; ?> py-2 px-3 ml-0 leading-tight bg-white border border-gray-300 rounded-l-lg">Prev</a></li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li><a href="?page=<?php echo $i; ?>&<?php echo $query_string; ?>" class="<?php echo $page == $i ? 'z-10 py-2 px-3 text-blue-600 bg-blue-50 border-blue-300' : 'py-2 px-3 text-gray-500 bg-white border border-gray-300 hover:bg-gray-100'; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                    <li><a href="<?php echo $page < $total_pages ? '?page='.($page+1).'&'.$query_string : '#'; ?>" class="<?php echo $page >= $total_pages ? 'pointer-events-none text-gray-400' : 'text-gray-500 hover:bg-gray-100'; ?> py-2 px-3 leading-tight bg-white border border-gray-300 rounded-r-lg">Next</a></li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Penilaian -->
<div id="grade-modal" class="fixed z-20 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="laporan.php" method="POST">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="grade-modal-title">Penilaian Laporan</h3>
                    <div class="mt-4 space-y-4">
                        <input type="hidden" name="laporan_id" id="laporan-id">
                        <div>
                            <p class="text-sm text-gray-500">Mahasiswa: <strong id="grade-mahasiswa" class="text-gray-800"></strong></p>
                            <p class="text-sm text-gray-500">Modul: <strong id="grade-modul" class="text-gray-800"></strong></p>
                            <p class="text-sm text-gray-500">File: <a id="grade-file-link" href="#" target="_blank" class="text-blue-500 hover:underline">Unduh Laporan</a></p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-1">
                                <label for="grade-nilai" class="block text-sm font-medium text-gray-700">Nilai (0-100)</label>
                                <input type="number" step="0.01" min="0" max="100" id="grade-nilai" name="nilai" class="mt-1 shadow-sm border border-gray-300 rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div class="md:col-span-2">
                                <label for="grade-feedback" class="block text-sm font-medium text-gray-700">Feedback</label>
                                <textarea id="grade-feedback" name="feedback" rows="3" class="mt-1 shadow-sm border border-gray-300 rounded w-full py-2 px-3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" name="simpan_nilai" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan Nilai</button>
                    <button type="button" onclick="closeGradeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const gradeModal = document.getElementById('grade-modal');

    function openGradeModal(data) {
        document.getElementById('laporan-id').value = data.id;
        document.getElementById('grade-mahasiswa').textContent = data.nama_mahasiswa;
        document.getElementById('grade-modul').textContent = data.nama_modul;
        document.getElementById('grade-nilai').value = data.nilai || '';
        document.getElementById('grade-feedback').value = data.feedback || '';
        document.getElementById('grade-file-link').href = `../uploads/laporan/${data.file_laporan}`;
        gradeModal.classList.remove('hidden');
    }

    function closeGradeModal() {
        gradeModal.classList.add('hidden');
    }

    const alertBox = document.getElementById('alert-box');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.5s';
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 500);
        }, 3000);
    }
</script>

<?php 
$conn->close();
include_once 'templates/footer.php'; 
?>