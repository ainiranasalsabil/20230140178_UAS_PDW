<?php
require_once '../config.php';
session_start();

$pageTitle = "Praktikum Saya";
$activePage = "my_courses";

include 'templates/header_mahasiswa.php';

$mahasiswa_id = $_SESSION['user_id'];
$sql = "
    SELECT mp.* FROM pendaftaran p
    JOIN mata_praktikum mp ON p.mata_praktikum_id = mp.id
    WHERE p.mahasiswa_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mahasiswa_id);
$stmt->execute();
$praktikumList = $stmt->get_result();
$stmt->close();
?>

<div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow mt-6">
    <h1 class="text-2xl font-bold mb-4">Praktikum yang Anda Ikuti</h1>
    <?php while ($praktikum = $praktikumList->fetch_assoc()): ?>
        <div class="mb-4 p-4 border rounded-md bg-gray-50">
            <h2 class="text-lg font-semibold"><?= htmlspecialchars($praktikum['nama_praktikum']) ?></h2>
            <p><?= htmlspecialchars($praktikum['deskripsi']) ?></p>
            <a href="praktikum_detail.php?id=<?= $praktikum['id'] ?>" class="text-blue-600 hover:underline">Lihat Detail</a>
        </div>
    <?php endwhile; ?>
</div>

<?php include 'templates/footer_mahasiswa.php'; ?>
