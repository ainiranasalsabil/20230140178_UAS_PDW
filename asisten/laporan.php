<?php
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';
require_once '../config.php';
require_once 'templates/header.php';

// Handle filter
$where = "1";
$params = [];
$bindings = "";

if (!empty($_GET['modul_id'])) {
    $where .= " AND l.modul_id = ?";
    $params[] = $_GET['modul_id'];
    $bindings .= "i";
}
if (!empty($_GET['mahasiswa_nama'])) {
    $where .= " AND u.nama LIKE ?";
    $params[] = "%" . $_GET['mahasiswa_nama'] . "%";
    $bindings .= "s";
}
if (isset($_GET['status']) && $_GET['status'] !== '') {
    if ($_GET['status'] === 'belum') {
        $where .= " AND l.nilai IS NULL";
    } elseif ($_GET['status'] === 'sudah') {
        $where .= " AND l.nilai IS NOT NULL";
    }
}

// Ambil semua modul
$moduls = $conn->query("SELECT id, judul_modul FROM modul ORDER BY judul_modul ASC");

// Query laporan
$sql = "SELECT l.id, u.nama, m.judul_modul, l.tanggal_kumpul, l.nilai
        FROM laporan l
        JOIN pendaftaran p ON l.pendaftaran_id = p.id
        JOIN users u ON p.mahasiswa_id = u.id
        JOIN modul m ON l.modul_id = m.id
        WHERE $where
        ORDER BY l.tanggal_kumpul DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($bindings, ...$params);
}
$stmt->execute();
$laporanList = $stmt->get_result();
?>

<div class="bg-white p-6 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Laporan Masuk</h2>

    <!-- Filter -->
    <form method="GET" class="flex flex-wrap gap-4 mb-4">
        <select name="modul_id" class="border p-2 rounded-md">
            <option value="">Filter Modul</option>
            <?php while ($m = $moduls->fetch_assoc()): ?>
                <option value="<?= $m['id'] ?>" <?= ($_GET['modul_id'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                    <?= $m['judul_modul'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <input type="text" name="mahasiswa_nama" placeholder="Cari Mahasiswa"
               class="border p-2 rounded-md" value="<?= $_GET['mahasiswa_nama'] ?? '' ?>">

        <select name="status" class="border p-2 rounded-md">
            <option value="">Semua Status</option>
            <option value="belum" <?= ($_GET['status'] ?? '') == 'belum' ? 'selected' : '' ?>>Belum Dinilai</option>
            <option value="sudah" <?= ($_GET['status'] ?? '') == 'sudah' ? 'selected' : '' ?>>Sudah Dinilai</option>
        </select>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md">Filter</button>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-100 text-gray-600 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Mahasiswa</th>
                    <th class="px-4 py-3 text-left">Modul</th>
                    <th class="px-4 py-3 text-left">Tanggal Kumpul</th>
                    <th class="px-4 py-3 text-left">Nilai</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php while ($row = $laporanList->fetch_assoc()): ?>
                    <tr>
                        <td class="px-4 py-3"><?= htmlspecialchars($row['nama']) ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($row['judul_modul']) ?></td>
                        <td class="px-4 py-3"><?= date('d M Y H:i', strtotime($row['tanggal_kumpul'])) ?></td>
                        <td class="px-4 py-3"><?= $row['nilai'] ?? '<span class="text-red-500 italic">Belum</span>' ?></td>
                        <td class="px-4 py-3 text-center">
                            <a href="beri_nilai.php?id=<?= $row['id'] ?>"
                               class="text-blue-600 hover:underline">Beri Nilai</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($laporanList->num_rows == 0): ?>
                    <tr><td colspan="5" class="text-center py-4 text-gray-500">Tidak ada data</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
