<?php
include "../koneksi.php"; 

if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];

    $stmt = mysqli_prepare($koneksi, "DELETE FROM meetings WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date("n");
    $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date("Y");
    header("Location: Calendars.php?bulan=$bulan&tahun=$tahun");
    exit;
}

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date("n");
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date("Y");

$namaBulan = [
    1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",
    5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",
    9=>"September",10=>"Oktober",11=>"November",12=>"Desember"
];

$agenda = [];

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM meetings WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?"
);
mysqli_stmt_bind_param($stmt, "ii", $bulan, $tahun);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $tgl = (int)date("j", strtotime($row['tanggal']));
    $agenda[$tgl][] = $row;
}
mysqli_stmt_close($stmt);

$hariPertama = mktime(0, 0, 0, $bulan, 1, $tahun);
$jumlahHari  = (int)date("t", $hariPertama);
$hariAwal    = (int)date("w", $hariPertama);

$prevBulan = $bulan - 1; $prevTahun = $tahun;
if ($prevBulan < 1) { $prevBulan = 12; $prevTahun--; }

$nextBulan = $bulan + 1; $nextTahun = $tahun;
if ($nextBulan > 12) { $nextBulan = 1; $nextTahun++; }

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Calender Agenda Rapat</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.navbar { background-color: #c3c7ceff !important; }
#sidebarToggle { 
    background-color: #7a8ca0 !important;
    width: 250px;
    overflow: hidden; 
    flex-shrink: 0;
    transition: width 0.3s ease;
}
#sidebarToggle.collapse:not(.show) { width:0; }
#sidebarToggle.collapse.show { width:250px; }
#sidebarToggle.collapsing { width:0 !important; transition: width 0.3s ease; }
.sidebar-nav { overflow-y: auto; height: 100%; }
.sidebar-link:hover { background-color: #343a4041 !important; color: #fff !important; border-radius: 0.5rem; transition:0.3s; }
main { transition:none; }
.dropdown-menu { padding:0.4rem; overflow:hidden; }
.dropdown-menu .dropdown-item { padding:0.55rem 1rem; border-radius:0.375rem; transition:0.2s; }
.dropdown-menu .dropdown-item:hover { background-color:#d8f8fcff; color:#212529; }
.dropdown-menu .dropdown-item.text-danger:hover { background-color:#fdecea; color:#dc3545; }
.footer-custom { background-color:#e9ecef; color:#6c757d; }
.card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.25) !important; transform: translateY(-4px); transition:0.3s; cursor: pointer; }
.active-link { background-color: #343a4041; border-radius:0.5rem; color:#fff !important; }
</style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary py-4 flex-shrink-0">
<div class="container-fluid">
  <button class="btn btn-light ms-4" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarToggle" style="width:50px; height:50px;">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
      <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
    </svg>
  </button>

  <div class="mx-auto position-absolute start-50 translate-middle-x">
    <span class="navbar-brand fs-2 fw-bold text-dark">Pengelolaan Rapat</span>
  </div>

  <div class="dropdown me-4">
    <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center shadow-sm" type="button" data-bs-toggle="dropdown" style="width:50px; height:50px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="#333" viewBox="0 0 16 16">
        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
      </svg>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><a class="dropdown-item" href="Profil.php">Profil</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="../Logout.php">Logout</a></li>
    </ul>
  </div>
</div>
</nav>

<div class="d-flex flex-grow-1">
<div class="collapse collapse-horizontal show bg-dark min-vh-100 d-flex flex-column" id="sidebarToggle">
  <div class="pt-3 sidebar-nav">
    <a href="Home.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Home.php') ? 'active-link' : '' ?>">Home</a>
    <a href="Rooms.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Rooms.php') ? 'active-link' : '' ?>">Meeting Rooms</a>
    <a href="Calendars.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Calendars.php') ? 'active-link' : '' ?>">Calendars</a>
    <a href="History.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'History.php') ? 'active-link' : '' ?>">History</a>
    <a href="detail.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'detail.php') ? 'active-link' : '' ?>">Detail</a>
  </div>
</div>

<main class="col py-5">
  <div class="card shadow-lg mx-auto" style="max-width: 900px;">
    <div class="card-body">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="?bulan=<?= $prevBulan ?>&tahun=<?= $prevTahun ?>" class="btn btn-outline-primary">&laquo; Bulan Sebelumnya</a>
        <h2 class="fs-1 text-center"><?= $namaBulan[$bulan] ?> <?= $tahun ?></h2>
        <a href="?bulan=<?= $nextBulan ?>&tahun=<?= $nextTahun ?>" class="btn btn-outline-primary">Bulan Berikutnya &raquo;</a>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered text-center mb-0">
          <thead class="table-primary fs-5">
            <tr><th>Min</th><th>Sen</th><th>Sel</th><th>Rab</th><th>Kam</th><th>Jum</th><th>Sab</th></tr>
          </thead>
          <tbody>
          <?php
            $hari_counter = 0;
            echo "<tr>";
            for($i=0;$i<$hariAwal;$i++){ echo "<td></td>"; $hari_counter++; }

            for($tgl=1;$tgl<=$jumlahHari;$tgl++,$hari_counter++){
              if($hari_counter%7==0 && $tgl!=1) echo "</tr><tr>";
              
              $kelas = ($tgl==date("j") && $bulan==date("n") && $tahun==date("Y")) ? "table-warning" : "";
              $hasAgenda = isset($agenda[$tgl]);

              if($hasAgenda){
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

            while($hari_counter%7!=0){ echo "<td></td>"; $hari_counter++; }
            echo "</tr>";
          ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</main>
</div>

<?php
foreach($agenda as $tgl => $agendas){
?>
<div class="modal fade" id="Agenda<?= $tgl ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border border-secondary rounded">
      <div class="modal-header justify-content-center">
        <h5 class="modal-title text-center w-100 fw-bold fs-4">Jadwal Rapat <?= $tgl ?> <?= $namaBulan[$bulan] ?> <?= $tahun ?></h5>
      </div>
      <div class="modal-body text-center fs-5 py-3">
        <ul class="list-group list-group-flush text-start">
            <?php foreach($agendas as $data): 
                $waktuMulai = date("H.i", strtotime($data['waktu']));
                $waktuSelesai = date("H.i", strtotime($data['waktu'] . ' +3 hours'));
            ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="d-block"><?= htmlspecialchars($data['judul']) ?></strong>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> <?= $waktuMulai ?> - <?= $waktuSelesai ?> WIB
                            <span class="mx-2">|</span>
                            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($data['lokasi']) ?>
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="isi.php?project_id=<?= $data['project_id'] ?>" class="btn btn-sm btn-primary px-3">Detail</a>
                        
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $data['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger px-3" onclick="return confirm('Yakin ingin menghapus rapat ini?')">Hapus</button>
                        </form>
                    </div>
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
<?php } ?>

<footer class="footer-custom text-center py-3 border-top mt-auto">
    <div class="text-muted small">&copy; 2025 - Admin Pengelolaan Rapat</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>