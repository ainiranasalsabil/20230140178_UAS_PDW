<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Detail Praktikum';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';

$mahasiswa_id = $_SESSION['user_id'];
$praktikum_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Cek apakah mahasiswa terdaftar
$stmt = $conn->prepare("SELECT * FROM pendaftaran WHERE mahasiswa_id = ? AND mata_praktikum_id = ?");
$stmt->bind_param("ii", $mahasiswa_id, $praktikum_id);
$stmt->execute();
$pendaftaran = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pendaftaran) {
    echo "<div class='text-center text-red-600 font-semibold p-6 bg-white rounded-lg shadow-md mt-6 max-w-xl mx-auto'>
        Anda belum mendaftar pada praktikum ini.
        <div class='mt-4'>
            <a href='courses.php' class='text-blue-600 underline'>Kembali ke daftar praktikum</a>
        </div>
    </div>";
    require_once 'templates/footer_mahasiswa.php';
    exit;
}

// Ambil info praktikum
$praktikum = $conn->query("SELECT * FROM mata_praktikum WHERE id = $praktikum_id")->fetch_assoc();

// Ambil daftar modul
$stmt = $conn->prepare("SELECT * FROM modul WHERE mata_praktikum_id = ? ORDER BY tanggal_deadline ASC");
$stmt->bind_param("i", $praktikum_id);
$stmt->execute();
$modulList = $stmt->get_result();
$stmt->close();
?>

<div class="bg-white p-6 rounded-xl shadow-md max-w-5xl mx-auto mt-6">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h2 class="text-2xl font-bold mb-1 text-gray-800"><?= htmlspecialchars($praktikum['nama_praktikum']) ?></h2>
            <p class="text-gray-600 mb-1"><?= htmlspecialchars($praktikum['deskripsi']) ?></p>
            <p class="text-sm text-gray-500">Semester: <?= $praktikum['semester'] ?> | Tahun Ajaran: <?= $praktikum['tahun_ajaran'] ?></p>
        </div>
    </div>

    <h3 class="text-lg font-semibold mb-3 text-gray-700">Modul & Tugas</h3>

    <?php if ($modulList->num_rows > 0): ?>
        <div class="space-y-4">
            <?php while ($modul = $modulList->fetch_assoc()): ?>
                <?php
                    // Cek laporan mahasiswa
                    $stmt = $conn->prepare("SELECT * FROM laporan WHERE pendaftaran_id = ? AND modul_id = ?");
                    $stmt->bind_param("ii", $pendaftaran['id'], $modul['id']);
                    $stmt->execute();
                    $laporan = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                ?>
                <div class="p-4 border rounded-lg bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($modul['judul_modul']) ?></p>
                            <p class="text-sm text-gray-500">Deadline: <?= date('d M Y', strtotime($modul['tanggal_deadline'])) ?></p>
                            <?php if ($modul['file_materi']): ?>
                                <p class="text-sm mt-1">
                                    Materi: <a href="../uploads/<?= $modul['file_materi'] ?>" class="text-blue-600 underline" target="_blank">Unduh</a>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="text-sm text-right">
                            <?php if ($laporan): ?>
                                <p class="text-green-600 font-medium">Sudah dikumpulkan</p>
                                <?php if (!is_null($laporan['nilai'])): ?>
                                    <p class="text-sm text-gray-600">Nilai: <strong><?= $laporan['nilai'] ?></strong></p>
                                    <p class="text-sm italic text-gray-500"><?= htmlspecialchars($laporan['feedback']) ?></p>
                                <?php else: ?>
                                    <p class="text-yellow-500 italic">Menunggu penilaian</p>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="upload_laporan.php" class="text-blue-600 hover:underline font-semibold">
                                    Upload Laporan
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500">Belum ada modul untuk praktikum ini.</p>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
