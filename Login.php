<?php
$isSecure = false;
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $isSecure = true;
}

session_set_cookie_params(0, '/', '', $isSecure, true);
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

include "Koneksi.php";

function isCsrfValid($token) {
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}

function handleLoginAttempt() {
    if (!isset($_SESSION['login_attempt'])) {
        $_SESSION['login_attempt'] = 1;
    } else {
        $_SESSION['login_attempt']++;
    }

    if ($_SESSION['login_attempt'] > 5) {
        throw new Exception("Terlalu banyak percobaan login. Coba lagi nanti.");
    }
}

function resetLoginAttempt() {
    unset($_SESSION['login_attempt'], $_SESSION['login_time']);
}

if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/Dasboard.php");
        exit();
    }
    if ($_SESSION['role'] === 'user') {
        header("Location: user/Dasboard_user.php");
        exit();
    }
}

if (!isset($_SESSION['login_time'])) {
    $_SESSION['login_time'] = time();
}
if (time() - $_SESSION['login_time'] > 900) {
    resetLoginAttempt();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    if (!isCsrfValid($_POST['csrf'])) {
        die("Invalid CSRF token");
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        handleLoginAttempt();

        $stmt = $koneksi->prepare(
            "SELECT username, password, role FROM users WHERE username = ? LIMIT 1"
        );

        if (!$stmt) {
            throw new Exception("Prepare statement gagal");
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $data = $res->fetch_assoc();

            if (password_verify($password, $data['password'])) {

                session_regenerate_id(true);
                unset($_SESSION['csrf_token']);
                resetLoginAttempt();

                $_SESSION['username'] = $data['username'];
                $_SESSION['role']     = $data['role'];

                switch ($data['role']) {
                    case "admin":
                        header("Location: admin/Dasboard.php");
                        break;
                    case "user":
                        header("Location: user/Dasboard_user.php");
                        break;
                }
                exit();

            } else {
                sleep(2);
                $error = "Password salah!";
            }
        } else {
            $error = "Username atau password salah!";
        }

    } catch (Exception $e) {
        error_log($e->getMessage());
        $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <title>Form Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            width: 100%;
            overflow-x: hidden;
        }
        
        .main-container {
            min-height: 100vh; 
            min-height: 100dvh; 
            position: relative;
            z-index: 1;
        }

        .modal-dialog {
            max-width: 90%; 
            margin: 1rem auto; 
        }
        @media (min-width: 576px) {
            .modal-dialog {
                max-width: 500px; 
            }
        }
    </style>
</head>
<body class="bg-light">

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

<div class="container-fluid main-container d-flex justify-content-center align-items-center px-2 px-sm-3 px-md-4 py-3 py-md-5">

    <div class="card shadow-lg w-100 p-3 p-sm-4 p-md-5 rounded-3" style="max-width: 35rem;">
        
        <img src="assets/P.png" class="img-fluid mb-4 d-block mx-auto" style="max-width: 170px; width: 100%; height: auto;" alt="Logo">
        
        <h1 class="text-center mb-3 mb-md-4 mb-lg-5 text-dark fw-bold fs-3 fs-md-2 fs-lg-1">Login</h1>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger text-center mb-3 mb-md-4"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" class="mt-2 mt-md-3">

            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="mb-3 mb-md-4">
                <label class="form-label fw-semibold fs-6 fs-md-5">Username</label>
                <input type="text" name="username"  autocomplete="username" class="form-control form-control-lg" placeholder="Masukkan username" required>
            </div>

            <div class="mb-3 mb-md-4">
                <label class="form-label fw-semibold fs-6 fs-md-5">Password</label>
                <input type="password" name="password" autocomplete="current-password" class="form-control form-control-lg" placeholder="Masukkan password" required>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <button type="submit" name="login" class="btn btn-dark btn-lg w-100 py-2 py-md-3">
                        Login
                    </button>
                </div>
            </div>
        </form>

        <div class="text-center mt-3 mt-md-4">
            <small class="text-muted">
                <a href="#" id="btnForgot" class="text-decoration-none fw-semibold text-dark">Lupa Password?</a>
            </small>
        </div>
    </div>

</div>

    <div class="modal fade" id="modalEmail" tabindex="-1" aria-labelledby="modalEmailLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mx-2 mx-sm-auto">
            <div class="modal-content p-3 p-md-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title w-100 text-center fw-bold fs-5 fs-md-4" id="modalEmailLabel">Verifikasi Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body pt-3">
                    <form id="formSendOtp">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-6">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-6">Email Terdaftar</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-2 mt-2">Kirim OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalOtp" tabindex="-1" aria-labelledby="modalOtpLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mx-2 mx-sm-auto">
            <div class="modal-content p-3 p-md-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title w-100 text-center fw-bold fs-5 fs-md-4" id="modalOtpLabel">Masukkan Kode OTP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body pt-3">
                    <form id="formVerifyOtp">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-6">Kode OTP</label>
                            <input type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" class="form-control" name="otp" required>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-2 mt-2">Verifikasi OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="modalReset" tabindex="-1" aria-labelledby="modalResetLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-2 mx-sm-auto">
        <div class="modal-content p-3 p-md-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title w-100 text-center fw-bold fs-5 fs-md-4" id="modalResetLabel">Reset Password Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body pt-3" style="max-height:70vh; overflow-y:auto;">
                <form id="formResetPassword">
                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-6">Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" name="newpassword" required minlength="6">
                            <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" id="eyeIconNew" viewBox="0 0 16 16">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                </svg>
                            </button>
                        </div>
                        <small class="text-muted">Minimal 6 karakter (huruf besar, angka, simbol)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-6">Konfirmasi Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" name="confirmpassword" required minlength="6">
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" id="eyeIconConfirm" viewBox="0 0 16 16">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dark w-100 py-2 mt-2">Simpan Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
var modalEmail = new bootstrap.Modal(document.getElementById('modalEmail'));
var modalOtp   = new bootstrap.Modal(document.getElementById('modalOtp'));
var modalReset = new bootstrap.Modal(document.getElementById('modalReset'));

document.getElementById("btnForgot").addEventListener("click", function (e) {
    e.preventDefault();
    modalEmail.show();
});

document.getElementById("formSendOtp").addEventListener("submit", function(e) {
    e.preventDefault();

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Sedang Mengirim...
    `;

    fetch("send_otp.php", {
        method: "POST",
        body: new FormData(this)
    })
    .then(response => response.text())
    .then(res => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;

        if (res === "OTP_SENT") {
            modalEmail.hide();
            modalOtp.show();
            alert("Kode OTP telah dikirim ke email Anda!");
        } else {
            alert(res);
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        
        alert("Terjadi kesalahan. Silakan coba lagi.");
        console.error(err);
    });
});

document.getElementById("formVerifyOtp").addEventListener("submit", function(e) {
    e.preventDefault();

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Memverifikasi...
    `;

    fetch("verify_otp.php", {
        method: "POST",
        body: new FormData(this)
    })
    .then(r => r.text())
    .then(r => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;

        if (r.trim() === "OK") {
            modalOtp.hide();
            setTimeout(function() {
                modalReset.show();
            }, 300);
        } else {
            alert("OTP salah atau sudah kadaluarsa!");
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        
        alert("Terjadi kesalahan. Silakan coba lagi.");
        console.error(err);
    });
});

document.getElementById("formResetPassword").addEventListener("submit", function(e) {
    e.preventDefault();

    const newPass = document.querySelector('[name="newpassword"]').value;
    const confirmPass = document.querySelector('[name="confirmpassword"]').value;

    if (newPass !== confirmPass) {
        alert("Password tidak cocok!");
        return;
    }

    if (newPass.length < 6) {
        alert("Password minimal 6 karakter!");
        return;
    }

    const hasUpperCase = /[A-Z]/.test(newPass);
    const hasNumber = /\d/.test(newPass);
    const hasSymbol = /[\W_]/.test(newPass);

    if (!hasUpperCase || !hasNumber || !hasSymbol) {
        alert("Password harus mengandung:\n- Minimal 6 karakter\n- Minimal 1 huruf besar (A-Z)\n- Minimal 1 angka (0-9)\n- Minimal 1 simbol (!@#$%^&* dll)");
        return;
    }

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Menyimpan...
    `;

    fetch("reset_password.php", {
        method: "POST",
        body: new FormData(this)
    })
    .then(r => r.text())
    .then(r => {
        console.log("Server response:", r); 
        
        if (r.includes("berhasil")) {
            submitBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle me-2" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                </svg>
                Berhasil!
            `;
            
            alert("Password berhasil direset! Anda akan diarahkan ke halaman login.");
            modalReset.hide();
            setTimeout(() => {
                window.location.href = "Login.php";
            }, 500);
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            alert(r);
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        
        alert("Terjadi kesalahan. Silakan coba lagi.");
        console.error(err);
    });
});

document.getElementById("toggleNewPassword").addEventListener("click", function() {
    const passwordInput = document.getElementById("newPassword");
    const eyeIcon = document.getElementById("eyeIconNew");
    
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.innerHTML = `
            <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/>
            <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/>
            <path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/>
        `;
    } else {
        passwordInput.type = "password";
        eyeIcon.innerHTML = `
            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
        `;
    }
});

document.getElementById("toggleConfirmPassword").addEventListener("click", function() {
    const passwordInput = document.getElementById("confirmPassword");
    const eyeIcon = document.getElementById("eyeIconConfirm");
    
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.innerHTML = `
            <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/>
            <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/>
            <path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/>
        `;
    } else {
        passwordInput.type = "password";
        eyeIcon.innerHTML = `
            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
        `;
    }
});
</script>

</body>
</html>