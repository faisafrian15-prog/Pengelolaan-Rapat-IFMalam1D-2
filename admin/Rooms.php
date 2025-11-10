<?php
// Rooms.php
$search = isset($_GET['query']) ? $_GET['query'] : '';

$rooms = [
    ['name' => 'Ruangan Lt 2', 'img' => '../assets/Ruangan Lt 2.jpeg'],
    ['name' => 'Ruangan Lt 3', 'img' => '../assets/Ruangan.3.jpeg'],
    ['name' => 'Ruangan Lt 4', 'img' => '../assets/Ruangan Lt 4.jpeg'],
    ['name' => 'Ruangan Lt 7', 'img' => '../assets/Rungan Lt 7.jpeg'],
];

// ✅ Tambah ruangan baru
if (isset($_POST['add_room'])) {
    $name = trim($_POST['room_name']);
    $img = trim($_POST['room_img']);
    if ($name !== '') {
        $rooms[] = [
            'name' => $name,
            'img'  => $img !== '' ? $img : '../assets/default-room.jpg'
        ];
    }
}

// ✅ Edit ruangan
if (isset($_POST['edit_room'])) {
    $index = $_POST['room_index'];
    $name = trim($_POST['room_name']);
    $img  = trim($_POST['room_img']);
    if (isset($rooms[$index])) {
        $rooms[$index]['name'] = $name;
        $rooms[$index]['img']  = $img !== '' ? $img : '../assets/default-room.jpg';
    }
}

// ✅ Hapus ruangan
if (isset($_POST['delete_room'])) {
    $index = $_POST['room_index'];
    if (isset($rooms[$index])) {
        unset($rooms[$index]);
        $rooms = array_values($rooms); // reindex array
    }
}

// ✅ Filter data
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
<style>
    .dropdown-item:hover {
        background-color: #5bc0de;
        color: white;
        border-radius: 5px;
    }
</style>
</head>

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
            <li><a class="dropdown-item" href="Profil.php">Profil</a></li>
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
            <a href="Home.php" class="btn btn-dark w-100 fs-4 mb-2 text-start ps-3">Home</a>
            <a href="Rooms.php" class="btn btn-dark w-100 fs-4 mb-2 text-start ps-3">Meeting Rooms</a>
            <a href="Calendars.php" class="btn btn-dark w-100 fs-4 mb-2 text-start ps-3">Calendars</a>
            <a href="History.php" class="btn btn-dark w-100 fs-4 text-start ps-3">History</a>
        </div>
    </div>
</div>

<main class="container my-4 flex-grow-1">

    <!-- Search + Tambah -->
    <form class="w-100 text-center mt-4 mb-5 d-flex justify-content-center" method="get" action="Rooms.php">
        <button type="button" class="btn btn-success btn-lg me-3" data-bs-toggle="modal" data-bs-target="#createModal">
            + Tambah
        </button>

        <div class="input-group w-50">
            <input type="text" name="query" class="form-control form-control-lg border border-2 border-dark-subtle"
                   placeholder="Cari ruangan..." value="<?= htmlspecialchars($search) ?>">

            <?php if ($search !== ''): ?>
                <button type="button" class="btn btn-outline-secondary border border-2 border-dark-subtle"
                        style="padding:0 0.5rem; font-weight:bold; font-size:1.25rem;"
                        onclick="window.location='Rooms.php'">×</button>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-lg">Search</button>
        </div>
    </form>

    <!-- Foto grid -->
    <div class="row g-4 justify-content-center">
        <?php if (count($filteredRooms) === 0): ?>
            <div class="col-12 text-center"><p class="fs-4 text-muted">Tidak ada ruangan yang ditemukan.</p></div>
        <?php else: ?>
            <?php foreach($filteredRooms as $index => $room): ?>
                <div class="col-6 col-md-3">
                    <div class="card h-100">
                        <img src="<?= htmlspecialchars($room['img']) ?>" class="card-img-top h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($room['name']) ?>">
                        <div class="card-body text-center">
                            <p class="card-text fw-bold"><?= htmlspecialchars($room['name']) ?></p>
                            <!-- Tombol Edit dan Hapus -->
                            <div class="d-flex justify-content-center gap-2 mt-2">
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $index ?>">Edit</button>
                                <form method="POST" action="Rooms.php" style="display:inline;">
                                    <input type="hidden" name="room_index" value="<?= $index ?>">
                                    <button type="submit" name="delete_room" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus ruangan ini?')">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Edit Ruangan -->
                <div class="modal fade" id="editModal<?= $index ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $index ?>" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST" action="Rooms.php">
                        <div class="modal-header text-dark">
                          <h5 class="modal-title" id="editModalLabel<?= $index ?>">Edit Ruangan</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="room_index" value="<?= $index ?>">
                          <div class="mb-3">
                            <label class="form-label fw-bold">Nama Ruangan</label>
                            <input type="text" name="room_name" class="form-control" required value="<?= htmlspecialchars($room['name']) ?>">
                          </div>
                          <div class="mb-3">
                            <label class="form-label fw-bold">URL Gambar</label>
                            <input type="text" name="room_img" class="form-control" value="<?= htmlspecialchars($room['img']) ?>">
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" name="edit_room" class="btn btn-success">Simpan Perubahan</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
</div>

<!-- Modal Tambah Ruangan -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="Rooms.php">
        <div class="modal-header text-dark">
          <h5 class="modal-title" id="createModalLabel">Tambah Ruangan Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Nama Ruangan</label>
            <input type="text" name="room_name" class="form-control" placeholder="Masukkan nama ruangan" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">URL Gambar</label>
            <input type="text" name="room_img" class="form-control" placeholder="Contoh: ../assets/RuanganBaru.jpeg">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_room" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<footer class="bg-dark text-white text-center py-2 mt-auto">
    &copy; 2025 - Dashboard Admin
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
