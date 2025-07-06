<?php
$pageTitle = 'Manajemen Pengguna';
$activePage = 'pengguna';

require_once '../config.php';
require_once 'templates/header.php';
// Proses hapus jika ada parameter ?delete=id
if (isset($_GET['delete'])) {
    $idToDelete = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $idToDelete);
    $stmt->execute();
    $stmt->close();
    header("Location: pengguna.php");
    exit();
}

// Ambil semua pengguna (kecuali asisten yang sedang login)
$sql = "SELECT id, nama, email, role FROM users ORDER BY role ASC, nama ASC";
$result = $conn->query($sql);
?>

<div class="bg-white p-6 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Daftar Pengguna</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">Nama</th>
                    <th class="px-6 py-3 text-left">Email</th>
                    <th class="px-6 py-3 text-left">Role</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($user['nama']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-6 py-4 capitalize"><?php echo $user['role']; ?></td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="pengguna.php?delete=<?php echo $user['id']; ?>" 
                                       class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-xs transition"
                                       onclick="return confirm('Yakin ingin menghapus pengguna ini?');">
                                        Hapus
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400 italic text-xs">Login aktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-6 text-gray-500">Belum ada pengguna terdaftar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
