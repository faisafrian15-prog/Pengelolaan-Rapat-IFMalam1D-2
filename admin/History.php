<?php 
include "../koneksi.php"; 
session_start();

// ==== HANDLE CREATE ====
if (isset($_POST['tambah'])) {
  $tanggal = $_POST['hari_tanggal'];
  $waktu = $_POST['waktu'];
  $lokasi = $_POST['lokasi'];
  $agenda = $_POST['agenda'];
  $peserta = $_POST['daftar_peserta'];
  $status = $_POST['status'];

  $query = "INSERT INTO history (hari_tanggal, waktu, lokasi, agenda, daftar_peserta, status)
            VALUES ('$tanggal', '$waktu', '$lokasi', '$agenda', '$peserta', '$status')";
  mysqli_query($koneksi, $query) or die("Gagal menambah data: " . mysqli_error($koneksi));

  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// ==== HANDLE UPDATE ====
if (isset($_POST['edit'])) {
  $id = $_POST['id'];
  $tanggal = $_POST['hari_tanggal'];
  $waktu = $_POST['waktu'];
  $lokasi = $_POST['lokasi'];
  $agenda = $_POST['agenda'];
  $peserta = $_POST['daftar_peserta'];
  $status = $_POST['status'];

  $query = "UPDATE history SET hari_tanggal='$tanggal', waktu='$waktu', lokasi='$lokasi', agenda='$agenda', daftar_peserta='$peserta', status='$status' WHERE id=$id";
  mysqli_query($koneksi, $query) or die("Gagal mengupdate data: " . mysqli_error($koneksi));

  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// ==== HANDLE DELETE ====
if (isset($_POST['hapus'])) {
  $id = $_POST['id'];
  mysqli_query($koneksi, "DELETE FROM history WHERE id=$id") or die("Gagal menghapus data: " . mysqli_error($koneksi));
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>History Rapat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .dropdown-item:hover {
      background-color: #5bc0de;
      color: white;
      border-radius: 5px;
    }
  </style>
</head>

<body class="bg-light d-flex flex-column min-vh-100">

<!-- Navbar -->
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
  <!-- Sidebar -->
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

  <!-- Konten utama -->
  <main class="flex-grow-1 p-4">
    <div class="mt-4">
      <div class="d-flex justify-content-between align-items-center w-75 mx-auto mb-4">
        <h2 class="mb-0">History Rapat</h2>
        <button class="btn btn-success fs-5" data-bs-toggle="modal" data-bs-target="#tambahModal">+ Tambah</button>
      </div>

      <div class="card shadow w-75 mx-auto mb-5">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle text-center fs-5">
              <thead class="table-primary">
                <tr>
                  <th>No</th>
                  <th>Hari / Tanggal</th>
                  <th>Waktu</th>
                  <th>Lokasi</th>
                  <th>Agenda</th>
                  <th>Daftar Peserta</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $query = mysqli_query($koneksi, "SELECT * FROM history ORDER BY hari_tanggal DESC");
                $no = 1;

                $hari = [
                  'Sunday' => 'Minggu','Monday' => 'Senin','Tuesday' => 'Selasa','Wednesday' => 'Rabu',
                  'Thursday' => 'Kamis','Friday' => 'Jumat','Saturday' => 'Sabtu'
                ];
                $bulan = [
                  'January' => 'Januari','February' => 'Februari','March' => 'Maret','April' => 'April',
                  'May' => 'Mei','June' => 'Juni','July' => 'Juli','August' => 'Agustus',
                  'September' => 'September','October' => 'Oktober','November' => 'November','December' => 'Desember'
                ];

                while ($data = mysqli_fetch_array($query)):
                  $tanggal = $data['hari_tanggal'];
                  $namaHari = $hari[date('l', strtotime($tanggal))];
                  $namaBulan = $bulan[date('F', strtotime($tanggal))];
                  $tanggalIndo = "$namaHari, " . date('d', strtotime($tanggal)) . " $namaBulan " . date('Y', strtotime($tanggal));

                  $waktuMulai = date('H:i', strtotime($data['waktu']));
                  $waktuSelesai = date('H:i', strtotime($data['waktu'].' +2 hours'));
                  $waktuGabung = "$waktuMulai - $waktuSelesai WIB";
                ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= $tanggalIndo ?></td>
                  <td><?= $waktuGabung ?></td>
                  <td><?= $data['lokasi'] ?></td>
                  <td><?= $data['agenda'] ?></td>
                  <td class="text-start ps-4">
                    <ul class="mb-0">
                      <?php foreach (explode(',', $data['daftar_peserta']) as $p): ?>
                        <li><?= trim($p) ?></li>
                      <?php endforeach; ?>
                    </ul>
                  </td>
                  <td><?= $data['status'] ?></td>
                  <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $data['id'] ?>">Edit</button>
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="id" value="<?= $data['id'] ?>">
                      <button type="submit" name="hapus" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</button>
                    </form>
                  </td>
                </tr>

                <!-- Modal Edit -->
                <div class="modal fade" id="editModal<?= $data['id'] ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST">
                        <div class="modal-header bg-warning">
                          <h5 class="modal-title text-white">Edit Rapat</h5>
                        </div>
                        <div class="modal-body fs-5">
                          <input type="hidden" name="id" value="<?= $data['id'] ?>">
                          <label class="form-label">Tanggal</label>
                          <input type="date" class="form-control mb-2" name="hari_tanggal" value="<?= $data['hari_tanggal'] ?>" required>
                          <label class="form-label">Waktu</label>
                          <input type="time" class="form-control mb-2" name="waktu" value="<?= $data['waktu'] ?>" required>
                          <label class="form-label">Lokasi</label>
                          <input type="text" class="form-control mb-2" name="lokasi" value="<?= $data['lokasi'] ?>" required>
                          <label class="form-label">Agenda</label>
                          <textarea class="form-control mb-2" name="agenda" rows="2" required><?= $data['agenda'] ?></textarea>
                          <label class="form-label">Daftar Peserta (pisahkan dengan koma)</label>
                          <textarea class="form-control mb-2" name="daftar_peserta" rows="2" required><?= $data['daftar_peserta'] ?></textarea>
                          <label class="form-label">Status</label>
                          <select class="form-select" name="status" required>
                            <option <?= ($data['status']=='Selesai'?'selected':'') ?>>Selesai</option>
                            <option <?= ($data['status']=='Dibatalkan'?'selected':'') ?>>Dibatalkan</option>
                            <option <?= ($data['status']=='Ditunda'?'selected':'') ?>>Ditunda</option>
                          </select>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" name="edit" class="btn btn-warning text-white">Simpan Perubahan</button>
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Tambah Rapat Baru</h5>
        </div>
        <div class="modal-body fs-5">
          <label class="form-label">Tanggal</label>
          <input type="date" name="hari_tanggal" class="form-control mb-2" required>
          <label class="form-label">Waktu</label>
          <input type="time" name="waktu" class="form-control mb-2" required>
          <label class="form-label">Lokasi</label>
          <input type="text" name="lokasi" class="form-control mb-2" required>
          <label class="form-label">Agenda</label>
          <textarea name="agenda" class="form-control mb-2" rows="2" required></textarea>
          <label class="form-label">Daftar Peserta (pisahkan dengan koma)</label>
          <textarea name="daftar_peserta" class="form-control mb-2" rows="2" required></textarea>
          <label class="form-label">Status</label>
          <select class="form-select" name="status" required>
            <option>Selesai</option>
            <option>Ditunda</option>
            <option>Dibatalkan</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="submit" name="tambah" class="btn btn-success">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-auto">
  &copy; 2025 - Dashboard Admin
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
