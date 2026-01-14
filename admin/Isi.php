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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'update_meeting') {
        check_csrf();

        $id         = (int)$_POST['id'];
        $project_id = (int)$_POST['project_id'];

        mysqli_query($koneksi, "
            UPDATE meetings SET
            judul   = '".mysqli_real_escape_string($koneksi, $_POST['judul'])."',
            tanggal = '".mysqli_real_escape_string($koneksi, $_POST['tanggal'])."',
            waktu   = '".mysqli_real_escape_string($koneksi, $_POST['waktu'])."',
            lokasi  = '".mysqli_real_escape_string($koneksi, $_POST['lokasi'])."',
            agenda  = '".mysqli_real_escape_string($koneksi, $_POST['agenda'])."',
            peserta = '".mysqli_real_escape_string($koneksi, $_POST['peserta'])."',
            ppt     = '".mysqli_real_escape_string($koneksi, $_POST['ppt'])."'
            WHERE id = $id
        ");

        header("Location: isi.php?project_id=$project_id");
        exit;
    }

    if ($action === 'delete_meeting') {
        check_csrf();

        $id         = (int)$_POST['id'];
        $project_id = (int)$_POST['project_id'];

        mysqli_query($koneksi, "DELETE FROM meetings WHERE id = $id");

        header("Location: isi.php?project_id=$project_id");
        exit;
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

$project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
if ($project_id <= 0) die("Project tidak valid");

$p = mysqli_query($koneksi, "SELECT * FROM projects WHERE id=$project_id");
$project = mysqli_fetch_assoc($p);
if (!$project) die("Project tidak ditemukan");

$q = mysqli_query($koneksi, "
    SELECT * FROM meetings 
    WHERE project_id=$project_id 
    ORDER BY tanggal DESC
");

$query_users = mysqli_query($koneksi, "SELECT * FROM daftar_peserta ORDER BY fullname ASC");

$q_meetings = mysqli_query($koneksi, "SELECT lokasi, tanggal, waktu FROM meetings");
$meetings_data = [];
while ($m = mysqli_fetch_assoc($q_meetings)) {
    $meetings_data[] = $m;
}

$q_rooms = mysqli_query($koneksi, "SELECT * FROM rooms_meeting ORDER BY room_name ASC");
$rooms_data = [];

$now = time();
while ($r = mysqli_fetch_assoc($q_rooms)) {
    $r['status'] = 'available';
    foreach ($meetings_data as $m) {
        if (
            $m['lokasi'] === $r['room_name'] &&
            strtotime($m['tanggal'].' '.$m['waktu']) >= $now
        ) {
            $r['status'] = 'booked';
            break;
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
                <p class="fs-5 text-muted">
                    Jadwal rapat untuk project ini masih kosong.
                </p>
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

        Demikian undangan ini kami sampaikan. Besar harapan kami Bapak/Ibu/Saudara
        dapat hadir tepat waktu. Atas perhatian dan kehadirannya kami ucapkan terima kasih.

        Hormat kami,
        Jurusan Teknik Informatika
        ";
        
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $link = $scheme.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        ?>

        <div class="card shadow-sm w-75 mt-4 mx-auto" style="max-width:800px;">
        <div class="card-body py-5">

            <h2 class="card-title text-center mb-5 fs-2">
                <?= htmlspecialchars($row['judul']) ?>
            </h2>

            <div class="container">
                <div class="row mb-2 fs-5">
                    <div class="col-3"><strong>Hari/Tanggal</strong></div>
                    <div class="col-1">:</div>
                    <div class="col-8"><?= date('l, d F Y',strtotime($row['tanggal'])) ?></div>
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
            </div>

            <div class="d-flex justify-content-end mt-4 align-items-center flex-wrap gap-2">

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

                <div class="d-flex gap-2 border-start ps-3">
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>

                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapusModal<?= $row['id'] ?>">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>

            </div>

        </div>
        </div>

        <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="isi.php">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                        <input type="hidden" name="action" value="update_meeting">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="project_id" value="<?= $project_id ?>">
                        
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Jadwal Rapat</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Judul</label>
                                <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($row['judul']) ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" name="tanggal" class="form-control" id="editTanggal<?= $counter ?>" value="<?= $row['tanggal'] ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Waktu</label>
                                    <input type="time" name="waktu" class="form-control" id="editWaktu<?= $counter ?>" value="<?= $row['waktu'] ?>" required>
                                </div>
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
                            
                            <div class="mb-3">
                                <label class="form-label">Agenda</label>
                                <textarea name="agenda" class="form-control" rows="3"><?= htmlspecialchars($row['agenda']) ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Daftar Peserta</label>
                                
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center" 
                                            type="button" 
                                            id="pesertaEditBtn<?= $counter ?>" 
                                            data-bs-toggle="dropdown" 
                                            data-bs-auto-close="outside" 
                                            aria-expanded="false">
                                        <span id="selectedEditLabel<?= $counter ?>">Pilih Peserta...</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                                        </svg>
                                    </button>
                                    
                                    <ul class="dropdown-menu participant-dropdown-menu" aria-labelledby="pesertaEditBtn<?= $counter ?>">
                                        <li class="p-2 border-bottom" onclick="event.stopPropagation()">
                                            <input type="text" class="form-control form-control-sm searchEdit<?= $counter ?>" placeholder="Cari Nama atau NIK...">
                                        </li>
                                        
                                        <li class="participant-list">
                                            <?php 
                                            mysqli_data_seek($query_users, 0);
                                            if ($query_users && mysqli_num_rows($query_users) > 0): 
                                                $selectedPeserta = array_map('trim', explode(',', $row['peserta']));
                                                while($u = mysqli_fetch_assoc($query_users)): 
                                                    $isChecked = in_array($u['fullname'], $selectedPeserta);
                                            ?>
                                                <div class="participant-item" onclick="event.stopPropagation()">
                                                    <div class="form-check">
                                                        <input class="form-check-input peserta-edit-checkbox<?= $counter ?>" 
                                                               type="checkbox" 
                                                               value="<?= htmlspecialchars($u['fullname']) ?>" 
                                                               id="pesertaEdit<?= $counter ?>_<?= $u['id'] ?>"
                                                               data-name="<?= htmlspecialchars($u['fullname']) ?>"
                                                               data-nik="<?= htmlspecialchars($u['nik']) ?>"
                                                               <?= $isChecked ? 'checked' : '' ?>>
                                                        
                                                        <label class="participant-label" for="pesertaEdit<?= $counter ?>_<?= $u['id'] ?>">
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
                                                    Tidak ada data peserta.
                                                </div>
                                            <?php endif; ?>
                                        </li>
                                    </ul>

                                    <input type="hidden" name="peserta" id="inputEditHidden<?= $counter ?>" value="<?= htmlspecialchars($row['peserta']) ?>">
                                </div>
                                <small class="text-muted">Centang nama peserta di atas.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">PPT / Slide (Link)</label>
                                <input type="text" name="ppt" class="form-control" placeholder="https://..." value="<?= htmlspecialchars($row['ppt']) ?>">
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
                        
                        <div class="modal-header">
                            <h5 class="modal-title text-danger">Hapus Jadwal Rapat</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p>Apakah Anda yakin ingin menghapus jadwal rapat ini?</p>
                            <hr>
                            <h6><?= htmlspecialchars($row['judul']) ?></h6>
                            <p class="text-muted mb-0"><?= date('l, d F Y', strtotime($row['tanggal'])) ?> - <?= $mulai ?> WIB</p>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
(function() {
    const counter = <?= $counter ?>;
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
                
                if (name.includes(term) || nik.includes(term)) {
                    container.classList.remove('d-none');
                } else {
                    container.classList.add('d-none');
                }
            });
        });
    }

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

    updatePesertaValue();

    const bookedData = <?php echo json_encode($meetings_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;
    const dateInput = document.getElementById('editTanggal' + counter);
    const timeInput = document.getElementById('editWaktu' + counter);
    const roomSelect = document.getElementById('editLokasi' + counter);
    const currentMeetingId = <?= $row['id'] ?>;
    const currentMeetingRoom = "<?= addslashes($row['lokasi']) ?>";
    const currentMeetingDate = "<?= $row['tanggal'] ?>";
    const currentMeetingTime = "<?= $row['waktu'] ?>";

    function isTimeOverlap(start1, end1, start2, end2) {
        return start1 < end2 && end1 > start2;
    }

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

        Array.from(roomSelect.options).forEach(opt => {
            if(opt.value === "") return;
            
            opt.disabled = false;
            opt.text = opt.text.replace(/ \(TERBOOKING\)$/, '');
        });

        if (!selectedDate || !selectedTime) {
            return;
        }

        const selectedEndTime = addHours(selectedTime, 3);

        const isDateChanged = selectedDate !== currentMeetingDate;
        const isTimeChanged = selectedTime !== currentMeetingTime;
        
        Array.from(roomSelect.options).forEach(opt => {
            if(opt.value === "") return;
            
            const optRoom = opt.value.trim().toLowerCase();
            let roomIsBlocked = false;

            bookedData.forEach(booking => {
                if(!booking.tanggal || !booking.waktu || !booking.lokasi) return;

                if(booking.tanggal === selectedDate && 
                   booking.lokasi.trim().toLowerCase() === optRoom) {
                    
                    const bookingStartTime = booking.waktu.substring(0, 5);
                    const bookingEndTime = addHours(bookingStartTime, 3);

                    if(isTimeOverlap(selectedTime, selectedEndTime, bookingStartTime, bookingEndTime)) {
                        if (opt.value.trim().toLowerCase() === currentMeetingRoom.trim().toLowerCase() 
                            && !isDateChanged 
                            && !isTimeChanged) {
                            return;
                        } else {
                            roomIsBlocked = true;
                        }
                    }
                }
            });

            if(roomIsBlocked) {
                opt.disabled = true;
                opt.text += " (TERBOOKING)";
            }
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
        
        const modal = document.getElementById('editModal<?= $row['id'] ?>');
        if (modal) {
            modal.addEventListener('shown.bs.modal', function() {
                updateRoomAvailability();
            });
        }
    }
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