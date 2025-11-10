<?php
$bulan = date("n");
$tahun = date("Y");

$namaBulan = [
    1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",
    5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",
    9=>"September",10=>"Oktober",11=>"November",12=>"Desember"
];

// Contoh agenda rapat
$agenda = [
    20 => "Rapat Bulanan Tim IT"
];

$hariPertama = mktime(0, 0, 0, $bulan, 1, $tahun);
$jumlahHari = date("t", $hariPertama);
$hariAwal = date("w", $hariPertama);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Calendars</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
/* Hover biru muda dengan border-radius untuk dropdown */
      .dropdown-item:hover {
        background-color: #5bc0de; /* biru muda */
        color: white;
        border-radius: 5px;
      }
</style>
<body class="d-flex flex-column min-vh-100">

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
        <li><a class="dropdown-item" href="Profil_user.php">Profil</a></li>
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
        <a href="Home_user.php" class="btn btn-dark w-100 fs-4 mb-2 text-start ps-3">Home</a>
        <a href="Rooms_user.php" class="btn btn-dark w-100 fs-4 mb-2 text-start ps-3">Meeting Rooms</a>
        <a href="Calendars_user.php" class="btn btn-dark w-100 fs-4 mb-2 text-start ps-3">Calendars</a>
        <a href="History_user.php" class="btn btn-dark w-100 fs-4 text-start ps-3">History</a>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <main class="col py-5">
    <div class="card shadow-lg mx-auto" style="max-width: 900px;">
      <div class="card-body">
        <!-- Teks bulan besar -->
        <h2 class="fs-1 my-4 text-center"><?= $namaBulan[$bulan] ?> <?= $tahun ?></h2>

        <!-- Calendar table -->
        <div class="table-responsive">
          <table class="table table-bordered text-center mb-0">
            <thead class="table-primary fs-5">
              <tr>
                <th>Min</th><th>Sen</th><th>Sel</th><th>Rab</th>
                <th>Kam</th><th>Jum</th><th>Sab</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $hari_counter = 0;
              echo "<tr>";
              for ($i=0; $i<$hariAwal; $i++) { echo "<td class='py-4 px-3'></td>"; $hari_counter++; }

              for ($tgl=1; $tgl<=$jumlahHari; $tgl++, $hari_counter++) {
                if ($hari_counter % 7 == 0 && $tgl != 1) echo "</tr><tr>";

                $kelas = ($tgl == date("j") && $bulan==date("n") && $tahun==date("Y")) ? "table-warning" : "";

                if (isset($agenda[$tgl])) {
                  echo "<td class='$kelas p-4'>";
                  echo "<button type='button' class='btn btn-outline-success w-100 h-100 position-relative py-3 fs-5' data-bs-toggle='modal' data-bs-target='#agenda$tgl'>";
                  echo $tgl;
                  echo "<span class='position-absolute bottom-0 end-0 translate-middle p-2 bg-danger rounded-circle'></span>";
                  echo "</button>";
                  echo "</td>";
                } else {
                  echo "<td class='$kelas p-4 fs-5'>$tgl</td>";
                }
              }

              while ($hari_counter % 7 !=0) { echo "<td class='p-4'></td>"; $hari_counter++; }
              echo "</tr>";
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal Statis -->
<?php foreach($agenda as $tgl => $isi): ?>
<div class="modal fade" id="agenda<?= $tgl ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border border-secondary rounded">
      <div class="modal-header justify-content-center">
        <h5 class="modal-title text-center w-100 fw-bold fs-4">Jadwal Rapat <?= $tgl ?> <?= $namaBulan[$bulan] ?> <?= $tahun ?></h5>
      </div>
      <div class="modal-body text-center fs-4 py-5"><?= $isi ?></div>
      <div class="modal-footer d-flex justify-content-center">
        <button type="button" class="btn btn-secondary fw-bold fs-5" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<footer class="bg-dark text-white text-center py-3 mt-auto">
    &copy; 2025 - Dashboard Kamu
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
