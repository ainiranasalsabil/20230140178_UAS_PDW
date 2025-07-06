<?php
$pageTitle = 'Mata Praktikum';
$activePage = 'mata_praktikum';

require_once '../config.php';
require_once 'templates/header.php';
$nama = $deskripsi = $semester = $tahun_ajaran = "";
$isEdit = false;
$idEdit = null;

// === HANDLE TAMBAH / EDIT ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);
    $semester = trim($_POST['semester']);
    $tahun_ajaran = trim($_POST['tahun_ajaran']);

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Edit
        $idEdit = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE mata_praktikum SET nama_praktikum = ?, deskripsi = ?, semester = ?, tahun_ajaran = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nama, $deskripsi, $semester, $tahun_ajaran, $idEdit);
        $stmt->execute();
        $stmt->close();
    } else {
        // Tambah
        $stmt = $conn->prepare("INSERT INTO mata_praktikum (nama_praktikum, deskripsi, semester, tahun_ajaran) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $deskripsi, $semester, $tahun_ajaran);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: mata_praktikum.php");
    exit();
}

// === HANDLE DELETE ===
if (isset($_GET['delete'])) {
    $idToDelete = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM mata_praktikum WHERE id = ?");
    $stmt->bind_param("i", $idToDelete);
    $stmt->execute();
    $stmt->close();
    header("Location: mata_praktikum.php");
    exit();
}

// === HANDLE EDIT FORM VIEW ===
if (isset($_GET['edit'])) {
    $idEdit = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM mata_praktikum WHERE id = ?");
    $stmt->bind_param("i", $idEdit);
    $stmt->execute();
    $resultEdit = $stmt->get_result();
    if ($resultEdit->num_rows === 1) {
        $isEdit = true;
        $data = $resultEdit->fetch_assoc();
        $nama = $data['nama_praktikum'];
        $deskripsi = $data['deskripsi'];
        $semester = $data['semester'];
        $tahun_ajaran = $data['tahun_ajaran'];
    }
    $stmt->close();
}

// === Ambil Semua Data ===
$result = $conn->query("SELECT * FROM mata_praktikum ORDER BY tahun_ajaran DESC, semester DESC");
?>

<div class="bg-white p-6 rounded-xl shadow-md mb-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= $isEdit ? 'Edit Mata Praktikum' : 'Tambah Mata Praktikum' ?></h2>

    <form action="mata_praktikum.php" method="POST" class="space-y-4 max-w-xl">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $idEdit ?>">
        <?php endif; ?>

        <div>
            <label class="block text-sm font-medium text-gray-700">Nama Praktikum</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($nama) ?>" required class="mt-1 w-full p-2 border rounded-md">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
            <textarea name="deskripsi" rows="2" class="mt-1 w-full p-2 border rounded-md"><?= htmlspecialchars($deskripsi) ?></textarea>
        </div>

        <div class="flex gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Semester</label>
                <input type="text" name="semester" value="<?= htmlspecialchars($semester) ?>" required class="mt-1 w-full p-2 border rounded-md">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" value="<?= htmlspecialchars($tahun_ajaran) ?>" required class="mt-1 w-full p-2 border rounded-md">
            </div>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
            <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Praktikum' ?>
        </button>
        <?php if ($isEdit): ?>
            <a href="mata_praktikum.php" class="ml-2 text-gray-600 underline">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="bg-white p-6 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Daftar Mata Praktikum</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">Nama Praktikum</th>
                    <th class="px-6 py-3 text-left">Deskripsi</th>
                    <th class="px-6 py-3 text-left">Semester</th>
                    <th class="px-6 py-3 text-left">Tahun Ajaran</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($praktikum = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-800">
                                <?= htmlspecialchars($praktikum['nama_praktikum']); ?>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($praktikum['deskripsi']); ?></td>
                            <td class="px-6 py-4"><?= $praktikum['semester']; ?></td>
                            <td class="px-6 py-4"><?= $praktikum['tahun_ajaran']; ?></td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <a href="mata_praktikum.php?edit=<?= $praktikum['id']; ?>"
                                   class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-md text-xs">
                                   Edit
                                </a>
                                <a href="mata_praktikum.php?delete=<?= $praktikum['id']; ?>"
                                   onclick="return confirm('Yakin ingin menghapus praktikum ini?');"
                                   class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-xs">
                                   Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-500">Belum ada mata praktikum.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
