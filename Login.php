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
<html lang="en"> 
<head> 
     <meta charset="utf-8"> 
     <meta name="viewport" content="width=device-width, initial-scale=1"> 
    <title>Login Page</title> 
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> 
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fontawesome/6.0.0/css/all.min.css"> 
 <style> 
    body { 
        background-color: #f8f9fa; 
 } 
     .login-container { 
        max-width: 400px; 
        margin: auto; 
        margin-top: 100px; 
        padding: 20px; 
        background: white; 
        border-radius: 8px; 
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
 } 
    .login-container h2 { 
        margin-bottom: 20px; 
 } 
 </style> 
</head> 
<body
style="background-image: url(https://www.polibatam.ac.id/wp-content/uploads/2023/05/Gedung.jpg);
background-size: cover; background-position: center;
backdrop-filter: blur(10px); z-index: -1;"> 
    <div class="login-container"> 
    <h2 class="text-center"> Login</h2> 
 <form> 
    <div class="mb-3"> 
    <label for="username" class="form-label">Username</label> 
    <div class="input-group"> 
    <span class="group-text"><i class="fas fa-user"></i></span> 
    <input type="text" class="form-control" id="username" placeholder="Enter username" required> 
 </div> 
 </div> 
 <div class="mb-3"> 
 <label for="password" class="form-label">Password</label> 
 <div class="input-group"> 
 <span class="group-text"><i class="fas fa-lock"></i></span> 
 <input type="password" class="form-control" id="password" placeholder="Enter password" required> 
</div> 
</div> 
<button type="submit" class="btn btn-primary w-100"><i class="fas fa-signinalt"></i> Login</button> 
</form> 
<p class="text-center mt-3"> 
<a href="https://share.google/1PUQ5biu7GLcH43Qf" class="link-primary">Forgot password?</a> 
<a href="https://share.google/gfFXJ0rilrsJTmJOp" class="linkprimary">Register</a></p> 
</div> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> 
</body> 
</html> 