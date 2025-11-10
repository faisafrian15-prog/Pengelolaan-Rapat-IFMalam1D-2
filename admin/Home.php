<?php
// Home.php

session_start();

// Simulasi database (gunakan session agar data tetap sementara)
if (!isset($_SESSION['rooms'])) {
    $_SESSION['rooms'] = [
        ['id'=>1,'name'=>'Projek Dinas','desc'=>'Dinas Pendidikan','badge'=>'Mendatang','badge_color'=>'warning','link'=>'Isi.php'],
        ['id'=>2,'name'=>'Projek IT','desc'=>'Dinas Teknologi','badge'=>'Selesai','badge_color'=>'success','link'=>'Isi.php'],
        ['id'=>3,'name'=>'Projek Infrastruktur','desc'=>'Dinas Pekerjaan Umum','badge'=>'Tertunda','badge_color'=>'danger','link'=>'Isi.php'],
    ];
}

$rooms = $_SESSION['rooms'];

// Tambah Projek
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='create') {
    $newId = end($rooms)['id'] + 1;
    $rooms[] = [
        'id' => $newId,
        'name' => $_POST['name'],
        'desc' => $_POST['desc'],
        'badge' => $_POST['status'],
        'badge_color' => $_POST['status'] === 'Mendatang' ? 'warning' : ($_POST['status'] === 'Selesai' ? 'success' : 'danger'),
        'link' => 'Isi.php'
    ];
    $_SESSION['rooms'] = $rooms;
    header("Location: Home.php");
    exit;
}

// Edit Projek
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='edit') {
    foreach ($rooms as &$room) {
        if ($room['id'] == $_POST['id']) {
            $room['name'] = $_POST['name'];
            $room['desc'] = $_POST['desc'];
            $room['badge'] = $_POST['status'];
            $room['badge_color'] = $_POST['status'] === 'Mendatang' ? 'warning' : ($_POST['status'] === 'Selesai' ? 'success' : 'danger');
        }
    }
    $_SESSION['rooms'] = $rooms;
    header("Location: Home.php");
    exit;
}

// Hapus Projek
if (isset($_GET['delete'])) {
    $_SESSION['rooms'] = array_values(array_filter($rooms, function($r) {
        return $r['id'] != $_GET['delete'];
    }));
    header("Location: Home.php");
    exit;
}

// Search
$search = isset($_GET['query']) ? $_GET['query'] : '';
$filteredRooms = [];
foreach ($rooms as $room) {
    if ($search === '' ||
        stripos($room['name'], $search) !== false ||
        stripos($room['desc'], $search) !== false ||
        stripos($room['badge'], $search) !== false) {
        $filteredRooms[] = $room;
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
        .dropdown-item:hover {
            background-color: #5bc0de;
            color: white;
            border-radius: 5px;
        }
        .card:hover {
            transform: scale(1.03);
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .card-footer,
        .card-footer * {
            position: relative;
            z-index: 5;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary py-4">
    <div class="container-fluid d-flex justify-content-center align-items-center position-relative">
        <button class="btn btn-light position-absolute start-0 ms-5" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">&#9776;</button>
        <span class="navbar-brand mb-0 h1 text-center fs-1">Pengelolaan Rapat</span>

        <div class="dropdown position-absolute end-0 me-5">
            <button class="btn btn-light rounded-circle" type="button" id="profileDropdown" data-bs-toggle="dropdown" style="width:50px; height:50px;">
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

<!-- Sidebar + Main Content -->
<div class="d-flex flex-grow-1">

    <!-- Sidebar -->
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

    <!-- Main Content -->
    <main class="container my-4 d-flex flex-column flex-grow-1">

        <!-- Search + Create -->
        <div class="d-flex justify-content-center align-items-center mt-4 mb-5">
            <button type="button" class="btn btn-success btn-lg me-3" data-bs-toggle="modal" data-bs-target="#createModal">
                + Tambah
            </button>

            <form class="d-flex w-50" method="get" action="Home.php">
                <div class="input-group w-100">
                    <input type="text" name="query" class="form-control form-control-lg border border-2 border-dark-subtle"
                        placeholder="Cari nama, deskripsi, atau status..." value="<?= htmlspecialchars($search) ?>">
                    <?php if ($search !== ''): ?>
                        <button type="button" class="btn btn-outline-secondary border border-2 border-dark-subtle"
                            style="padding:0 0.5rem; font-weight:bold; font-size:1.25rem;"
                            onclick="window.location='Home.php'">×</button>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary btn-lg">Search</button>
                </div>
            </form>
        </div>

        <!-- Cards -->
        <div class="row g-4 justify-content-start">
            <?php if (count($filteredRooms) === 0): ?>
                <div class="col-12 text-center"><p class="fs-5 text-muted">Tidak ada hasil ditemukan.</p></div>
            <?php else: ?>
                <?php foreach($filteredRooms as $room): ?>
                    <div class="col-md-6 col-lg-3 d-flex">
                        <div class="card h-100 shadow-sm position-relative d-flex flex-column justify-content-between w-100">
                            <div class="card-body py-5">
                                <h5 class="card-title fs-5"><?= htmlspecialchars($room['name']) ?></h5>
                                <p class="card-text fs-6"><?= htmlspecialchars($room['desc']) ?></p>

                                <!-- Klik card untuk buka Isi.php -->
                                <a href="<?= htmlspecialchars($room['link'] . '?id=' . $room['id']) ?>" class="stretched-link"></a>
                            </div>
                            <span class="badge bg-<?= htmlspecialchars($room['badge_color']) ?> m-3 align-self-start"><?= htmlspecialchars($room['badge']) ?></span>

                            <div class="card-footer d-flex justify-content-between">
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $room['id'] ?>">Edit</button>
                                <a href="Home.php?delete=<?= $room['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal<?= $room['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $room['id'] ?>" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form method="POST" action="Home.php">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= $room['id'] ?>">
                            <div class="modal-header">
                              <h5 class="modal-title" id="editModalLabel<?= $room['id'] ?>">Edit Projek</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                              <div class="mb-3">
                                <label class="form-label">Nama Projek</label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($room['name']) ?>">
                              </div>
                              <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="desc" class="form-control" rows="3" required><?= htmlspecialchars($room['desc']) ?></textarea>
                              </div>
                              <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                  <option value="Mendatang" <?= $room['badge']=='Mendatang'?'selected':'' ?>>Mendatang</option>
                                  <option value="Selesai" <?= $room['badge']=='Selesai'?'selected':'' ?>>Selesai</option>
                                  <option value="Tertunda" <?= $room['badge']=='Tertunda'?'selected':'' ?>>Tertunda</option>
                                </select>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="submit" class="btn btn-success">Simpan Perubahan</button>
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

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-auto w-100">
    &copy; 2025 - Dashboard Admin
</footer>

<!-- Modal Create dengan Tabs (Projek & Jadwal Rapat) -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createModalLabel">Tambah Data Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="createTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="projek-tab" data-bs-toggle="tab" data-bs-target="#projek" type="button" role="tab">Tambah Projek</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="rapat-tab" data-bs-toggle="tab" data-bs-target="#rapat" type="button" role="tab">Tambah Jadwal Rapat</button>
          </li>
        </ul>

        <div class="tab-content" id="createTabsContent">
          <!-- Form Tambah Projek -->
          <div class="tab-pane fade show active" id="projek" role="tabpanel">
            <form method="POST" action="Home.php">
              <input type="hidden" name="action" value="create">
              <div class="mb-3">
                <label class="form-label">Nama Projek</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="desc" class="form-control" rows="3" required></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                  <option value="Mendatang">Mendatang</option>
                  <option value="Selesai">Selesai</option>
                  <option value="Tertunda">Tertunda</option>
                </select>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-success">💾 Simpan Projek</button>
              </div>
            </form>
          </div>

          <!-- Form Tambah Jadwal Rapat -->
          <div class="tab-pane fade" id="rapat" role="tabpanel">
            <form method="POST" action="Isi.php">
              <input type="hidden" name="action" value="create_rapat">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Judul Rapat</label>
                    <input type="text" name="judul" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Hari / Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Waktu</label>
                    <input type="text" name="waktu" class="form-control" placeholder="Contoh: 10:00 - 12:00 WIB" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="lokasi" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Agenda</label>
                    <textarea name="agenda" class="form-control" rows="3" required></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Daftar Peserta</label>
                    <textarea name="peserta" class="form-control" rows="3" placeholder="Pisahkan dengan koma" required></textarea>
                    <small class="text-muted">Gunakan koma untuk memisahkan nama peserta</small>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Slide / PPT</label>
                    <input type="text" name="ppt" class="form-control" placeholder="Nama file atau URL lengkap">
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-success">💾 Simpan Jadwal Rapat</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
