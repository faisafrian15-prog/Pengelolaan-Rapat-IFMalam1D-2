<?php
// Rooms.php

$search = isset($_GET['query']) ? $_GET['query'] : '';

// Data ruangan / projek
$rooms = [
    ['name' => 'Projek Dinas', 'desc' => 'Dinas Pendidikan', 'badge' => 'Mendatang', 'badge_color'=>'warning', 'link'=>'Isi_user.php'],
    ['name' => 'Projek IT', 'desc' => 'Dinas Teknologi', 'badge' => 'Selesai', 'badge_color'=>'success', 'link'=>'#'],
    ['name' => 'Projek Infrastruktur', 'desc' => 'Dinas Pekerjaan Umum', 'badge' => 'Tertunda', 'badge_color'=>'danger', 'link'=>'#'],
];

// Filter data sesuai search (bisa dari nama, deskripsi, atau status)
$filteredRooms = [];
if ($search === '') {
    $filteredRooms = $rooms;
} else {
    foreach ($rooms as $room) {
        if (
            stripos($room['name'], $search) !== false ||
            stripos($room['desc'], $search) !== false ||
            stripos($room['badge'], $search) !== false // ✅ Tambahan: pencarian berdasarkan status
        ) {
            $filteredRooms[] = $room;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Hover biru muda dengan border-radius untuk dropdown */
        .dropdown-item:hover {
            background-color: #5bc0de; /* biru muda */
            color: white;
            border-radius: 5px;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary py-4">
    <div class="container-fluid d-flex justify-content-center align-items-center position-relative">
        <button class="btn btn-light position-absolute start-0 ms-5" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">&#9776;</button>
        <span class="navbar-brand mb-0 h1 text-center fs-1">Pengelolaan Rapat</span>

        <!-- Profil dropdown -->
        <div class="dropdown position-absolute end-0 me-5">
            <button class="btn btn-light rounded-circle" type="button" id="profileDropdown" data-bs-toggle="dropdown" style="width:50px; height:50px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#333">
                    <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/>
                </svg>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                <li><a class="dropdown-item" href="Profil_user.php">Profil</a></li>
                <li><a class="dropdown-item mt-2" href="../Logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Sidebar + Main Content -->
<div class="d-flex flex-grow-1">

    <!-- Sidebar Offcanvas -->
    <div class="collapse collapse-horizontal" id="sidebar">
        <div class="d-flex flex-column bg-dark text-white h-100" style="width:220px;">
            <div class="d-flex flex-column mt-4 mb-5">
                <a href="Home_user.php" class="btn btn-dark w-100 fs-4 mb-2 text-start ps-3">Home</a>
                <a href="Rooms_user.php" class="btn btn-dark w-100 fs-4 mb-2 text-start ps-3">Meeting Rooms</a>
                <a href="Calendars_user.php" class="btn btn-dark w-100 fs-4 mb-2 text-start ps-3">Calendars</a>
                <a href="History_user.php" class="btn btn-dark w-100 fs-4 text-start ps-3">History</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container my-4 d-flex flex-column flex-grow-1">

        <!-- Search Bar -->
        <form class="w-100 text-center mt-4 mb-5 d-flex justify-content-center" method="get" action="Home_user.php">
            <div class="input-group w-50">
                <input type="text" name="query" class="form-control form-control-lg border border-2 border-dark-subtle" placeholder="Cari nama, deskripsi, atau status..." value="<?= htmlspecialchars($search) ?>">

                <?php if ($search !== ''): ?>
                    <button type="button" class="btn btn-outline-secondary border border-2 border-dark-subtle" style="padding:0 0.5rem; font-weight:bold; font-size:1.25rem;" onclick="window.location='Home_user.php'">×</button>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary btn-lg">Search</button>
            </div>
        </form>

        <!-- Cards Grid -->
        <div class="row g-4 justify-content-start">
            <?php if (count($filteredRooms) === 0): ?>
                <div class="col-12 text-center"><p class="fs-5 text-muted">Tidak ada hasil ditemukan.</p></div>
            <?php else: ?>
                <?php foreach($filteredRooms as $room): ?>
                    <div class="col-md-6 col-lg-3 d-flex">
                        <a href="<?= $room['link'] ?>" class="card h-100 shadow-sm text-decoration-none d-flex flex-column justify-content-between w-100">
                            <div class="card-body py-5">
                                <h5 class="card-title fs-5"><?= htmlspecialchars($room['name']) ?></h5>
                                <p class="card-text fs-6"><?= htmlspecialchars($room['desc']) ?></p>
                            </div>
                            <span class="badge bg-<?= $room['badge_color'] ?> m-3 align-self-start"><?= htmlspecialchars($room['badge']) ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>
</div>

<!-- Footer Full-Width -->
<footer class="bg-dark text-white text-center py-3 mt-auto w-100">
    &copy; 2025 - Dashboard Kamu
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
