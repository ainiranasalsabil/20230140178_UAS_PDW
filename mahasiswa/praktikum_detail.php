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

<div class="max-w-4xl mx-auto mt-6 bg-white p-6 rounded-lg shadow-md">
  <div class="flex justify-between items-center mb-4">
    <div>
      <h2 class="text-2xl font-bold"><?= htmlspecialchars($prak['nama_praktikum']) ?></h2>
      <p class="text-gray-600"><?= htmlspecialchars($prak['deskripsi']) ?></p>
    </div>
    <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">Logout</a>
  </div>

  <?php while ($mod = $moduls->fetch_assoc()):
    // cek laporan
    $st = $conn->prepare("SELECT * FROM laporan WHERE pendaftaran_id=? AND modul_id=?");
    $st->bind_param("ii", $pendaftaran['id'], $mod['id']);
    $st->execute();
    $lap = $st->get_result()->fetch_assoc();
    $st->close();
  ?>
    <div class="border p-4 mb-4 rounded-lg">
      <div class="flex justify-between">
        <div>
          <h3 class="font-semibold"><?= htmlspecialchars($mod['judul_modul']) ?></h3>
          <p class="text-sm text-gray-500">Deadline: <?= date('d M Y', strtotime($mod['tanggal_deadline'])) ?></p>
          <?php if ($mod['file_materi']): ?>
            <a href="../uploads/<?= $mod['file_materi'] ?>" class="text-blue-600 hover:underline">Unduh Materi</a>
          <?php endif; ?>
        </div>
        <div class="text-right">
          <?php if ($lap): ?>
            <p class="text-green-600">Sudah dikumpulkan</p>
            <?php if (!is_null($lap['nilai'])): ?>
              <p>Nilai: <strong><?= $lap['nilai'] ?></strong></p>
              <p class="italic"><?= htmlspecialchars($lap['feedback']) ?></p>
            <?php else: ?>
              <a href="upload_laporan.php" class="text-blue-600 hover:underline">Re-upload Laporan</a>
            <?php endif; ?>
          <?php else: ?>
            <a href="upload_laporan.php" class="text-blue-600 hover:underline">Upload Laporan</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
