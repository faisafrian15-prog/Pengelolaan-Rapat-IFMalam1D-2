<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Isi Jadwal Rapat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<style>
      /* Hover biru muda dengan border-radius untuk dropdown */
      .dropdown-item:hover {
        background-color: #5bc0de; /* biru muda */
        color: white;
        border-radius: 5px;
      }
</style>
<body class="d-flex flex-column min-vh-100 bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary py-4">
    <div class="container-fluid d-flex justify-content-center align-items-center position-relative">
        <span class="navbar-brand mb-0 h1 text-center fs-1">Pengelolaan Rapat</span>

        <!-- Profil dropdown -->
        <div class="dropdown position-absolute end-0 me-5">
            <button class="btn btn-light rounded-circle" type="button" id="profileDropdown" data-bs-toggle="dropdown" style="width:50px; height:50px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#333">
                    <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/>
                </svg>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                <li><a class="dropdown-item" href="/user/Profil_user.php">Profil</a></li>
                <li><a class="dropdown-item mt-2" href="Logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

    <!-- Konten Tengah -->
    <div class="flex-grow-1 d-flex flex-column align-items-center">

        <!-- Tombol Kembali -->
  <div class="container-fluid mt-4">
    <a href="Dasboard_user.php" class="btn btn-outline-secondary btn-lg">
      ← Kembali
    </a>
  </div>

        <!-- Card Jadwal di Tengah -->
        <div class="card shadow-sm w-75 mt-3" style="max-width: 800px;">
          <div class="card-body py-5">
            <h2 class="card-title text-center mb-5 fs-2">Rapat Bulanan Tim IT</h2>

            <!-- Detail Jadwal dengan Bootstrap Grid dan teks lebih besar -->
            <div class="container">
              <div class="row mb-2 fs-5">
                <div class="col-3"><strong>Hari/Tanggal</strong></div>
                <div class="col-1">:</div>
                <div class="col-8">Kamis 20 November 2025</div>
              </div>
              <div class="row mb-2 fs-5">
                <div class="col-3"><strong>Waktu</strong></div>
                <div class="col-1">:</div>
                <div class="col-8">10:00 WIB - 12:00 WIB</div>
              </div>
              <div class="row mb-2 fs-5">
                <div class="col-3"><strong>Lokasi</strong></div>
                <div class="col-1">:</div>
                <div class="col-8">Ruang Meeting Lt. 2</div>
              </div>
              <div class="row mb-2 fs-5">
                <div class="col-3"><strong>Agenda</strong></div>
                <div class="col-1">:</div>
                <div class="col-8">Evaluasi Proyek & Perencanaan Bulan Depan</div>
              </div>
              <div class="row mb-2 fs-5">
                <div class="col-3"><strong>Daftar Peserta</strong></div>
                <div class="col-1">:</div>
                <div class="col-8">
                  <ul class="ps-4 mb-0">
                    <li>Indah Yanti</li>
                    <li>Rizky Saputra</li>
                    <li>Budi Santoso</li>
                    <li>Siti Aminah</li>
                  </ul>
                </div>
              </div>

              <!-- Bagian Slide/PPT tanpa bullet -->
              <div class="row mt-4 fs-5">
                <div class="col-3"><strong>Slide/PPT</strong></div>
                <div class="col-1">:</div>
                <div class="col-8">
                  <ul class="ps-0 list-unstyled mb-0">
                    <li class="py-1">
                      <a href="slides/rapat_bulanan_it_september.pptx" class="btn btn-outline-primary btn-sm" target="_blank"><i class="bi bi-file-earmark-ppt-fill me-1"></i>Rapat Bulanan IT - September.pptx</a>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Tombol Email dan Salin Tautan tanpa JS manual dengan ikon -->
              <div class="d-flex justify-content-end mt-4 align-items-center">
                <!-- Tombol Email -->
                <a href="mailto:?subject=Rapat%20Bulanan%20Tim%20IT&body=Detail%20rapat%20tersedia%20di%20link%20ini:%20https://holli-crummiest-josphine.ngrok-free.dev/PBL/Isi.php" class="btn btn-success me-2">
                  <i class="bi bi-envelope-fill me-1"></i>Email
                </a>

                <!-- Input readonly untuk Salin Tautan manual dengan ikon -->
                <div class="input-group" style="max-width:250px;">
                  <input type="text" class="form-control form-control-sm" value="https://holli-crummiest-josphine.ngrok-free.dev/PBL/Isi.php" readonly>
                  <span class="input-group-text btn btn-secondary"><i class="bi bi-clipboard-fill"></i></span>
                </div>
              </div>

            </div>

          </div>
        </div>

    </div>

</div>

<!-- Footer Full Width -->
<footer class="bg-dark text-white text-center py-3 mt-auto w-100 fs-5">
  &copy; 2025 - Dashboard Kamu
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
