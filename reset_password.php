<?php

$isSecure = false;
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $isSecure = true;
}

session_set_cookie_params(0, '/', '', $isSecure, true);

session_start();

include "Koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method not allowed");
}

if (empty($_SESSION['otp_valid'])) {
    exit("Sesi OTP kadaluarsa. Silakan verifikasi ulang.");
}

if (empty($_SESSION['reset_user'])) {
    exit("User tidak ditemukan dalam sesi.");
}

$post_token = isset($_POST['csrf']) ? trim($_POST['csrf']) : '';
$session_token = isset($_SESSION['csrf_token']) ? trim($_SESSION['csrf_token']) : '';

if (empty($post_token)) {
    exit("CSRF token tidak ditemukan di form");
}

if (empty($session_token)) {
    exit("Session token tidak ditemukan. Silakan refresh halaman.");
}

if (!hash_equals($session_token, $post_token)) {
    exit("Invalid CSRF token - Token mismatch");
}

$new     = isset($_POST['newpassword']) ? trim($_POST['newpassword']) : '';
$confirm = isset($_POST['confirmpassword']) ? trim($_POST['confirmpassword']) : '';

if (empty($new) || empty($confirm)) {
    exit("Password tidak boleh kosong!");
}

if ($new !== $confirm) {
    exit("Konfirmasi password tidak cocok!");
}

if (strlen($new) < 6) {
    exit("Password minimal 6 karakter!");
}

if (!preg_match('/[A-Z]/', $new)) {
    exit("Password harus mengandung minimal 1 huruf besar (A-Z)");
}

if (!preg_match('/\d/', $new)) {
    exit("Password harus mengandung minimal 1 angka (0-9)");
}

if (!preg_match('/[\W_]/', $new)) {
    exit("Password harus mengandung minimal 1 simbol (!@#$%^&*_- dll)");
}

$hashed = password_hash($new, PASSWORD_DEFAULT);

$stmt = $koneksi->prepare(
    "UPDATE users SET password = ? WHERE username = ? LIMIT 1"
);

if ($stmt === false) {
    error_log("MySQL Error: " . $koneksi->error);
    exit("Terjadi kesalahan sistem. Silakan coba lagi.");
}

$stmt->bind_param("ss", $hashed, $_SESSION['reset_user']);

if ($stmt->execute()) {
    
    if ($stmt->affected_rows > 0) {
        
        unset(
            $_SESSION['otp'],
            $_SESSION['otp_valid'],
            $_SESSION['reset_user']
        );

        session_regenerate_id(true);

        echo "Password berhasil direset!";
        
    } else {
        error_log("Update gagal: User tidak ditemukan atau password sama");
        echo "User tidak ditemukan atau password tidak berubah.";
    }
    
} else {
    error_log("Execute Error: " . $stmt->error);
    echo "Gagal mereset password. Silakan coba lagi.";
}

$stmt->close();
$koneksi->close();
?>