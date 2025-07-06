<?php
$pageTitle = 'Katalog Mata Praktikum';
$activePage = 'courses';
require_once '../config.php';
require_once 'templates/header_mahasiswa.php';

// Pastikan hanya mahasiswa yang bisa mengakses
if ($_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$mahasiswa_id = $_SESSION['user_id'];

// Proses pendaftaran
if (isset($_GET['daftar'])) {
    $praktikum_id = (int) $_GET['daftar'];

    // Cek apakah sudah terdaftar
    $cek = $conn->prepare("SELECT * FROM pendaftaran WHERE mahasiswa_id = ? AND mata_praktikum_id = ?");
    $cek->bind_param("ii", $mahasiswa_id, $praktikum_id);
    $cek->execute();
    $cek_result = $cek->get_result();

    if ($cek_result->num_rows == 0) {
        // Jika belum, tambahkan
        $stmt = $conn->prepare("INSERT INTO pendaftaran (mahasiswa_id, mata_praktikum_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $mahasiswa_id, $praktikum_id);
        $stmt->execute();
        $stmt->close();

        $message = "<p class='text-green-600 mb-4'>Berhasil mendaftar ke praktikum!</p>";
    } else {
        $message = "<p class='text-yellow-600 mb-4'>Anda sudah terdaftar di praktikum ini.</p>";
    }
    $cek->close();
}

// Ambil semua praktikum
$result = $conn->query("SELECT * FROM mata_praktikum ORDER BY tahun_ajaran DESC, semester DESC");
?>

<div class="bg-white p-6 rounded-xl shadow-md max-w-5xl mx-auto mt-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Katalog Mata Praktikum</h2>

    <?php if (!empty($message)) echo $message; ?>

    <?php if ($result->num_rows > 0): ?>
        <div class="space-y-4">
            <?php while ($praktikum = $result->fetch_assoc()): ?>
                <div class="border p-4 rounded-md bg-gray-50 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-blue-600"><?= htmlspecialchars($praktikum['nama_praktikum']) ?></h3>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($praktikum['deskripsi']) ?></p>
                        <p class="text-sm text-gray-500">Semester: <?= $praktikum['semester'] ?> | Tahun Ajaran: <?= $praktikum['tahun_ajaran'] ?></p>
                    </div>
                    <a href="courses.php?daftar=<?= $praktikum['id'] ?>" 
                       onclick="return confirm('Yakin ingin mendaftar ke praktikum ini?');"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-semibold">
                        Daftar
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500">Belum ada mata praktikum tersedia.</p>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
