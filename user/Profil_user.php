<?php
session_start();
include "../Koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../Login.php");
    exit();
}

$username = $_SESSION['username'];

// Ambil data profil dari database
$stmt = $koneksi->prepare("SELECT username, jurusan, email FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$userData = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard & Profil</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

  <nav class="navbar navbar-dark bg-secondary py-4">
    <div class="container justify-content-center">
      <span class="navbar-brand mb-0 h1 fs-1 text-center">Pengelolaan Rapat</span>
    </div>
  </nav>

  <div class="container-fluid mt-4">
    <a href="Dasboard_user.php" class="btn btn-outline-secondary btn-lg">
      ← Kembali
    </a>
  </div>

  <div class="container my-3">
    <div class="card mx-auto shadow-lg" style="max-width: 420px; padding: 25px;">
      <div class="card-body text-center">

        <!-- Huruf pertama username -->
        <div class="rounded-circle bg-success text-white d-flex justify-content-center align-items-center mx-auto mb-4" 
             style="width:100px; height:100px; font-size:40px;">
          <?php echo strtoupper(substr($userData['username'], 0, 1)); ?>
        </div>

        <h4 class="mb-4 fw-bold">Profil Pengguna</h4>

        <div class="mb-3 text-start fs-6">
          <label class="form-label fw-semibold">User:</label>
          <input type="text" class="form-control" value="<?php echo $userData['username']; ?>" readonly>
        </div>

        <div class="mb-3 text-start fs-6">
          <label class="form-label fw-semibold">Departemen:</label>
          <input type="text" class="form-control" value="<?php echo $userData['jurusan']; ?>" readonly>
        </div>

        <div class="mb-3 text-start fs-6">
          <label class="form-label fw-semibold">Email:</label>
          <input type="text" class="form-control" value="<?php echo $userData['email']; ?>" readonly>
        </div>

      </div>
    </div>
  </div>

  <footer class="bg-dark text-white text-center py-3 mt-auto">
    &copy; 2025 - Dashboard Admin
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
