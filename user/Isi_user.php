<?php
session_start();
include "../Koneksi.php";

function isUserLoggedIn() {
    return isset($_SESSION['username']) && isset($_SESSION['role']);
}

function authorizeUserOnly() {
    if ($_SESSION['role'] !== 'user') {
        header("Location: ../forbidden.php");
        exit();
    }
}

try {
    if (!isUserLoggedIn()) {
        throw new Exception("Session tidak valid");
    }

    authorizeUserOnly();

    if (!$GLOBALS['koneksi']) {
        throw new Exception("Koneksi database gagal");
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    echo "Terjadi kesalahan sistem.";
    exit();
}

function formatTanggalIndo($tanggal) {
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

if (!isset($_GET['project_id'])) {
    echo "Project tidak ditemukan";
    exit();
}
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : 0;

try {
    $stmt = $koneksi->prepare("SELECT * FROM projects WHERE id = ?");
    if (!$stmt) throw new Exception("Gagal menyiapkan query project: " . $koneksi->error);

    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();
    $stmt->close();

    if (!$project) {
        throw new Exception("Project tidak ditemukan");
    }

    $stmt = $koneksi->prepare("SELECT * FROM meetings WHERE project_id = ? ORDER BY tanggal DESC, waktu DESC");
    if (!$stmt) throw new Exception("Gagal menyiapkan query meetings: " . $koneksi->error);

    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $q = $stmt->get_result(); 
    $stmt->close();

} catch (Exception $e) {
    error_log($e->getMessage());
    echo "Data tidak dapat ditampilkan.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Isi Jadwal Rapat</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
.navbar { background-color: #c3c7ceff !important; }
.footer-custom { background-color:#e9ecef; color:#6c757d; }
</style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary py-4">
<div class="container-fluid">
  <a href="Home_user.php" class="btn btn-outline-secondary btn-lg ms-3">← Kembali</a>
  <div class="mx-auto position-absolute start-50 translate-middle-x">
    <span class="navbar-brand fs-2 fw-bold text-dark">Pengelolaan Rapat</span>
  </div>
</div>
</nav>

<div class="flex-grow-1 d-flex flex-column align-items-center">

<?php if(mysqli_num_rows($q) == 0): ?>
    <div class="card shadow-sm w-75 mt-4 text-center" style="max-width:800px;">
        <div class="card-body py-5">
            <h3 class="text-muted mb-3">📅 Belum Ada Jadwal Rapat</h3>
            <p class="fs-5 text-muted">
                Jadwal rapat untuk project ini masih kosong atau masih mendatang.
            </p>
        </div>
    </div>
<?php else: ?>
    <?php while($row=mysqli_fetch_assoc($q)): ?>
    <?php
    $mulai = date('H.i', strtotime($row['waktu']));
    $selesai = date('H.i', strtotime($row['waktu'].' +3 hours'));
    ?>

    <div class="card shadow-sm w-75 mt-4" style="max-width:800px;">
    <div class="card-body py-5">

    <h2 class="card-title text-center mb-5 fs-2">
    <?= htmlspecialchars($row['judul']) ?>
    </h2>

    <div class="container">
    <div class="row mb-2 fs-5">
    <div class="col-3"><strong>Hari/Tanggal</strong></div>
    <div class="col-1">:</div>
    <div class="col-8"><?= formatTanggalIndo($row['tanggal']) ?></div>
    </div>

    <div class="row mb-2 fs-5">
    <div class="col-3"><strong>Waktu</strong></div>
    <div class="col-1">:</div>
    <div class="col-8">
    <?= $mulai ?> - <?= $selesai ?> WIB
    </div>
    </div>

    <div class="row mb-2 fs-5">
    <div class="col-3"><strong>Lokasi</strong></div>
    <div class="col-1">:</div>
    <div class="col-8"><?= htmlspecialchars($row['lokasi']) ?></div>
    </div>

    <div class="row mb-2 fs-5">
    <div class="col-3"><strong>Agenda</strong></div>
    <div class="col-1">:</div>
    <div class="col-8"><?= nl2br(htmlspecialchars($row['agenda'])) ?></div>
    </div>

    <div class="row mb-2 fs-5">
    <div class="col-3"><strong>Daftar Peserta</strong></div>
    <div class="col-1">:</div>
    <div class="col-8">
    <ul class="ps-4 mb-0">
    <?php foreach(explode(',',$row['peserta']) as $p): if(trim($p)!==''): ?>
    <li><?= htmlspecialchars(trim($p)) ?></li>
    <?php endif; endforeach; ?>
    </ul>
    </div>
    </div>

    <div class="row mt-4 fs-5">
    <div class="col-3"><strong>Slide/PPT</strong></div>
    <div class="col-1">:</div>
    <div class="col-8">
    <?php if($row['ppt']): ?>
    <a href="<?= htmlspecialchars($row['ppt']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-file-earmark-ppt-fill me-1"></i>Lihat Presentasi
    </a>
    <?php else: ?>
    <span class="text-muted fst-italic">Tidak ada presentasi</span>
    <?php endif; ?>
    </div>
    </div>

    <?php
    $tanggalIndo = formatTanggalIndo($row['tanggal']);
    $pesertaText = trim(implode("\n- ", array_map('trim', explode(',', $row['peserta']))));
    $emailBody = "
Dengan hormat,

Sehubungan dengan akan dilaksanakannya rapat, bersama ini kami mengundang
Bapak/Ibu/Saudara untuk hadir pada rapat yang akan dilaksanakan dengan rincian
sebagai berikut:

Agenda   : {$row['agenda']}
Tanggal  : {$tanggalIndo}
Waktu    : {$mulai} - {$selesai} WIB
Lokasi   : {$row['lokasi']}
Peserta  :
- {$pesertaText}

Demikian undangan ini kami sampaikan. Atas perhatian dan kehadirannya kami ucapkan terima kasih.

Hormat kami,
Jurusan Teknik Informatika
";
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $link = $scheme.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    ?>
    <div class="d-flex justify-content-end mt-4 align-items-center">

    <a href="mailto:?subject=<?=rawurlencode('Undangan Rapat: '.$row['judul'])?>&body=<?=rawurlencode(trim($emailBody))?>" 
       class="btn btn-success me-2">
    <i class="bi bi-envelope-fill me-1"></i>Email
    </a>

    <div class="input-group" style="max-width:260px;">
    <input type="text" class="form-control form-control-sm" value="<?= $link ?>" readonly id="copyLink<?= $row['id'] ?>">
    <button class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('copyLink<?= $row['id'] ?>').value)">
    <i class="bi bi-clipboard-fill"></i>
    </button>
    </div>
    </div>

    </div>
    </div>
    </div>

    <?php endwhile; ?>
<?php endif; ?>

</div>

<footer class="footer-custom text-center py-3 border-top mt-auto">
  <div class="text-muted small">&copy; 2025 - Dashboard Pengelolaan Rapat</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>