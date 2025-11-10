<?php include "../koneksi.php"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>History Rapat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Hover biru muda untuk dropdown */
    .dropdown-item:hover {
      background-color: #5bc0de;
      color: white;
      border-radius: 5px;
    }
  </style>
</head>

<body class="bg-light d-flex flex-column min-vh-100">

<!-- Header -->
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
        <li><a class="dropdown-item" href="Profil_user.php">Profil</a></li>
        <li><a class="dropdown-item mt-2" href="../Logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="d-flex flex-grow-1">
  <div class="collapse collapse-horizontal" id="sidebar">
    <div class="d-flex flex-column bg-dark text-white h-100" style="width:220px;">
      <div class="d-flex flex-column mt-4 mb-5">
        <a href="Home_user.php" class="btn btn-dark w-100 fs-4 mb-2 text-start ps-3">Home</a>
        <a href="Rooms_user.php" class="btn btn-dark w-100 fs-4 mb-2 text-start ps-3">Meeting Rooms</a>
        <a href="Calendars_user.php" class="btn btn-dark w-100 fs-4 mb-2 text-start ps-3">Calendars</a>
        <a href="History_user.php" class="btn btn-dark w-100 fs-4 text-start ps-3">History</a>
      </div>
    </div>
  </div>

  <!-- Konten utama -->
  <main class="flex-grow-1 p-4">
    <div class="mt-4">
      <h2 class="text-center mb-5">History Rapat</h2>

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
                </tr>
              </thead>
              <tbody>
                <?php
                $query = mysqli_query($koneksi, "SELECT * FROM history ORDER BY hari_tanggal DESC");
                if (!$query) {
                  die("Query gagal: " . mysqli_error($koneksi));
                }

                $no = 1;
                $hari = [
                  'Sunday' => 'Minggu',
                  'Monday' => 'Senin',
                  'Tuesday' => 'Selasa',
                  'Wednesday' => 'Rabu',
                  'Thursday' => 'Kamis',
                  'Friday' => 'Jumat',
                  'Saturday' => 'Sabtu'
                ];

                $bulan = [
                  'January' => 'Januari',
                  'February' => 'Februari',
                  'March' => 'Maret',
                  'April' => 'April',
                  'May' => 'Mei',
                  'June' => 'Juni',
                  'July' => 'Juli',
                  'August' => 'Agustus',
                  'September' => 'September',
                  'October' => 'Oktober',
                  'November' => 'November',
                  'December' => 'Desember'
                ];

                while ($data = mysqli_fetch_array($query)) {
                  $tanggal = $data['hari_tanggal'];
                  $namaHari = $hari[date('l', strtotime($tanggal))];
                  $namaBulan = $bulan[date('F', strtotime($tanggal))];
                  $tanggalIndo = $namaHari . ', ' . date('d', strtotime($tanggal)) . ' ' . $namaBulan . ' ' . date('Y', strtotime($tanggal));

                  // Waktu rapat: mulai dan selesai otomatis +2 jam
                  $waktuMulai = date('H:i', strtotime($data['waktu']));
                  $waktuSelesai = date('H:i', strtotime($data['waktu'] . ' +2 hours'));
                  $waktuGabung = $waktuMulai . ' WIB - ' . $waktuSelesai . ' WIB';

                  echo "<tr>";
                  echo "<td>".$no++."</td>";
                  echo "<td>".$tanggalIndo."</td>";
                  echo "<td>".$waktuGabung."</td>";
                  echo "<td>".$data['lokasi']."</td>";
                  echo "<td>".$data['agenda']."</td>";

                  // Format daftar peserta ke dalam list
                  echo "<td class='text-start ps-4'><ul class='mb-0'>";
                  $pesertaArray = explode(',', $data['daftar_peserta']);
                  foreach ($pesertaArray as $p) {
                    echo "<li>".trim($p)."</li>";
                  }
                  echo "</ul></td>";

                  echo "<td>".$data['status']."</td>";
                  echo "</tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-auto">
  &copy; 2025 - Dashboard Kamu
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
