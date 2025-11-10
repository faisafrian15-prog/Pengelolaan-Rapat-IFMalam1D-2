<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Isi Jadwal Rapat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
      .dropdown-item:hover {
        background-color: #5bc0de;
        color: white;
        border-radius: 5px;
      }
  </style>
</head>
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
              <li><a class="dropdown-item" href="Profil.php">Profil</a></li>
              <li><a class="dropdown-item mt-2" href="Logout.php">Logout</a></li>
          </ul>
      </div>
  </div>
</nav>

<!-- Konten Tengah -->
<div class="flex-grow-1 d-flex flex-column align-items-center">

  <!-- Tombol Kembali -->
  <div class="container-fluid mt-4">
    <a href="Home.php" class="btn btn-outline-secondary btn-lg">
      ← Kembali
    </a>
  </div>

  <!-- Card Jadwal -->
<div class="card shadow-sm w-75" style="max-width: 800px; margin-top: -40px; margin-bottom: 30px;">
    <div class="card-body py-7">
      <h2 class="card-title text-center mb-5 fs-2">Rapat Bulanan Tim IT</h2>

      <!-- Detail Jadwal -->
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

        <!-- Slide/PPT -->
        <div class="row mt-4 fs-5">
          <div class="col-3"><strong>Slide/PPT</strong></div>
          <div class="col-1">:</div>
          <div class="col-8">
            <ul class="ps-0 list-unstyled mb-0">
              <li class="py-1">
                <a href="slides/rapat_bulanan_it_september.pptx" class="btn btn-outline-primary btn-sm" target="_blank">
                  <i class="bi bi-file-earmark-ppt-fill me-1"></i>Rapat Bulanan IT - September.pptx
                </a>
              </li>
            </ul>
          </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="d-flex justify-content-between align-items-center mt-5">
          <div>
            <button class="btn btn-warning btn-lg me-2" data-bs-toggle="modal" data-bs-target="#editModal">
              <i class="bi bi-pencil-square me-1"></i>Edit
            </button>
            <a href="Hapus.php?id=1" class="btn btn-danger btn-lg" onclick="return confirm('Yakin ingin menghapus rapat ini?')">
              <i class="bi bi-trash-fill me-1"></i>Hapus
            </a>
          </div>

          <div class="d-flex align-items-center">
            <a href="mailto:?subject=Rapat%20Bulanan%20Tim%20IT&body=Detail%20rapat%20tersedia%20di%20link%20ini:%20https://example.com/Isi.php" class="btn btn-success me-2">
              <i class="bi bi-envelope-fill me-1"></i>Email
            </a>
            <div class="input-group" style="max-width:250px;">
              <input type="text" class="form-control form-control-sm" value="https://example.com/Isi.php" readonly>
              <span class="input-group-text btn btn-secondary"><i class="bi bi-clipboard-fill"></i></span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="Edit.php">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Edit Jadwal Rapat</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Nama Rapat</label>
                <input type="text" name="nama" class="form-control" required value="Rapat Bulanan Tim IT">
              </div>
              <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" required value="2025-11-20">
              </div>
              <div class="mb-3">
                <label class="form-label">Waktu</label>
                <input type="text" name="waktu" class="form-control" required value="10:00 - 12:00 WIB">
              </div>
              <div class="mb-3">
                <label class="form-label">Lokasi</label>
                <input type="text" name="lokasi" class="form-control" required value="Ruang Meeting Lt. 2">
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Agenda</label>
                <textarea name="agenda" class="form-control" rows="3" required>Evaluasi Proyek & Perencanaan Bulan Depan</textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Daftar Peserta</label>
                <textarea name="peserta" class="form-control" rows="3" placeholder="Pisahkan dengan koma" required>Indah Yanti, Rizky Saputra, Budi Santoso, Siti Aminah</textarea>
                <small class="text-muted">Gunakan koma untuk memisahkan nama peserta</small>
              </div>
              <div class="mb-3">
                <label class="form-label">Slide / PPT</label>
                <input type="text" name="ppt" class="form-control" value="slides/rapat_bulanan_it_september.pptx">
                <small class="text-muted">Isi dengan nama file atau URL lengkap</small>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">💾 Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-auto w-100 fs-5">
  &copy; 2025 - Dashboard Admin
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
