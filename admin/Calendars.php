<?php
session_start(); // Agar agenda bisa disimpan sementara

$bulan = date("n");
$tahun = date("Y");

$namaBulan = [
    1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",
    5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",
    9=>"September",10=>"Oktober",11=>"November",12=>"Desember"
];

// Jika belum ada agenda di session
if (!isset($_SESSION['Agenda'])) {
    $_SESSION['Agenda'] = [
        20 => ["Rapat Bulanan Tim IT"]
    ];
}

// ✅ Tangani form tambah & hapus agenda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tambah agenda baru
    if (isset($_POST['tanggal']) && isset($_POST['keterangan'])) {
        $tanggal = intval($_POST['tanggal']);
        $keterangan = trim($_POST['keterangan']);
        if ($tanggal > 0 && $tanggal <= 31 && $keterangan != '') {
            if (!isset($_SESSION['Agenda'][$tanggal])) {
                $_SESSION['Agenda'][$tanggal] = [];
            }
            $_SESSION['Agenda'][$tanggal][] = htmlspecialchars($keterangan);
        }
    }

    // Hapus agenda tertentu
    if (isset($_POST['hapus_tanggal']) && isset($_POST['hapus_index'])) {
        $tgl = intval($_POST['hapus_tanggal']);
        $idx = intval($_POST['hapus_index']);

        if (isset($_SESSION['Agenda'][$tgl][$idx])) {
            unset($_SESSION['Agenda'][$tgl][$idx]);
            $_SESSION['Agenda'][$tgl] = array_values($_SESSION['Agenda'][$tgl]); // reindex
            if (empty($_SESSION['Agenda'][$tgl])) {
                unset($_SESSION['Agenda'][$tgl]); // hapus tanggal jika kosong
            }
        }
    }

    // Redirect untuk cegah resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$Agenda = $_SESSION['Agenda']; // gunakan session agenda

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
<style>
.dropdown-item:hover {
  background-color: #5bc0de;
  color: white;
  border-radius: 5px;
}

/* Sedikit kurangi tinggi sel kalender */
.table td {
  vertical-align: middle;
  padding: 0.9rem !important;
}
</style>
</head>
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

  <!-- Main Content -->
  <main class="col py-5">
    <div class="card shadow-lg mx-auto" style="max-width: 800px;">
      <div class="card-body">
        <!-- Header kalender -->
        <div class="d-flex justify-content-center align-items-center mb-4 position-relative">
          <h2 class="fs-1 text-center mb-0"><?= $namaBulan[$bulan] ?> <?= $tahun ?></h2>
          <button class="btn btn-success fs-5 position-absolute end-0" data-bs-toggle="modal" data-bs-target="#tambahAgendaModal">+ Tambah</button>
        </div>

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
              for ($i=0; $i<$hariAwal; $i++) { echo "<td></td>"; $hari_counter++; }

              for ($tgl=1; $tgl<=$jumlahHari; $tgl++, $hari_counter++) {
                if ($hari_counter % 7 == 0 && $tgl != 1) echo "</tr><tr>";

                $kelas = ($tgl == date("j") && $bulan==date("n") && $tahun==date("Y")) ? "table-warning" : "";

                if (isset($Agenda[$tgl])) {
                  echo "<td class='$kelas py-3'>";
                  echo "<button type='button' class='btn btn-outline-success w-50 h-75 position-relative py-2 fs-5' data-bs-toggle='modal' data-bs-target='#Agenda$tgl'>";
                  echo $tgl;
                  echo "<span class='position-absolute bottom-0 end-0 translate-middle p-1 bg-danger rounded-circle'></span>";
                  echo "</button>";
                  echo "</td>";
                } else {
                  echo "<td class='$kelas fs-5'>$tgl</td>";
                }
              }

              while ($hari_counter % 7 !=0) { echo "<td></td>"; $hari_counter++; }
              echo "</tr>";
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal Tambah Agenda -->
<div class="modal fade" id="tambahAgendaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border border-success rounded">
      <form method="POST">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title w-100 text-center fs-4">Tambah Agenda Rapat</h5>
        </div>
        <div class="modal-body fs-5">
          <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal:</label>
            <select class="form-select fs-5" name="tanggal" id="tanggal" required>
              <option value="">-- Pilih Tanggal --</option>
              <?php for ($i=1; $i<=$jumlahHari; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?> <?= $namaBulan[$bulan] ?> <?= $tahun ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan Agenda:</label>
            <textarea name="keterangan" id="keterangan" class="form-control fs-5" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-center">
          <button type="submit" class="btn btn-success fw-bold fs-5">Simpan</button>
          <button type="button" class="btn btn-secondary fw-bold fs-5" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Agenda (dengan tombol hapus) -->
<?php foreach($Agenda as $tgl => $daftarAgenda): ?>
<div class="modal fade" id="Agenda<?= $tgl ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border border-secondary rounded">
      <div class="modal-header justify-content-center">
        <h5 class="modal-title text-center w-100 fw-bold fs-4">Jadwal Rapat <?= $tgl ?> <?= $namaBulan[$bulan] ?> <?= $tahun ?></h5>
      </div>
      <div class="modal-body text-start fs-5 py-4">
        <ul class="list-group list-group-flush">
          <?php foreach($daftarAgenda as $i => $AgendaItem): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><?= ($i+1) . ". " . htmlspecialchars($AgendaItem) ?></span>
              <form method="POST" style="margin:0;">
                <input type="hidden" name="hapus_tanggal" value="<?= $tgl ?>">
                <input type="hidden" name="hapus_index" value="<?= $i ?>">
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus agenda ini?')">Hapus</button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="modal-footer d-flex justify-content-center">
        <button type="button" class="btn btn-secondary fw-bold fs-5" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<footer class="bg-dark text-white text-center py-2 mt-auto">
    &copy; 2025 - Dashboard Admin
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
