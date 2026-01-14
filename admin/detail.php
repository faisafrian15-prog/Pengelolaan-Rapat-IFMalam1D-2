<?php
session_start();
include "../Koneksi.php";

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../Login.php");
    exit();
}

$fixedJurusan = ["Teknik Informatika", "Teknik Mesin", "Teknik Elektro", "Manajemen"];
$current_page = basename($_SERVER['PHP_SELF']);

function queryDatabase($koneksi, $sql) {
    $res = mysqli_query($koneksi, $sql);
    if (!$res) {
        throw new Exception("Query gagal: " . mysqli_error($koneksi));
    }
    return $res;
}

function insertUser($koneksi, $jurusan, $email, $nik, $fullname, $username, $password) {
    $sql = "INSERT INTO users (jurusan, email, nik, fullname, username, password, role) 
            VALUES ('$jurusan','$email','$nik','$fullname','$username','$password','user')";
    return queryDatabase($koneksi, $sql);
}

function insertDaftarPeserta($koneksi, $fullname, $nik, $jurusan) {
    $sql = "INSERT INTO daftar_peserta (fullname, nik, jurusan) 
            VALUES ('$fullname','$nik','$jurusan')";
    return queryDatabase($koneksi, $sql);
}

function getDaftarPeserta($koneksi, $jurusan) {
    $sql = "SELECT fullname, nik FROM daftar_peserta WHERE jurusan='$jurusan' ORDER BY fullname ASC";
    $res = queryDatabase($koneksi, $sql);
    $rows = [];
    while($row = mysqli_fetch_assoc($res)) $rows[] = $row;
    return $rows;
}

try {
    if (isset($_POST['submit_popup'])) {
        $jurusan  = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
        $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
        $nik      = mysqli_real_escape_string($koneksi, $_POST['nik']);
        $fullname = mysqli_real_escape_string($koneksi, $_POST['fullname']);
        $username = mysqli_real_escape_string($koneksi, $_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $cek = queryDatabase($koneksi, "SELECT id FROM users WHERE username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            throw new Exception("Username sudah digunakan!");
        }

        insertUser($koneksi, $jurusan, $email, $nik, $fullname, $username, $password);
        insertDaftarPeserta($koneksi, $fullname, $nik, $jurusan);

        echo "<script>
                alert('Akun berhasil dibuat!');
                window.location.href='detail.php';
              </script>";
        exit();
    }
} catch(Exception $e) {
    $error_message = $e->getMessage();
    error_log($error_message);
}

$dataPeserta = [];
foreach($fixedJurusan as $jurusan) {
    try {
        $dataPeserta[$jurusan] = getDaftarPeserta($koneksi, $jurusan);
    } catch(Exception $e) {
        $dataPeserta[$jurusan] = [];
        error_log("Gagal ambil peserta $jurusan: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Jurusan & user</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

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
    .card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.25) !important; transform: translateY(-4px); transition:0.3s; cursor: pointer; }
    .active-link { background-color: #343a4041; border-radius:0.5rem; color:#fff !important; }
    
    .jurusan-card {
        cursor: pointer;
        position: relative;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }
    
    .jurusan-card:hover {
        background-color: #f8f9fa;
    }
    
    .more-icon {
        font-size: 24px;
        font-weight: bold;
        color: #6c757d;
        cursor: pointer;
        padding: 0 10px;
        flex-shrink: 0;
    }
    
    .jurusan-title {
        flex-grow: 1;
    }
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
      <li><a class="dropdown-item" href="Profil.php">Profil</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="../Logout.php">Logout</a></li>
    </ul>
  </div>
</div>
</nav>

<div class="d-flex flex-grow-1">

<div class="collapse collapse-horizontal show bg-dark d-flex flex-column min-vh-100" id="sidebarToggle">
  <div class="pt-3 sidebar-nav">
    <a href="Home.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Home.php') ? 'active-link' : '' ?>">Home</a>
    <a href="Rooms.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Rooms.php') ? 'active-link' : '' ?>">Meeting Rooms</a>
    <a href="Calendars.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'Calendars.php') ? 'active-link' : '' ?>">Calendars</a>
    <a href="History.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'History.php') ? 'active-link' : '' ?>">History</a>
    <a href="detail.php" class="nav-link text-white-50 text-decoration-none py-2 px-4 sidebar-link fs-4 h1 <?= ($current_page == 'detail.php') ? 'active-link' : '' ?>">Detail</a>
  </div>
</div>

        <div class="container mt-5">
            <h2 class="mb-4">Info Lebih Detail</h2>

            <div class="col-md-6" style="margin-left:50px;">

                <?php foreach ($fixedJurusan as $jurusan): 
                    $id = md5($jurusan); ?>

                    <div class="card p-3 mb-3 shadow-sm jurusan-card"
                         onclick="togglePeserta('<?= $id ?>')">
                        
                        <h5 class="fw-bold mb-0 jurusan-title"><?= htmlspecialchars($jurusan) ?></h5>
                        <span class="more-icon">⋮</span>

                    </div>

                    <div id="peserta-<?= $id ?>" class="ps-3" style="display:none;">

                        <button class="btn btn-primary mb-3"
                                onclick="openTambahUser('<?= $jurusan ?>')">
                            + Tambah User Jurusan Ini
                        </button>

                        <div class="card shadow mb-3">
                            <div class="card-body">

                                <?php if (!empty($dataPeserta[$jurusan])): ?>
                                    <?php foreach ($dataPeserta[$jurusan] as $p): ?>
                                        <div class="card p-2 mb-2 shadow-sm">
                                            <strong><?= htmlspecialchars($p["fullname"]) ?></strong><br>
                                            <span class="text-muted">NIK: <?= htmlspecialchars($p["nik"]) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">Belum ada peserta dalam jurusan ini.</p>
                                <?php endif; ?>

                            </div>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>
        </div>

    </div>

    <footer class="footer-custom text-center py-3 border-top mt-auto">
        <div class="text-muted small">&copy; 2025 - Admin Pengelolaan Rapat</div>
    </footer>

    <div class="modal fade" id="modalTambahUser" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">Tambah User Jurusan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">

            <form method="POST">

                <input type="hidden" name="jurusan" id="popup_jurusan">

                <div class="mb-3">
                    <label class="form-label">Jurusan</label>
                    <input type="text" id="popup_jurusan_text" class="form-control" disabled>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="text" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>NIK</label>
                    <input type="text" name="nik" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Nama Lengkap</label>
                    <input type="text" name="fullname" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" name="submit_popup" class="btn btn-primary w-100">
                    Buat Akun User
                </button>

            </form>

          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePeserta(id) {
            $("#peserta-" + id).slideToggle(250);
        }

        function openTambahUser(jurusan) {
            document.getElementById("popup_jurusan").value = jurusan;
            document.getElementById("popup_jurusan_text").value = jurusan;

            let modal = new bootstrap.Modal(document.getElementById("modalTambahUser"));
            modal.show();
        }
    </script>

</body>

</html>