<?php
session_start();
include "../koneksi.php";

$current_page = basename($_SERVER['PHP_SELF']);

$p_res = mysqli_query($koneksi, "SELECT * FROM projects");

$meetings_to_show = [];

if ($p_res) {
    while ($proj = mysqli_fetch_assoc($p_res)) {

        $pid = (int)$proj['id'];
        $db_status = $proj['status'];

        $stmt_last = mysqli_prepare(
            $koneksi,
            "SELECT * FROM meetings WHERE project_id = ? ORDER BY tanggal DESC, waktu DESC LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt_last, "i", $pid);
        mysqli_stmt_execute($stmt_last);
        $last_res = mysqli_stmt_get_result($stmt_last);
        $last_meet = mysqli_fetch_assoc($last_res);
        mysqli_stmt_close($stmt_last);

        $effective_status = $db_status;

        if ($last_meet && !in_array($db_status, ['Tertunda', 'Dibatalkan'])) {
            $meet_time = strtotime($last_meet['tanggal'].' '.$last_meet['waktu']);
            $effective_status = ($meet_time < time()) ? 'Selesai' : 'Mendatang';
        }

        if (in_array($effective_status, ['Selesai', 'Dibatalkan'])) {

            $stmt_all = mysqli_prepare(
                $koneksi,
                "SELECT * FROM meetings WHERE project_id = ? ORDER BY tanggal DESC, waktu ASC"
            );
            mysqli_stmt_bind_param($stmt_all, "i", $pid);
            mysqli_stmt_execute($stmt_all);
            $all_res = mysqli_stmt_get_result($stmt_all);

            while ($row = mysqli_fetch_assoc($all_res)) {
                $row['project_status'] = $effective_status;
                $meetings_to_show[] = $row;
            }
            mysqli_stmt_close($stmt_all);
        }
    }
}

function formatTanggalIndo($tanggal) {
    if (!$tanggal) return '-';

    $hari = [
        'Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
        'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'
    ];
    $bulan = [
        'January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April',
        'May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus',
        'September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'
    ];
    $t = strtotime($tanggal);
    return $hari[date('l',$t)].', '.date('d',$t).' '.$bulan[date('F',$t)].' '.date('Y',$t);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Rapat</title>
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
  
  .sidebar-nav {
      overflow-y: auto;
      height: 100%;
  }

  .sidebar-link:hover { background-color: #343a4041 !important; color: #fff !important; border-radius: 0.5rem; transition:0.3s; }
  main { transition:none; }
  .dropdown-menu { padding:0.4rem; overflow:hidden; }
  .dropdown-menu .dropdown-item { padding:0.55rem 1rem; border-radius:0.375rem; transition:0.2s; }
  .dropdown-menu .dropdown-item:hover { background-color:#d8f8fcff; color:#212529; }
  .dropdown-menu .dropdown-item.text-danger:hover { background-color:#fdecea; color:#dc3545; }
  .footer-custom { background-color:#e9ecef; color:#6c757d; }
  .card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.25) !important; transform: translateY(-4px); transition:0.3s; }
  .active-link { background-color: #343a4041; border-radius:0.5rem; color:#fff !important; }
  </style>
</head>

<body class="bg-light d-flex flex-column min-vh-100">

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
      <li><a class="dropdown-item" href="Profil_user.php">Profil</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="../Logout.php">Logout</a></li>
    </ul>
  </div>
</div>
</nav>

<div class="d-flex flex-grow-1">

<div class="collapse collapse-horizontal show bg-dark min-vh-100 d-flex flex-column" id="sidebarToggle">
  <div class="pt-3 sidebar-nav">
    <a href="Home_user.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Home_user.php') ? 'active-link' : '' ?>">Home</a>
    <a href="Rooms_user.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Rooms_user.php') ? 'active-link' : '' ?>">Meeting Rooms</a>
    <a href="Calendars_user.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Calendars_user.php') ? 'active-link' : '' ?>">Calendars</a>
    <a href="History_user.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'History_user.php') ? 'active-link' : '' ?>">History</a>
  </div>
</div>

    <main class="flex-grow-1 p-4">
      <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="fw-bold text-dark">History Rapat</h3>
        </div>

        <div class="card shadow-lg border-0">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover table-bordered align-middle">
                <thead class="table-primary text-center align-middle">
                  <tr>
                    <th width="50">No</th>
                    <th>Hari / Tanggal</th>
                    <th>Waktu</th>
                    <th>Lokasi</th>
                    <th>Agenda</th>
                    <th>Peserta</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                <?php 
                if (count($meetings_to_show) === 0): 
                ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Tidak ada riwayat rapat selesai atau dibatalkan.</td>
                    </tr>
                <?php 
                else: 
                    $no=1; 
                    foreach($meetings_to_show as $m): 
                        
                        $waktu_mulai = isset($m['waktu']) ? strtotime($m['waktu']) : time();
                        $waktu_selesai = $waktu_mulai + (3 * 3600); 

                        $status_meeting = $m['project_status']; 

                        $peserta_raw = isset($m['peserta']) ? $m['peserta'] : "";
                        if (!empty($peserta_raw)) {
                            $participants = array_map('trim', explode(',', $peserta_raw));
                        } else {
                            $participants = []; 
                        }
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= formatTanggalIndo($m['tanggal']) ?></td>
                    <td class="text-nowrap">
                        <?= date("H.i", $waktu_mulai) ?> - <?= date("H.i", $waktu_selesai) ?> WIB
                    </td>
                    <td><?= htmlspecialchars($m['lokasi']) ?></td>
                    <td>
                        <?= htmlspecialchars(substr($m['judul'],0,50)) ?>...
                        <a class="small text-primary text-decoration-none ms-1" 
                           style="cursor: pointer;"
                           data-bs-toggle="modal" 
                           data-bs-target="#modalAgenda" 
                           data-content="<?= htmlspecialchars($m['judul']) ?>">(Lihat)</a>
                    </td>
                    <td>
                        <ul class="mb-1 ps-4 small">
                            <?php foreach(array_slice($participants,0,2) as $p) echo "<li>".htmlspecialchars($p)."</li>"; ?>
                        </ul>
                        <?php if(count($participants)>2): 
                        $data_list = '';
                        foreach($participants as $p) $data_list .= '<li>'.htmlspecialchars($p).'</li>';
                        ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                style="font-size: 0.75rem; padding: 2px 6px;"
                                data-bs-toggle="modal" 
                                data-bs-target="#modalPeserta" 
                                data-list="<?= htmlspecialchars($data_list) ?>">
                          +Lihat <?= count($participants)-2 ?> lainnya
                        </button>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php
                        if($status_meeting == 'Selesai') {
                            echo "<span class='badge bg-success-subtle text-success border border-success-subtle'>Selesai</span>";
                        } elseif($status_meeting == 'Dibatalkan') {
                            echo "<span class='badge bg-danger-subtle text-danger border border-danger-subtle'>Dibatalkan</span>";
                        } else {
                            echo "<span class='badge bg-secondary'>".$status_meeting."</span>";
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>

              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <div class="modal fade" id="modalAgenda" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detail Agenda</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p id="modalAgendaContent" class="lh-base" style="white-space: pre-wrap; font-size: 0.95rem;"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalPeserta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Daftar Peserta Lengkap</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <ul id="modalPesertaList" class="lh-base" style="list-style-type: disc; padding-left: 20px;"></ul>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <footer class="footer-custom text-center py-3 border-top mt-auto">
    <div class="text-muted small">&copy; 2025 - User Pengelolaan Rapat</div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const modalAgenda = document.getElementById('modalAgenda');
    const modalAgendaContent = document.getElementById('modalAgendaContent');

    modalAgenda.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const content = button.getAttribute('data-content');
      modalAgendaContent.textContent = content;
    });

    const modalPeserta = document.getElementById('modalPeserta');
    const modalPesertaList = document.getElementById('modalPesertaList');

    modalPeserta.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const listHtml = button.getAttribute('data-list');
      modalPesertaList.innerHTML = listHtml;
    });
  </script>

</body>
</html>