<?php
include "../Koneksi.php";

$search = isset($_GET['query']) ? trim($_GET['query']) : '';

$meetings = [];
$result_meetings = mysqli_query($koneksi, "SELECT * FROM meetings");

if ($result_meetings) {
    while ($row = mysqli_fetch_assoc($result_meetings)) {
        $meetings[] = $row;
    }
}

if ($search !== '') {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT * FROM rooms_meeting WHERE room_name LIKE ? ORDER BY id DESC"
    );
    $like = "%{$search}%";
    mysqli_stmt_bind_param($stmt, "s", $like);
} else {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT * FROM rooms_meeting ORDER BY id DESC"
    );
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rooms = mysqli_fetch_all($result, MYSQLI_ASSOC);

$currentTime = time();

foreach ($rooms as &$room) {
    $isBooked = false;

    foreach ($meetings as $meeting) {
        if (
            isset($meeting['lokasi'], $meeting['tanggal'], $meeting['waktu']) &&
            $meeting['lokasi'] === $room['room_name']
        ) {
            $meetingDateTime = strtotime($meeting['tanggal'] . ' ' . $meeting['waktu']);

            if ($meetingDateTime !== false && $meetingDateTime >= $currentTime) {
                $isBooked = true;
                break;
            }
        }
    }

    $room['status'] = $isBooked ? 'booked' : 'available';
}
unset($room);

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ruangan Rapat</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.navbar { background-color: #c3c7ceff !important; }
#sidebarToggle { 
    background-color: #7a8ca0 !important; 
    width:250px; 
    flex-shrink:0; 
    overflow:hidden;
    transition:width 0.3s ease;
}
#sidebarToggle.collapse:not(.show) { width:0; }
#sidebarToggle.collapse.show { width:250px; }
#sidebarToggle.collapsing { width:0 !important; transition: width 0.3s ease; }
.sidebar-nav { overflow-y: auto; height: 100%; }
.sidebar-link:hover { background-color: #343a4041 !important; color: #fff !important; border-radius: 0.5rem; transition:0.3s; }
main { transition:none; }
.dropdown-menu { padding:0.4rem; overflow:hidden; }
.dropdown-menu .dropdown-item { padding:0.55rem 1rem; border-radius:0.375rem; transition:0.2s; }
.dropdown-menu .dropdown-item:hover { background-color:#d8f8fcff; color:#212529; }
.dropdown-menu .dropdown-item.text-danger:hover { background-color:#fdecea; color:#dc3545; }
.footer-custom { background-color:#e9ecef; color:#6c757d; }
.card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.25) !important; transform: translateY(-4px); transition:0.3s; }
.active-link { background-color: #343a4041; border-radius:0.5rem; color:#fff !important; }
</style>
</head>

<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary py-4 flex-shrink-0">
<div class="container-fluid">
  <button class="btn btn-light ms-4" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarToggle" style="width:50px; height:50px;">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
      <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
    </svg>
  </button>

  <div class="mx-auto position-absolute start-50 translate-middle-x">
    <span class="navbar-brand fs-2 fw-bold text-dark">Pengelolaan Rapat</span>
  </div>

  <div class="dropdown me-4">
    <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center shadow-sm" type="button" data-bs-toggle="dropdown" style="width:50px; height:50px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="#333" viewBox="0 0 16 16">
        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
      </svg>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><a class="dropdown-item" href="Profil_user.php">Profil</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="../Logout.php">Logout</a></li>
    </ul>
  </div>
</div>
</nav>

<div class="d-flex flex-grow-1">
<div class="collapse collapse-horizontal show bg-dark min-vh-100 d-flex flex-column" id="sidebarToggle">
  <div class="pt-3 sidebar-nav">
    <a href="Home_user.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Home_user.php') ? 'active-link' : '' ?>">Home</a>
    <a href="Rooms_user.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Rooms_user.php') ? 'active-link' : '' ?>">Meeting Rooms</a>
    <a href="Calendars_user.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Calendars_user.php') ? 'active-link' : '' ?>">Calendars</a>
    <a href="History_user.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'History_user.php') ? 'active-link' : '' ?>">History</a>
  </div>
</div>

<main class="container my-4 d-flex flex-column flex-grow-1">

    <form class="w-100 text-center mt-4 mb-5 d-flex justify-content-center" method="get" action="Rooms_user.php" style="position:relative;">
        <div class="input-group w-50">
            <input type="text" name="query" class="form-control form-control-lg" placeholder="Cari ruangan..." value="<?= htmlspecialchars($search) ?>">
            <?php if ($search !== ''): ?>
                <span class="input-group-text" style="cursor:pointer;" onclick="window.location='Rooms_user.php'">&times;</span>
            <?php endif; ?>
            <button class="btn btn-primary btn-lg" type="submit">Search</button>
        </div>
    </form>

    <?php if (count($rooms) === 0): ?>
    <div class="col-12 text-center">
        <p class="fs-4 text-muted">Tidak ada ruangan.</p>
    </div>
    <?php else: ?>
    <div class="row g-4 justify-content-center">
    <?php foreach ($rooms as $room): ?>
    <div class="col-6 col-md-3">
        <div class="card h-100">
          <?php
          $isUrl = preg_match('/^https?:\/\//', $room['photo_name']);
          $imgSrc = $isUrl ? $room['photo_name'] : $room['photo_path'].$room['photo_name'];
          ?>
            <img src="<?= htmlspecialchars($imgSrc) ?>" class="card-img-top" style="height:180px;object-fit:cover;" alt="<?= htmlspecialchars($room['room_name']) ?>">

            <div class="card-body text-center">
                <h6 class="fw-bold"><?= htmlspecialchars($room['room_name']) ?></h6>
                <p class="mb-1">Kapasitas: <?= $room['capacity'] ?> orang</p>

                <?php if ($room['status'] === 'available'): ?>
                    <span class="badge bg-success">Tersedia</span>
                <?php else: ?>
                    <span class="badge bg-danger">Terbooking</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

</main> 
</div> 

<footer class="footer-custom text-center py-3 border-top mt-auto">
    <div class="text-muted small">&copy; 2025 - User Pengelolaan Rapat</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>