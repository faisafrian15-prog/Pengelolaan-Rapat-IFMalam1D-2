<?php
session_start();
include "../koneksi.php";

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(openssl_random_pseudo_bytes(32));
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_POST['action']) ? $_POST['action'] : '';

function check_csrf() {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        die("CSRF tidak valid");
    }
}

function runQuery($koneksi, $sql, $redirect = null) {
    try {
        $result = mysqli_query($koneksi, $sql);
        if (!$result) {
            throw new Exception("Database Error: " . mysqli_error($koneksi) . " | Query: " . $sql);
        }
        return $result;
    } catch (Exception $e) {
        error_log($e->getMessage(), 3, __DIR__ . '/db_errors.log');
        echo "<div class='alert alert-danger'>Terjadi kesalahan pada database. Silakan hubungi admin.</div>";
        exit;
    }
}

if ($method === 'POST') {
    check_csrf();

    switch($action) {
        case 'create':
            $name   = mysqli_real_escape_string($koneksi, $_POST['name']);
            $desc   = mysqli_real_escape_string($koneksi, $_POST['desc']);
            $status = mysqli_real_escape_string($koneksi, $_POST['status']);

            runQuery($koneksi, "
                INSERT INTO projects (name, description, status)
                VALUES ('$name', '$desc', '$status')
            ");

            header("Location: Home.php");
            exit;

        case 'edit':
            $id     = (int)$_POST['id'];
            $name   = mysqli_real_escape_string($koneksi, $_POST['name']);
            $desc   = mysqli_real_escape_string($koneksi, $_POST['desc']);
            $status = mysqli_real_escape_string($koneksi, $_POST['status']);

            runQuery($koneksi, "
                UPDATE projects 
                SET name='$name', description='$desc', status='$status'
                WHERE id=$id
            ");

            header("Location: Home.php");
            exit;

        case 'delete':
            $id = (int)$_POST['id'];
            runQuery($koneksi, "DELETE FROM projects WHERE id=$id");

            header("Location: Home.php");
            exit;

        case 'create_meeting':
            $project_id = (int)$_POST['project_id'];
            $judul      = mysqli_real_escape_string($koneksi, $_POST['judul']);
            $tanggal    = $_POST['tanggal'];
            $waktu      = $_POST['waktu'];
            $lokasi     = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
            $agenda     = mysqli_real_escape_string($koneksi, $_POST['agenda']);
            $peserta    = mysqli_real_escape_string($koneksi, $_POST['peserta']);
            $ppt        = mysqli_real_escape_string($koneksi, $_POST['ppt']);

            runQuery($koneksi, "
                INSERT INTO meetings (project_id, judul, tanggal, waktu, lokasi, agenda, peserta, ppt)
                VALUES ($project_id, '$judul', '$tanggal', '$waktu', '$lokasi', '$agenda', '$peserta', '$ppt')
            ");

            header("Location: isi.php?project_id=$project_id");
            exit;
    }
}

$search = isset($_GET['query']) ? mysqli_real_escape_string($koneksi, $_GET['query']) : '';

$sql = "SELECT * FROM projects";
if ($search !== '') {
    $sql .= " WHERE name LIKE '%$search%' 
              OR description LIKE '%$search%' 
              OR status LIKE '%$search%'";
}
$sql .= " ORDER BY id DESC";

$data = runQuery($koneksi, $sql);
$query_users = runQuery($koneksi, "SELECT * FROM daftar_peserta ORDER BY fullname ASC");
$q_meetings  = runQuery($koneksi, "SELECT lokasi, tanggal, waktu FROM meetings");
$q_rooms     = runQuery($koneksi, "SELECT * FROM rooms_meeting ORDER BY room_name ASC");

$meetings_data = [];
while ($m = mysqli_fetch_assoc($q_meetings)) {
    $meetings_data[] = $m;
}

$rooms_data = [];
$currentTime = time();

while ($room = mysqli_fetch_assoc($q_rooms)) {
    $isBooked = false;

    foreach ($meetings_data as $meeting) {
        if (isset($meeting['lokasi'], $meeting['tanggal'], $meeting['waktu']) &&
            $meeting['lokasi'] === $room['room_name']
        ) {
            $meetingDT = strtotime($meeting['tanggal'].' '.$meeting['waktu']);
            if ($meetingDT >= $currentTime) {
                $isBooked = true;
                break;
            }
        }
    }

    $room['status'] = $isBooked ? 'booked' : 'available';
    $rooms_data[] = $room;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rencana Rapat</title>
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
    .card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.25) !important; transform: translateY(-4px); transition:0.3; cursor: pointer; }
    .active-link { background-color: #343a4041; border-radius:0.5rem; color:#fff !important; }

    .participant-dropdown-menu {
        width: 100%;
        padding: 10px;
        max-height: 300px;
        overflow-y: auto;
    }
    .participant-list {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-top: 5px;
    }
    .participant-item {
        padding: 5px 10px;
        border-bottom: 1px solid #f1f1f1;
        cursor: pointer;
        transition: background 0.2s;
    }
    .participant-item:hover {
        background-color: #f8f9fa;
    }
    .participant-item:last-child {
        border-bottom: none;
    }
    .participant-label {
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }
    
    option:disabled {
        color: #dc3545 !important;
        background-color: #f8d7da !important;
        font-weight: bold !important;
        font-style: italic !important;
        cursor: not-allowed !important;
    }
    
    select option:disabled {
        opacity: 0.6;
    }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

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

        <main class="container my-4 d-flex flex-column flex-grow-1">

            <div class="d-flex justify-content-center mb-5">
                
                <button type="button"
                        class="btn btn-success btn-lg me-3"
                        data-bs-toggle="modal"
                        data-bs-target="#createModal">
                    + Tambah
                </button>

                <form method="get" action="" style="position: relative;" class="w-50">
                    <div class="input-group">
                        <input type="text" 
                               name="query" 
                               class="form-control form-control-lg" 
                               placeholder="Cari nama, deskripsi, atau status..." 
                               value="<?= htmlspecialchars($search) ?>">
                        
                        <?php if ($search !== ''): ?>
                            <span onclick="window.location='Home.php'" 
                                  class="position-absolute" 
                                  style="right: 100px; top:50%; transform:translateY(-50%); cursor:pointer; font-weight:bold; font-size:1.25rem; color:#495057; user-select:none; z-index: 10;" 
                                  title="Hapus pencarian">&times;</span>
                        <?php endif; ?>

                        <button class="btn btn-primary btn-lg" type="submit">Search</button>
                    </div>
                </form>
            </div>

            <div class="row g-4">
                <?php 
                if (mysqli_num_rows($data) === 0): 
                ?>
                    <div class="col-12 text-center">
                        <p class="fs-4 text-muted">Tidak ada jadwal rapat</p>
                    </div>
                <?php 
                else: 
                    while ($row = mysqli_fetch_assoc($data)): 
                        
                        $currentStatus = $row['status'];
                        $displayStatus = $currentStatus;

                        $qMeet = mysqli_query($koneksi, "SELECT * FROM meetings WHERE project_id = " . $row['id'] . " ORDER BY tanggal DESC, waktu DESC LIMIT 1");
                        $lastMeeting = mysqli_fetch_assoc($qMeet);

                        if ($lastMeeting && $currentStatus != 'Tertunda' && $currentStatus != 'Dibatalkan') {
                            $meetDateTime = strtotime($lastMeeting['tanggal'] . ' ' . $lastMeeting['waktu']);
                            $now = time();

                            if ($meetDateTime < $now) {
                                $displayStatus = 'Selesai';
                            } else {
                                $displayStatus = 'Mendatang';
                            }
                            
                            if ($displayStatus !== $currentStatus) {
                                mysqli_query($koneksi, "UPDATE projects SET status = '$displayStatus' WHERE id = " . $row['id']);
                            }
                        }
                        
                        if ($displayStatus == 'Mendatang') {
                            $color = 'warning'; 
                        } elseif ($displayStatus == 'Selesai') {
                            $color = 'success'; 
                        } elseif ($displayStatus == 'Tertunda') {
                            $color = 'secondary'; 
                        } elseif ($displayStatus == 'Dibatalkan') {
                            $color = 'danger'; 
                        } else {
                            $color = 'secondary'; 
                        }
                ?>
                
                <div class="col-md-6 col-lg-3 d-flex">
                    <div class="card h-100 w-100"
                         onclick="window.location='isi.php?project_id=<?= $row['id'] ?>'">
                        
                        <div class="card-body py-5">
                            <h5><?= htmlspecialchars($row['name']) ?></h5>
                            <p class="text-truncate"><?= htmlspecialchars($row['description']) ?></p>
                        </div>

                        <span class="badge bg-<?= $color ?> ms-3 mb-2 align-self-start px-2 py-1" 
                              style="font-size: 0.75rem;">
                            <?= $displayStatus ?>
                        </span>

                        <div class="card-footer d-flex justify-content-between bg-white border-top-0">
                            <button class="btn btn-warning btn-sm"
                                    onclick="event.stopPropagation()"
                                    data-bs-toggle="modal"
                                    data-bs-target="#edit<?= $row['id'] ?>">
                                Edit
                            </button>

                            <form method="POST"
                                  onclick="event.stopPropagation()"
                                  onsubmit="return confirm('Yakin hapus?')">
                                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="edit<?= $row['id'] ?>">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                <div class="modal-header">
                                    <h5>Edit Projek</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Project</label>
                                        <input class="form-control" 
                                               name="name" 
                                               value="<?= htmlspecialchars($row['name']) ?>" 
                                               required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea class="form-control" 
                                                  name="desc" 
                                                  required><?= htmlspecialchars($row['description']) ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option>Tertunda</option>
                                            <option>Dibatalkan</option>
                                        </select>
                                        <small class="text-muted">Status Mendatang & Selesai dihitung otomatis dari jadwal rapat terakhir.</small>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php endwhile; ?>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <div class="modal fade" id="createModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <button class="nav-link active" 
                                data-bs-toggle="tab" 
                                data-bs-target="#projectTab">
                            Project
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" 
                                data-bs-toggle="tab" 
                                data-bs-target="#isiTab">
                            Isi Rapat
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                    <div class="tab-pane fade show active p-4" id="projectTab">
                        <form method="POST">
                            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                            <input type="hidden" name="action" value="create">

                            <div class="mb-3">
                                <label class="form-label">Nama Project</label>
                                <input class="form-control" name="name" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="desc" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" value="Mendatang" disabled>
                                <input type="hidden" name="status" value="Mendatang">
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-success">Simpan Project</button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade p-4" id="isiTab">
                        <form method="POST">
                            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                            <input type="hidden" name="action" value="create_meeting">

                            <div class="mb-3">
                                <label class="form-label">Project</label>
                                <select name="project_id" class="form-select" required>
                                    <?php
                                    $q = mysqli_query($koneksi, "SELECT id, name FROM projects");
                                    while ($p = mysqli_fetch_assoc($q)):
                                    ?>
                                        <option value="<?= $p['id'] ?>">
                                            <?= htmlspecialchars($p['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Judul</label>
                                <input class="form-control" name="judul" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Hari / Tanggal</label>
                                    <input type="date" class="form-control" name="tanggal" id="inputTanggal" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Waktu</label>
                                    <input type="time" class="form-control" name="waktu" id="inputWaktu" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Lokasi</label>
                                <select name="lokasi" id="selectLokasi" class="form-select" required>
                                    <option value="" selected disabled>Pilih Lokasi Ruangan</option>
                                    <?php foreach ($rooms_data as $r): ?>
                                        <option value="<?= htmlspecialchars($r['room_name']) ?>" 
                                                <?= ($r['status'] === 'booked') ? 'disabled' : '' ?>>
                                            <?= htmlspecialchars($r['room_name']) ?>
                                            <?= ($r['status'] === 'booked') ? ' (TERBOOKING - Tidak Tersedia)' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-danger fw-bold">
                                    ⚠️ Ruangan dengan status TERBOOKING tidak bisa dipilih karena sudah ada jadwal rapat.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Agenda</label>
                                <textarea class="form-control" name="agenda" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Daftar Peserta</label>
                                
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center" 
                                            type="button" 
                                            id="pesertaDropdownBtn" 
                                            data-bs-toggle="dropdown" 
                                            data-bs-auto-close="outside" 
                                            aria-expanded="false">
                                        <span id="selectedPesertaLabel">Pilih Peserta...</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                                        </svg>
                                    </button>
                                    
                                    <ul class="dropdown-menu participant-dropdown-menu" aria-labelledby="pesertaDropdownBtn">
                                        <li class="p-2 border-bottom" onclick="event.stopPropagation()">
                                            <input type="text" class="form-control form-control-sm" id="searchPesertaInput" placeholder="Cari Nama atau NIK...">
                                        </li>
                                        
                                        <li class="participant-list" id="pesertaListContainer">
                                            <?php 
                                            mysqli_data_seek($query_users, 0); 
                                            if ($query_users && mysqli_num_rows($query_users) > 0): 
                                                while($u = mysqli_fetch_assoc($query_users)): ?>
                                                    
                                                    <div class="participant-item" onclick="event.stopPropagation()">
                                                        <div class="form-check">
                                                            <input class="form-check-input peserta-checkbox" 
                                                                   type="checkbox" 
                                                                   value="<?= htmlspecialchars($u['fullname']) ?>" 
                                                                   id="peserta_<?= $u['id'] ?>"
                                                                   data-name="<?= htmlspecialchars($u['fullname']) ?>"
                                                                   data-nik="<?= htmlspecialchars($u['nik']) ?>">
                                                            
                                                            <label class="participant-label" for="peserta_<?= $u['id'] ?>">
                                                                <div class="d-flex flex-column">
                                                                    <strong><?= htmlspecialchars($u['fullname']) ?></strong>
                                                                    <small class="text-muted">NIK: <?= htmlspecialchars($u['nik']) ?></small>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endwhile; 
                                            else: ?>
                                                <div class="text-center p-2 text-danger small">
                                                    Tidak ada data peserta.<br>
                                                    Pastikan tabel 'daftar_peserta' ada dan memiliki kolom 'id', 'fullname', dan 'nik'.
                                                </div>
                                            <?php endif; ?>
                                        </li>
                                    </ul>

                                    <input type="hidden" name="peserta" id="inputHiddenPeserta">
                                </div>
                                <small class="text-muted">Centang nama peserta di atas (Bisa pilih banyak).</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">PPT / Slide (Link)</label>
                                <input class="form-control" 
                                       name="ppt" 
                                       placeholder="https://...">
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Simpan Isi Rapat</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <footer class="footer-custom text-center py-3 border-top mt-auto">
        <div class="text-muted small">&copy; 2025 - Admin Pengelolaan Rapat</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchPesertaInput');
            const checkboxes = document.querySelectorAll('.peserta-checkbox');
            const hiddenInput = document.getElementById('inputHiddenPeserta');
            const labelBtn = document.getElementById('selectedPesertaLabel');

            searchInput.addEventListener('keyup', function(e) {
                const term = e.target.value.toLowerCase();
                checkboxes.forEach(function(chk) {
                    const container = chk.closest('.participant-item');
                    const name = chk.getAttribute('data-name').toLowerCase();
                    const nik = chk.getAttribute('data-nik').toLowerCase();
                    
                    if (name.includes(term) || nik.includes(term)) {
                        container.classList.remove('d-none');
                    } else {
                        container.classList.add('d-none');
                    }
                });
            });

            function updatePesertaValue() {
                let selectedNames = [];
                checkboxes.forEach(function(chk) {
                    if (chk.checked) {
                        selectedNames.push(chk.value);
                    }
                });

                hiddenInput.value = selectedNames.join(', ');

                if (selectedNames.length > 0) {
                    if (selectedNames.length <= 2) {
                        labelBtn.textContent = selectedNames.join(', ');
                    } else {
                        labelBtn.textContent = selectedNames.length + ' Orang Terpilih';
                    }
                    labelBtn.classList.add('text-dark');
                } else {
                    labelBtn.textContent = 'Pilih Peserta...';
                    labelBtn.classList.remove('text-dark');
                }
            }

            checkboxes.forEach(function(chk) {
                chk.addEventListener('change', updatePesertaValue);
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bookedData = <?php echo json_encode($meetings_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;
            
            const dateInput = document.getElementById('inputTanggal');
            const timeInput = document.getElementById('inputWaktu');
            const roomSelect = document.getElementById('selectLokasi');

            function updateRoomAvailability() {
                const selectedDate = dateInput.value;
                const selectedTime = timeInput.value;

                const bookingKeys = new Set();
                
                bookedData.forEach(booking => {
                    if(booking.tanggal && booking.waktu && booking.lokasi) {
                        const timePart = booking.waktu.substring(0, 5); 
                        const roomPart = booking.lokasi.trim().toLowerCase();
                        
                        const key = `${booking.tanggal}|${timePart}|${roomPart}`;
                        bookingKeys.add(key);
                    }
                });

                Array.from(roomSelect.options).forEach(opt => {
                    opt.disabled = false;
                    opt.text = opt.text.split(' (')[0];
                });
                
                if(roomSelect.options[0].value === "") roomSelect.options[0].disabled = true;

                if (!selectedDate || !selectedTime) return;

                let currentlySelectedRoom = roomSelect.value;
                let isSelectionInvalid = false;

                Array.from(roomSelect.options).forEach(opt => {
                    if(opt.value === "") return;

                    const optRoom = opt.value.trim().toLowerCase();
                    const searchKey = `${selectedDate}|${selectedTime}|${optRoom}`;

                    if (bookingKeys.has(searchKey)) {
                        opt.disabled = true;
                        opt.text += " (TERBOOKING)";
                        
                        if (currentlySelectedRoom && opt.value.toLowerCase() === currentlySelectedRoom.toLowerCase()) {
                            isSelectionInvalid = true;
                        }
                    }
                });

                if (isSelectionInvalid) {
                    roomSelect.value = "";
                    alert("Ruangan yang Anda pilih sudah terbooking pada jam tersebut. Silakan pilih ruangan lain.");
                }
            }

            dateInput.addEventListener('change', updateRoomAvailability);
            timeInput.addEventListener('change', updateRoomAvailability);
        });
    </script>
</body>
</html>