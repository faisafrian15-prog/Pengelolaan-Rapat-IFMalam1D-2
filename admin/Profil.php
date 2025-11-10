<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard & Profil</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

  <!-- Header -->
  <nav class="navbar navbar-dark bg-secondary py-4">
    <div class="container justify-content-center">
      <span class="navbar-brand mb-0 h1 fs-1 text-center">Pengelolaan Rapat</span>
    </div>
  </nav>

  <!-- Tombol Kembali -->
  <div class="container-fluid mt-4">
    <a href="Dasboard.php" class="btn btn-outline-secondary btn-lg">
      ← Kembali
    </a>
  </div>

  <!-- Profil -->
  <div class="container my-3">
    <div class="card mx-auto shadow-lg" style="max-width: 600px; padding: 30px;">
      <div class="card-body text-center">
        <div class="rounded-circle bg-success text-white d-flex justify-content-center align-items-center mx-auto mb-4" 
             style="width:130px; height:130px; font-size:55px;">
          U
        </div>

        <h3 class="mb-4 fw-bold">Profil Pengguna</h3>

        <div class="mb-4 text-start fs-5">
          <label class="form-label fw-semibold">User:</label>
          <input type="text" class="form-control form-control-lg" value="Indah" readonly>
        </div>

        <div class="mb-4 text-start fs-5">
          <label class="form-label fw-semibold">Departemen:</label>
          <input type="text" class="form-control form-control-lg" value="IF" readonly>
        </div>

        <div class="mb-4 text-start fs-5">
          <label class="form-label fw-semibold">Email:</label>
          <input type="text" class="form-control form-control-lg" value="indahyanti@gmail.com" readonly>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-3 mt-auto">
    &copy; 2025 - Dashboard Admin
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
