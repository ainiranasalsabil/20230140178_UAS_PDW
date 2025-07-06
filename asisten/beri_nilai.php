<?php
$pageTitle = 'Beri Nilai';
$activePage = 'laporan';

require_once '../config.php';
require_once 'templates/header.php';
// Ambil ID laporan dari URL
$laporan_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message = "";

// Proses penyimpanan nilai
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nilai = (int) $_POST['nilai'];
    $feedback = trim($_POST['feedback']);

    // Validasi nilai
    if ($nilai < 0 || $nilai > 100) {
        $message = "Nilai harus antara 0 dan 100.";
    } else {
        $stmt = $conn->prepare("UPDATE laporan SET nilai = ?, feedback = ? WHERE id = ?");
        $stmt->bind_param("isi", $nilai, $feedback, $laporan_id);

        if ($stmt->execute()) {
            header("Location: laporan.php");
            exit();
        } else {
            $message = "Gagal menyimpan nilai. Silakan coba lagi.";
        }
        $stmt->close();
    }
}

// Ambil detail laporan
$sql = "SELECT l.*, u.nama AS nama_mahasiswa, m.judul_modul 
        FROM laporan l
        JOIN pendaftaran p ON l.pendaftaran_id = p.id
        JOIN users u ON p.mahasiswa_id = u.id
        JOIN modul m ON l.modul_id = m.id
        WHERE l.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $laporan_id);
$stmt->execute();
$result = $stmt->get_result();
$laporan = $result->fetch_assoc();
$stmt->close();

// Jika laporan tidak ditemukan
if (!$laporan) {
    echo "<div class='text-center text-red-600 p-6 bg-white rounded-lg shadow-md mt-6 max-w-xl mx-auto'>
            Laporan tidak ditemukan.
            <div class='mt-4'>
                <a href='laporan.php' class='text-blue-600 underline'>Kembali ke Laporan</a>
            </div>
        </div>";
    require_once 'templates/footer.php';
    exit();
}
?>

<div class="bg-white p-6 rounded-xl shadow-md max-w-xl mx-auto mt-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Beri Nilai Laporan</h2>

    <?php if (!empty($message)): ?>
        <p class="text-red-600 mb-4"><?= $message; ?></p>
    <?php endif; ?>

    <div class="mb-6 text-sm">
        <p><strong>Nama Mahasiswa:</strong> <?= htmlspecialchars($laporan['nama_mahasiswa']); ?></p>
        <p><strong>Modul:</strong> <?= htmlspecialchars($laporan['judul_modul']); ?></p>
        <p><strong>Tanggal Kumpul:</strong> <?= date('d M Y, H:i', strtotime($laporan['tanggal_kumpul'])); ?></p>
        <p><strong>File Laporan:</strong>
            <a href="../uploads/<?= htmlspecialchars($laporan['file_laporan']); ?>" target="_blank" 
               class="text-blue-500 hover:underline">Unduh File</a>
        </p>
    </div>

    <form action="" method="POST" class="space-y-4">
        <div>
            <label for="nilai" class="block text-sm font-medium text-gray-700">Nilai</label>
            <input type="number" id="nilai" name="nilai" min="0" max="100" 
                   value="<?= $laporan['nilai'] ?? ''; ?>"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2" required>
        </div>

        <div>
            <label for="feedback" class="block text-sm font-medium text-gray-700">Feedback (opsional)</label>
            <textarea id="feedback" name="feedback" rows="4"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2"><?= htmlspecialchars($laporan['feedback'] ?? '') ?></textarea>
        </div>

        <div class="flex justify-between items-center mt-6">
            <a href="laporan.php" class="text-sm text-blue-600 hover:underline">← Kembali ke Daftar</a>
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-md">
                Simpan Nilai
            </button>
        </div>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>
