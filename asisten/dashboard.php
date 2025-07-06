<?php
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once '../config.php'; // koneksi database
require_once 'templates/header.php';

// Hitung total modul
$totalModul = $conn->query("SELECT COUNT(*) as total FROM modul")->fetch_assoc()['total'];

// Hitung total laporan
$totalLaporan = $conn->query("SELECT COUNT(*) as total FROM laporan")->fetch_assoc()['total'];

// Hitung laporan yang belum dinilai
$laporanBelumDinilai = $conn->query("SELECT COUNT(*) as total FROM laporan WHERE nilai IS NULL")->fetch_assoc()['total'];
?>

<!-- Kartu Statistik -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Modul Diajarkan -->
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-5">
        <div class="bg-blue-100 p-4 rounded-full">
            <svg class="w-7 h-7 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75..." />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Modul Diajarkan</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $totalModul; ?></p>
        </div>
    </div>

    <!-- Laporan Masuk -->
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-5">
        <div class="bg-green-100 p-4 rounded-full">
            <svg class="w-7 h-7 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75..." />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $totalLaporan; ?></p>
        </div>
    </div>

    <!-- Laporan Belum Dinilai -->
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-5">
        <div class="bg-yellow-100 p-4 rounded-full">
            <svg class="w-7 h-7 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5..." />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $laporanBelumDinilai; ?></p>
        </div>
    </div>
</div>

<!-- Aktivitas Terbaru -->
<div class="bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Laporan Terbaru</h3>
    <ul class="space-y-4">
        <?php
        $latest = $conn->query("
            SELECT u.nama AS mahasiswa, m.judul_modul, l.tanggal_kumpul 
            FROM laporan l
            JOIN pendaftaran p ON l.pendaftaran_id = p.id
            JOIN users u ON p.mahasiswa_id = u.id
            JOIN modul m ON l.modul_id = m.id
            ORDER BY l.tanggal_kumpul DESC
            LIMIT 5
        ");
        if ($latest->num_rows > 0):
            while ($row = $latest->fetch_assoc()):
                $initials = strtoupper(substr($row['mahasiswa'], 0, 1) . substr(strrchr($row['mahasiswa'], ' '), 1, 1));
        ?>
            <li class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-sm font-bold text-gray-600">
                    <?= htmlspecialchars($initials); ?>
                </div>
                <div>
                    <p class="text-gray-800"><strong><?= htmlspecialchars($row['mahasiswa']); ?></strong> mengumpulkan laporan untuk <strong><?= htmlspecialchars($row['judul_modul']); ?></strong></p>
                    <p class="text-sm text-gray-500"><?= date('d M Y H:i', strtotime($row['tanggal_kumpul'])); ?></p>
                </div>
            </li>
        <?php endwhile; else: ?>
            <p class="text-gray-500">Belum ada aktivitas terbaru.</p>
        <?php endif; ?>
    </ul>
</div>

<?php require_once 'templates/footer.php'; ?>
