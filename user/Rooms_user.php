<?php
// Rooms.php
$search = isset($_GET['query']) ? $_GET['query'] : '';

$rooms = [
    ['name' => 'Ruangan Lt 2', 'img' => '../assets/Ruangan Lt 2.jpeg'],
    ['name' => 'Ruangan Lt 3', 'img' => '../assets/Ruangan.3.jpeg'],
    ['name' => 'Ruangan Lt 4', 'img' => '../assets/Ruangan Lt 4.jpeg'],
    ['name' => 'Ruangan Lt 7', 'img' => '../assets/Rungan Lt 7.jpeg'],
];

// Filter data
$filteredRooms = [];
if ($search === '') {
    $filteredRooms = $rooms;
} else {
    foreach ($rooms as $room) {
        if (stripos($room['name'], $search) !== false) {
            $filteredRooms[] = $room;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Meeting Rooms</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
        /* Hover biru muda dengan border-radius untuk dropdown */
        .dropdown-item:hover {
            background-color: #5bc0de; /* biru muda */
            color: white;
            border-radius: 5px;
        }
    </style>
<body class="d-flex flex-column min-vh-100">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary py-4">
<div class="container-fluid d-flex justify-content-center align-items-center position-relative">
    <button class="btn btn-light position-absolute start-0 ms-5" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">&#9776;</button>
    <span class="navbar-brand mb-0 h1 text-center fs-1">Pengelolaan Rapat</span>

<div class="dropdown position-absolute end-0 me-5">
    <button class="btn btn-light rounded-circle" type="button" id="profileDropdown" data-bs-toggle="dropdown" style="width:50px;height:50px;">
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

<!-- Sidebar + Main -->
<div class="d-flex flex-grow-1">
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

<main class="container my-4 flex-grow-1">

    <!-- Search bar modern, compact -->
    <form class="w-100 text-center mt-4 mb-5 d-flex justify-content-center" method="get" action="Rooms_user.php">
        <div class="input-group w-50">
            <input type="text" name="query" class="form-control form-control-lg border border-2 border-dark-subtle" placeholder="Cari ruangan..." value="<?= htmlspecialchars($search) ?>">

            <?php if ($search !== ''): ?>
                <button type="button" class="btn btn-outline-secondary border border-2 border-dark-subtle" style="padding:0 0.5rem; font-weight:bold; font-size:1.25rem;" onclick="window.location='Rooms_user.php'">×</button>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-lg">Search</button>
        </div>
    </form>

    <!-- Foto grid -->
    <div class="row g-4 justify-content-center">
        <?php if (count($filteredRooms) === 0): ?>
            <div class="col-12 text-center"><p class="fs-4 text-muted">Tidak ada ruangan yang ditemukan.</p></div>
        <?php else: ?>
            <?php foreach($filteredRooms as $room): ?>
                <div class="col-6 col-md-3">
                    <div class="card h-100">
                        <img src="<?= htmlspecialchars($room['img']) ?>" class="card-img-top h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($room['name']) ?>">
                        <div class="card-body text-center">
                            <p class="card-text fw-bold"><?= htmlspecialchars($room['name']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
</div>

<footer class="bg-dark text-white text-center py-2 mt-auto">
    &copy; 2025 - Dashboard Kamu
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
