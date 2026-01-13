<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); 
header("Expires: 0");

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

} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: Login.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .navbar { background-color: #c3c7ceff !important; }
    
    #sidebarToggle { 
        background-color: #7a8ca0 !important;
        width: 250px;
        overflow: hidden; 
        flex-shrink: 0;
        position: sticky;
        top: 0;
        align-self: flex-start;
        height: 100vh;
    }
    #sidebarToggle.collapse:not(.show) { width:0; }
    #sidebarToggle.collapse.show { width:250px; }
    #sidebarToggle.collapsing { width:0 !important; }
    
    .sidebar-link:hover { 
        background-color: rgba(255, 255, 255, 0.1) !important; 
        color: #fff !important; 
        border-radius: 0.5rem; 
        transition:0.3s; 
    }
    
    .dropdown-item:hover { background-color:#d8f8fcff; color:#212529; }
    .dropdown-item.text-danger:hover { background-color:#fdecea; color:#dc3545; }
    
    .card:hover { 
        box-shadow: 0 8px 20px rgba(0,0,0,0.25) !important; 
        transform: translateY(-4px); 
        transition:0.3s; 
        cursor: pointer; 
    }
    
    .active-link { 
        background-color: rgba(255, 255, 255, 0.15); 
        border-radius:0.5rem; 
        color:#fff !important; 
    }

    @media (max-width: 767px) {
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1050;
        }
        
        #sidebarToggle {
            position: fixed !important;
            top: 90px !important;
            left: -250px !important;
            height: calc(100vh - 90px) !important;
            z-index: 1045;
            transition: left 0.3s ease;
        }
        
        #sidebarToggle.show {
            left: 0 !important;
        }
        
        #sidebarToggle.show::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }
        
        footer {
            position: relative;
            z-index: 1050;
        }
    }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary py-4 flex-shrink-0">
<div class="container-fluid">
  <button class="btn btn-light ms-md-4 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarToggle" style="width:50px; height:50px;">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
      <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
    </svg>
  </button>

  <div class="mx-auto position-absolute start-50 translate-middle-x">
    <span class="navbar-brand fs-2 fs-md-2 fs-sm-5 fw-bold text-dark mb-0">Pengelolaan Rapat</span>
  </div>

  <div class="dropdown me-md-4 me-2">
    <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center shadow-sm" type="button" data-bs-toggle="dropdown" style="width:50px; height:50px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="#333" viewBox="0 0 16 16">
        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
      </svg>
    </button>
    <ul class="dropdown-menu dropdown-menu-end p-2">
      <li><a class="dropdown-item rounded px-3 py-2" href="Profil_user.php">Profil</a></li>
      <li><hr class="dropdown-divider my-2"></li>
      <li><a class="dropdown-item rounded px-3 py-2 text-danger" href="../Logout.php">Logout</a></li>
    </ul>
  </div>
</div>
</nav>

<div class="d-flex flex-grow-1">

<div class="collapse collapse-horizontal show d-flex flex-column" id="sidebarToggle">
  <div class="pt-3 overflow-y-auto h-100">
    <a href="Home_user.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 <?= ($current_page == 'Home_user.php') ? 'active-link' : '' ?>">Home</a>
    <a href="Rooms_user.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 <?= ($current_page == 'Rooms_user.php') ? 'active-link' : '' ?>">Meeting Rooms</a>
    <a href="Calendars_user.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 <?= ($current_page == 'Calendars_user.php') ? 'active-link' : '' ?>">Calendars</a>
    <a href="History_user.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 <?= ($current_page == 'History_user.php') ? 'active-link' : '' ?>">History</a>
  </div>
</div>

  <main class="container my-4 my-md-4 my-sm-2 d-flex flex-column align-items-center text-center flex-grow-1 overflow-auto px-3 px-md-4">
    <div class="card shadow mt-5 mt-md-5 mt-sm-3 mb-5 mb-md-5 mb-sm-3 w-100 py-5 py-md-5 py-sm-3" style="max-width: 900px; min-height: 250px; border-radius: 20px; background-color: #d4d4cbbd;">
        <div class="card-body d-flex justify-content-center align-items-center">
            <h2 class="card-title fw-bold fs-1 fs-md-1 fs-sm-4 px-2">Selamat Datang, <?= htmlspecialchars($_SESSION['username']); ?>!</h2>
        </div>
    </div>

    <p class="fs-4 fs-md-4 fs-sm-6 w-75 w-md-75 w-sm-100 text-center lh-base">
        Website Pengelolaan Rapat ini dibuat untuk membantu mengatur jadwal rapat, mencatat 
        ruang rapat yang tersedia, serta menyusun agenda agar lebih terorganisir. Dengan 
        adanya sistem ini, proses koordinasi menjadi lebih mudah, cepat, dan efisien.
    </p>
  </main>
</div>

<footer class="bg-light text-center py-3 py-md-3 py-sm-2 border-top mt-auto">
    <div class="text-muted small">&copy; 2025 - Dashboard User Pengelolaan Rapat</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth <= 767) {
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        const sidebar = document.getElementById('sidebarToggle');
        
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                const bsCollapse = bootstrap.Collapse.getInstance(sidebar);
                if (bsCollapse) bsCollapse.hide();
            });
        });
        
        sidebar.addEventListener('click', function(e) {
            if (e.target === this) {
                const bsCollapse = bootstrap.Collapse.getInstance(sidebar);
                if (bsCollapse) bsCollapse.hide();
            }
        });
    }
});
</script>

</body>
</html>