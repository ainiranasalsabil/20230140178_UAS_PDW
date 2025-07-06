<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login atau bukan asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Asisten - <?= $pageTitle ?? 'Dashboard'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-blue-600 text-white flex flex-col shadow-md">
        <div class="p-6 border-b border-blue-500 text-center">
            <h3 class="text-2xl font-bold tracking-wide">Panel Asisten</h3>
            <p class="text-sm text-blue-200 mt-1 italic"><?= htmlspecialchars($_SESSION['nama']); ?></p>
        </div>
        <nav class="flex-grow mt-4">
            <ul class="space-y-1 px-4">
                <?php 
                    $activeClass = 'bg-blue-700 text-white font-semibold';
                    $inactiveClass = 'text-white hover:bg-blue-700 hover:text-white';
                ?>
                <li>
                    <a href="dashboard.php" class="<?= $activePage == 'dashboard' ? $activeClass : $inactiveClass; ?> flex items-center gap-3 px-4 py-3 rounded-md transition duration-200">
                        <span>🏠</span> <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="mata_praktikum.php" class="<?= $activePage == 'mata_praktikum' ? $activeClass : $inactiveClass; ?> flex items-center gap-3 px-4 py-3 rounded-md transition duration-200">
                        <span>📘</span> <span>Mata Praktikum</span>
                    </a>
                </li>
                <li>
                    <a href="modul.php" class="<?= $activePage == 'modul' ? $activeClass : $inactiveClass; ?> flex items-center gap-3 px-4 py-3 rounded-md transition duration-200">
                        <span>📄</span> <span>Manajemen Modul</span>
                    </a>
                </li>
                <li>
                    <a href="laporan.php" class="<?= $activePage == 'laporan' ? $activeClass : $inactiveClass; ?> flex items-center gap-3 px-4 py-3 rounded-md transition duration-200">
                        <span>📥</span> <span>Laporan Masuk</span>
                    </a>
                </li>
                <li>
                    <a href="pengguna.php" class="<?= $activePage == 'pengguna' ? $activeClass : $inactiveClass; ?> flex items-center gap-3 px-4 py-3 rounded-md transition duration-200">
                        <span>👥</span> <span>Kelola Pengguna</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content Start -->
    <main class="flex-1 p-6 lg:p-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold tracking-tight"><?= $pageTitle ?? 'Dashboard'; ?></h1>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded-md transition duration-300">
                Logout
            </a>
        </div>
