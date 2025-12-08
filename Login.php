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
            max-width: 400px;
            margin: auto;
            margin-top: 100px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body style="background-image: url(https://www.polibatam.ac.id/wp-content/uploads/2023/05/Gedung.jpg);
background-size: cover; background-position: center; backdrop-filter: blur(10px);">

<div class="login-container">
    <h2 class="text-center">Login</h2>

    <!-- FORM LOGIN -->
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" name="username" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" name="login" class="btn btn-primary w-100">
            <i class="fas fa-sign-in-alt"></i> Login
        </button>
    </form>

    <p class="text-center mt-3">
        <a href="#" data-bs-toggle="modal" data-bs-target="#resetModal" class="link-primary">Forgot password?</a> |
        <a href="#" class="link-primary">Register</a>
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
