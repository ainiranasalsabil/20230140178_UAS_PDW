<?php
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once '../config.php';
require_once 'templates/header_mahasiswa.php';

$mahasiswa_id = $_SESSION['user_id'];

// Total praktikum yang diikuti
$totalPraktikum = $conn->query("SELECT COUNT(*) as total FROM pendaftaran WHERE mahasiswa_id = $mahasiswa_id")->fetch_assoc()['total'];

// Total laporan dikumpulkan
$sqlSelesai = "
    SELECT COUNT(*) as total
    FROM laporan l
    JOIN pendaftaran p ON l.pendaftaran_id = p.id
    WHERE p.mahasiswa_id = $mahasiswa_id
";
$totalSelesai = $conn->query($sqlSelesai)->fetch_assoc()['total'];

// Total laporan belum dinilai
$sqlMenunggu = "
    SELECT COUNT(*) as total
    FROM laporan l
    JOIN pendaftaran p ON l.pendaftaran_id = p.id
    WHERE p.mahasiswa_id = $mahasiswa_id AND l.nilai IS NULL
";
$totalMenunggu = $conn->query($sqlMenunggu)->fetch_assoc()['total'];
?>

<!-- ✅ Bar atas dengan tombol Logout -->
<div class="flex justify-between items-center mb-4">
<h1 class="text-3xl font-bold text-gray-800 mb-4">Dashboard Mahasiswa</h1>
</div>

<!-- ✅ Selamat datang -->
<div class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white p-8 rounded-xl shadow-lg mb-8">
    <h2 class="text-3xl font-bold">Selamat Datang Kembali, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h2>
    <p class="mt-2 opacity-90">Terus semangat dalam menyelesaikan semua modul praktikummu.</p>
</div>

<!-- ✅ Kartu Ringkasan -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-blue-600"><?= $totalPraktikum ?></div>
        <div class="mt-2 text-lg text-gray-600">Praktikum Diikuti</div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-green-500"><?= $totalSelesai ?></div>
        <div class="mt-2 text-lg text-gray-600">Tugas Selesai</div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-yellow-500"><?= $totalMenunggu ?></div>
        <div class="mt-2 text-lg text-gray-600">Tugas Menunggu</div>
    </div>
</div>

<!-- ✅ Notifikasi -->
<div class="bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-2xl font-bold text-gray-800 mb-4">Notifikasi Terbaru</h3>
    <ul class="space-y-4">
        <?php
        $notif = $conn->query("
            SELECT m.judul_modul, l.nilai, l.tanggal_kumpul, mp.nama_praktikum
            FROM laporan l
            JOIN modul m ON l.modul_id = m.id
            JOIN pendaftaran p ON l.pendaftaran_id = p.id
            JOIN mata_praktikum mp ON m.mata_praktikum_id = mp.id
            WHERE p.mahasiswa_id = $mahasiswa_id
            ORDER BY l.tanggal_kumpul DESC
            LIMIT 3
        ");

        if ($notif->num_rows > 0) {
            while ($row = $notif->fetch_assoc()) {
                if ($row['nilai'] !== null) {
                    echo "<li class='flex items-start p-3 border-b border-gray-100 last:border-b-0'>
                            <span class='text-xl mr-4'>🔔</span>
                            <div>Nilai untuk <strong class='text-blue-600'>" . htmlspecialchars($row['judul_modul']) . "</strong> telah diberikan.</div>
                          </li>";
                } else {
                    echo "<li class='flex items-start p-3 border-b border-gray-100 last:border-b-0'>
                            <span class='text-xl mr-4'>⏳</span>
                            <div>Laporan <strong class='text-blue-600'>" . htmlspecialchars($row['judul_modul']) . "</strong> masih menunggu penilaian.</div>
                          </li>";
                }
            }
        } else {
            echo "<li class='text-gray-500'>Belum ada aktivitas terbaru.</li>";
        }
        ?>
    </ul>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
