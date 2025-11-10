<?php
session_start();
include "koneksi.php"; // koneksi ke database

// 🚫 Jika user sudah login, arahkan ke dashboard sesuai rolenya
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'user') {
        header("Location: user/Dasboard_user.php");
        exit();
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: admin/Dasboard.php");
        exit();
    }
}

// 🔒 Tambahkan header untuk mencegah halaman ini disimpan di cache browser
header("Cache-Control: no-cache, no-store, must-revalidate"); // untuk HTTP 1.1
header("Pragma: no-cache"); // untuk HTTP 1.0
header("Expires: 0"); // untuk proxy

// 🧩 Proses registrasi
if (isset($_POST['register'])) {
    $email    = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];
    $role     = $_POST['role']; // admin atau user

    if ($password !== $confirm) {
        echo "<script>alert('Password dan konfirmasi password tidak sama!'); window.location='Register.php';</script>";
        exit();
    }

    // Cek apakah username sudah digunakan
    $cek = $koneksi->prepare("SELECT username FROM users WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Username sudah digunakan!'); window.location='Register.php';</script>";
        exit();
    }

    // Hash password dan simpan ke database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = $koneksi->prepare("INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, ?)");
    $query->bind_param("ssss", $email, $username, $hashedPassword, $role);

    if ($query->execute()) {
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='Login.php';</script>";
        exit();
    } else {
        echo "<script>alert('Gagal menyimpan ke database!'); window.location='Register.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow-lg border-0 rounded-4" style="width: 36rem; height: 780px;">
        <div class="card-body p-5">
            <h2 class="text-center mb-4 fw-bold text-dark">Form Registrasi</h2>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label fw-semibold">E-mail</label>
                    <input type="email" name="email" class="form-control py-3" placeholder="Masukkan e-mail" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control py-3" placeholder="Masukkan username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control py-3" placeholder="Masukkan password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Konfirmasi Password</label>
                    <input type="password" name="confirm" class="form-control py-3" placeholder="Ulangi password" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Pilih Role</label>
                    <select name="role" class="form-select py-3" required>
                        <option value="" disabled selected>-- Pilih Role --</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" name="register" class="btn btn-dark w-75 py-3 fs-5 rounded-4">Register</button>
                </div>
            </form>
            <div class="text-center mt-4 fs-6">
                <a href="Login.php" class="text-decoration-none fw-semibold text-dark">Sudah punya akun? Login</a>
            </div>
        </div>
    </div>

</body>
</html>
