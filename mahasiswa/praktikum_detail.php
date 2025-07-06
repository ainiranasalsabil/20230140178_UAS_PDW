<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') header("Location: ../login.php");

$mahasiswa = $_SESSION['user_id'];
$praktikum_id = intval($_GET['id'] ?? 0);

// Cek pendaftaran
$stmt = $conn->prepare("SELECT * FROM pendaftaran WHERE mahasiswa_id=? AND mata_praktikum_id=?");
$stmt->bind_param("ii", $mahasiswa, $praktikum_id);
$stmt->execute();
$pendaftaran = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$pendaftaran) {
  header("Location: my_courses.php");
  exit;
}

// Ambil praktikum & modul
$prak = $conn->query("SELECT * FROM mata_praktikum WHERE id=$praktikum_id")->fetch_assoc();
$stmt = $conn->prepare("SELECT * FROM modul WHERE mata_praktikum_id=? ORDER BY tanggal_deadline");
$stmt->bind_param("i", $praktikum_id);
$stmt->execute();
$moduls = $stmt->get_result();
$stmt->close();

require_once 'templates/header_mahasiswa.php';
?>

<!-- ✅ Header Praktikum -->
<div class="max-w-5xl mx-auto mt-6 bg-white p-8 rounded-xl shadow-md">
  <h2 class="text-3xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($prak['nama_praktikum']) ?></h2>
  <p class="text-gray-600"><?= htmlspecialchars($prak['deskripsi']) ?></p>
</div>

<!-- ✅ Daftar Modul -->
<div class="max-w-5xl mx-auto mt-6 space-y-6">
  <?php while ($mod = $moduls->fetch_assoc()):
    $st = $conn->prepare("SELECT * FROM laporan WHERE pendaftaran_id=? AND modul_id=?");
    $st->bind_param("ii", $pendaftaran['id'], $mod['id']);
    $st->execute();
    $lap = $st->get_result()->fetch_assoc();
    $st->close();
  ?>
    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
      <div class="flex flex-col md:flex-row md:justify-between gap-4">
        <div>
          <h3 class="text-xl font-semibold text-blue-700"><?= htmlspecialchars($mod['judul_modul']) ?></h3>
          <p class="text-sm text-gray-500">🕒 Deadline: <?= date('d M Y', strtotime($mod['tanggal_deadline'])) ?></p>
          <?php if ($mod['file_materi']): ?>
            <a href="../uploads/<?= $mod['file_materi'] ?>" class="text-sm text-blue-600 hover:underline mt-1 inline-block">📥 Unduh Materi</a>
          <?php endif; ?>
        </div>
        <div class="text-sm md:text-right text-gray-700">
          <?php if ($lap): ?>
            <p class="text-green-600 font-medium">✅ Sudah dikumpulkan</p>
            <?php if (!is_null($lap['nilai'])): ?>
              <p>Nilai: <span class="font-bold"><?= $lap['nilai'] ?></span></p>
              <?php if ($lap['feedback']): ?>
                <p class="italic text-gray-500 mt-1">"<?= htmlspecialchars($lap['feedback']) ?>"</p>
              <?php endif; ?>
            <?php else: ?>
              <a href="upload_laporan.php" class="text-blue-600 hover:underline">📤 Re-upload Laporan</a>
            <?php endif; ?>
          <?php else: ?>
            <a href="upload_laporan.php" class="text-blue-600 hover:underline">📤 Upload Laporan</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
