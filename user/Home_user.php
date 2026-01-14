<?php
session_start();
include "../Koneksi.php";

function isUserLoggedIn() {
    return isset($_SESSION['username']) && isset($_SESSION['role']);
}

function authorizeUserOnly() {
    if ($_SESSION['role'] !== 'user') {
        header("Location: ../forbidden.php");
        exit();
    }
}

try {
    if (!isUserLoggedIn()) {
        throw new Exception("Session tidak valid");
    }

    authorizeUserOnly();

    if (!$GLOBALS['koneksi']) {
        throw new Exception("Koneksi database gagal");
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: Login.php");
    exit();
}

$search = '';
if (isset($_GET['query'])) {
    $search = mysqli_real_escape_string($koneksi, $_GET['query']);
}

function getProjects($koneksi, $search) {
    $sql = "SELECT * FROM projects";

    if ($search !== '') {
        $sql .= " WHERE name LIKE '%$search%' 
                  OR description LIKE '%$search%' 
                  OR status LIKE '%$search%'";
    }

    $sql .= " ORDER BY id DESC";
    return mysqli_query($koneksi, $sql);
}

$result = getProjects($koneksi, $search);

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rapat</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.navbar { background-color: #c3c7ceff !important; }
#sidebarToggle { background-color: #7a8ca0 !important; width:250px; flex-shrink:0; overflow:hidden; transition:width 0.3s ease; }
#sidebarToggle.collapse:not(.show) { width:0; }
#sidebarToggle.collapse.show { width:250px; }
#sidebarToggle.collapsing { width:0 !important; transition: width 0.3s ease; }
.sidebar-link:hover {
    background-color: #343a4041 !important;
    color: #fff !important;
    border-radius: 0.5rem;
    transition: 0.3s;
}
main { transition:none; }
.dropdown-menu { padding:0.4rem; overflow:hidden; }
.dropdown-menu .dropdown-item { padding:0.55rem 1rem; border-radius:0.375rem; transition:0.2s; }
.dropdown-menu .dropdown-item:hover { background-color:#d8f8fcff; color:#212529; }
.dropdown-menu .dropdown-item.text-danger:hover { background-color:#fdecea; color:#dc3545; }
.footer-custom { background-color:#e9ecef; color:#6c757d; }
.card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.25) !important; transform: translateY(-4px); transition:0.3s; }
.active-link {
    background-color: #343a4041;
    border-radius: 0.5rem;
    color: #fff !important; 
}
</style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary py-4">
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
    <a href="Home_user.php" 
       class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 
       <?= ($current_page == 'Home_user.php') ? 'active-link' : '' ?>">
      Home
    </a>
    <a href="Rooms_user.php" 
       class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 
       <?= ($current_page == 'Rooms_user.php') ? 'active-link' : '' ?>">
      Meeting Rooms
    </a>
    <a href="Calendars_user.php" 
       class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 
       <?= ($current_page == 'Calendars_user.php') ? 'active-link' : '' ?>">
      Calendars
    </a>
    <a href="History_user.php" 
       class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 
       <?= ($current_page == 'History_user.php') ? 'active-link' : '' ?>">
      History
    </a>
  </div>
</div>


<main class="container my-4 d-flex flex-column flex-grow-1">

<form class="w-100 text-center mt-4 mb-5 d-flex justify-content-center" method="get" action="" style="position: relative;">
    <div class="input-group w-50">
        <input type="text" name="query" class="form-control form-control-lg border border-2 border-dark-subtle" 
               placeholder="Cari nama, deskripsi, atau status..." 
               value="<?= htmlspecialchars($search) ?>" 
               style="padding-right: 2rem;">
        <?php if ($search !== ''): ?>
            <span onclick="window.location='Home_user.php'" class="position-absolute" style="right: calc(50% - 220px); top:50%; transform:translateY(-50%); cursor:pointer; font-weight:bold; font-size:1.25rem; color:#495057; user-select:none;" title="Hapus pencarian">&times;</span>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary btn-lg">Search</button>
    </div>
</form>

<div class="row g-4 justify-content-start">
<?php if (mysqli_num_rows($result) === 0): ?>
    <div class="col-12 text-center"><p class="fs-5 text-muted">Tidak ada hasil ditemukan.</p></div>
<?php else: ?>
    <?php while($row = mysqli_fetch_assoc($result)): 
        
        $currentStatus = $row['status'];
        $displayStatus = $currentStatus;

        $qMeet = mysqli_query($koneksi, "SELECT * FROM meetings WHERE project_id = " . $row['id'] . " ORDER BY tanggal DESC, waktu DESC LIMIT 1");
        $lastMeeting = mysqli_fetch_assoc($qMeet);

        if ($lastMeeting && $currentStatus != 'Tertunda' && $currentStatus != 'Dibatalkan') {
            $meetDateTime = strtotime($lastMeeting['tanggal'] . ' ' . $lastMeeting['waktu']);
            $now = time();

            if ($meetDateTime < $now) {
                $displayStatus = 'Selesai';
            } else {
                $displayStatus = 'Mendatang';
            }
            
            if ($displayStatus !== $currentStatus) {
                mysqli_query($koneksi, "UPDATE projects SET status = '$displayStatus' WHERE id = " . $row['id']);
            }
        }
        
        if ($displayStatus == 'Mendatang') {
            $color = 'warning'; 
        } elseif ($displayStatus == 'Selesai') {
            $color = 'success'; 
        } elseif ($displayStatus == 'Tertunda') {
            $color = 'secondary'; 
        } elseif ($displayStatus == 'Dibatalkan') {
            $color = 'danger'; 
        } else {
            $color = 'secondary'; 
        }
    ?>
    <div class="col-md-6 col-lg-3 d-flex">
        <a href="Isi_user.php?project_id=<?= $row['id'] ?>" class="card h-100 shadow-sm text-decoration-none d-flex flex-column justify-content-between w-100 rounded-4">
            <div class="card-body py-5">
                <h5 class="card-title fs-5"><?= htmlspecialchars($row['name']) ?></h5>
                <p class="card-text fs-6"><?= htmlspecialchars($row['description']) ?></p>
            </div>
            <span class="badge bg-<?= $color ?> m-3 align-self-start"><?= htmlspecialchars($displayStatus) ?></span>
        </a>
    </div>
    <?php endwhile; ?>
<?php endif; ?>
</div>

</main>
</div>

<footer class="footer-custom text-center py-3 border-top mt-auto">
  <div class="text-muted small">&copy; 2025 - User Pengelolaan Rapat</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>