<?php
$pageTitle = 'Manajemen Modul';
$activePage = 'modul';

require_once '../config.php';
require_once 'templates/header.php';

$judul_modul = $tanggal_deadline = $file_materi = $mata_praktikum_id = '';
$isEdit = false;
$editId = null;

// Ambil daftar praktikum untuk dropdown
$praktikumList = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum ASC");

// === HANDLE DELETE ===
if (isset($_GET['delete'])) {
    $idToDelete = (int) $_GET['delete'];
    // Hapus file jika ada
    $q = $conn->query("SELECT file_materi FROM modul WHERE id = $idToDelete");
    if ($q && $q->num_rows) {
        $f = $q->fetch_assoc();
        if ($f['file_materi'] && file_exists("../uploads/" . $f['file_materi'])) {
            unlink("../uploads/" . $f['file_materi']);
        }
    }
    $stmt = $conn->prepare("DELETE FROM modul WHERE id = ?");
    $stmt->bind_param("i", $idToDelete);
    $stmt->execute();
    $stmt->close();
    header("Location: modul.php");
    exit();
}

// === HANDLE FORM SUBMIT ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_modul = trim($_POST['judul_modul']);
    $tanggal_deadline = $_POST['tanggal_deadline'];
    $mata_praktikum_id = (int) $_POST['mata_praktikum_id'];
    $uploadOk = true;
    $filename = '';

    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) {
        $allowed = ['pdf', 'doc', 'docx'];
        $ext = strtolower(pathinfo($_FILES['file_materi']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $uploadOk = false;
        } else {
            $filename = uniqid() . '_' . basename($_FILES['file_materi']['name']);
            move_uploaded_file($_FILES['file_materi']['tmp_name'], "../uploads/" . $filename);
        }
    }

    if (isset($_POST['id']) && $_POST['id']) {
        // UPDATE
        $editId = (int) $_POST['id'];
        if ($uploadOk && $filename !== '') {
            $stmt = $conn->prepare("UPDATE modul SET judul_modul = ?, tanggal_deadline = ?, mata_praktikum_id = ?, file_materi = ? WHERE id = ?");
            $stmt->bind_param("ssisi", $judul_modul, $tanggal_deadline, $mata_praktikum_id, $filename, $editId);
        } else {
            $stmt = $conn->prepare("UPDATE modul SET judul_modul = ?, tanggal_deadline = ?, mata_praktikum_id = ? WHERE id = ?");
            $stmt->bind_param("ssii", $judul_modul, $tanggal_deadline, $mata_praktikum_id, $editId);
        }
        $stmt->execute();
        $stmt->close();
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO modul (judul_modul, tanggal_deadline, mata_praktikum_id, file_materi) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $judul_modul, $tanggal_deadline, $mata_praktikum_id, $filename);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: modul.php");
    exit();
}

// === HANDLE EDIT MODE ===
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM modul WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $isEdit = true;
        $modul = $result->fetch_assoc();
        $judul_modul = $modul['judul_modul'];
        $tanggal_deadline = $modul['tanggal_deadline'];
        $mata_praktikum_id = $modul['mata_praktikum_id'];
    }
    $stmt->close();
}

// === FETCH SEMUA MODUL ===
$sql = "SELECT m.id, m.judul_modul, m.file_materi, m.tanggal_deadline, p.nama_praktikum 
        FROM modul m 
        LEFT JOIN mata_praktikum p ON m.mata_praktikum_id = p.id
        ORDER BY p.nama_praktikum ASC, m.judul_modul ASC";
$modulList = $conn->query($sql);
?>

<div class="bg-white p-6 rounded-xl shadow-md mb-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= $isEdit ? "Edit Modul" : "Tambah Modul Praktikum" ?></h2>

    <form action="modul.php" method="POST" enctype="multipart/form-data" class="space-y-4 max-w-xl">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $editId ?>">
        <?php endif; ?>

        <div>
            <label class="block text-sm font-medium text-gray-700">Judul Modul</label>
            <input type="text" name="judul_modul" value="<?= htmlspecialchars($judul_modul) ?>" required class="w-full border rounded-md p-2 mt-1">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Tanggal Deadline</label>
            <input type="date" name="tanggal_deadline" value="<?= htmlspecialchars($tanggal_deadline) ?>" required class="w-full border rounded-md p-2 mt-1">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Mata Praktikum</label>
            <select name="mata_praktikum_id" required class="w-full border rounded-md p-2 mt-1">
                <option value="">-- Pilih Praktikum --</option>
                <?php while ($row = $praktikumList->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id'] == $mata_praktikum_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['nama_praktikum']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">File Materi (PDF/DOCX)</label>
            <input type="file" name="file_materi" class="w-full border rounded-md p-2 mt-1">
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
            <?= $isEdit ? "Simpan Perubahan" : "Tambah Modul" ?>
        </button>
        <?php if ($isEdit): ?>
            <a href="modul.php" class="ml-2 text-gray-600 underline">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="bg-white p-6 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Daftar Modul Praktikum</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">Praktikum</th>
                    <th class="px-6 py-3 text-left">Judul Modul</th>
                    <th class="px-6 py-3 text-left">File Materi</th>
                    <th class="px-6 py-3 text-left">Deadline</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                <?php if ($modulList->num_rows > 0): ?>
                    <?php while ($row = $modulList->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['nama_praktikum']); ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['judul_modul']); ?></td>
                            <td class="px-6 py-4">
                                <?php if ($row['file_materi']): ?>
                                    <a href="../uploads/<?= $row['file_materi']; ?>" target="_blank" class="text-blue-500 hover:underline">
                                        Lihat File
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400 italic">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4"><?= date('d M Y', strtotime($row['tanggal_deadline'])); ?></td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <a href="modul.php?edit=<?= $row['id']; ?>"
                                   class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-md text-xs">
                                   Edit
                                </a>
                                <a href="modul.php?delete=<?= $row['id']; ?>"
                                   onclick="return confirm('Yakin ingin menghapus modul ini?');"
                                   class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-xs">
                                   Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-500">Belum ada modul ditambahkan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
