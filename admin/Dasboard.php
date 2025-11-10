<?php
session_start();
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

<!-- Navbar Header -->
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

<footer class="bg-dark text-white text-center py-3 mt-auto">
    &copy; 2025 - Dashboard Admin
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
