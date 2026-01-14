<?php
session_start();
include "../Koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../Login.php");
    exit();
}

$username = $_SESSION['username'];

function getUserData($koneksi, $username) {
    try {
        $stmt = $koneksi->prepare("SELECT username, jurusan, email FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        $stmt->close();
        return $userData ?: null;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

function getInitial($username) {
    return strtoupper(substr($username, 0, 1));
}

$userData = getUserData($koneksi, $username);

if (!$userData) {
    echo "<h3 class='text-center mt-5'>Data pengguna tidak ditemukan.</h3>";
    echo "<p class='text-center'><a href='../Logout.php'>Logout</a> dan login kembali.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profil User</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.navbar { background-color: #c3c7ceff !important; }
.dropdown-menu { padding:0.4rem; overflow:hidden; }
.dropdown-menu .dropdown-item { padding:0.55rem 1rem; border-radius:0.375rem; transition:0.2s; }
.dropdown-menu .dropdown-item:hover { background-color:#d8f8fcff; color:#212529; }
.dropdown-menu .dropdown-item.text-danger:hover { background-color:#fdecea; color:#dc3545; }
.footer-custom { background-color:#e9ecef; color:#6c757d; }
</style>
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary py-4">
<div class="container-fluid">
  <a href="Home_user.php" class="btn btn-outline-secondary btn-lg ms-3">← Kembali</a>
  <div class="mx-auto position-absolute start-50 translate-middle-x">
    <span class="navbar-brand fs-2 fw-bold text-dark">Pengelolaan Rapat</span>
  </div>
</div>
</nav>

  <div class="container my-3">
    <div class="card mx-auto shadow-lg" style="max-width: 420px; padding: 25px;">
      <div class="card-body text-center">

        <div class="rounded-circle bg-success text-white d-flex justify-content-center align-items-center mx-auto mb-4" 
             style="width:100px; height:100px; font-size:40px;">
          <?= $userData['username'] ? getInitial($userData['username']) : '?'; ?>
        </div>

        <h4 class="mb-4 fw-bold">Profil Pengguna</h4>

        <div class="mb-3 text-start fs-6">
          <label class="form-label fw-semibold">User:</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($userData['username']); ?>" readonly>
        </div>

        <div class="mb-3 text-start fs-6">
          <label class="form-label fw-semibold">Departemen:</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($userData['jurusan']); ?>" readonly>
        </div>

        <div class="mb-3 text-start fs-6">
          <label class="form-label fw-semibold">Email:</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($userData['email']); ?>" readonly>
        </div>

      </div>
    </div>
  </div>

<footer class="footer-custom text-center py-3 border-top mt-auto">
  <div class="text-muted small">&copy; 2025 - Profil User Pengelolaan Rapat</div>
</footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
