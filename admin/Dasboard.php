<?php
session_start();

try {
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        throw new Exception("User belum login");
    }

    if ($_SESSION['role'] !== 'admin') {
        throw new Exception("Akses ditolak untuk role: " . $_SESSION['role']);
    }

    header("Cache-Control: no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");

    $current_page = basename($_SERVER['PHP_SELF']);
    $username = $_SESSION['username'];

} catch (Exception $e) {
    error_log("Session Error: " . $e->getMessage());
    header("Location: Login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .navbar { background-color: #c3c7ceff !important; }
    
    #sidebarToggle { 
        background-color: #7a8ca0 !important;
        width: 250px;
        overflow: hidden; 
        flex-shrink: 0;
        transition: width 0.3s ease;
    }
    #sidebarToggle.collapse:not(.show) { width:0; }
    #sidebarToggle.collapse.show { width:250px; }
    #sidebarToggle.collapsing { width:0 !important; transition: width 0.3s ease; }
    
    .sidebar-nav {
        overflow-y: auto;
        height: 100%;
    }

    .sidebar-link:hover { background-color: #343a4041 !important; color: #fff !important; border-radius: 0.5rem; transition:0.3s; }
    main { transition:none; }
    .dropdown-menu { padding:0.4rem; overflow:hidden; }
    .dropdown-menu .dropdown-item { padding:0.55rem 1rem; border-radius:0.375rem; transition:0.2s; }
    .dropdown-menu .dropdown-item:hover { background-color:#d8f8fcff; color:#212529; }
    .dropdown-menu .dropdown-item.text-danger:hover { background-color:#fdecea; color:#dc3545; }
    .footer-custom { background-color:#e9ecef; color:#6c757d; }
    .card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.25) !important; transform: translateY(-4px); transition:0.3s; cursor: pointer; }
    .active-link { background-color: #343a4041; border-radius:0.5rem; color:#fff !important; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

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
      <li><a class="dropdown-item" href="Profil.php">Profil</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="../Logout.php">Logout</a></li>
    </ul>
  </div>
</div>
</nav>

<div class="d-flex flex-grow-1">

<div class="collapse collapse-horizontal show bg-dark d-flex flex-column min-vh-100" id="sidebarToggle">
  <div class="pt-3 sidebar-nav">
    <a href="Home.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Home.php') ? 'active-link' : '' ?>">Home</a>
    <a href="Rooms.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Rooms.php') ? 'active-link' : '' ?>">Meeting Rooms</a>
    <a href="Calendars.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Calendars.php') ? 'active-link' : '' ?>">Calendars</a>
    <a href="History.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'History.php') ? 'active-link' : '' ?>">History</a>
    <a href="detail.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'detail.php') ? 'active-link' : '' ?>">Detail</a>
  </div>
</div>

  <main class="container my-4 d-flex flex-column align-items-center text-center flex-grow-1 overflow-auto">
    <div class="card shadow mt-5 mb-5 w-100 py-5" style="max-width: 900px; min-height: 250px; border-radius: 20px; background-color: #d4d4cbbd;">
        <div class="card-body d-flex justify-content-center align-items-center">
            <h2 class="card-title fw-bold fs-1">Selamat Datang, <?= htmlspecialchars($_SESSION['username']); ?>! </h2>
        </div>
    </div>

    <p class="fs-4 w-75 text-center">
        Website Pengelolaan Rapat ini dibuat untuk membantu mengatur jadwal rapat, mencatat <br>
        ruang rapat yang tersedia, serta menyusun agenda agar lebih terorganisir. Dengan <br>
        adanya sistem ini, proses koordinasi menjadi lebih mudah, cepat, dan efisien.
    </p>
  </main>
</div>

<footer class="footer-custom text-center py-3 border-top mt-auto">
        <div class="text-muted small">&copy; 2025 - Dashboard Admin Pengelolaan Rapat</div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
