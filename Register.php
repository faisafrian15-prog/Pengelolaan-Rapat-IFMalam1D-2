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
<html lang="en"> 
<head> 
     <meta charset="utf-8"> 
     <meta name="viewport" content="width=device-width, initial-scale=1"> 
    <title>Register Page</title> 
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> 
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fontawesome/6.0.0/css/all.min.css"> 
 <style> 
    body { 
        background-color: #f8f9fa; 
 } 
     .register-container { 
        max-width: 400px; 
        margin: auto; 
        margin-top: 100px; 
        padding: 20px; 
        background: white; 
        border-radius: 8px; 
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
 } 
    .register-container h2 { 
        margin-bottom: 20px; 
 } 
 </style> 
</head> 
<body
style="background-image: url(https://www.polibatam.ac.id/wp-content/uploads/2023/05/Gedung.jpg);
background-size: cover; background-position: center;
backdrop-filter: blur(10px); z-index: -1;"> 
    <div class="Register-container"> 
    <h2 class="text-center"> Register</h2> 
 <form> 
    <div class="mb-3"> 
    <label for="username" class="form-label">E-mail</label> 
    <div class="input-group"> 
    <span class="group-text"><i class="fas fa-user"></i></span> 
    <input type="text" class="form-control" id="E-mail" placeholder="Enter E-mail" required> 
 </div> 
 </div> 
  <div class="mb-3"> 
    <label for="username" class="form-label">Username</label> 
    <div class="input-group"> 
    <span class="group-text"><i class="fas fa-user"></i></span> 
    <input type="text" class="form-control" id="username" placeholder="Enter Username" required> 
 </div> 
 </div> 
 <div class="mb-3"> 
 <label for="password" class="form-label">Password</label> 
 <div class="input-group"> 
 <span class="group-text"><i class="fas fa-lock"></i></span> 
 <input type="password" class="form-control" id="password" placeholder="Enter password" required> 
</div> 
</div> 
<div class="mb-3"> 
 <label for="password" class="form-label">Confirm Password</label> 
 <div class="input-group"> 
 <span class="group-text"><i class="fas fa-lock"></i></span> 
 <input type="password" class="form-control" id="Confirm password" placeholder="Enter your password again" required> 
</div> 
</div> 
<button type="submit" class="btn btn-primary w-100"><i class="fas fa-signinalt"></i> Register</button> 
</form> 
</div> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> 
</body> 
</html> 