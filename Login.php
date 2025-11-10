<?php
session_start();
include "Koneksi.php";

// 🚫 Jika user sudah login, arahkan langsung ke halaman sesuai role
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'user') {
        header("Location: user/Dasboard_user.php");
        exit();
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: admin/Dasboard.php");
        exit();
    }
}

// 🧩 PROSES LOGIN
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role     = trim($_POST['role']);

    $stmt = $koneksi->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        // Cek role
        if (strtolower($role) !== strtolower($data['role'])) {
            echo "<script>alert('Anda tidak memiliki akses sebagai $role!'); window.location='Login.php';</script>";
            exit();
        }

        // Verifikasi password
        if (password_verify($password, $data['password'])) {
            $_SESSION['username'] = $data['username'];
            $_SESSION['role']     = $data['role'];

            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");

            if ($data['role'] === 'admin') {
                header("Location: admin/Dasboard.php");
            } else {
                header("Location: user/Dasboard_user.php");
            }
            exit();
        } else {
            echo "<script>alert('Password salah!'); window.location='Login.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Username tidak ditemukan!'); window.location='Login.php';</script>";
        exit();
    }
}

// 🧩 PROSES RESET PASSWORD
if (isset($_POST['reset'])) {
    $username       = trim($_POST['username']);
    $newpassword    = trim($_POST['newpassword']);
    $confirmpassword = trim($_POST['confirmpassword']);

    if ($newpassword !== $confirmpassword) {
        echo "<script>alert('Konfirmasi password tidak cocok!'); window.location='Login.php';</script>";
        exit();
    }

    // Cek apakah username ada di database
    $cek = $koneksi->prepare("SELECT * FROM users WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $hasil = $cek->get_result();

    if ($hasil->num_rows > 0) {
        $hashed = password_hash($newpassword, PASSWORD_DEFAULT);
        $update = $koneksi->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update->bind_param("ss", $hashed, $username);

        if ($update->execute()) {
            echo "<script>alert('Password berhasil direset! Silakan login dengan password baru.'); window.location='Login.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan saat mereset password.'); window.location='Login.php';</script>";
        }
    } else {
        echo "<script>alert('Username tidak ditemukan!'); window.location='Login.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow-lg p-5" style="width: 35rem; border-radius: 10px;"> 
        <h1 class="text-center mb-5 text-dark fw-bold">Login</h1>
        <form method="post" class="mt-3">
            <div class="mb-4">
                <label class="form-label fw-semibold fs-5">Username</label>
                <input type="text" name="username" class="form-control form-control-lg" placeholder="Masukkan username" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold fs-5">Password</label>
                <input type="password" name="password" class="form-control form-control-lg" placeholder="Masukkan password" required>
            </div>
            <div class="mb-5">
                <label class="form-label fw-semibold fs-5">Login Sebagai</label>
                <select name="role" class="form-select form-select-lg" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
            <div class="text-center mt-4">
                <button type="submit" name="login" class="btn btn-dark w-50 py-3 fs-5 rounded-3">Login</button>
            </div>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">
                <a href="#" data-bs-toggle="modal" data-bs-target="#forgotModal" class="text-decoration-none fw-semibold text-dark me-2">Lupa Password?</a> |
                <a href="Register.php" class="text-decoration-none fw-semibold text-dark ms-2">Register</a>
            </small>
        </div>
    </div>

    <!-- Modal Reset Password -->
    <div class="modal fade" id="forgotModal" tabindex="-1" aria-labelledby="forgotModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 550px; margin-top: 40px;">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                <div class="modal-header border-0 d-block text-center mt-3">
                    <h5 class="modal-title fw-bold fs-3 mb-2" id="forgotModalLabel">Reset Password</h5>
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body px-4 mt-0">
                    <form method="post">
                        <div class="mb-4">
                            <label class="form-label fw-semibold fs-5">Username</label>
                            <input type="text" name="username" class="form-control form-control-lg" placeholder="Masukkan username" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold fs-5">Password Baru</label>
                            <input type="password" name="newpassword" class="form-control form-control-lg" placeholder="Masukkan password baru" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold fs-5">Konfirmasi Password</label>
                            <input type="password" name="confirmpassword" class="form-control form-control-lg" placeholder="Konfirmasi password" required>
                        </div>
                        <div class="text-center mt-5">
                            <button type="submit" name="reset" class="btn btn-dark px-5 py-3 fs-5 rounded-3">Reset Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
