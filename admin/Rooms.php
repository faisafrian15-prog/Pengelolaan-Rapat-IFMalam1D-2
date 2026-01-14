<?php
include "../Koneksi.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $search = isset($_GET['query']) ? trim($_GET['query']) : '';

    $meetings = [];
    $resMeetings = mysqli_query($koneksi, "SELECT lokasi, tanggal, waktu FROM meetings");
    while ($row = mysqli_fetch_assoc($resMeetings)) {
        $meetings[] = $row;
    }

    function uploadRoomImage($fileInput, $urlInput = '', $oldFile = 'default-room.jpg') {
        $photoName = $oldFile;

        if (!empty($urlInput)) {
            $photoName = trim($urlInput);
        } elseif (!empty($fileInput['name']) && $fileInput['error'] === 0) {
            $allowedExts = ['jpg','jpeg','png','gif'];
            $fileExt = strtolower(pathinfo($fileInput['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, $allowedExts) && $fileInput['size'] <= 5000000) {
                $newFileName = uniqid('room_') . '.' . $fileExt;
                if (move_uploaded_file($fileInput['tmp_name'], "../assets/$newFileName")) {
                    if ($oldFile !== 'default-room.jpg' && !preg_match('/^https?:\/\//', $oldFile)) {
                        @unlink("../assets/$oldFile");
                    }
                    $photoName = $newFileName;
                }
            }
        }
        return $photoName;
    }

if (isset($_POST['add_room'])) {
    $name = trim($_POST['room_name']);
    $capacity = (int)$_POST['capacity'];
    $useUrl = isset($_POST['use_url']) && $_POST['use_url'] === '1';

    if ($useUrl && isset($_POST['room_img_url'])) {
        $photoName = trim($_POST['room_img_url']);
    } elseif (isset($_FILES['room_img_file'])) {
        $photoName = uploadRoomImage($_FILES['room_img_file']);
    } else {
        $photoName = 'default-room.jpg';
    }

    $photoPath = '../assets/';
    $status = 'available';

    $stmt = mysqli_prepare($koneksi, "INSERT INTO rooms_meeting (room_name, capacity, status, photo_name, photo_path) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) throw new Exception("Prepare failed: " . mysqli_error($koneksi));

    mysqli_stmt_bind_param($stmt, "sisss", $name, $capacity, $status, $photoName, $photoPath);
    if (!mysqli_stmt_execute($stmt)) throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);

    header("Location: Rooms.php");
    exit;
}

if (isset($_POST['edit_room'])) {
    $id = (int)$_POST['room_id'];
    $name = trim($_POST['room_name']);
    $capacity = (int)$_POST['capacity'];
    $useUrl = isset($_POST['use_url_edit']) && $_POST['use_url_edit'] === '1';

    $oldResult = mysqli_query($koneksi, "SELECT photo_name FROM rooms_meeting WHERE id=$id");
    $old = mysqli_fetch_assoc($oldResult);
    $oldPhoto = isset($old['photo_name']) ? $old['photo_name'] : 'default-room.jpg';

    if ($useUrl && isset($_POST['room_img_url_edit'])) {
        $photoName = trim($_POST['room_img_url_edit']);
    } elseif (isset($_FILES['room_img_file_edit'])) {
        $photoName = uploadRoomImage($_FILES['room_img_file_edit'], '', $oldPhoto);
    } else {
        $photoName = $oldPhoto;
    }

    $stmt = mysqli_prepare($koneksi, "UPDATE rooms_meeting SET room_name=?, capacity=?, photo_name=? WHERE id=?");
    if (!$stmt) throw new Exception("Prepare failed: " . mysqli_error($koneksi));

    mysqli_stmt_bind_param($stmt, "sisi", $name, $capacity, $photoName, $id);
    if (!mysqli_stmt_execute($stmt)) throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);

    header("Location: Rooms.php");
    exit;
}

    if (isset($_POST['delete_room'])) {
        $id = (int)$_POST['room_id'];

        $old = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT photo_name FROM rooms_meeting WHERE id=$id"));
        if ($old && $old['photo_name'] !== 'default-room.jpg' && !preg_match('/^https?:\/\//', $old['photo_name'])) {
            @unlink("../assets/".$old['photo_name']);
        }

        $stmt = mysqli_prepare($koneksi, "DELETE FROM rooms_meeting WHERE id=?");
        if (!$stmt) throw new Exception("Prepare failed: " . mysqli_error($koneksi));

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: Rooms.php");
        exit;
    }

    if ($search !== '') {
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM rooms_meeting WHERE room_name LIKE ? ORDER BY id DESC");
        if (!$stmt) throw new Exception("Prepare failed: " . mysqli_error($koneksi));

        $like = "%$search%";
        mysqli_stmt_bind_param($stmt, "s", $like);
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM rooms_meeting ORDER BY id DESC");
        if (!$stmt) throw new Exception("Prepare failed: " . mysqli_error($koneksi));
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rooms = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    $currentTime = time();
    foreach ($rooms as &$room) {
        $isBooked = false;
        foreach ($meetings as $meeting) {
            if ($meeting['lokasi'] === $room['room_name']) {
                $meetingTime = strtotime($meeting['tanggal'].' '.$meeting['waktu']);
                if ($meetingTime && $meetingTime >= $currentTime) {
                    $isBooked = true;
                    break;
                }
            }
        }

        $newStatus = $isBooked ? 'booked' : 'available';
        if ($room['status'] !== $newStatus) {
            $u = mysqli_prepare($koneksi, "UPDATE rooms_meeting SET status=? WHERE id=?");
            mysqli_stmt_bind_param($u, "si", $newStatus, $room['id']);
            mysqli_stmt_execute($u);
            mysqli_stmt_close($u);
        }
        $room['status'] = $newStatus;
    }
    unset($room);

    $current_page = basename($_SERVER['PHP_SELF']);

} catch (Exception $e) {
    echo "<div style='color:red; padding:10px; border:1px solid #f00;'>Terjadi error: " . htmlspecialchars($e->getMessage()) . "</div>";
    error_log($e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ruangan Rapat</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.navbar { background-color: #c3c7ceff !important; }
#sidebarToggle { 
    background-color: #7a8ca0 !important; 
    width:250px; 
    flex-shrink:0; 
    overflow:hidden;
    transition:width 0.3s ease;
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
.card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.25) !important; transform: translateY(-4px); transition:0.3s; }
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

<main class="container my-4 d-flex flex-column flex-grow-1">

    <form class="w-100 text-center mt-4 mb-5 d-flex justify-content-center" method="get" action="Rooms.php" style="position:relative;">
    <button type="button" class="btn btn-success btn-lg me-3" data-bs-toggle="modal" data-bs-target="#createModal">
        + Tambah
    </button>

    <div class="input-group w-50">
    <input type="text" name="query" class="form-control form-control-lg" placeholder="Cari ruangan..." value="<?= htmlspecialchars($search) ?>">
    <?php if ($search !== ''): ?>
        <span class="input-group-text" style="cursor:pointer;" onclick="window.location='Rooms.php'">&times;</span>
    <?php endif; ?>
    <button class="btn btn-primary btn-lg" type="submit">Search</button>
</div>
</form>

    <?php if (count($rooms) === 0): ?>
    <div class="col-12 text-center">
        <p class="fs-4 text-muted">Tidak ada ruangan.</p>
    </div>
<?php else: ?>
<div class="row g-4 justify-content-center">
<?php foreach ($rooms as $room): ?>
<div class="col-6 col-md-3">
    <div class="card h-100">
      <?php
$isUrl = preg_match('/^https?:\/\//', $room['photo_name']);
$imgSrc = $isUrl ? $room['photo_name'] : $room['photo_path'].$room['photo_name'];
?>
        <img src="<?= htmlspecialchars($imgSrc) ?>" class="card-img-top" style="height:180px;object-fit:cover;">

        <div class="card-body text-center">
            <h6 class="fw-bold"><?= htmlspecialchars($room['room_name']) ?></h6>
            <p class="mb-1">Kapasitas: <?= $room['capacity'] ?> orang</p>

            <?php if ($room['status'] === 'available'): ?>
                <span class="badge bg-success">Tersedia</span>
            <?php else: ?>
                <span class="badge bg-danger">Terbooking</span>
            <?php endif; ?>

            <div class="d-flex justify-content-center gap-2 mt-3">
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $room['id'] ?>">Edit</button>
                <form method="POST" action="Rooms.php">
                    <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                    <button name="delete_room" class="btn btn-danger btn-sm" onclick="return confirm('Hapus ruangan ini?')">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal<?= $room['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="Rooms.php" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Edit Ruangan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        
        <div class="modal-body">
          <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
          
          <div class="mb-3">
            <label class="form-label fw-bold">Nama Ruangan</label>
            <input type="text" name="room_name" class="form-control" required value="<?= htmlspecialchars($room['room_name']) ?>">
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Kapasitas</label>
            <input type="number" name="capacity" class="form-control" min="1" required value="<?= $room['capacity'] ?>">
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Status Ruangan (Otomatis)</label>
            <div>
              <?php if ($room['status'] === 'available'): ?>
                <span class="badge bg-success fs-6">Tersedia</span>
              <?php else: ?>
                <span class="badge bg-danger fs-6">Terbooking</span>
              <?php endif; ?>
              <small class="d-block text-muted mt-1" style="font-size: 0.8rem;">Status berdasarkan jadwal rapat yang ada.</small>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Foto Ruangan</label>
            <ul class="nav nav-tabs mb-3" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="upload-tab-edit<?= $room['id'] ?>" data-bs-toggle="tab" data-bs-target="#upload-pane-edit<?= $room['id'] ?>" type="button" role="tab">Upload dari Galeri</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="url-tab-edit<?= $room['id'] ?>" data-bs-toggle="tab" data-bs-target="#url-pane-edit<?= $room['id'] ?>" type="button" role="tab">Dari URL</button>
              </li>
            </ul>
            
            <div class="tab-content">
              <div class="tab-pane fade show active" id="upload-pane-edit<?= $room['id'] ?>" role="tabpanel">
                <input type="file" name="room_img_file_edit" class="form-control" id="fileInputEdit<?= $room['id'] ?>" accept="image/*">
                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto</small>
              </div>
              
              <div class="tab-pane fade" id="url-pane-edit<?= $room['id'] ?>" role="tabpanel">
                <input type="hidden" name="use_url_edit" id="useUrlEdit<?= $room['id'] ?>" value="0">
                <input type="text" name="room_img_url_edit" class="form-control" id="urlInputEdit<?= $room['id'] ?>" placeholder="https://example.com/image.jpg" value="<?= preg_match('/^https?:\/\//', $room['photo_name']) ? htmlspecialchars($room['photo_name']) : '' ?>">
                <small class="text-muted">Masukkan URL gambar lengkap</small>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Preview</label>
            <div class="text-center border rounded p-3" style="min-height: 200px;">
              <img id="previewEdit<?= $room['id'] ?>" src="<?= htmlspecialchars($imgSrc) ?>" class="img-fluid rounded" style="max-height: 180px; object-fit: cover;">
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="edit_room" class="btn btn-success">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="Rooms.php" enctype="multipart/form-data">
        <div class="modal-header text-dark">
          <h5 class="modal-title">Tambah Ruangan Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Nama Ruangan</label>
            <input type="text" name="room_name" class="form-control" placeholder="Masukkan nama ruangan" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Kapasitas</label>
            <input type="number" name="capacity" class="form-control" min="1" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold text-muted">Status</label>
            <input type="text" class="form-control" value="Otomatis: Tersedia" disabled>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Foto Ruangan</label>
            <ul class="nav nav-tabs mb-3" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-pane" type="button" role="tab">Upload dari Galeri</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="url-tab" data-bs-toggle="tab" data-bs-target="#url-pane" type="button" role="tab">Dari URL</button>
              </li>
            </ul>
            
            <div class="tab-content">
              <div class="tab-pane fade show active" id="upload-pane" role="tabpanel">
                <input type="file" name="room_img_file" class="form-control" id="fileInputAdd" accept="image/*">
                <small class="text-muted">Format: JPG, JPEG, PNG, GIF (Max 5MB)</small>
              </div>
              
              <div class="tab-pane fade" id="url-pane" role="tabpanel">
                <input type="hidden" name="use_url" id="useUrlAdd" value="0">
                <input type="text" name="room_img_url" class="form-control" id="urlInputAdd" placeholder="https://example.com/image.jpg">
                <small class="text-muted">Masukkan URL gambar lengkap</small>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Preview</label>
            <div class="text-center border rounded p-3" style="min-height: 200px;">
              <img id="previewAdd" src="../assets/default-room.jpg" class="img-fluid rounded" style="max-height: 180px; object-fit: cover;">
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="add_room" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

</main> 
</div> 

<footer class="footer-custom text-center py-3 border-top mt-auto">
    <div class="text-muted small">&copy; 2025 - Admin Pengelolaan Rapat</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('fileInputAdd')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('previewAdd').src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('urlInputAdd')?.addEventListener('input', function(e) {
    const url = e.target.value.trim();
    if (url) {
        document.getElementById('previewAdd').src = url;
    }
});

document.getElementById('url-tab')?.addEventListener('click', function() {
    document.getElementById('useUrlAdd').value = '1';
});

document.getElementById('upload-tab')?.addEventListener('click', function() {
    document.getElementById('useUrlAdd').value = '0';
});

document.querySelectorAll('[id^="fileInputEdit"]').forEach(input => {
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const roomId = this.id.replace('fileInputEdit', '');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('previewEdit' + roomId).src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});

document.querySelectorAll('[id^="urlInputEdit"]').forEach(input => {
    input.addEventListener('input', function(e) {
        const url = e.target.value.trim();
        const roomId = this.id.replace('urlInputEdit', '');
        if (url) {
            document.getElementById('previewEdit' + roomId).src = url;
        }
    });
});

document.querySelectorAll('[id^="url-tab-edit"]').forEach(tab => {
    tab.addEventListener('click', function() {
        const roomId = this.id.replace('url-tab-edit', '');
        document.getElementById('useUrlEdit' + roomId).value = '1';
    });
});

document.querySelectorAll('[id^="upload-tab-edit"]').forEach(tab => {
    tab.addEventListener('click', function() {
        const roomId = this.id.replace('upload-tab-edit', '');
        document.getElementById('useUrlEdit' + roomId).value = '0';
    });
});
</script>
</body>
</html>