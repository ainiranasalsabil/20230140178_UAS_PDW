<?php
$pageTitle = 'Upload Laporan';
$activePage = 'upload';
require_once '../config.php';
require_once 'templates/header_mahasiswa.php';

$user_id = $_SESSION['user_id'];
$message = "";

// Ambil daftar modul yang sudah didaftarkan mahasiswa
$sql = "
    SELECT m.id, m.judul_modul, m.tanggal_deadline, p.id AS pendaftaran_id
    FROM pendaftaran p
    JOIN modul m ON m.mata_praktikum_id = p.mata_praktikum_id
    WHERE p.mahasiswa_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$modulList = $stmt->get_result();
$stmt->close();

// Proses upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modul_id = (int) $_POST['modul_id'];
    $pendaftaran_id = (int) $_POST['pendaftaran_id'];

    if (isset($_FILES['laporan']) && $_FILES['laporan']['error'] === 0) {
        $allowed = ['pdf', 'doc', 'docx'];
        $ext = strtolower(pathinfo($_FILES['laporan']['name'], PATHINFO_EXTENSION));
        $size = $_FILES['laporan']['size'];

        if (!in_array($ext, $allowed)) {
            $message = "<span class='text-red-600'>Hanya file PDF, DOC, atau DOCX yang diperbolehkan.</span>";
        } elseif ($size > 2 * 1024 * 1024) {
            $message = "<span class='text-red-600'>Ukuran file maksimal 2MB.</span>";
        } else {
            $filename = uniqid() . '_' . basename($_FILES['laporan']['name']);
            $filepath = '../uploads/' . $filename;

            if (!is_dir('../uploads')) {
                mkdir('../uploads', 0777, true);
            }

            if (move_uploaded_file($_FILES['laporan']['tmp_name'], $filepath)) {
                // Simpan ke database
                $stmt = $conn->prepare("INSERT INTO laporan (pendaftaran_id, modul_id, file_laporan, tanggal_kumpul) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iis", $pendaftaran_id, $modul_id, $filename);

                if ($stmt->execute()) {
                    $message = "<span class='text-green-600'>Laporan berhasil diupload!</span>";
                } else {
                    $message = "<span class='text-red-600'>Gagal menyimpan ke database.</span>";
                }
                $stmt->close();
            } else {
                $message = "<span class='text-red-600'>Upload file gagal.</span>";
            }
        }
    } else {
        $message = "<span class='text-red-600'>Silakan pilih file untuk diunggah.</span>";
    }
}
?>

<div class="bg-white p-6 rounded-xl shadow-md max-w-xl mx-auto mt-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Upload Laporan Praktikum</h2>

    <?php if (!empty($message)): ?>
        <div class="mb-4 text-sm"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($modulList->num_rows > 0): ?>
        <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="modul_id" class="block mb-1 font-medium text-sm text-gray-700">Pilih Modul</label>
                <select name="modul_id" id="modul_id" required class="w-full border rounded-md p-2">
                    <?php while ($modul = $modulList->fetch_assoc()): ?>
                        <option value="<?= $modul['id']; ?>" data-pendaftaran="<?= $modul['pendaftaran_id']; ?>">
                            <?= htmlspecialchars($modul['judul_modul']); ?> (Deadline: <?= date('d M Y', strtotime($modul['tanggal_deadline'])); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <input type="hidden" name="pendaftaran_id" id="pendaftaran_id" value="">

            <div>
                <label for="laporan" class="block mb-1 font-medium text-sm text-gray-700">File Laporan</label>
                <input type="file" name="laporan" id="laporan" required class="w-full border rounded-md p-2">
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                Upload
            </button>
        </form>

        <script>
            const modulSelect = document.getElementById("modul_id");
            const pendaftaranInput = document.getElementById("pendaftaran_id");
            modulSelect.addEventListener("change", function () {
                const selectedOption = modulSelect.options[modulSelect.selectedIndex];
                pendaftaranInput.value = selectedOption.dataset.pendaftaran;
            });
            modulSelect.dispatchEvent(new Event('change'));
        </script>
    <?php else: ?>
        <p class="text-gray-500">Anda belum terdaftar di praktikum mana pun.</p>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
