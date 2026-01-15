<?php
session_start();
include "../Koneksi.php";

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function check_csrf() {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        die("CSRF token tidak valid");
    }
}

// --- Fungsi Helper untuk Upload PPT ---
function handlePptUpload($fileInput, $urlInput, $oldFile = '') {
    global $koneksi; // Akses koneksi database jika perlu query tambahan (opsional)
    
    // 1. Cek jika user memilih input URL (Lebih prioritas jika tab URL aktif)
    // Kita deteksi dari input hidden 'use_ppt_url' yang kita set di JS/Form
    $useUrl = isset($_POST['use_ppt_url']) && $_POST['use_ppt_url'] === '1';

    if ($useUrl && !empty($urlInput)) {
        // Hapus file lama jika sebelumnya adalah file upload (bukan link)
        if ($oldFile && !preg_match('/^https?:\/\//', $oldFile)) {
            $filePath = "../assets/" . $oldFile;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        return trim($urlInput);
    }

    // 2. Cek jika user mengupload file baru
    if (!empty($fileInput['name']) && $fileInput['error'] === 0) {
        $allowedExts = ['pdf', 'ppt', 'pptx', 'doc', 'docx'];
        $fileExt = strtolower(pathinfo($fileInput['name'], PATHINFO_EXTENSION));
        
        // Validasi ekstensi
        if (in_array($fileExt, $allowedExts) && $fileInput['size'] <= 10485760) { // Max 10MB
            $newFileName = uniqid('ppt_') . '.' . $fileExt;
            
            if (move_uploaded_file($fileInput['tmp_name'], "../assets/" . $newFileName)) {
                // Hapus file lama jika ada
                if ($oldFile && !preg_match('/^https?:\/\//', $oldFile)) {
                    $oldFilePath = "../assets/" . $oldFile;
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                return $newFileName;
            }
        }
    }

    // 3. Jika tidak ada perubahan (kosong), kembalikan nilai lama
    return $oldFile;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'update_meeting') {
        check_csrf();

        $id         = (int)$_POST['id'];
        $project_id = (int)$_POST['project_id'];
        
        // Ambil data PPT lama untuk referensi penghapusan
        $q_old = mysqli_query($koneksi, "SELECT ppt FROM meetings WHERE id=$id");
        $d_old = mysqli_fetch_assoc($q_old);
        $oldPpt = isset($d_old['ppt']) ? $d_old['ppt'] : '';

        // Proses PPT (File atau URL)
        $pptValue = handlePptUpload(
            isset($_FILES['ppt_file']) ? $_FILES['ppt_file'] : null,
            isset($_POST['ppt_url']) ? $_POST['ppt_url'] : '',
            $oldPpt
        );

        $judul      = mysqli_real_escape_string($koneksi, $_POST['judul']);
        $tanggal    = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
        $waktu      = mysqli_real_escape_string($koneksi, $_POST['waktu']);
        $lokasi     = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
        $agenda     = mysqli_real_escape_string($koneksi, $_POST['agenda']);
        $peserta    = mysqli_real_escape_string($koneksi, $_POST['peserta']);
        // $pptValue sudah aman dihandle fungsi, tapi tetap escape untuk query
        $pptEscaped = mysqli_real_escape_string($koneksi, $pptValue);

        mysqli_query($koneksi, "
            UPDATE meetings SET
            judul   = '$judul',
            tanggal = '$tanggal',
            waktu   = '$waktu',
            lokasi  = '$lokasi',
            agenda  = '$agenda',
            peserta = '$peserta',
            ppt     = '$pptEscaped'
            WHERE id = $id
        ");

        header("Location: isi.php?project_id=$project_id");
        exit;
    }

    if ($action === 'delete_meeting') {
        check_csrf();
        $id         = (int)$_POST['id'];
        $project_id = (int)$_POST['project_id'];

        // Hapus file ppt fisik jika ada
        $d = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT ppt FROM meetings WHERE id=$id"));
        if($d && $d['ppt'] && !preg_match('/^https?:\/\//', $d['ppt'])){
            @unlink("../assets/".$d['ppt']);
        }

        mysqli_query($koneksi, "DELETE FROM meetings WHERE id = $id");
        header("Location: isi.php?project_id=$project_id");
        exit;
    }
}

function formatTanggalIndo($tanggal) {
    if (!$tanggal) return '-';
    $hari = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
    $bulan = ['January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus','September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'];
    $t = strtotime($tanggal);
    return $hari[date('l',$t)].', '.date('d',$t).' '.$bulan[date('F',$t)].' '.date('Y',$t);
}

 $project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
if ($project_id <= 0) die("Project tidak valid");

 $p = mysqli_query($koneksi, "SELECT * FROM projects WHERE id=$project_id");
 $project = mysqli_fetch_assoc($p);
if (!$project) die("Project tidak ditemukan");

 $q = mysqli_query($koneksi, "SELECT * FROM meetings WHERE project_id=$project_id ORDER BY tanggal DESC");
 $query_users = mysqli_query($koneksi, "SELECT * FROM daftar_peserta ORDER BY fullname ASC");

 $q_meetings = mysqli_query($koneksi, "SELECT lokasi, tanggal, waktu FROM meetings");
 $meetings_data = [];
while ($m = mysqli_fetch_assoc($q_meetings)) { $meetings_data[] = $m; }

 $q_rooms = mysqli_query($koneksi, "SELECT * FROM rooms_meeting ORDER BY room_name ASC");
 $rooms_data = [];
 $now = time();
while ($r = mysqli_fetch_assoc($q_rooms)) {
    $r['status'] = 'available';
    foreach ($meetings_data as $m) {
        if ($m['lokasi'] === $r['room_name'] && strtotime($m['tanggal'].' '.$m['waktu']) >= $now) {
            $r['status'] = 'booked'; break;
        }
    }
    $rooms_data[] = $r;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isi Jadwal Rapat - <?= htmlspecialchars($project['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
    .navbar { background-color: #c3c7ceff !important; }
    .footer-custom { background-color:#e9ecef; color:#6c757d; }
    .participant-dropdown-menu { width: 100%; padding: 10px; max-height: 300px; overflow-y: auto; }
    .participant-list { max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 5px; }
    .participant-item { padding: 5px 10px; border-bottom: 1px solid #f1f1f1; cursor: pointer; transition: background 0.2s; }
    .participant-item:hover { background-color: #f8f9fa; }
    .participant-item:last-child { border-bottom: none; }
    .participant-label { cursor: pointer; display: flex; justify-content: space-between; align-items: center; width: 100%; }
    option:disabled { color: #dc3545 !important; background-color: #f8d7da !important; font-weight: bold !important; cursor: not-allowed !important; }
    select option:disabled { opacity: 0.6; }
    
    /* Style khusus Preview PPT */
    .ppt-preview-box {
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        background-color: #f8f9fa;
        position: relative;
        overflow: hidden;
    }
    .ppt-preview-box img { width: 100%; height: 100%; object-fit: cover; }
    .ppt-preview-box .file-icon { font-size: 3rem; color: #6c757d; }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary py-4">
<div class="container-fluid">
  <a href="Home.php" class="btn btn-outline-secondary btn-lg ms-3">← Kembali</a>
  <div class="mx-auto position-absolute start-50 translate-middle-x">
    <span class="navbar-brand fs-2 fw-bold text-dark">Pengelolaan Rapat</span>
  </div>
</div>
</nav>

<div class="flex-grow-1 d-flex flex-column align-items-center">
<main class="flex-grow-1 p-4 overflow-auto w-100">
  <div class="container-fluid">

    <?php if(mysqli_num_rows($q) == 0): ?>
        <div class="card shadow-sm w-75 mt-4 mx-auto text-center" style="max-width:800px;">
            <div class="card-body py-5">
                <h3 class="text-muted mb-3">📅 Belum Ada Jadwal Rapat</h3>
                <p class="fs-5 text-muted">Jadwal rapat untuk project ini masih kosong.</p>
            </div>
        </div>
    <?php else: ?>

        <?php 
        $counter = 0;
        while($row=mysqli_fetch_assoc($q)): 
        $counter++;
        $mulai = date('H.i', strtotime($row['waktu']));
        $selesai = date('H.i', strtotime($mulai.' +3 hours'));
        $tanggalIndo = formatTanggalIndo($row['tanggal']);
        $pesertaText = trim(implode("\n- ", array_map('trim', explode(',', $row['peserta']))));
        
        $emailBody = "Dengan hormat,\n\nSehubungan dengan akan dilaksanakannya rapat, bersama ini kami mengundang Bapak/Ibu/Saudara untuk hadir pada rapat yang akan dilaksanakan dengan rincian sebagai berikut:\n\nAgenda   : {$row['agenda']}\nTanggal  : {$tanggalIndo}\nWaktu    : {$mulai} - {$selesai} WIB\nLokasi   : {$row['lokasi']}\nPeserta  :\n- {$pesertaText}\n\nDemikian undangan ini kami sampaikan...";
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $link = $scheme.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        
        // Logika Tampilan PPT (Icon File atau Link)
        $pptDisplay = '';
        $pptIsLink = false;
        if(!empty($row['ppt'])){
            if(preg_match('/^https?:\/\//', $row['ppt'])){
                $pptIsLink = true;
                $pptDisplay = $row['ppt'];
            } else {
                $pptDisplay = "../assets/".$row['ppt'];
            }
        }
        ?>

        <div class="card shadow-sm w-75 mt-4 mx-auto" style="max-width:800px;">
        <div class="card-body py-5">
            <h2 class="card-title text-center mb-5 fs-2"><?= htmlspecialchars($row['judul']) ?></h2>
            <div class="container">
                <div class="row mb-2 fs-5"><div class="col-3"><strong>Hari/Tanggal</strong></div><div class="col-1">:</div><div class="col-8"><?= date('l, d F Y',strtotime($row['tanggal'])) ?></div></div>
                <div class="row mb-2 fs-5"><div class="col-3"><strong>Waktu</strong></div><div class="col-1">:</div><div class="col-8"><?= $mulai ?> - <?= $selesai ?> WIB</div></div>
                <div class="row mb-2 fs-5"><div class="col-3"><strong>Lokasi</strong></div><div class="col-1">:</div><div class="col-8"><?= htmlspecialchars($row['lokasi']) ?></div></div>
                <div class="row mb-2 fs-5"><div class="col-3"><strong>Agenda</strong></div><div class="col-1">:</div><div class="col-8"><?= nl2br(htmlspecialchars($row['agenda'])) ?></div></div>
                <div class="row mb-2 fs-5"><div class="col-3"><strong>Daftar Peserta</strong></div><div class="col-1">:</div><div class="col-8"><ul class="ps-4 mb-0"><?php foreach(explode(',',$row['peserta']) as $p): if(trim($p)!==''): ?><li><?= htmlspecialchars(trim($p)) ?></li><?php endif; endforeach; ?></ul></div></div>
                <div class="row mt-4 fs-5"><div class="col-3"><strong>Slide/PPT</strong></div><div class="col-1">:</div><div class="col-8">
                    <?php if($pptDisplay): ?>
                        <a href="<?= htmlspecialchars($pptDisplay) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-file-earmark-<?= $pptIsLink ? 'link' : 'ppt-fill' ?> me-1"></i> Lihat Presentasi
                        </a>
                    <?php else: ?>
                        <span class="text-muted fst-italic">Tidak ada presentasi</span>
                    <?php endif; ?>
                </div></div>
            </div>

            <div class="d-flex justify-content-end mt-4 align-items-center flex-wrap gap-2">
                <a href="mailto:?subject=<?=rawurlencode('Undangan Rapat: '.$row['judul'])?>&body=<?=rawurlencode(trim($emailBody))?>" class="btn btn-success me-2"><i class="bi bi-envelope-fill me-1"></i>Email</a>
                <div class="input-group" style="max-width:260px;">
                    <input type="text" class="form-control form-control-sm" value="<?= $link ?>" readonly id="copyLink<?= $row['id'] ?>">
                    <button class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('copyLink<?= $row['id'] ?>').value)"><i class="bi bi-clipboard-fill"></i></button>
                </div>
                <div class="d-flex gap-2 border-start ps-3">
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>"><i class="bi bi-pencil-square"></i> Edit</button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapusModal<?= $row['id'] ?>"><i class="bi bi-trash"></i> Hapus</button>
                </div>
            </div>
        </div>
        </div>

        <!-- MODAL EDIT (Diperbarui) -->
        <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="isi.php" enctype="multipart/form-data">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                        <input type="hidden" name="action" value="update_meeting">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="project_id" value="<?= $project_id ?>">
                        
                        <div class="modal-header"><h5 class="modal-title">Edit Jadwal Rapat</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

                        <div class="modal-body">
                            <div class="mb-3"><label class="form-label">Judul</label><input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($row['judul']) ?>" required></div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" id="editTanggal<?= $counter ?>" value="<?= $row['tanggal'] ?>" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Waktu</label><input type="time" name="waktu" class="form-control" id="editWaktu<?= $counter ?>" value="<?= $row['waktu'] ?>" required></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Lokasi</label>
                                <select name="lokasi" id="editLokasi<?= $counter ?>" class="form-select" required>
                                    <option value="" disabled>Pilih Lokasi Ruangan</option>
                                    <?php foreach ($rooms_data as $r): ?>
                                        <option value="<?= htmlspecialchars($r['room_name']) ?>" 
                                                <?= ($r['room_name'] == $row['lokasi']) ? 'selected' : '' ?>
                                                <?= ($r['status'] === 'booked' && $r['room_name'] != $row['lokasi']) ? 'disabled' : '' ?>>
                                            <?= htmlspecialchars($r['room_name']) ?>
                                            <?= ($r['status'] === 'booked' && $r['room_name'] != $row['lokasi']) ? ' (TERBOOKING)' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Ruangan yang sedang Anda gunakan tetap bisa dipilih.</small>
                            </div>
                            
                            <div class="mb-3"><label class="form-label">Agenda</label><textarea name="agenda" class="form-control" rows="3"><?= htmlspecialchars($row['agenda']) ?></textarea></div>
                            
                            <div class="mb-3">
                                <label class="form-label">Daftar Peserta</label>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center" type="button" id="pesertaEditBtn<?= $counter ?>" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                        <span id="selectedEditLabel<?= $counter ?>">Pilih Peserta...</span>
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                    <ul class="dropdown-menu participant-dropdown-menu" aria-labelledby="pesertaEditBtn<?= $counter ?>">
                                        <li class="p-2 border-bottom"><input type="text" class="form-control form-control-sm searchEdit<?= $counter ?>" placeholder="Cari Nama atau NIK..."></li>
                                        <li class="participant-list">
                                            <?php mysqli_data_seek($query_users, 0); if ($query_users && mysqli_num_rows($query_users) > 0): $selectedPeserta = array_map('trim', explode(',', $row['peserta'])); while($u = mysqli_fetch_assoc($query_users)): ?>
                                                <div class="participant-item">
                                                    <div class="form-check">
                                                        <input class="form-check-input peserta-edit-checkbox<?= $counter ?>" type="checkbox" value="<?= htmlspecialchars($u['fullname']) ?>" id="pesertaEdit<?= $counter ?>_<?= $u['id'] ?>" data-name="<?= htmlspecialchars($u['fullname']) ?>" data-nik="<?= htmlspecialchars($u['nik']) ?>" <?= in_array($u['fullname'], $selectedPeserta) ? 'checked' : '' ?>>
                                                        <label class="participant-label" for="pesertaEdit<?= $counter ?>_<?= $u['id'] ?>">
                                                            <div class="d-flex flex-column"><strong><?= htmlspecialchars($u['fullname']) ?></strong><small class="text-muted">NIK: <?= htmlspecialchars($u['nik']) ?></small></div>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endwhile; endif; ?>
                                        </li>
                                    </ul>
                                    <input type="hidden" name="peserta" id="inputEditHidden<?= $counter ?>" value="<?= htmlspecialchars($row['peserta']) ?>">
                                </div>
                                <small class="text-muted">Centang nama peserta di atas.</small>
                            </div>
                            
                            <!-- BAGIAN PPT BARU (TAB: FILE / URL) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">PPT / Slide</label>
                                <ul class="nav nav-tabs mb-2" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link <?= !$pptIsLink && !empty($row['ppt']) ? 'active' : '' ?>" id="upload-tab-ppt<?= $counter ?>" data-bs-toggle="tab" data-bs-target="#upload-pane-ppt<?= $counter ?>" type="button" role="tab">Upload File</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link <?= $pptIsLink ? 'active' : '' ?>" id="url-tab-ppt<?= $counter ?>" data-bs-toggle="tab" data-bs-target="#url-pane-ppt<?= $counter ?>" type="button" role="tab">Link URL</button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content">
                                    <!-- Tab Upload File -->
                                    <div class="tab-pane fade <?= !$pptIsLink && !empty($row['ppt']) ? 'show active' : '' ?>" id="upload-pane-ppt<?= $counter ?>" role="tabpanel">
                                        <input type="file" name="ppt_file" class="form-control" id="fileInputPpt<?= $counter ?>" accept=".pdf,.ppt,.pptx,.doc,.docx">
                                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah file.</small>
                                    </div>
                                    
                                    <!-- Tab URL -->
                                    <div class="tab-pane fade <?= $pptIsLink ? 'show active' : '' ?>" id="url-pane-ppt<?= $counter ?>" role="tabpanel">
                                        <input type="hidden" name="use_ppt_url" id="useUrlPpt<?= $counter ?>" value="<?= $pptIsLink ? '1' : '0' ?>">
                                        <input type="text" name="ppt_url" class="form-control" id="urlInputPpt<?= $counter ?>" placeholder="https://..." value="<?= $pptIsLink ? htmlspecialchars($row['ppt']) : '' ?>">
                                        <small class="text-muted">Masukkan URL lengkap presentasi.</small>
                                    </div>
                                </div>

                                <!-- Preview Area -->
                                <div class="mt-2">
                                    <label class="small text-muted">Preview Saat Ini:</label>
                                    <div class="ppt-preview-box" id="previewBoxPpt<?= $counter ?>">
                                        <?php if(!empty($row['ppt'])): ?>
                                            <?php if($pptIsLink): ?>
                                                <!-- Jika Link -->
                                                <div class="text-center">
                                                    <i class="bi bi-link-45deg file-icon"></i>
                                                    <div class="small text-break px-2 mt-1"><?= htmlspecialchars($row['ppt']) ?></div>
                                                </div>
                                            <?php else: ?>
                                                <!-- Jika File Upload -->
                                                <div class="text-center">
                                                    <i class="bi bi-file-earmark-ppt-fill file-icon"></i>
                                                    <div class="small text-break px-2 mt-1"><?= htmlspecialchars($row['ppt']) ?></div>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">Tidak ada file</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="hapusModal<?= $row['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="isi.php">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                        <input type="hidden" name="action" value="delete_meeting">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="project_id" value="<?= $project_id ?>">
                        <div class="modal-header"><h5 class="modal-title text-danger">Hapus Jadwal Rapat</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body"><p>Apakah Anda yakin ingin menghapus jadwal rapat ini?</p><hr><h6><?= htmlspecialchars($row['judul']) ?></h6><p class="text-muted mb-0"><?= date('l, d F Y', strtotime($row['tanggal'])) ?> - <?= $mulai ?> WIB</p></div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Ya, Hapus</button></div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        (function() {
            const counter = <?= $counter ?>;
            
            // Logic Peserta
            const searchInput = document.querySelector('.searchEdit' + counter);
            const checkboxes = document.querySelectorAll('.peserta-edit-checkbox' + counter);
            const hiddenInput = document.getElementById('inputEditHidden' + counter);
            const labelBtn = document.getElementById('selectedEditLabel' + counter);

            if (searchInput) {
                searchInput.addEventListener('keyup', function(e) {
                    const term = e.target.value.toLowerCase();
                    checkboxes.forEach(function(chk) {
                        const container = chk.closest('.participant-item');
                        const name = chk.getAttribute('data-name').toLowerCase();
                        const nik = chk.getAttribute('data-nik').toLowerCase();
                        if (name.includes(term) || nik.includes(term)) container.classList.remove('d-none'); else container.classList.add('d-none');
                    });
                });
            }

            function updatePesertaValue() {
                let selectedNames = [];
                checkboxes.forEach(function(chk) { if (chk.checked) selectedNames.push(chk.value); });
                hiddenInput.value = selectedNames.join(', ');
                if (selectedNames.length > 0) {
                    labelBtn.textContent = selectedNames.length <= 2 ? selectedNames.join(', ') : selectedNames.length + ' Orang Terpilih';
                    labelBtn.classList.add('text-dark');
                } else {
                    labelBtn.textContent = 'Pilih Peserta...';
                    labelBtn.classList.remove('text-dark');
                }
            }
            checkboxes.forEach(function(chk) { chk.addEventListener('change', updatePesertaValue); });
            updatePesertaValue();

            // Logic Room Availability
            const bookedData = <?php echo json_encode($meetings_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;
            const dateInput = document.getElementById('editTanggal' + counter);
            const timeInput = document.getElementById('editWaktu' + counter);
            const roomSelect = document.getElementById('editLokasi' + counter);
            const currentMeetingId = <?= $row['id'] ?>;
            const currentMeetingRoom = "<?= addslashes($row['lokasi']) ?>";
            const currentMeetingDate = "<?= $row['tanggal'] ?>";
            const currentMeetingTime = "<?= $row['waktu'] ?>";

            function isTimeOverlap(start1, end1, start2, end2) { return start1 < end2 && end1 > start2; }
            function addHours(timeStr, hours) {
                const [h, m] = timeStr.split(':').map(Number);
                const totalMinutes = h * 60 + m + (hours * 60);
                const newHours = Math.floor(totalMinutes / 60) % 24;
                const newMinutes = totalMinutes % 60;
                return `${String(newHours).padStart(2, '0')}:${String(newMinutes).padStart(2, '0')}`;
            }

            function updateRoomAvailability() {
                const selectedDate = dateInput.value;
                const selectedTime = timeInput.value;
                Array.from(roomSelect.options).forEach(opt => { if(opt.value !== "") { opt.disabled = false; opt.text = opt.text.replace(/ \(TERBOOKING\)$/, ''); }});
                if (!selectedDate || !selectedTime) return;
                const selectedEndTime = addHours(selectedTime, 3);
                const isDateChanged = selectedDate !== currentMeetingDate;
                const isTimeChanged = selectedTime !== currentMeetingTime;
                
                Array.from(roomSelect.options).forEach(opt => {
                    if(opt.value === "") return;
                    const optRoom = opt.value.trim().toLowerCase();
                    let roomIsBlocked = false;
                    bookedData.forEach(booking => {
                        if(!booking.tanggal || !booking.waktu || !booking.lokasi) return;
                        if(booking.tanggal === selectedDate && booking.lokasi.trim().toLowerCase() === optRoom) {
                            const bookingStartTime = booking.waktu.substring(0, 5);
                            const bookingEndTime = addHours(bookingStartTime, 3);
                            if(isTimeOverlap(selectedTime, selectedEndTime, bookingStartTime, bookingEndTime)) {
                                if (opt.value.trim().toLowerCase() === currentMeetingRoom.trim().toLowerCase() && !isDateChanged && !isTimeChanged) return;
                                else roomIsBlocked = true;
                            }
                        }
                    });
                    if(roomIsBlocked) { opt.disabled = true; opt.text += " (TERBOOKING)"; }
                });
                const currentlySelected = roomSelect.value;
                const selectedOption = Array.from(roomSelect.options).find(opt => opt.value === currentlySelected);
                if (selectedOption && selectedOption.disabled) {
                    roomSelect.value = "";
                    alert('Ruangan yang Anda pilih tidak tersedia pada tanggal dan waktu tersebut. Silakan pilih ruangan lain.');
                }
            }

            if (dateInput && timeInput && roomSelect) {
                dateInput.addEventListener('change', updateRoomAvailability);
                timeInput.addEventListener('change', updateRoomAvailability);
                document.getElementById('editModal<?= $row['id'] ?>').addEventListener('shown.bs.modal', updateRoomAvailability);
            }

            // --- LOGIC PPT (FILE / URL) ---
            const fileInputPpt = document.getElementById('fileInputPpt' + counter);
            const urlInputPpt = document.getElementById('urlInputPpt' + counter);
            const useUrlPpt = document.getElementById('useUrlPpt' + counter);
            const previewBoxPpt = document.getElementById('previewBoxPpt' + counter);
            const tabUpload = document.getElementById('upload-tab-ppt' + counter);
            const tabUrl = document.getElementById('url-tab-ppt' + counter);

            function updatePptPreview() {
                // Reset preview box content
                previewBoxPpt.innerHTML = ''; 

                if (useUrlPpt.value === '1' && urlInputPpt.value.trim() !== '') {
                    // Preview URL
                    const url = urlInputPpt.value.trim();
                    // Cek apakah URL berakhiran gambar untuk preview visual
                    if (url.match(/\.(jpeg|jpg|gif|png)$/) != null) {
                         previewBoxPpt.innerHTML = `<img src="${url}" onerror="this.style.display='none';this.nextElementSibling.style.display='block'"><div class="text-center" style="display:none"><i class="bi bi-link-45deg file-icon"></i><div class="small text-break px-2 mt-1">${url}</div></div>`;
                    } else {
                         previewBoxPpt.innerHTML = `<div class="text-center"><i class="bi bi-link-45deg file-icon"></i><div class="small text-break px-2 mt-1">${url}</div></div>`;
                    }
                } else if (fileInputPpt.files && fileInputPpt.files[0]) {
                    // Preview File
                    const fileName = fileInputPpt.files[0].name;
                    previewBoxPpt.innerHTML = `<div class="text-center"><i class="bi bi-file-earmark-arrow-up-fill file-icon"></i><div class="small text-break px-2 mt-1">${fileName}</div></div>`;
                } else {
                    // Default state (kita gunakan info PHP asli jika perlu, atau 'No file')
                    // Tapi untuk edit dinamis, kita tampilkan placeholder jika kosong
                     previewBoxPpt.innerHTML = `<span class="text-muted small">Preview akan muncul di sini</span>`;
                }
            }

            if(tabUpload) {
                tabUpload.addEventListener('shown.bs.tab', function () {
                    useUrlPpt.value = '0';
                    updatePptPreview();
                });
            }
            if(tabUrl) {
                tabUrl.addEventListener('shown.bs.tab', function () {
                    useUrlPpt.value = '1';
                    updatePptPreview();
                });
            }
            if(fileInputPpt) {
                fileInputPpt.addEventListener('change', updatePptPreview);
            }
            if(urlInputPpt) {
                urlInputPpt.addEventListener('input', updatePptPreview);
            }
            
            // Init preview saat modal dibuka untuk memastikan state sesuai
            document.getElementById('editModal<?= $row['id'] ?>').addEventListener('shown.bs.modal', function(){
                updatePptPreview();
            });

        })();
        </script>
        <?php endwhile; ?>
        <?php endif; ?>

    </div>
</main>
</div>

<footer class="footer-custom text-center py-3 border-top mt-auto">
    <div class="text-muted small">&copy; 2025 - Dashboard Pengelolaan Rapat</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>