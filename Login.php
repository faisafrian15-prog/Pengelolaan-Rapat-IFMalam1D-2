<?php
session_start();
include "Koneksi.php";

// 🚫 Jika user sudah login, arahkan langsung ke dashboard
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

        if (strtolower($role) !== strtolower($data['role'])) {
            echo "<script>alert('Anda tidak memiliki akses sebagai $role!'); window.location='Login.php';</script>";
            exit();
        }

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

    $cek = $koneksi->prepare("SELECT * FROM users WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $hasil = $cek->get_result();

    if ($hasil->num_rows > 0) {
        $hashed = password_hash($newpassword, PASSWORD_DEFAULT);
        $update = $koneksi->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update->bind_param("ss", $hashed, $username);

        if ($update->execute()) {
            echo "<script>alert('Password berhasil direset! Silakan login.'); window.location='Login.php';</script>";
        } else {
            echo "<script>alert('Kesalahan saat mereset password.'); window.location='Login.php';</script>";
        }
    } else {
        echo "<script>alert('Username tidak ditemukan!'); window.location='Login.php';</script>";
    }
}
?>
<html lang="en">
<head>
    <meta charset="utf-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1"> 
    <title>Login Page</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fontawesome/6.0.0/css/all.min.css">

    <style>
        .login-container {
            max-width: 550px;
            margin: auto;
            margin-top: 50px;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div style="
        position: fixed;
        inset: 0;
        background-image: url('https://www.polibatam.ac.id/wp-content/uploads/2023/05/Gedung.jpg');
        background-size: cover;
        background-position: center;
        filter: blur(5px);
        overflow: hidden;
        transform: scale(1.1);
        z-index: -1;
    "></div>

<div class="login-container">
    <img src="assets/poltek.jpeg" class="img-fluid mb-3 d-block mx-auto" style="width:170px;" alt="Logo">
    <h2 class="text-center mb-4 text-dark fw-bold">Login</h2>

    <!-- FORM LOGIN -->
    <form method="post" class="mt-3">
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold fs-6">Username</label>
                <input type="text" class="form-control" id="username" placeholder="Enter username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold fs-6">Password</label>
                <input type="password" class="form-control" id="password" placeholder="Enter password" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold fs-7">Login Sebagai</label>
                <select name="role" class="form-select form-select-md" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>

            <div class="text-center">
                <button type="submit" class="mt-3 btn btn-dark w-50 py-3 fs-5">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </div>
        </form>


    <p class="text-center mt-3">
        <a href="#" data-bs-toggle="modal" data-bs-target="#resetModal" class="link-primary">Forgot password?</a> |
        <a href="Register.php" class="link-primary">Register</a>
    </p>
</div>

<!-- ====================== -->
<!--       POPUP RESET      -->
<!-- ====================== -->
<div class="modal fade" id="resetModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="newpassword" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="confirmpassword" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" name="reset" class="btn btn-primary w-100">
                        <i class="fas fa-sync-alt"></i> Reset Password
                    </button>

                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
